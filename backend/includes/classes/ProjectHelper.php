<?php
class ProjectHelper
{
  public static function getDaysRemaining($deadline)
  {
    if (!$deadline) return null;
    $deadlineDate = strtotime($deadline);
    $today = strtotime(date('Y-m-d'));
    $diff = $deadlineDate - $today;
    return floor($diff / (60 * 60 * 24));
  }

  public static function isOverdue($deadline)
  {
    if (!$deadline) return false;
    return self::getDaysRemaining($deadline) < 0;
  }

  // Helper function to get project status info
  public static function getStatusInfo($project)
  {
    $status = $project->getStatus();
    $deadline = $project->getDeadline();

    if ($status === 'Done') {
      return [
        'class' => 'border-primary',
        'badge' => 'COMPLETED',
        'badgeClass' => 'bg-primary',
        'textClass' => 'text-primary'
      ];
    }

    $daysRemaining = self::getDaysRemaining($deadline);

    if ($daysRemaining === null) {
      return [
        'class' => 'border-secondary',
        'badge' => 'ACTIVE',
        'badgeClass' => 'bg-secondary',
        'textClass' => 'text-secondary',
        'daysText' => 'No deadline set'
      ];
    }

    if ($daysRemaining < 0) {
      return [
        'class' => 'border-danger',
        'badge' => 'OVERDUE',
        'badgeClass' => 'bg-danger',
        'textClass' => 'text-danger',
        'daysText' => abs($daysRemaining) . ' days overdue'
      ];
    }

    if ($daysRemaining <= 7) {
      return [
        'class' => 'border-warning',
        'badge' => 'DUE SOON',
        'badgeClass' => 'bg-warning text-dark',
        'textClass' => 'text-warning',
        'daysText' => $daysRemaining . ' days left'
      ];
    }

    return [
      'class' => 'border-success',
      'badge' => 'ON TRACK',
      'badgeClass' => 'bg-success',
      'textClass' => 'text-success',
      'daysText' => $daysRemaining . ' days left'
    ];
  }

  // Project View (slightly different keys/icons)
  public static function getViewStatusInfo($project)
  {
    $status = $project->getStatus();
    $deadline = $project->getDeadline();

    if ($status === 'Done') {
      return [
        'headerClass' => 'bg-primary text-white',
        'cardClass' => 'border-primary',
        'badge' => '✓ Project Completed',
        'badgeClass' => 'bg-primary',
        'textClass' => 'text-primary',
        'icon' => '✓',
        'message' => 'This project has been completed'
      ];
    }

    $daysRemaining = self::getDaysRemaining($deadline);

    if ($daysRemaining === null) {
      return [
        'headerClass' => 'bg-secondary text-white',
        'cardClass' => 'border-secondary',
        'badge' => '🔄 Active Project',
        'badgeClass' => 'bg-secondary',
        'textClass' => 'text-secondary',
        'icon' => '🔄',
        'message' => 'No deadline set'
      ];
    }

    if ($daysRemaining < 0) {
      return [
        'headerClass' => 'bg-danger text-white',
        'cardClass' => 'border-danger',
        'badge' => '⚠️ Project Overdue',
        'badgeClass' => 'bg-danger',
        'textClass' => 'text-danger',
        'icon' => '⚠️',
        'message' => abs($daysRemaining) . ' days overdue'
      ];
    }

    if ($daysRemaining <= 7) {
      return [
        'headerClass' => 'bg-warning',
        'cardClass' => 'border-warning',
        'badge' => '⏰ Due Soon',
        'badgeClass' => 'bg-warning text-dark',
        'textClass' => 'text-warning',
        'icon' => '⏰',
        'message' => 'Only ' . $daysRemaining . ' days remaining'
      ];
    }

    return [
      'headerClass' => 'bg-success text-white',
      'cardClass' => 'border-success',
      'badge' => '✓ On Track',
      'badgeClass' => 'bg-success',
      'textClass' => 'text-success',
      'icon' => '✓',
      'message' => $daysRemaining . ' days remaining'
    ];
  }
}
