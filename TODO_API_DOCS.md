# Todo API Documentation

Base URL: `http://127.0.0.1:8000/api`

## Authentication
All todo endpoints require Bearer token in Authorization header:
```
Authorization: Bearer your_jwt_token_here
```

## Todo Endpoints

### 1. Get All Todos
```
GET /todos
```

**Query Parameters:**
- `status` (optional): `completed`, `pending`
- `search` (optional): Search by title
- `sort_by` (optional): `created_at`, `due_date`, `title` (default: `created_at`)
- `sort_order` (optional): `asc`, `desc` (default: `desc`)
- `per_page` (optional): Number of items per page (default: 15)

**Response:**
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "title": "Learn Laravel",
        "description": "Complete Laravel tutorial",
        "is_completed": false,
        "due_date": "2025-08-30T00:00:00.000000Z",
        "user_id": 1,
        "created_at": "2025-08-23T13:31:39.000000Z",
        "updated_at": "2025-08-23T13:31:39.000000Z"
      }
    ],
    "total": 1,
    "per_page": 15
  }
}
```

### 2. Create Todo
```
POST /todos
```

**Body:**
```json
{
  "title": "Learn Flutter",
  "description": "Complete Flutter course",
  "due_date": "2025-08-30"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Todo created successfully",
  "data": {
    "id": 2,
    "title": "Learn Flutter",
    "description": "Complete Flutter course",
    "is_completed": false,
    "due_date": "2025-08-30T00:00:00.000000Z",
    "user_id": 1,
    "created_at": "2025-08-23T13:31:39.000000Z",
    "updated_at": "2025-08-23T13:31:39.000000Z"
  }
}
```

### 3. Get Single Todo
```
GET /todos/{id}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "title": "Learn Laravel",
    "description": "Complete Laravel tutorial",
    "is_completed": false,
    "due_date": "2025-08-30T00:00:00.000000Z",
    "user_id": 1,
    "created_at": "2025-08-23T13:31:39.000000Z",
    "updated_at": "2025-08-23T13:31:39.000000Z"
  }
}
```

### 4. Update Todo
```
PUT /todos/{id}
```

**Body:**
```json
{
  "title": "Updated title",
  "description": "Updated description",
  "is_completed": true,
  "due_date": "2025-09-01"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Todo updated successfully",
  "data": {
    "id": 1,
    "title": "Updated title",
    "description": "Updated description",
    "is_completed": true,
    "due_date": "2025-09-01T00:00:00.000000Z",
    "user_id": 1,
    "created_at": "2025-08-23T13:31:39.000000Z",
    "updated_at": "2025-08-23T13:35:00.000000Z"
  }
}
```

### 5. Delete Todo
```
DELETE /todos/{id}
```

**Response:**
```json
{
  "status": "success",
  "message": "Todo deleted successfully"
}
```

### 6. Mark Todo as Completed
```
PATCH /todos/{id}/complete
```

**Response:**
```json
{
  "status": "success",
  "message": "Todo marked as completed",
  "data": {
    "id": 1,
    "title": "Learn Laravel",
    "description": "Complete Laravel tutorial",
    "is_completed": true,
    "due_date": "2025-08-30T00:00:00.000000Z",
    "user_id": 1,
    "created_at": "2025-08-23T13:31:39.000000Z",
    "updated_at": "2025-08-23T13:35:00.000000Z"
  }
}
```

### 7. Mark Todo as Pending
```
PATCH /todos/{id}/pending
```

**Response:**
```json
{
  "status": "success",
  "message": "Todo marked as pending",
  "data": {
    "id": 1,
    "title": "Learn Laravel",
    "description": "Complete Laravel tutorial",
    "is_completed": false,
    "due_date": "2025-08-30T00:00:00.000000Z",
    "user_id": 1,
    "created_at": "2025-08-23T13:31:39.000000Z",
    "updated_at": "2025-08-23T13:35:00.000000Z"
  }
}
```

### 8. Get Todo Statistics
```
GET /todos/stats
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "total_todos": 10,
    "completed_todos": 7,
    "pending_todos": 3,
    "overdue_todos": 1,
    "completion_rate": 70.0
  }
}
```

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 404 Not Found
```json
{
  "status": "error",
  "message": "Todo not found"
}
```

### 422 Validation Error
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

## Usage Examples

### 1. Get all pending todos
```
GET /todos?status=pending
```

### 2. Search todos by title
```
GET /todos?search=Laravel
```

### 3. Get completed todos sorted by due date
```
GET /todos?status=completed&sort_by=due_date&sort_order=asc
```

### 4. Get todos with pagination
```
GET /todos?per_page=10&page=2
```
