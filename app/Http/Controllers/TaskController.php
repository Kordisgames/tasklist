<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Services\TelegramService;
use Carbon\Carbon;

class TaskController extends Controller
{
    // Получение списка задач с фильтрами
    public function index(Request $request)
    {
        $query = Task::where('user_id', Auth::id());

        if ($request->has('status')) {
            $query->where('is_completed', $request->status);
        }

        if ($request->has('title')) {
            $query->where('title', 'like', "%{$request->title}%");
        }

        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        return response()->json($query->get());
    }

    // Создание новой задачи
    public function store(Request $request, TelegramService $telegram, GoogleSheetsService $sheets)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'boolean',
        ]);

        $task = Task::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date ?? Carbon::now(),
            'is_completed' => $request->is_completed ?? false,
        ]);

        // Отправляем уведомление в Telegram
        $message = "📝 <b>Новая задача</b>\n"
        . "📌 <b>Название:</b> {$task->title}\n"
        . "📅 <b>Дата выполнения:</b> {$task->due_date}\n"
        . "✏ <b>Описание:</b> " . ($task->description ?: 'Нет');

        $telegram->sendMessage($message);
        $sheets->addTask($task);

        return response()->json(['message' => 'Задача создана', 'task' => $task]);
    }

    // Обновление задачи
    public function update(Request $request, Task $task, GoogleSheetsService $sheets)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Нет доступа'], 403);
        }

        $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'boolean',
        ]);

        $task->update($request->all());
        $sheets->updateTask($task);

        return response()->json(['message' => 'Задача обновлена', 'task' => $task]);
    }

    // Удаление задачи
    public function destroy(Task $task, GoogleSheetsService $sheets)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Нет доступа'], 403);
        }

        $sheets->deleteTask($task->id);
        $task->delete();

        return response()->json(['message' => 'Задача удалена']);
    }
}
