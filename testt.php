<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestionnaire de Tâches</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="task-manager">
        <h1>Gestionnaire de Tâches</h1>
        <input type="text" id="new-task" placeholder="Ajouter une nouvelle tâche...">
        <button id="add-task">Ajouter</button>
        <div id="tasks-list">
            <!-- Les tâches ajoutées apparaîtront ici -->
        </div>
    </div>
    <script>
        $(document).ready(function() {
            // Votre code JavaScript ici
            $('#add-task').click(function() {
                var newTaskText = $('#new-task').val().trim();
                if(newTaskText !== '') {
                    $('#tasks-list').append('<div class="task">' + newTaskText + '<button class="delete-task">X</button></div>');
                    $('#new-task').val('');
                    saveTasks();
                }
            });

            $('#tasks-list').on('dblclick', '.task', function() {
                $(this).toggleClass('completed');
                saveTasks();
            });

            $('#tasks-list').on('click', '.delete-task', function() {
                $(this).parent().remove();
                saveTasks();
            });

            function loadTasks() {
                var tasks = JSON.parse(localStorage.getItem('tasks')) || [];
                tasks.forEach(function(task) {
                    $('#tasks-list').append('<div class="task' + (task.completed ? ' completed' : '') + '">' + task.text + '<button class="delete-task">X</button></div>');
                });
            }

            function saveTasks() {
                var tasks = [];
                $('.task').each(function() {
                    var taskText = $(this).text().slice(0, -1);
                    var taskCompleted = $(this).hasClass('completed');
                    tasks.push({ text: taskText, completed: taskCompleted });
                });
                localStorage.setItem('tasks', JSON.stringify(tasks));
            }

            loadTasks();
        });
    </script>
</body>
</html>
