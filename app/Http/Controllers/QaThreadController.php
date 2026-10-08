<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreQaThreadRequest;
use App\Http\Requests\UpdateQaThreadRequest;
use App\Models\Certification;
use App\Models\QaThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QaThreadController extends Controller
{
    public function index(Request $request): View
    {
        $threads = QaThread::query()
        ->with(['user', 'certification'])
        ->withCount('replies')
        ->keyword($request->string('keyword')->toString())
        ->latestFirst()
        ->paginate(20)
        ->withQueryString();

    $filters = $request->only([
        'status',
        'certification_id',
        'keyword',
    ]);

    $certifications = Certification::query()
        ->orderBy('name')
        ->get();

    $indexRoute = 'qa-board.index';

    $publishedStatus = \App\Enums\CertificationStatus::Published;

    return view('qa-thread.index', compact(
        'threads',
        'filters',
        'certifications',
        'indexRoute',
        'publishedStatus'
    ));
    }

    public function create(): View
    {
        $certifications = Certification::query()
            ->orderBy('name')
            ->get();

        return view(
            'qa-thread.create',
            compact('certifications')
        );
    }

    public function store(
        StoreQaThreadRequest $request
    ): RedirectResponse {
        $thread = QaThread::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('qa-threads.show', $thread)
            ->with('success', '質問を投稿しました。');
    }

    public function show(QaThread $qaThread): View
    {
        $qaThread->load([
            'user',
            'certification',
            'replies.user',
        ]);

        return view(
            'qa-thread.show',
            compact('qaThread')
        );
    }

    public function edit(QaThread $qaThread): View
    {
        $this->authorize('update', $qaThread);

        return view(
            'qa-thread.edit',
            compact('qaThread')
        );
    }

    public function update(
        UpdateQaThreadRequest $request,
        QaThread $qaThread
    ): RedirectResponse {
        $this->authorize('update', $qaThread);

        $qaThread->update($request->validated());

        return redirect()
            ->route('qa-threads.show', $qaThread)
            ->with('success', '質問を更新しました。');
    }

    public function destroy(
        QaThread $qaThread
    ): RedirectResponse {
        $this->authorize('delete', $qaThread);

        $qaThread->delete();

        return redirect()
            ->route('qa-threads.index')
            ->with('success', '質問を削除しました。');
    }
}
