<?php

class DatabaseSeeder
{

  // Hash password securely
  private static function hash(string $p): string
  {
    return password_hash($p, PASSWORD_DEFAULT);
  }

  // Seed initial data into the database
  public static function seed(PDO $pdo): void
  {
    // USERS
    $u = $pdo->prepare("
      INSERT INTO users (name,email,password,level,is_team_lead,is_approved)
      VALUES (?,?,?,?,?,?)
    ");

    $u->execute(['Admin User', 'admin@example.com', self::hash('admin123'), 'Admin', 0, 1]);
    $u->execute(['Team Lead Senior 1', 'lead1@example.com', self::hash('lead123'), 'Senior', 1, 1]);
    $u->execute(['Regular Senior 1', 'senior1@example.com', self::hash('senior123'), 'Senior', 0, 1]);
    $u->execute(['Mid Developer 1', 'mid1@example.com', self::hash('mid123'), 'Mid', 0, 1]);
    $u->execute(['Junior Developer 1', 'junior1@example.com', self::hash('junior123'), 'Junior', 0, 1]);
    $u->execute(['Team Lead Senior 2', 'lead2@example.com', self::hash('lead456'), 'Senior', 1, 1]);
    $u->execute(['Regular Senior 2', 'senior2@example.com', self::hash('senior456'), 'Senior', 0, 1]);

    // PROJECTS
    $p = $pdo->prepare("
      INSERT INTO projects (title,description,requirements,estimated_time,deadline,team_lead_id,status)
      VALUES (?,?,?,?,?,?,?)
    ");

    $p->execute(['Project Management System', 'Internal tool to handle tasks, teams, and comments.', 'PHP OOP, MySQL, JS, jQuery', '2 months', '2024-12-31', 2, 'Active']);
    $p->execute(['E-Commerce Shop', 'Full online shop with cart, orders, and admin panel.', 'PHP MVC, JS, MySQL, Bootstrap', '3 months', '2026-02-15', 2, 'Active']);
    $p->execute(['Learning Platform', 'Video courses, quizzes, progress tracking.', 'PHP, REST API, Vanilla JS', '4 months', '2026-03-10', 2, 'Active']);
    $p->execute(['Mobile App Development', 'Build a cross-platform mobile app for task management.', 'React Native, Firebase, REST API', '5 months', '2024-06-30', 6, 'Done']);
    $p->execute(['Website Redesign', 'Redesign company website with modern UI/UX.', 'HTML/CSS, JavaScript, Bootstrap', '6 months', '2026-06-15', 2, 'Active']);

    // PROJECT USERS
    $pu = $pdo->prepare("INSERT INTO project_users VALUES (?,?)");

    foreach ([1, 2, 3] as $pid) {
      foreach ([2, 3, 4, 5] as $uid) {
        $pu->execute([$pid, $uid]);
      }
    }

    foreach ([[4, 6], [4, 3], [4, 4], [4, 5], [4, 7], [5, 2], [5, 3], [5, 4], [5, 5], [5, 6]] as $r) {
      $pu->execute($r);
    }

    // TASKS
    $t = $pdo->prepare("
      INSERT INTO tasks (project_id,title,description,created_by,status,assigned_to)
      VALUES (?,?,?,?,?,?)
    ");

    $tasks = [
      [1, 'Fix login bug', 'Users sometimes stay stuck in loop.', 2, 'In Progress', 3],
      [1, 'Improve dashboard UI', 'Add charts and stats.', 3, 'To Do', 4],
      [1, 'Create task modal', 'Modal must load via AJAX.', 2, 'QA', 5],
      [2, 'Implement cart system', 'Add-to-cart and cart API.', 3, 'In Progress', 3],
      [2, 'Product filtering', 'Filter by category and price.', 2, 'To Do', 4],
      [2, 'Orders page', 'Admin order management.', 3, 'To Do', null],
      [3, 'Course list page', 'Display courses with search.', 2, 'Done', 3],
      [3, 'Video player', 'Add speed controls and chapters.', 3, 'In Progress', 4],
      [3, 'Quiz system', 'Multiple-choice quiz engine.', 2, 'To Do', 5],
      [4, 'Implement login screen', 'Add secure authentication.', 6, 'Done', 3],
      [4, 'Add push notifications', 'Real-time updates.', 7, 'Done', 4],
      [4, 'Optimize performance', 'Reduce load times.', 6, 'Done', 5],
      [5, 'Update homepage layout', 'Modern responsive design.', 2, 'In Progress', 3],
      [5, 'Add blog section', 'CMS integration.', 3, 'QA', 4],
      [5, 'SEO optimization', 'Meta tags and sitemap.', 2, 'To Do', 5]
    ];

    foreach ($tasks as $task) {
      $t->execute($task);
    }

    // COMMENTS
    $c = $pdo->prepare("INSERT INTO comments (task_id,user_id,content) VALUES (?,?,?)");

    $comments = [
      [1, 2, 'Please provide more details about the login loop.'],
      [1, 3, 'Found that issue only happens on Safari.'],
      [2, 4, 'Started working on the chart layout.'],
      [3, 5, 'Modal loads but animations are missing.'],
      [4, 3, 'I implemented the cart logic. Review needed.'],
      [6, 2, 'Orders page must include pagination.'],
      [7, 4, 'Course list looks great!'],
      [9, 5, 'Quiz system design is ready.'],
      [10, 6, 'Login screen implemented, testing complete.'],
      [11, 7, 'Push notifications working on iOS and Android.'],
      [12, 6, 'Performance optimized, load times reduced by 40%.'],
      [13, 2, 'Homepage layout updated, review pending.'],
      [14, 3, 'Blog section integrated with WordPress CMS.'],
      [15, 2, 'SEO tags added, sitemap generated.']
    ];

    foreach ($comments as $comment) {
      $c->execute($comment);
    }
  }
}
