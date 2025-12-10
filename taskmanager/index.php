<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY due_date ASC, id ASC");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Task Manager</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <!-- Theme Toggle Button -->
  <button id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode">🌓</button>

  <div class="container">
    <header>
      <h1>Task Manager</h1>
      <div id="currentWeekdayTime" class="weekday-time"></div>
      <div id="currentFullDate" class="full-date"></div>
      <p>Stay organized, Stay on top of Life</p>
    </header>

    <div class="search-container">
      <input type="text" id="taskSearch" placeholder="Search your tasks (title or category)…" />
    </div>

    <!-- Task Stats Counter -->
    <div id="taskStats" class="task-stats"></div>

    <form id="taskForm" class="task-form">
      <div class="form-group">
        <input type="text" id="title" placeholder="Task title (e.g., Dentist appointment)" required />
        <select id="category">
          <option value="personal">Personal</option>
          <option value="work">Work</option>
          <option value="birthday">Birthday</option>
          <option value="anniversary">Anniversary</option>
          <option value="meeting">Meeting</option>
          <option value="health">Health</option>
        </select>
        <input type="date" id="due_date" required />
      </div>
      <button type="submit">➕ Add Task</button>
    </form>

    <ul id="taskList" class="task-list">
      <?php foreach ($tasks as $task): ?>
        <li class="task-item" data-id="<?= $task['id'] ?>">
          <div class="task-icon"><?= getIcon($task['category']) ?></div>
          <div class="task-content">
            <div class="task-title <?= $task['completed'] ? 'completed' : '' ?>">
              <?= htmlspecialchars($task['title']) ?>
            </div>
            <div class="task-meta">
              <span><?= ucfirst($task['category']) ?></span>
              <?php if ($task['due_date']): 
                $due = new DateTime($task['due_date']);
                $today = new DateTime('today');
                $dateStr = $due->format('M j');
                if ($due < $today): ?>
                  <span class="due-date overdue"><?= $dateStr ?> <span class="date-badge overdue">Overdue</span></span>
                <?php elseif ($due == $today): ?>
                  <span class="due-date today"><?= $dateStr ?> <span class="date-badge today">Today</span></span>
                <?php else: ?>
                  <span class="due-date"><?= $dateStr ?></span>
                <?php endif;
              else: ?>
                <span>No date</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="task-actions">
            <button class="action-btn complete-btn" onclick="toggleComplete(<?= $task['id'] ?>, <?= $task['completed'] ?>)">
              <?= $task['completed'] ? '✓' : '○' ?>
            </button>
            <button class="action-btn delete-btn" onclick="deleteTask(<?= $task['id'] ?>)">×</button>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <div id="empty-message">✨ No tasks yet. Add your first one!</div>
  </div>

  <script>
    // === Theme Toggle ===
    (function() {
      const toggle = document.getElementById('themeToggle');
      const html = document.documentElement;
      const saved = localStorage.getItem('theme');
      const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      const theme = saved || (systemDark ? 'dark' : 'light');
      if (theme === 'dark') html.setAttribute('data-theme', 'dark');
      toggle?.addEventListener('click', () => {
        const isDark = html.getAttribute('data-theme') === 'dark';
        html.setAttribute('data-theme', isDark ? 'light' : 'dark');
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
      });
    })();

    // === App Logic ===
    function getCategoryIcon(category) {
      const icons = {
        'birthday': '🎂', 'anniversary': '💍', 'meeting': '👥',
        'health': '🩺', 'work': '💼', 'personal': '📌'
      };
      return icons[category] || '✅';
    }

    document.getElementById('taskForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const title = document.getElementById('title').value;
      const category = document.getElementById('category').value;
      const due_date = document.getElementById('due_date').value;

      const res = await fetch('api/add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, category, due_date })
      });

      if (res.ok) {
        loadTasks();
        document.getElementById('taskForm').reset();
        document.getElementById('due_date').valueAsDate = new Date();
      }
    });

    async function toggleComplete(id, current) {
      const newStatus = current ? 0 : 1;
      const res = await fetch('api/toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, completed: newStatus })
      });
      if (res.ok) loadTasks();
    }

    async function deleteTask(id) {
      if (!confirm('Delete this task?')) return;
      const res = await fetch('api/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      if (res.ok) loadTasks();
    }

    async function loadTasks() {
      const res = await fetch('api/fetch.php');
      const tasks = await res.json();
      const list = document.getElementById('taskList');
      const emptyMsg = document.getElementById('empty-message');
      const statsEl = document.getElementById('taskStats');

      list.innerHTML = '';
      if (tasks.length === 0) {
        emptyMsg.style.display = 'block';
        emptyMsg.textContent = '✨ No tasks yet. Add your first one!';
        if (statsEl) statsEl.textContent = '0 tasks';
        return;
      }

      emptyMsg.style.display = 'none';
      const total = tasks.length;
      const completed = tasks.filter(t => t.completed).length;
      const now = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

      if (statsEl) {
        statsEl.textContent = `${total} task${total !== 1 ? 's' : ''} • ${completed} completed`;
      }

      tasks.forEach(task => {
        const li = document.createElement('li');
        li.className = 'task-item';
        li.dataset.id = task.id;

        let dateDisplay = 'No date';
        let dateClass = '';
        let badge = '';

        if (task.due_date) {
          const due = new Date(task.due_date);
          const dueDay = new Date(due.getFullYear(), due.getMonth(), due.getDate());
          dateDisplay = due.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

          if (dueDay < today) {
            dateClass = 'overdue';
            badge = '<span class="date-badge overdue">Overdue</span>';
          } else if (dueDay.getTime() === today.getTime()) {
            dateClass = 'today';
            badge = '<span class="date-badge today">Today</span>';
          }
        }

        li.innerHTML = `
          <div class="task-icon">${getCategoryIcon(task.category)}</div>
          <div class="task-content">
            <div class="task-title ${task.completed ? 'completed' : ''}">${task.title}</div>
            <div class="task-meta">
              <span>${task.category.charAt(0).toUpperCase() + task.category.slice(1)}</span>
              <span class="due-date ${dateClass}">${dateDisplay} ${badge}</span>
            </div>
          </div>
          <div class="task-actions">
            <button class="action-btn complete-btn" onclick="toggleComplete(${task.id}, ${task.completed})">
              ${task.completed ? '✓' : '○'}
            </button>
            <button class="action-btn delete-btn" onclick="deleteTask(${task.id})">×</button>
          </div>
        `;
        list.appendChild(li);
      });

      const query = document.getElementById('taskSearch')?.value || '';
      filterTasks(query);
    }

    function filterTasks(query) {
      const items = document.querySelectorAll('.task-item');
      const lower = query.toLowerCase().trim();
      let visible = 0;

      items.forEach(item => {
        const title = item.querySelector('.task-title')?.textContent.toLowerCase() || '';
        const category = item.querySelector('.task-meta span:first-child')?.textContent.toLowerCase() || '';
        const show = title.includes(lower) || category.includes(lower);
        item.style.display = show ? '' : 'none';
        if (show) visible++;
      });

      const emptyMsg = document.getElementById('empty-message');
      if (query && visible === 0) {
        emptyMsg.textContent = '📭 No tasks match your search.';
        emptyMsg.style.display = 'block';
      } else if (!query && visible === 0) {
        emptyMsg.textContent = '✨ No tasks yet. Add your first one!';
        emptyMsg.style.display = 'block';
      } else {
        emptyMsg.style.display = 'none';
      }
    }

    function updateDateTime() {
      const now = new Date();
      const weekday = now.toLocaleDateString('en-US', { weekday: 'long' }).toUpperCase();
      const hours24 = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      document.getElementById('currentWeekdayTime').textContent = `${weekday} ${hours24}:${minutes}`;
      const day = String(now.getDate()).padStart(2, '0');
      const month = now.toLocaleDateString('en-US', { month: 'long' });
      const year = now.getFullYear();
      document.getElementById('currentFullDate').textContent = `${day} ${month} : ${year}`;
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);

    document.addEventListener('DOMContentLoaded', () => {
      const search = document.getElementById('taskSearch');
      if (search) {
        search.addEventListener('input', (e) => filterTasks(e.target.value));
      }
    });

    document.getElementById('due_date').valueAsDate = new Date();
    loadTasks();
  </script>

  <?php
  function getIcon($category) {
      $icons = [
          'birthday' => '🎂',
          'anniversary' => '💍',
          'meeting' => '👥',
          'health' => '🩺',
          'work' => '💼',
          'personal' => '📌'
      ];
      return $icons[$category] ?? '✅';
  }
  ?>
</body>
</html>