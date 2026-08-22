<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\BulkDeleteCommentRequest;
use App\Http\Requests\Comment\IndexCommentRequest;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(IndexCommentRequest $request, Project $project, Task $task): JsonResponse
    {
        $validate = $request->validated();

        $perPage = $validate['per_page'] ?? 50;

        $comments = $task->comments()->paginate($perPage);
        $comments->through(fn (Comment $comment): array => (new CommentResource($comment))->resolve());

        return response()->json([
            'success' => true,
            'message' => 'Comentários listados com sucesso!',
            'data' => $comments,
        ], 200);
    }

    public function store(StoreCommentRequest $request, Project $project, Task $task): JsonResponse
    {
        $validate = $request->validated();

        $comment = new Comment;
        $comment->task_id = $task->id;
        $comment->user_id = $request->user()->id;
        $comment->content = $validate['content'];
        $comment->author = $request->user()->name;
        $comment->save();

        return response()->json([
            'success' => true,
            'message' => 'Comentário criado com sucesso!',
            'data' => new CommentResource($comment),
        ], 201);
    }

    public function show(Project $project, Task $task, Comment $comment): JsonResponse
    {
        $this->authorize('view', $comment);

        return response()->json([
            'success' => true,
            'message' => 'Comentário encontrado!',
            'data' => new CommentResource($comment),
        ], 200);
    }

    public function update(UpdateCommentRequest $request, Project $project, Task $task, Comment $comment): JsonResponse
    {
        $validate = $request->validated();

        $comment->content = $validate['content'];
        $comment->save();

        return response()->json([
            'success' => true,
            'message' => 'Comentário atualizado com sucesso!',
            'data' => new CommentResource($comment),
        ], 200);
    }

    public function destroy(Request $request, Project $project, Task $task, Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comentário excluído com sucesso!',
            'data' => new CommentResource($comment),
        ], 200);
    }

    public function bulkDelete(BulkDeleteCommentRequest $request, Project $project, Task $task): JsonResponse
    {
        $validated = $request->validated();

        $foundIds = $task->comments()
            ->whereIn('id', $validated['comment_ids'])
            ->pluck('id')
            ->all();

        $notFound = array_values(array_diff($validated['comment_ids'], $foundIds));

        $task->comments()->whereIn('id', $foundIds)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Operação concluída!',
            'deleted' => count($foundIds),
            'not_found' => $notFound,
        ], 200);
    }
}
