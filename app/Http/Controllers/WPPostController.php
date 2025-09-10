<?php
// app/Http/Controllers/WPPostController.php
namespace App\Http\Controllers;

use App\Models\PostPriorities;
use App\Contracts\IWP;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WPPostController extends Controller
{
    public function __construct(private IWP $wp) {}

    public function index(Request $req)
    {
        $wpResp = $this->wp->listPosts([
            'number' => (int)$req->get('number', 50),
            'status' => $req->get('status', 'publish,draft'),
            'page'   => (int)$req->get('page', 1),
        ]);

        $posts = $wpResp['posts'] ?? [];
        $ids   = array_map(fn($p) => $p['ID'], $posts);

        $priorityMap = PostPriorities::whereIn('wp_post_id', $ids)
            ->pluck('priority', 'wp_post_id');

        foreach ($posts as &$p) {
            $p['priority'] = (int)($priorityMap[$p['ID']] ?? 0);
        }

        if ($req->get('sort') === 'priority') {
            usort($posts, fn($a,$b) => ($b['priority'] <=> $a['priority']) ?: strcmp($a['title'],$b['title']));
        }

        return response()->json(['posts' => $posts]);
    }

    public function show($id)
    {
        $post = $this->wp->getPost($id);
        $post['priority'] = (int) PostPriorities::where('wp_post_id',$id)->value('priority') ?? 0;
        return response()->json($post);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'status' => 'nullable|string|in:publish,draft',
            'priority' => 'nullable|integer',
        ]);
        try {
            $wp = $this->wp->createPost(Arr::only($data, ['title','content','status']));
            if (!empty($wp['ID']) && isset($data['priority'])) {
                PostPriorities::updateOrCreate(
                    ['wp_post_id' => $wp['ID']],
                    ['priority' => (int)$data['priority']]
                );
            }
            return response()->json($wp, 201);
        } catch (\Throwable $e) {
            $code = $e->getCode();
            if ($code < 400 || $code >= 600) $code = 500;
            return response()->json(['message' => $e->getMessage()], $code);
        }

    }


    public function update(Request $req, $id)
    {
        $data = $req->validate([
            'title' => 'sometimes|required|string|max:200',
            'content' => 'sometimes|required|string',
            'status' => 'sometimes|nullable|string|in:publish,draft',
            'priority' => 'nullable|integer',
        ]);

        try {
            $wp = $this->wp->updatePost($id, Arr::only($data, ['title','content','status']));
    
            if (array_key_exists('priority', $data)) {
                PostPriorities::updateOrCreate(
                    ['wp_post_id' => $id],
                    ['priority' => (int)$data['priority']]
                );
            }
            return response()->json($wp);
        } catch (\Throwable $e) {
            $code = $e->getCode();
            if ($code < 400 || $code >= 600) $code = 500;
            return response()->json(['message' => $e->getMessage()], $code);
        }
    }

    public function destroy($id)
    {
        try {
            $wp = $this->wp->deletePost($id);
            PostPriorities::where('wp_post_id',$id)->delete();
            return response()->json($wp);
        } catch (\Throwable $e) {
            $code = $e->getCode();
            if ($code < 400 || $code >= 600) $code = 500;
            return response()->json(['message' => $e->getMessage()], $code);
        }
    }

    public function setPriority(Request $req, $id)
    {
        $validated = $req->validate(['priority' => 'required|integer']);
        PostPriorities::updateOrCreate(
            ['wp_post_id' => $id],
            ['priority' => (int)$validated['priority']]
        );
        return response()->json(['ok' => true]);
    }
}
