import { changeTaskStatus } from "./api.js";
// Initialize Kanban board with SortableJS
export function initKanban() {
  const kanbanColumns = document.querySelectorAll(".kanban-cards");
  if (kanbanColumns.length > 0) {
    kanbanColumns.forEach((column) => {
      new Sortable(column, {
        group: "kanban",
        animation: 150,
        ghostClass: "sortable-ghost",
        onEnd: function (evt) {
          const itemEl = evt.item;
          const toColumn = evt.to;

          const taskId = itemEl.dataset.taskId;
          const newStatus = toColumn.parentElement.dataset.status;

          changeTaskStatus(taskId, newStatus);
        },
      });
    });
  }
}
