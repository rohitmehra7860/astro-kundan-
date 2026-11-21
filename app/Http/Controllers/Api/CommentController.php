<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    /**
     * List approved comments for a post (with nested replies).
     * Public endpoint — no auth required.
     */
    public function index(Post $post)
    {
        try {
            $comments = $post->comments()
                ->approved()
                ->whereNull('parent_id') // Top-level only
                ->with('replies')
                ->latest()
                ->get()
                ->map(fn($c) => $this->formatComment($c));

            return response()->json([
                'comments' => $comments,
                'total'    => $comments->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CommentController@index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Could not load comments.'], 500);
        }
    }

    /**
     * Submit a new comment (public, rate-limited).
     * Saved as 'pending' — admin must approve in admin panel.
     */
    public function store(Request $request, Post $post)
    {
        try {
            $validated = $request->validate([
                'name'      => 'required|string|max:100',
                'email'     => 'required|email|max:255',
                'content'   => 'required|string|min:5|max:1000',
                'parent_id' => 'nullable|integer|exists:post_comments,id',
            ]);

            // Ensure parent_id belongs to this post (prevent cross-post replies)
            if (!empty($validated['parent_id'])) {
                $parent = PostComment::find($validated['parent_id']);
                if (!$parent || $parent->post_id !== $post->id) {
                    return response()->json(['message' => 'Invalid reply target.'], 422);
                }
            }

            PostComment::create([
                'post_id'   => $post->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'content'   => $validated['content'],
                'status'    => 'pending',
            ]);

            return response()->json([
                'message' => 'Comment submitted successfully. It will appear after moderation.',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in CommentController@store: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Could not submit comment.'], 500);
        }
    }

    /** Format a comment for the API response (hide email for privacy). */
    private function formatComment(PostComment $comment): array
    {
        return [
            'id'         => $comment->id,
            'name'       => $comment->name,
            'content'    => $comment->content,
            'created_at' => $comment->created_at?->toIso8601String(),
            'replies'    => $comment->replies->map(fn($r) => $this->formatComment($r))->values(),
        ];
    }
}
