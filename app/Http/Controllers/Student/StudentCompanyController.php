<?php

namespace App\Http\Controllers\Student;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentCompanyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        // Students only see active, verified companies or existing partners (BR-20, product-spec)
        $query = Company::where('is_active', true)
            ->where(function ($q) {
                $q->where('verification_status', CompanyVerificationStatus::VERIFIED)
                    ->orWhere('classification', CompanyClassification::EXISTING_PARTNER);
            })
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('industry')) {
            $query->where('industry', $request->input('industry'));
        }

        $companies = $query->paginate(9)->withQueryString();

        $industries = Company::where('is_active', true)
            ->whereNotNull('industry')
            ->distinct()
            ->pluck('industry');

        return view('student.companies.index', compact('companies', 'industries'));
    }

    public function show(Company $company): View
    {
        $this->authorize('view', $company);

        return view('student.companies.show', compact('company'));
    }
}
