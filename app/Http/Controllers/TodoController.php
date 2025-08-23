<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of todos for authenticated user.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Todo::forUser($user->id);

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'completed') {
                $query->completed();
            } elseif ($request->status === 'pending') {
                $query->pending();
            }
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Sort by due_date or created_at
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $todos = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $todos
        ]);
    }

    /**
     * Store a newly created todo.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $todo = Todo::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Todo created successfully',
            'data' => $todo
        ], 201);
    }

    /**
     * Display the specified todo.
     */
    public function show($id)
    {
        $todo = Todo::forUser(auth()->id())->find($id);

        if (!$todo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Todo not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $todo
        ]);
    }

    /**
     * Update the specified todo.
     */
    public function update(Request $request, $id)
    {
        $todo = Todo::forUser(auth()->id())->find($id);

        if (!$todo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Todo not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'sometimes|boolean',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $todo->update($request->only(['title', 'description', 'is_completed', 'due_date']));

        return response()->json([
            'status' => 'success',
            'message' => 'Todo updated successfully',
            'data' => $todo->fresh()
        ]);
    }

    /**
     * Remove the specified todo.
     */
    public function destroy($id)
    {
        $todo = Todo::forUser(auth()->id())->find($id);

        if (!$todo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Todo not found'
            ], 404);
        }

        $todo->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Todo deleted successfully'
        ]);
    }

    /**
     * Mark todo as completed.
     */
    public function markCompleted($id)
    {
        $todo = Todo::forUser(auth()->id())->find($id);

        if (!$todo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Todo not found'
            ], 404);
        }

        $todo->update(['is_completed' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Todo marked as completed',
            'data' => $todo->fresh()
        ]);
    }

    /**
     * Mark todo as pending.
     */
    public function markPending($id)
    {
        $todo = Todo::forUser(auth()->id())->find($id);

        if (!$todo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Todo not found'
            ], 404);
        }

        $todo->update(['is_completed' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Todo marked as pending',
            'data' => $todo->fresh()
        ]);
    }

    /**
     * Get user statistics.
     */
    public function stats()
    {
        $user = auth()->user();
        
        $totalTodos = Todo::forUser($user->id)->count();
        $completedTodos = Todo::forUser($user->id)->completed()->count();
        $pendingTodos = Todo::forUser($user->id)->pending()->count();
        $overdueTodos = Todo::forUser($user->id)
            ->pending()
            ->where('due_date', '<', now())
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_todos' => $totalTodos,
                'completed_todos' => $completedTodos,
                'pending_todos' => $pendingTodos,
                'overdue_todos' => $overdueTodos,
                'completion_rate' => $totalTodos > 0 ? round(($completedTodos / $totalTodos) * 100, 2) : 0
            ]
        ]);
    }
}
