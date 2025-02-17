<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-4">
                <div>
                    <h1 class="text-2xl font-bold">Инфомация по задачам</h1>

                    <p class="mb-4">Выполненные задачи: <span id="completed-count">0</span>/<span
                            id="total-count">0</span></p>
                </div>

                <div>
                    <!-- Task Title -->
                    <div>
                        <x-input-label for="task_title" :value="__('Название задачи')" />
                        <x-text-input id="task_title" class="block mt-1 w-full" type="text" name="task_title"
                            :value="old('task_title')" required autofocus autocomplete="task_title" />
                        <x-input-error :messages="$errors->get('task_title')" class="mt-2" />
                    </div>

                    <!-- Task Description -->
                    <div class="mt-4">
                        <x-input-label for="task_description" :value="__('Описание задачи')" />
                        <x-textarea id="task_description" class="block mt-1 w-full" name="task_description"
                            :value="old('task_description')" required></x-textarea>
                        <x-input-error :messages="$errors->get('task_description')" class="mt-2" />
                    </div>

                    <!-- Due Date -->
                    <div class="mt-4">
                        <x-input-label for="due_date" :value="__('Дата окончания задачи')" />
                        <x-text-input id="due_date" class="block mt-1 w-full" type="datetime-local" name="due_date"
                            :value="old('due_date')" required />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    </div>

                    <!-- Add Button -->
                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button class="ms-4" onclick="addTask()">
                            {{ __('Добавить задачу') }}
                        </x-primary-button>
                    </div>
                </div>

            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="w-full">
                    <h2 class="text-xl font-bold mb-4">Список задач</h2>
                    <div id="tasks" class="flex flex-col gap-y-2"></div>
                </div>
            </div>
        </div>

        <script>
            let token =
            "3|5jNkKWUulDPRwPWQTcScnznRuMZbkJZuFh7y7oHV0c96fc4d"; // В реальном приложении токен должен подгружаться через ajax динамически

            // Настройка для отправки cookies с запросами
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                xhrFields: {
                    withCredentials: true // Необходимо для работы с куки
                }
            });


            function loadTasks() {
                $.ajax({
                    url: "/api/v1/tasks",
                    headers: {
                        "Authorization": "Bearer " + token
                    },
                    success: function(tasks) {
                        $("#tasks").empty();
                        let completedCount = 0;
                        let totalCount = tasks.length;

                        tasks.forEach(task => {
                            let taskHtml = `
                    <div class="flex justify-between task" data-id="${task.id}">
                        <span class="${task.is_completed ? 'completed' : ''}">${task.title} (до ${task.due_date})</span>
                        <div>
                            <x-primary-button class="ms-4" onclick="toggleTask(${task.id}, ${task.is_completed})">
                                ${task.is_completed ? 'Отменить' : 'Выполнить'}
                            </x-primary-button>
                            <x-danger-button class="ms-4" onclick="deleteTask(${task.id})">
                                {{ __('Удалить задачу') }}
                            </x-danger-button>
                        </div>
                    </div>
                `;
                            $("#tasks").append(taskHtml);
                            if (task.is_completed) completedCount++;
                        });

                        $("#completed-count").text(completedCount);
                        $("#total-count").text(totalCount);
                    }
                });
            }

            function addTask() {
                let title = $("#task_title").val();
                let description = $("#task_description").val();
                let dueDate = $("#due_date").val();

                $.ajax({
                    url: "/api/v1/tasks",
                    method: "POST",
                    headers: {
                        "Authorization": "Bearer " + token
                    },
                    contentType: "application/json",
                    data: JSON.stringify({
                        title: title,
                        description: description,
                        due_date: dueDate
                    }),
                    success: function() {
                        loadTasks();
                    }
                });
            }

            function toggleTask(id, isCompleted) {
                $.ajax({
                    url: `/api/v1/tasks/${id}`,
                    method: "PUT",
                    headers: {
                        "Authorization": "Bearer " + token
                    },
                    contentType: "application/json",
                    data: JSON.stringify({
                        is_completed: !isCompleted
                    }),
                    success: function() {
                        loadTasks();
                    }
                });
            }

            function deleteTask(id) {
                $.ajax({
                    url: `/api/v1/tasks/${id}`,
                    method: "DELETE",
                    headers: {
                        "Authorization": "Bearer " + token
                    },
                    success: function() {
                        loadTasks();
                    }
                });
            }

            $("#add-task").click(addTask);
            loadTasks();
        </script>
</x-app-layout>
