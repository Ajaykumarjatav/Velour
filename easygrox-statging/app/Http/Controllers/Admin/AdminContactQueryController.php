<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactUsQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super-admin inbox for public contact-form submissions (contact_us_query).
 */
class AdminContactQueryController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactUsQuery::query()->latest('id');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->search($search);
        }

        if ($topic = $request->string('topic')->trim()->toString()) {
            $query->where('help_topics', 'like', '%'.$topic.'%');
        }

        $queries = $query->paginate(25)->withQueryString();

        $topics = ContactUsQuery::query()
            ->select('help_topics')
            ->whereNotNull('help_topics')
            ->where('help_topics', '!=', '')
            ->distinct()
            ->orderBy('help_topics')
            ->pluck('help_topics');

        return view('admin.contact-queries.index', [
            'queries' => $queries,
            'topics'  => $topics,
            'total'   => ContactUsQuery::query()->count(),
        ]);
    }

    public function show(ContactUsQuery $contactQuery): View
    {
        return view('admin.contact-queries.show', [
            'query' => $contactQuery,
        ]);
    }
}
