<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Todo Management
 * 
 * APIs for managing user todos with CRUD operations, filtering, search, and status management
 */
class TodoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get User Todos
     * 
     * Retrieve a paginated list of todos for the authenticated user with optional filtering and search.
     * 
     * @authenticated
     * 
     * @queryParam status string Filter todos by status. Allowed values: completed, pending
     * @queryParam search string Search todos by title
     * @queryParam sort_by string Sort todos by field. Allowed values: created_at, due_date, title. Default: created_at
     * @queryParam sort_order string Sort order. Allowed values: asc, desc. Default: desc
     * @queryParam per_page integer Number of todos per page. Default: 15
     * 
     * @response 200 scenario="success" {
     *  "data": [
     *    {
     *      "id": 1,
     *      "title": "Complete project documentation",
     *      "description": "Write comprehensive API documentation for the project",
     *      "is_completed": false,
     *      "due_date": "2025-01-20",
     *      "created_at": "2025-01-15T10:30:00.000000Z",
     *      "updated_at": "2025-01-15T10:30:00.000000Z"
     *    }
     *  ],
     *  "links": {
     *    "first": "http://localhost:8000/api/todos?page=1",
     *    "last": "http://localhost:8000/api/todos?page=1",
     *    "prev": null,
     *    "next": null
     *  },
     *  "meta": {
     *    "current_page": 1,
     *    "from": 1,
     *    "last_page": 1,
     *    "per_page": 15,
     *    "to": 1,
     *    "total": 1
     *  }
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
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
     * Create New Todo
     * 
     * Create a new todo item for the authenticated user.
     * 
     * @authenticated
     * 
     * @bodyParam title string required The todo title. Example: Complete project documentation
     * @bodyParam description string The todo description. Example: Write comprehensive API documentation for the project
     * @bodyParam due_date string The due date for the todo (YYYY-MM-DD format, must be future date). Example: 2025-01-20
     * 
     * @response 201 scenario="success" {
     *  "status": "success",
     *  "message": "Todo created successfully",
     *  "data": {
     *    "id": 1,
     *    "title": "Complete project documentation",
     *    "description": "Write comprehensive API documentation for the project",
     *    "is_completed": false,
     *    "due_date": "2025-01-20",
     *    "user_id": 1,
     *    "created_at": "2025-01-15T10:30:00.000000Z",
     *    "updated_at": "2025-01-15T10:30:00.000000Z"
     *  }
     * }
     * 
     * @response 422 scenario="validation error" {
     *  "status": "error",
     *  "message": "Validation failed",
     *  "errors": {
     *    "title": ["The title field is required."]
     *  }
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
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
     * Get Specific Todo
     * 
     * Retrieve a specific todo by ID for the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam id integer required The ID of the todo. Example: 1
     * 
     * @response 200 scenario="success" {
     *  "status": "success",
     *  "data": {
     *    "id": 1,
     *    "title": "Complete project documentation",
     *    "description": "Write comprehensive API documentation for the project",
     *    "is_completed": false,
     *    "due_date": "2025-01-20",
     *    "created_at": "2025-01-15T10:30:00.000000Z",
     *    "updated_at": "2025-01-15T10:30:00.000000Z"
     *  }
     * }
     * 
     * @response 404 scenario="todo not found" {
     *  "status": "error",
     *  "message": "Todo not found"
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
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
     * Update Todo
     * 
     * Update a specific todo for the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam id integer required The ID of the todo. Example: 1
     * @bodyParam title string The todo title. Example: Updated project documentation
     * @bodyParam description string The todo description. Example: Updated description for the documentation
     * @bodyParam is_completed boolean Whether the todo is completed. Example: true
     * @bodyParam due_date string The due date for the todo (YYYY-MM-DD format). Example: 2025-01-25
     * 
     * @response 200 scenario="success" {
     *  "status": "success",
     *  "message": "Todo updated successfully",
     *  "data": {
     *    "id": 1,
     *    "title": "Updated project documentation",
     *    "description": "Updated description for the documentation",
     *    "is_completed": true,
     *    "due_date": "2025-01-25",
     *    "created_at": "2025-01-15T10:30:00.000000Z",
     *    "updated_at": "2025-01-15T11:30:00.000000Z"
     *  }
     * }
     * 
     * @response 404 scenario="todo not found" {
     *  "status": "error",
     *  "message": "Todo not found"
     * }
     * 
     * @response 422 scenario="validation error" {
     *  "status": "error",
     *  "message": "Validation failed",
     *  "errors": {
     *    "title": ["The title field is required."]
     *  }
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
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
     * Delete Todo
     * 
     * Delete a specific todo for the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam id integer required The ID of the todo to delete. Example: 1
     * 
     * @response 200 scenario="success" {
     *  "status": "success",
     *  "message": "Todo deleted successfully"
     * }
     * 
     * @response 404 scenario="todo not found" {
     *  "status": "error",
     *  "message": "Todo not found"
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
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
     * Mark Todo as Completed
     * 
     * Mark a specific todo as completed for the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam id integer required The ID of the todo to mark as completed. Example: 1
     * 
     * @response 200 scenario="success" {
     *  "status": "success",
     *  "message": "Todo marked as completed",
     *  "data": {
     *    "id": 1,
     *    "title": "Complete project documentation",
     *    "description": "Write comprehensive API documentation for the project",
     *    "is_completed": true,
     *    "due_date": "2025-01-20",
     *    "created_at": "2025-01-15T10:30:00.000000Z",
     *    "updated_at": "2025-01-15T11:30:00.000000Z"
     *  }
     * }
     * 
     * @response 404 scenario="todo not found" {
     *  "status": "error",
     *  "message": "Todo not found"
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
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
     * Mark Todo as Pending
     * 
     * Mark a specific todo as pending (not completed) for the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam id integer required The ID of the todo to mark as pending. Example: 1
     * 
     * @response 200 scenario="success" {
     *  "status": "success",
     *  "message": "Todo marked as pending",
     *  "data": {
     *    "id": 1,
     *    "title": "Complete project documentation",
     *    "description": "Write comprehensive API documentation for the project",
     *    "is_completed": false,
     *    "due_date": "2025-01-20",
     *    "created_at": "2025-01-15T10:30:00.000000Z",
     *    "updated_at": "2025-01-15T11:30:00.000000Z"
     *  }
     * }
     * 
     * @response 404 scenario="todo not found" {
     *  "status": "error",
     *  "message": "Todo not found"
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
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
     * Get User Todo Statistics
     * 
     * Get statistics about todos for the authenticated user including total, completed, pending, and overdue counts.
     * 
     * @authenticated
     * 
     * @response 200 scenario="success" {
     *  "status": "success",
     *  "data": {
     *    "total_todos": 10,
     *    "completed_todos": 5,
     *    "pending_todos": 5,
     *    "overdue_todos": 2,
     *    "completion_rate": 50
     *  }
     * }
     * 
     * @response 401 scenario="unauthenticated" {
     *  "message": "Unauthenticated."
     * }
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
