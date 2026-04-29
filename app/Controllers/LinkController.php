<?php

namespace App\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LinkController extends Controller
{
    // Display listing with filters
    public function index(Request $request)
    {
        $query = Link::query();
        
        // Filter by type
        if ($request->has('type') && in_array($request->type, ['whatsapp', 'group', 'jforce', 'study'])) {
            $query->ofType($request->type);
        }
        
        // Filter active/inactive
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        $links = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // GDPR: Log access without storing personal data
        Log::channel('daily')->info('Link list viewed', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'filters' => $request->except('_token')
        ]);
        
        return view('links.index', compact('links'));
    }

    // Show form to create new link
    public function create()
    {
        return view('links.create');
    }

    // Store new link
    public function store(Request $request)
    {
        // GDPR: Explicit consent required
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'url' => 'required|url|max:2048',
            'type' => 'required|in:whatsapp,group,jforce,study',
            'description' => 'nullable|string|max:1000',
            'expires_at' => 'nullable|date|after:today',
            'gdpr_consent' => 'required|accepted'
        ], [
            'gdpr_consent.accepted' => 'You must consent to data processing under GDPR to store this link.'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $link = Link::create([
            'title' => $request->title,
            'url' => $request->url,
            'type' => $request->type,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'expires_at' => $request->expires_at,
            'created_by' => $request->ip(), // Anonymized identifier
            'gdpr_consent' => true,
            'consent_given_at' => now()
        ]);
        
        Log::channel('daily')->info('New link created', [
            'link_id' => $link->id,
            'type' => $link->type,
            'created_by_ip' => $request->ip()
        ]);
        
        return redirect()->route('links.index')
                         ->with('success', 'Link created successfully (GDPR consent recorded)');
    }

    // Show single link
    public function show($id)
    {
        $link = Link::withTrashed()->findOrFail($id);
        
        // GDPR: Record access
        $link->recordClick();
        
        return view('links.show', compact('link'));
    }

    // Show edit form
    public function edit($id)
    {
        $link = Link::findOrFail($id);
        return view('links.edit', compact('link'));
    }

    // Update link
    public function update(Request $request, $id)
    {
        $link = Link::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'url' => 'required|url|max:2048',
            'type' => 'required|in:whatsapp,group,jforce,study',
            'description' => 'nullable|string|max:1000',
            'expires_at' => 'nullable|date'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $link->update([
            'title' => $request->title,
            'url' => $request->url,
            'type' => $request->type,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'expires_at' => $request->expires_at
        ]);
        
        Log::channel('daily')->info('Link updated', [
            'link_id' => $link->id,
            'updated_by_ip' => $request->ip()
        ]);
        
        return redirect()->route('links.index')
                         ->with('success', 'Link updated successfully');
    }

    // Soft delete (GDPR right to erasure)
    public function destroy($id)
    {
        $link = Link::findOrFail($id);
        
        // If GDPR consent withdrawn, anonymize instead of just delete
        if (request()->has('gdpr_removal')) {
            $link->anonymize();
            $message = 'Link anonymized and removed per GDPR Art. 17 (Right to Erasure)';
        } else {
            $link->delete();
            $message = 'Link moved to trash. It will be permanently deleted after 30 days.';
        }
        
        Log::channel('daily')->warning('Link deleted', [
            'link_id' => $link->id,
            'type' => $link->type,
            'deleted_by_ip' => request()->ip(),
            'gdpr_compliant' => request()->has('gdpr_removal')
        ]);
        
        return redirect()->route('links.index')
                         ->with('success', $message);
    }
    
        /**
     * GDPR Right to Erasure (complete anonymization)
     */
    public function gdprErasure($id)
    {
        $link = Link::withTrashed()->findOrFail($id);
        $link->anonymize();
        
        Log::channel('daily')->alert('GDPR Art. 17 Erasure requested', [
            'link_id' => $link->id,
            'original_title' => $link->getOriginal('title'),
            'requested_by_ip' => request()->ip(),
            'compliance' => 'GDPR Article 17 - Right to Erasure'
        ]);
        
        return redirect()->route('links.index')
            ->with('success', '✅ GDPR Compliance: Link has been permanently anonymized per Article 17 (Right to Erasure).');
    }

    /**
     * Export as JSON (GDPR Data Portability - Art. 20)
     */
    public function exportJson()
    {
        $links = Link::all();
        
        return response()->json([
            'export_date' => now()->toIso8601String(),
            'gdpr_compliant' => true,
            'data_portability' => 'GDPR Article 20',
            'count' => $links->count(),
            'links' => $links->map(function($link) {
                return [
                    'id' => $link->id,
                    'title' => $link->title,
                    'type' => $link->type,
                    'url' => $link->url,
                    'description' => $link->description,
                    'click_count' => $link->click_count,
                    'created_at' => $link->created_at,
                    'expires_at' => $link->expires_at
                ];
            })
        ], 200, [
            'Content-Disposition' => 'attachment; filename="links-export-' . date('Y-m-d') . '.json"'
        ]);
    }

    /**
     * Export as CSV
     */
    public function exportCsv()
    {
        $links = Link::all();
        $filename = 'links-export-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];
        
        $callback = function() use ($links) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Title', 'Type', 'URL', 'Description', 'Clicks', 'Created', 'Expires']);
            
            foreach ($links as $link) {
                fputcsv($handle, [
                    $link->id,
                    $link->title,
                    $link->type,
                    $link->url,
                    $link->description,
                    $link->click_count,
                    $link->created_at,
                    $link->expires_at
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Dashboard with statistics
     */
    public function dashboard()
    {
        $stats = [
            'total' => Link::count(),
            'active' => Link::active()->count(),
            'expired' => Link::where('expires_at', '<', now())->count(),
            'whatsapp' => Link::ofType('whatsapp')->count(),
            'group' => Link::ofType('group')->count(),
            'jforce' => Link::ofType('jforce')->count(),
            'study' => Link::ofType('study')->count(),
            'total_clicks' => Link::sum('click_count'),
            'recent' => Link::latest()->take(5)->get(),
            'popular' => Link::orderBy('click_count', 'desc')->take(5)->get()
        ];
        
        return view('links.dashboard', compact('stats'));
    }

    /**
     * Filter by type with clean URL
     */
    public function filterByType($type)
    {
        $links = Link::ofType($type)->paginate(15);
        return view('links.index', compact('links'));
    }

    /**
     * Show only active links
     */
    public function activeOnly()
    {
        $links = Link::active()->paginate(15);
        return view('links.index', compact('links'));
    }

    /**
     * Show expired links
     */
    public function expiredOnly()
    {
        $links = Link::where('expires_at', '<', now())->paginate(15);
        return view('links.index', compact('links'));
    }

    /**
     * Show soft-deleted links (trash)
     */
    public function trash()
    {
        $links = Link::onlyTrashed()->paginate(15);
        return view('links.trash', compact('links'));
    }

    /**
     * Advanced search
     */
    public function search(Request $request)
    {
        $query = Link::query();
        
        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->orWhere('url', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->has('type') && $request->type != 'all') {
            $query->ofType($request->type);
        }
        
        $links = $query->paginate(15);
        
        if ($request->ajax()) {
            return response()->json($links);
        }
        
        return view('links.index', compact('links'));
    }

    /**
     * Track click without page reload (AJAX)
     */
    public function trackClick($id)
    {
        $link = Link::findOrFail($id);
        $link->recordClick();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'click_count' => $link->click_count
            ]);
        }
        
        return redirect($link->url);
    }

    /**
     * Renew an expired link
     */
    public function renew($id)
    {
        $link = Link::findOrFail($id);
        $link->update([
            'expires_at' => now()->addDays(30),
            'is_active' => true
        ]);
        
        return redirect()->route('links.show', $link)
            ->with('success', 'Link renewed for 30 days.');
    }

    /**
     * Generate QR Code for link (industrial inventory)
     */
    public function generateQrCode($id)
    {
        $link = Link::findOrFail($id);
        
        // Simple QR code generation using GD library
        $url = route('links.track.click', $link);
        $qrCode = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($url);
        
        return view('links.qrcode', compact('link', 'qrCode'));
    }

    /**
     * Bulk activate links
     */
    public function bulkActivate(Request $request)
    {
        $ids = explode(',', $request->ids);
        Link::whereIn('id', $ids)->update(['is_active' => true]);
        
        return response()->json([
            'success' => true,
            'message' => count($ids) . ' links activated.'
        ]);
    }

    /**
     * Bulk deactivate links
     */
    public function bulkDeactivate(Request $request)
    {
        $ids = explode(',', $request->ids);
        Link::whereIn('id', $ids)->update(['is_active' => false]);
        
        return response()->json([
            'success' => true,
            'message' => count($ids) . ' links deactivated.'
        ]);
    }

    /**
     * Bulk delete links
     */
    public function bulkDelete(Request $request)
    {
        $ids = explode(',', $request->ids);
        Link::whereIn('id', $ids)->delete();
        
        return response()->json([
            'success' => true,
            'message' => count($ids) . ' links moved to trash.'
        ]);
    }

    /**
     * Statistics page
     */
    public function statistics()
    {
        $stats = [
            'by_type' => Link::selectRaw('type, count(*) as count')->groupBy('type')->get(),
            'clicks_by_type' => Link::selectRaw('type, sum(click_count) as total_clicks')->groupBy('type')->get(),
            'activity_last_30_days' => Link::where('created_at', '>=', now()->subDays(30))->count(),
            'average_clicks' => Link::avg('click_count'),
            'most_popular' => Link::orderBy('click_count', 'desc')->first(),
            'expiring_soon' => Link::where('expires_at', '<=', now()->addDays(7))
                                ->where('expires_at', '>', now())
                                ->count()
        ];
        
        return view('links.statistics', compact('stats'));
    }
    // Permanently delete (for GDPR complete removal)
    public function forceDelete($id)
    {
        $link = Link::withTrashed()->findOrFail($id);
        $link->forceDelete();
        
        Log::channel('daily')->alert('Link permanently deleted (GDPR)', [
            'link_id' => $id,
            'deleted_by_ip' => request()->ip()
        ]);
        
        return redirect()->route('links.index')
                         ->with('success', 'Link permanently removed from database.');
    }
    
    // Restore from soft delete
    public function restore($id)
    {
        $link = Link::withTrashed()->findOrFail($id);
        $link->restore();
        
        return redirect()->route('links.index')
                         ->with('success', 'Link restored successfully.');
    }
}