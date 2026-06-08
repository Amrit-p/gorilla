<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use App\Models\DiscussionAttachment;
use App\Models\DiscussionSection;
use App\Models\User;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->can(CrmPermissions::MANAGE_DISCUSSIONS), 403);
    }

    public function index(): View
    {
        $this->authorizeAdmin();

        $discussions = Discussion::with(['worker', 'sections.attachments'])
            ->orderByDesc('date')
            ->get();

        return view('admin.discussions.index', compact('discussions'));
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        $workers = User::role(CrmRoles::MOWER)->orderBy('name')->get(['id', 'name']);

        $defaultSections = [
            'worker' => [
                'Worker Information',
                'Performance',
                'Attendance & Punctuality',
                'Behaviour & Professionalism',
                'Safety & Compliance',
                'Communication & Teamwork',
                'Job Knowledge & Skills',
                'Equipment & Vehicle Care',
                'Salary & Employment',
                'Employee Support',
                'Training',
                'Disciplinary Action',
                'Manager Comments',
                'Action Items',
            ],
        ];

        return view('admin.discussions.create', compact('workers', 'defaultSections'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'title'                  => 'required|string|max:255',
            'category'               => ['required', Rule::in(['worker', 'budget', 'expansion', 'crm_update', 'general'])],
            'worker_id'              => 'nullable|exists:users,id',
            'date'                   => 'required|date',
            'sections'               => 'required|array|min:1',
            'sections.*.heading'     => 'required|string|max:255',
            'sections.*.body'        => 'nullable|string',
            'sections.*.files'       => 'nullable|array|max:10',
            'sections.*.files.*'     => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $discussion = Discussion::create([
            'title'      => $data['title'],
            'category'   => $data['category'],
            'worker_id'  => $data['worker_id'] ?? null,
            'date'       => $data['date'],
            'created_by' => auth()->id(),
        ]);

        foreach ($data['sections'] as $index => $sectionData) {
            $section = DiscussionSection::create([
                'discussion_id' => $discussion->id,
                'sort_order'    => $index,
                'heading'       => $sectionData['heading'],
                'body'          => $sectionData['body'] ?? null,
            ]);

            foreach ($request->file("sections.{$index}.files") ?? [] as $file) {
                $path = $file->store('discussion-attachments', 'public');
                DiscussionAttachment::create([
                    'discussion_section_id' => $section->id,
                    'original_name'         => $file->getClientOriginalName(),
                    'file_path'             => $path,
                    'file_type'             => $this->resolveFileType($file->getMimeType()),
                ]);
            }
        }

        return redirect()->route('admin.discussions.index')->with('success', 'Discussion saved.');
    }

    public function show(Discussion $discussion): View
    {
        $this->authorizeAdmin();

        $discussion->load(['sections.attachments', 'worker', 'createdBy']);

        return view('admin.discussions.show', compact('discussion'));
    }

    public function exportPdf(Request $request, Discussion $discussion): Response
    {
        $this->authorizeAdmin();

        $discussion->load(['sections.attachments', 'worker', 'createdBy']);

        $includedIds = array_map('intval', $request->input('include_sections', []));

        $sections = $discussion->sections->filter(
            fn ($s) => in_array($s->id, $includedIds, true)
        )->values();

        return Pdf::loadView('admin.discussions.export-pdf', compact('discussion', 'sections'))
            ->setPaper('a4', 'portrait')
            ->download('discussion-' . $discussion->id . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function edit(Discussion $discussion): View
    {
        $this->authorizeAdmin();

        $discussion->load('sections.attachments');
        $workers = User::role(CrmRoles::MOWER)->orderBy('name')->get(['id', 'name']);

        return view('admin.discussions.edit', compact('discussion', 'workers'));
    }

    public function update(Request $request, Discussion $discussion): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'title'                  => 'required|string|max:255',
            'category'               => ['required', Rule::in(['worker', 'budget', 'expansion', 'crm_update', 'general'])],
            'worker_id'              => 'nullable|exists:users,id',
            'date'                   => 'required|date',
            'sections'               => 'required|array|min:1',
            'sections.*.heading'     => 'required|string|max:255',
            'sections.*.body'        => 'nullable|string',
            'sections.*.files'       => 'nullable|array|max:10',
            'sections.*.files.*'     => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $oldPaths = $discussion->sections()
            ->with('attachments')
            ->get()
            ->flatMap(fn ($s) => $s->attachments->pluck('file_path'))
            ->all();

        DB::transaction(function () use ($discussion, $data, $request) {
            $discussion->update([
                'title'     => $data['title'],
                'category'  => $data['category'],
                'worker_id' => $data['worker_id'] ?? null,
                'date'      => $data['date'],
            ]);

            $discussion->sections()->delete();

            foreach ($data['sections'] as $index => $sectionData) {
                $section = DiscussionSection::create([
                    'discussion_id' => $discussion->id,
                    'sort_order'    => $index,
                    'heading'       => $sectionData['heading'],
                    'body'          => $sectionData['body'] ?? null,
                ]);

                foreach ($request->file("sections.{$index}.files") ?? [] as $file) {
                    $path = $file->store('discussion-attachments', 'public');
                    DiscussionAttachment::create([
                        'discussion_section_id' => $section->id,
                        'original_name'         => $file->getClientOriginalName(),
                        'file_path'             => $path,
                        'file_type'             => $this->resolveFileType($file->getMimeType()),
                    ]);
                }
            }
        });

        Storage::disk('public')->delete($oldPaths);

        return redirect()->route('admin.discussions.index')->with('success', 'Discussion updated.');
    }

    public function destroy(Discussion $discussion): RedirectResponse
    {
        $this->authorizeAdmin();

        $filePaths = $discussion->sections()
            ->with('attachments')
            ->get()
            ->flatMap(fn ($s) => $s->attachments->pluck('file_path'))
            ->all();

        $discussion->delete();

        Storage::disk('public')->delete($filePaths);

        return redirect()->route('admin.discussions.index')->with('success', 'Discussion deleted.');
    }

    private function resolveFileType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }

        if ($mime === 'application/pdf') {
            return 'pdf';
        }

        return 'document';
    }
}
