<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Authorization\Actions\ProvisionTenantRbac;
use App\Modules\Authorization\Roles;
use App\Modules\CRM\Actions\AddContact;
use App\Modules\CRM\Actions\CreateCustomer;
use App\Modules\CRM\Actions\CreateLead;
use App\Modules\CRM\Actions\ScheduleFollowUp;
use App\Modules\CRM\Data\ContactData;
use App\Modules\CRM\Data\CustomerData;
use App\Modules\CRM\Data\FollowUpData;
use App\Modules\CRM\Data\LeadData;
use App\Modules\CRM\Domain\FollowUpType;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\Lead;
use App\Modules\Finance\Actions\CreateInvoice;
use App\Modules\Finance\Actions\RecordExpense;
use App\Modules\Finance\Actions\RecordIncome;
use App\Modules\Finance\Actions\RecordInvoicePayment;
use App\Modules\Finance\Actions\RefundInvoice;
use App\Modules\Finance\Actions\SendInvoice;
use App\Modules\Finance\Data\ExpenseData;
use App\Modules\Finance\Data\IncomeData;
use App\Modules\Finance\Data\InvoiceData;
use App\Modules\Finance\Data\InvoicePaymentData;
use App\Modules\Finance\Data\InvoiceRefundData;
use App\Modules\Finance\Domain\ExpenseCategory;
use App\Modules\Finance\Domain\IncomeCategory;
use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Finance\Models\Invoice;
use App\Modules\HR\Actions\ApproveLeaveRequest;
use App\Modules\HR\Actions\CreateHoliday;
use App\Modules\HR\Actions\CreateLeaveType;
use App\Modules\HR\Actions\RecordAttendance;
use App\Modules\HR\Actions\RejectLeaveRequest;
use App\Modules\HR\Actions\RequestLeave;
use App\Modules\HR\Actions\SetLeaveBalance;
use App\Modules\HR\Actions\UploadEmployeeDocument;
use App\Modules\HR\Data\HolidayData;
use App\Modules\HR\Data\LeaveBalanceData;
use App\Modules\HR\Data\LeaveDecisionData;
use App\Modules\HR\Data\LeaveRequestData;
use App\Modules\HR\Data\LeaveTypeData;
use App\Modules\HR\Data\RecordAttendanceData;
use App\Modules\HR\Data\StoreEmployeeDocumentData;
use App\Modules\HR\Domain\AttendanceStatus;
use App\Modules\HR\Domain\EmployeeDocumentCategory;
use App\Modules\HR\Models\Holiday;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Industry\Travel\Actions\CreateBooking;
use App\Modules\Industry\Travel\Actions\IssueBooking;
use App\Modules\Industry\Travel\Actions\OpenVisaApplication;
use App\Modules\Industry\Travel\Actions\RaiseInvoiceForBooking;
use App\Modules\Industry\Travel\Actions\RegisterTraveller;
use App\Modules\Industry\Travel\Data\BookingData;
use App\Modules\Industry\Travel\Data\TravellerData;
use App\Modules\Industry\Travel\Data\VisaApplicationData;
use App\Modules\Industry\Travel\Data\VisaRequirementData;
use App\Modules\Industry\Travel\Domain\TravellerGender;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Operations\Actions\AddTaskComment;
use App\Modules\Operations\Actions\AssignTask;
use App\Modules\Operations\Actions\ChangeTaskStatus;
use App\Modules\Operations\Actions\CreateProject;
use App\Modules\Operations\Actions\CreateTask;
use App\Modules\Operations\Actions\UploadTaskAttachment;
use App\Modules\Operations\Data\ProjectData;
use App\Modules\Operations\Data\TaskAssignmentData;
use App\Modules\Operations\Data\TaskCommentData;
use App\Modules\Operations\Data\TaskData;
use App\Modules\Operations\Data\TaskStatusData;
use App\Modules\Operations\Domain\ProjectStatus;
use App\Modules\Operations\Domain\TaskPriority;
use App\Modules\Operations\Domain\TaskStatus;
use App\Modules\Operations\Models\Project;
use App\Modules\Organization\Actions\CreateBranch;
use App\Modules\Organization\Actions\CreateDepartment;
use App\Modules\Organization\Actions\CreateDesignation;
use App\Modules\Organization\Actions\CreateEmployee;
use App\Modules\Organization\Actions\CreateTeam;
use App\Modules\Organization\Actions\SetTeamMembers;
use App\Modules\Organization\Data\BranchData;
use App\Modules\Organization\Data\DepartmentData;
use App\Modules\Organization\Data\DesignationData;
use App\Modules\Organization\Data\EmployeeData;
use App\Modules\Organization\Data\TeamData;
use App\Modules\Organization\Data\TeamMembersData;
use App\Modules\Organization\Domain\EmploymentStatus;
use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Two demo tenants, each with one user per role. Passwords are all "password".
 * Emails: {role}@{slug}.test
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $rbac = app(ProvisionTenantRbac::class);
        $registrar = app(PermissionRegistrar::class);
        $context = app(TenantContext::class);

        foreach ([
            ['name' => 'Wanderlust Travel', 'slug' => 'wanderlust', 'industry' => 'travel'],
            ['name' => 'Skyline Properties', 'slug' => 'skyline', 'industry' => 'real_estate'],
            ['name' => 'Northbridge Consulting', 'slug' => 'northbridge', 'industry' => 'consultancy'],
        ] as $spec) {
            $tenant = Tenant::updateOrCreate(
                ['slug' => $spec['slug']],
                ['name' => $spec['name'], 'industry' => $spec['industry']],
            );

            $rbac->handle($tenant);

            $context->set($tenant);
            $registrar->setPermissionsTeamId($tenant->getKey());

            foreach (Roles::all() as $role) {
                $user = User::firstOrNew(['email' => "{$role}@{$spec['slug']}.test"]);
                $user->tenant_id = $tenant->getKey();
                $user->name = Str::headline($role).' '.Str::headline($spec['slug']);
                $user->password = Hash::make('password');
                $user->email_verified_at = now();
                $user->save();

                if (! $user->hasRole($role)) {
                    $user->assignRole($role);
                }
            }

            if (Branch::query()->where('tenant_id', $tenant->getKey())->doesntExist()) {
                app(CreateBranch::class)->handle(new BranchData(
                    name: 'Head Office',
                    code: 'HO',
                    address: null,
                    phone: null,
                    email: null,
                    isHeadOffice: true,
                ));

                foreach (['Sales', 'Operations', 'Finance'] as $i => $name) {
                    $department = app(CreateDepartment::class)->handle(new DepartmentData(
                        branchId: null,
                        name: $name,
                        code: strtoupper(substr($name, 0, 3)),
                        description: null,
                    ));

                    app(CreateDesignation::class)->handle(new DesignationData(
                        departmentId: $department->getKey(),
                        title: $name.' Manager',
                        rank: $i + 1,
                    ));
                }
            }

            if (LeaveType::query()->where('tenant_id', $tenant->getKey())->doesntExist()) {
                $this->seedLeaveTypes();
            }

            if (Employee::query()->where('tenant_id', $tenant->getKey())->doesntExist()) {
                $salesDept = Department::query()->where('code', 'SAL')->first();

                // Every demo login gets a linked Employee record so HR features
                // (attendance check-in/out, leave requests) work for each role,
                // not just "staff".
                foreach (Roles::all() as $i => $role) {
                    $roleUser = User::where('email', "{$role}@{$spec['slug']}.test")->first();

                    if ($roleUser === null) {
                        continue;
                    }

                    app(CreateEmployee::class)->handle(new EmployeeData(
                        userId: $roleUser->getKey(),
                        branchId: null,
                        departmentId: $salesDept?->getKey(),
                        designationId: null,
                        employeeCode: sprintf('EMP-%04d', $i + 1),
                        firstName: Str::headline($role),
                        lastName: 'Employee',
                        email: "{$role}.employee@{$spec['slug']}.test",
                        phone: null,
                        hireDate: now()->subYear()->format('Y-m-d'),
                        employmentStatus: EmploymentStatus::Active,
                    ));
                }
            }

            $this->seedFinance($tenant, $spec['slug']);

            if ($spec['industry'] === 'travel') {
                $this->seedTravel($tenant, $spec['slug']);
            }

            $this->seedOrgExtras($tenant, $spec['slug']);
            $this->seedOperations($tenant, $spec['slug']);
            $this->seedCrmExtras($tenant, $spec['slug']);
            $this->seedHrExtras($tenant, $spec['slug']);

            $context->clear();
            $registrar->setPermissionsTeamId(null);
        }
    }

    /**
     * The standard set of leave categories every tenant offers.
     */
    private function seedLeaveTypes(): void
    {
        foreach ([
            ['name' => 'Annual Leave', 'code' => 'ANNUAL', 'days' => 20, 'paid' => true],
            ['name' => 'Sick Leave', 'code' => 'SICK', 'days' => 10, 'paid' => true],
            ['name' => 'Casual Leave', 'code' => 'CASUAL', 'days' => 7, 'paid' => true],
        ] as $type) {
            app(CreateLeaveType::class)->handle(new LeaveTypeData(
                name: $type['name'],
                code: $type['code'],
                defaultDaysPerYear: $type['days'],
                isPaid: $type['paid'],
                requiresApproval: true,
            ));
        }
    }

    /**
     * A small slice of Finance demo data per tenant: a couple of expenses, one
     * other-income entry, and two invoices for a demo customer — one paid in
     * full, one part-paid.
     */
    private function seedFinance(Tenant $tenant, string $slug): void
    {
        if (Invoice::query()->where('tenant_id', $tenant->getKey())->exists()) {
            return;
        }

        $owner = User::where('email', "owner@{$slug}.test")->first();

        if ($owner === null) {
            return;
        }

        $customer = app(CreateCustomer::class)->handle(
            new CustomerData(
                ownerEmployeeId: null,
                name: Str::headline($slug).' Retail Client',
                type: 'business',
                company: null,
                email: "client@{$slug}.test",
                phone: null,
                address: null,
            ),
            $owner,
        );

        app(RecordExpense::class)->handle(
            new ExpenseData(
                category: ExpenseCategory::Office,
                employeeId: null,
                supplierName: null,
                title: 'Office rent',
                amount: '1200.00',
                spentOn: now()->startOfMonth()->format('Y-m-d'),
                method: PaymentMethod::BankTransfer,
                reference: null,
                note: null,
            ),
            $owner,
        );

        app(RecordExpense::class)->handle(
            new ExpenseData(
                category: ExpenseCategory::Supplier,
                employeeId: null,
                supplierName: 'Acme Supplies',
                title: 'Stationery and printing',
                amount: '180.50',
                spentOn: now()->subDays(10)->format('Y-m-d'),
                method: PaymentMethod::Card,
                reference: null,
                note: null,
            ),
            $owner,
        );

        app(RecordIncome::class)->handle(
            new IncomeData(
                customerId: null,
                category: IncomeCategory::Other,
                source: 'Bank interest',
                amount: '75.00',
                receivedOn: now()->subDays(5)->format('Y-m-d'),
                method: PaymentMethod::BankTransfer,
                reference: null,
                note: null,
            ),
            $owner,
        );

        $paidInvoice = app(CreateInvoice::class)->handle(
            new InvoiceData(
                customerId: $customer->getKey(),
                issueDate: now()->subDays(20)->format('Y-m-d'),
                dueDate: now()->subDays(6)->format('Y-m-d'),
                amount: '2000.00',
                notes: 'Consulting — March',
            ),
            $owner,
        );
        app(SendInvoice::class)->handle($paidInvoice);
        app(RecordInvoicePayment::class)->handle(
            $paidInvoice,
            new InvoicePaymentData(
                amount: '2000.00',
                paidOn: now()->subDays(3)->format('Y-m-d'),
                method: PaymentMethod::BankTransfer,
                reference: null,
                note: null,
            ),
            $owner,
        );

        $partInvoice = app(CreateInvoice::class)->handle(
            new InvoiceData(
                customerId: $customer->getKey(),
                issueDate: now()->subDays(8)->format('Y-m-d'),
                dueDate: now()->addDays(6)->format('Y-m-d'),
                amount: '1500.00',
                notes: 'Retainer — April',
            ),
            $owner,
        );
        app(SendInvoice::class)->handle($partInvoice);
        app(RecordInvoicePayment::class)->handle(
            $partInvoice,
            new InvoicePaymentData(
                amount: '500.00',
                paidOn: now()->subDays(1)->format('Y-m-d'),
                method: PaymentMethod::Cash,
                reference: null,
                note: null,
            ),
            $owner,
        );

        // A partial refund against the fully-paid invoice, and two additional
        // customers so CRM contacts/leads have more than one record to link to.
        app(RefundInvoice::class)->handle(
            $paidInvoice->refresh(),
            new InvoiceRefundData(
                paymentId: null,
                amount: '300.00',
                refundedOn: now()->subDays(2)->format('Y-m-d'),
                method: PaymentMethod::BankTransfer,
                reason: 'Client requested partial credit',
            ),
            $owner,
        );

        app(RecordIncome::class)->handle(
            new IncomeData(
                customerId: $customer->getKey(),
                category: IncomeCategory::CustomerPayment,
                source: 'Service upsell',
                amount: '320.00',
                receivedOn: now()->subDays(2)->format('Y-m-d'),
                method: PaymentMethod::Card,
                reference: null,
                note: null,
            ),
            $owner,
        );

        app(CreateCustomer::class)->handle(
            new CustomerData(
                ownerEmployeeId: null,
                name: Str::headline($slug).' Second Client',
                type: 'business',
                company: null,
                email: "second-client@{$slug}.test",
                phone: null,
                address: null,
            ),
            $owner,
        );

        app(CreateCustomer::class)->handle(
            new CustomerData(
                ownerEmployeeId: null,
                name: Str::headline($slug).' Individual Client',
                type: 'individual',
                company: null,
                email: "individual-client@{$slug}.test",
                phone: null,
                address: null,
            ),
            $owner,
        );
    }

    /**
     * A second branch per tenant (branches needs >= 3 rows across tenants) and
     * a team of three employees.
     */
    private function seedOrgExtras(Tenant $tenant, string $slug): void
    {
        if (Branch::query()->where('tenant_id', $tenant->getKey())->where('code', 'BR2')->exists()) {
            return;
        }

        $owner = User::where('email', "owner@{$slug}.test")->first();

        if ($owner === null) {
            return;
        }

        app(CreateBranch::class)->handle(new BranchData(
            name: 'Downtown Branch',
            code: 'BR2',
            address: null,
            phone: null,
            email: null,
            isHeadOffice: false,
        ));

        $employeeIds = Employee::query()
            ->where('tenant_id', $tenant->getKey())
            ->orderBy('id')
            ->limit(3)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        if (count($employeeIds) < 1) {
            return;
        }

        $team = app(CreateTeam::class)->handle(new TeamData(
            name: 'Front Desk Team',
            description: 'Handles day-to-day client-facing work.',
            leadEmployeeId: $employeeIds[0],
        ));

        app(SetTeamMembers::class)->handle($team, new TeamMembersData($employeeIds));
    }

    /**
     * A small Operations slice per tenant: one project, a handful of tasks in
     * different states with mixed assignment, a comment, a status change, and
     * an attachment — exercising tasks/task_comments/task_activities/
     * task_attachments together.
     */
    private function seedOperations(Tenant $tenant, string $slug): void
    {
        if (Project::query()->where('tenant_id', $tenant->getKey())->exists()) {
            return;
        }

        $owner = User::where('email', "owner@{$slug}.test")->first();
        $staffUser = User::where('email', "staff@{$slug}.test")->first();

        if ($owner === null) {
            return;
        }

        $salesDept = Department::query()->where('tenant_id', $tenant->getKey())->where('code', 'SAL')->first();
        $staffEmployee = $staffUser !== null
            ? Employee::query()->where('tenant_id', $tenant->getKey())->where('user_id', $staffUser->getKey())->first()
            : null;

        $project = app(CreateProject::class)->handle(
            new ProjectData(
                departmentId: $salesDept?->getKey(),
                leadEmployeeId: $staffEmployee?->getKey(),
                name: 'Client Onboarding Rollout',
                code: 'PRJ-'.strtoupper(Str::random(4)),
                description: 'Roll out the new client onboarding checklist.',
                status: ProjectStatus::Active,
                startDate: now()->subWeeks(2)->format('Y-m-d'),
                dueDate: now()->addWeeks(4)->format('Y-m-d'),
            ),
            $owner,
        );

        $taskOne = app(CreateTask::class)->handle(
            new TaskData(
                projectId: $project->getKey(),
                parentTaskId: null,
                title: 'Draft onboarding checklist',
                description: 'Write the first draft of the checklist.',
                priority: TaskPriority::High,
                dueAt: now()->addDays(3)->toIso8601String(),
            ),
            $owner,
        );

        $taskTwo = app(CreateTask::class)->handle(
            new TaskData(
                projectId: $project->getKey(),
                parentTaskId: null,
                title: 'Review checklist with team',
                description: null,
                priority: TaskPriority::Normal,
                dueAt: now()->addDays(7)->toIso8601String(),
            ),
            $owner,
        );

        $taskThree = app(CreateTask::class)->handle(
            new TaskData(
                projectId: $project->getKey(),
                parentTaskId: null,
                title: 'Publish checklist to intranet',
                description: null,
                priority: TaskPriority::Low,
                dueAt: null,
            ),
            $owner,
        );

        if ($staffEmployee !== null) {
            app(AssignTask::class)->handle(
                $taskOne,
                new TaskAssignmentData(employeeId: $staffEmployee->getKey(), teamId: null),
                $owner,
            );
        }

        app(AddTaskComment::class)->handle(
            $taskOne,
            new TaskCommentData(body: 'Started the first draft — should be ready by Friday.'),
            $owner,
        );

        if ($staffUser !== null) {
            app(AddTaskComment::class)->handle(
                $taskTwo,
                new TaskCommentData(body: 'Looks good, a couple of tweaks needed.'),
                $staffUser,
            );
        }

        app(ChangeTaskStatus::class)->handle($taskOne, new TaskStatusData(TaskStatus::InProgress), $owner);
        app(ChangeTaskStatus::class)->handle($taskTwo, new TaskStatusData(TaskStatus::Done), $owner);
        app(ChangeTaskStatus::class)->handle($taskThree, new TaskStatusData(TaskStatus::Blocked), $owner);

        app(UploadTaskAttachment::class)->handle(
            $taskOne,
            UploadedFile::fake()->create('checklist-draft.pdf', 12, 'application/pdf'),
            $owner,
        );

        app(UploadTaskAttachment::class)->handle(
            $taskTwo,
            UploadedFile::fake()->create('review-notes.txt', 2, 'text/plain'),
            $owner,
        );
    }

    /**
     * CRM fixtures beyond the one demo customer: leads, contacts against
     * existing customers, and follow-ups against both leads and customers.
     */
    private function seedCrmExtras(Tenant $tenant, string $slug): void
    {
        if (Lead::query()->where('tenant_id', $tenant->getKey())->exists()) {
            return;
        }

        $owner = User::where('email', "owner@{$slug}.test")->first();

        if ($owner === null) {
            return;
        }

        $customers = Customer::query()->where('tenant_id', $tenant->getKey())->orderBy('id')->get();

        $leadNames = $slug === 'skyline'
            ? ['Downtown condo buyer', 'Waterfront villa lead', 'Commercial lease enquiry']
            : ['Umrah group enquiry', 'Corporate travel lead', 'Family holiday enquiry'];

        $leads = [];

        foreach ($leadNames as $i => $name) {
            $leads[] = app(CreateLead::class)->handle(
                new LeadData(
                    ownerEmployeeId: null,
                    name: $name,
                    company: null,
                    email: "lead{$i}@{$slug}.test",
                    phone: null,
                    source: 'website',
                    estimatedValue: '1500.00',
                    notes: null,
                ),
                $owner,
            );
        }

        foreach ($customers as $i => $customer) {
            app(AddContact::class)->handle(
                $customer,
                new ContactData(
                    name: "Contact Person {$i}",
                    title: 'Coordinator',
                    email: "contact{$i}@{$slug}.test",
                    phone: null,
                    isPrimary: $i === 0,
                    notes: null,
                ),
            );
        }

        // Follow-ups spread across leads and customers.
        app(ScheduleFollowUp::class)->handle(
            $leads[0],
            new FollowUpData(assignedEmployeeId: null, type: FollowUpType::Call, dueAt: now()->addDays(2)->toIso8601String(), notes: 'Discuss requirements'),
            $owner,
        );

        app(ScheduleFollowUp::class)->handle(
            $leads[1],
            new FollowUpData(assignedEmployeeId: null, type: FollowUpType::Email, dueAt: now()->addDays(4)->toIso8601String(), notes: 'Send brochure'),
            $owner,
        );

        if ($customers->isNotEmpty()) {
            app(ScheduleFollowUp::class)->handle(
                $customers->first(),
                new FollowUpData(assignedEmployeeId: null, type: FollowUpType::Meeting, dueAt: now()->addDays(6)->toIso8601String(), notes: 'Quarterly check-in'),
                $owner,
            );
        }
    }

    /**
     * HR fixtures beyond leave types: holidays, leave balances/requests in a
     * mix of states, and a few days of attendance for a couple of employees.
     */
    private function seedHrExtras(Tenant $tenant, string $slug): void
    {
        if (Holiday::query()->where('tenant_id', $tenant->getKey())->exists()) {
            return;
        }

        $owner = User::where('email', "owner@{$slug}.test")->first();

        if ($owner === null) {
            return;
        }

        foreach ([
            ['name' => 'New Year\'s Day', 'date' => now()->startOfYear()->format('Y-m-d')],
            ['name' => 'Independence Day', 'date' => now()->startOfYear()->addMonths(2)->format('Y-m-d')],
            ['name' => 'Labour Day', 'date' => now()->startOfYear()->addMonths(4)->format('Y-m-d')],
        ] as $holiday) {
            app(CreateHoliday::class)->handle(new HolidayData(
                name: $holiday['name'],
                date: $holiday['date'],
                isRecurring: true,
            ));
        }

        $employees = Employee::query()->where('tenant_id', $tenant->getKey())->orderBy('id')->get();
        $annual = LeaveType::query()->where('tenant_id', $tenant->getKey())->where('code', 'ANNUAL')->first();
        $sick = LeaveType::query()->where('tenant_id', $tenant->getKey())->where('code', 'SICK')->first();

        if ($annual === null || $sick === null || $employees->isEmpty()) {
            return;
        }

        foreach ($employees as $employee) {
            app(SetLeaveBalance::class)->handle(new LeaveBalanceData(
                employeeId: (int) $employee->getKey(),
                leaveTypeId: (int) $annual->getKey(),
                year: (int) now()->year,
                entitledDays: 20,
            ));
        }

        // Leave requests in three different states.
        $approvedRequest = app(RequestLeave::class)->handle(
            new LeaveRequestData(
                employeeId: (int) $employees[0]->getKey(),
                leaveTypeId: (int) $annual->getKey(),
                startDate: now()->addWeeks(1)->format('Y-m-d'),
                endDate: now()->addWeeks(1)->addDays(2)->format('Y-m-d'),
                reason: 'Family trip',
            ),
            $owner,
        );
        app(ApproveLeaveRequest::class)->handle($approvedRequest, $owner, new LeaveDecisionData(note: 'Approved'));

        $rejectedRequest = app(RequestLeave::class)->handle(
            new LeaveRequestData(
                employeeId: (int) ($employees->count() > 1 ? $employees[1]->getKey() : $employees[0]->getKey()),
                leaveTypeId: (int) $sick->getKey(),
                startDate: now()->addWeeks(2)->format('Y-m-d'),
                endDate: now()->addWeeks(2)->addDays(1)->format('Y-m-d'),
                reason: 'Medical appointment',
            ),
            $owner,
        );
        app(RejectLeaveRequest::class)->handle($rejectedRequest, $owner, new LeaveDecisionData(note: 'Insufficient notice'));

        app(RequestLeave::class)->handle(
            new LeaveRequestData(
                employeeId: (int) ($employees->count() > 2 ? $employees[2]->getKey() : $employees[0]->getKey()),
                leaveTypeId: (int) $annual->getKey(),
                startDate: now()->addWeeks(3)->format('Y-m-d'),
                endDate: now()->addWeeks(3)->addDays(1)->format('Y-m-d'),
                reason: 'Personal',
            ),
            $owner,
        );

        // A document per employee for at least the first three employees.
        foreach ($employees->take(3) as $i => $employee) {
            app(UploadEmployeeDocument::class)->handle(
                new StoreEmployeeDocumentData(
                    employeeId: (int) $employee->getKey(),
                    category: EmployeeDocumentCategory::Nid,
                    title: 'National ID',
                    expiresAt: null,
                ),
                UploadedFile::fake()->create("nid-{$i}.pdf", 3, 'application/pdf'),
                $owner,
            );
        }

        // A few days of attendance for the first two employees.
        foreach ($employees->take(2) as $employee) {
            foreach ([3, 2, 1] as $daysAgo) {
                $date = now()->subDays($daysAgo);

                app(RecordAttendance::class)->handle(
                    new RecordAttendanceData(
                        employeeId: (int) $employee->getKey(),
                        date: $date->format('Y-m-d'),
                        status: AttendanceStatus::Present,
                        checkInAt: $date->copy()->setTime(9, 0)->toIso8601String(),
                        checkOutAt: $date->copy()->setTime(17, 30)->toIso8601String(),
                        note: null,
                    ),
                    $owner,
                );
            }
        }
    }

    /**
     * Travel-desk demo data for a travel tenant: two travellers with passports,
     * a visa application mid-pipeline with its checklist, and two bookings — an
     * issued air ticket and a confirmed Umrah package (invoiced).
     */
    private function seedTravel(Tenant $tenant, string $slug): void
    {
        if (Booking::query()->where('tenant_id', $tenant->getKey())->exists()) {
            return;
        }

        $owner = User::where('email', "owner@{$slug}.test")->first();
        $customer = Customer::query()->where('tenant_id', $tenant->getKey())->first();

        if ($owner === null || $customer === null) {
            return;
        }

        $primary = app(RegisterTraveller::class)->handle(
            new TravellerData(
                customerId: $customer->getKey(),
                fullName: 'Rahim Uddin',
                gender: TravellerGender::Male,
                dateOfBirth: '1988-04-12',
                nationality: 'Bangladeshi',
                passportNumber: 'BD0123456',
                passportExpiry: now()->addYears(4)->format('Y-m-d'),
                passportIssueCountry: 'Bangladesh',
                phone: '+8801711000000',
                email: "rahim@{$slug}.test",
                address: 'Dhaka',
                notes: null,
            ),
            $owner,
        );

        $spouse = app(RegisterTraveller::class)->handle(
            new TravellerData(
                customerId: $customer->getKey(),
                fullName: 'Ayesha Rahim',
                gender: TravellerGender::Female,
                dateOfBirth: '1992-09-03',
                nationality: 'Bangladeshi',
                passportNumber: 'BD0654321',
                passportExpiry: now()->addYears(3)->format('Y-m-d'),
                passportIssueCountry: 'Bangladesh',
                phone: '+8801711000001',
                email: null,
                address: 'Dhaka',
                notes: null,
            ),
            $owner,
        );

        app(OpenVisaApplication::class)->handle(
            new VisaApplicationData(
                travellerId: $primary->getKey(),
                customerId: $customer->getKey(),
                assignedEmployeeId: null,
                destinationCountry: 'Thailand',
                visaType: 'tourist',
                mission: 'VFS Global Dhaka',
                referenceNo: 'VA-1001',
                applicationNo: null,
                governmentFee: '4500.00',
                serviceCharge: '2000.00',
                expectedTravelDate: now()->addMonths(2)->format('Y-m-d'),
            ),
            $owner,
            [
                new VisaRequirementData('Passport', true, true, now()->subDays(3)->toDateString(), null),
                new VisaRequirementData('Photograph', true, true, now()->subDays(3)->toDateString(), null),
                new VisaRequirementData('Bank statement', true, false, null, 'Awaiting from client'),
            ],
        );
        // The visa stays in `documents_pending` for the demo — the client's bank
        // statement is still outstanding on the checklist.

        $ticket = app(CreateBooking::class)->handle(
            BookingData::fromArray([
                'customer_id' => $customer->getKey(),
                'type' => 'air_ticket',
                'title' => 'DAC–BKK return, Biman',
                'supplier_name' => 'Air Consolidator BD',
                'airline' => 'Biman Bangladesh',
                'origin' => 'DAC',
                'destination' => 'BKK',
                'depart_on' => now()->addWeeks(3)->format('Y-m-d'),
                'return_on' => now()->addWeeks(4)->format('Y-m-d'),
                'cost_amount' => '82000.00',
                'sell_amount' => '90000.00',
                'commission_amount' => '2500.00',
                'passengers' => [
                    ['traveller_id' => $primary->getKey(), 'baggage' => '30kg'],
                    ['traveller_id' => $spouse->getKey(), 'baggage' => '30kg'],
                ],
                'segments' => [
                    ['flight_number' => 'BG388', 'airline' => 'Biman Bangladesh', 'from_airport' => 'DAC', 'to_airport' => 'BKK', 'depart_at' => now()->addWeeks(3)->format('Y-m-d').' 09:30:00'],
                    ['flight_number' => 'BG389', 'airline' => 'Biman Bangladesh', 'from_airport' => 'BKK', 'to_airport' => 'DAC', 'depart_at' => now()->addWeeks(4)->format('Y-m-d').' 13:00:00'],
                ],
            ]),
            $owner,
        );
        app(IssueBooking::class)->handle($ticket, 'BQ7K2P', now()->toDateString());

        $umrah = app(CreateBooking::class)->handle(
            BookingData::fromArray([
                'customer_id' => $customer->getKey(),
                'type' => 'umrah',
                'title' => 'Umrah package 14N — Makkah + Madinah',
                'supplier_name' => 'Al-Haramain Travels',
                'depart_on' => now()->addMonths(2)->format('Y-m-d'),
                'return_on' => now()->addMonths(2)->addDays(14)->format('Y-m-d'),
                'cost_amount' => '210000.00',
                'sell_amount' => '245000.00',
                'commission_amount' => '0.00',
                'passengers' => [
                    ['traveller_id' => $primary->getKey()],
                    ['traveller_id' => $spouse->getKey()],
                ],
                'hotel_stays' => [
                    ['hotel_name' => 'Makkah Grand', 'city' => 'Makkah', 'country' => 'Saudi Arabia', 'check_in' => now()->addMonths(2)->format('Y-m-d'), 'check_out' => now()->addMonths(2)->addDays(7)->format('Y-m-d'), 'room_type' => 'Quad', 'guests' => 2, 'board_basis' => 'breakfast'],
                    ['hotel_name' => 'Madinah Plaza', 'city' => 'Madinah', 'country' => 'Saudi Arabia', 'check_in' => now()->addMonths(2)->addDays(7)->format('Y-m-d'), 'check_out' => now()->addMonths(2)->addDays(14)->format('Y-m-d'), 'room_type' => 'Quad', 'guests' => 2, 'board_basis' => 'half_board'],
                ],
                'itinerary' => [
                    ['day_number' => 1, 'title' => 'Arrival at Jeddah, transfer to Makkah', 'city' => 'Makkah'],
                    ['day_number' => 8, 'title' => 'Transfer to Madinah by high-speed rail', 'city' => 'Madinah'],
                ],
            ]),
            $owner,
        );
        app(IssueBooking::class)->handle($umrah, null, now()->toDateString());
        app(RaiseInvoiceForBooking::class)->handle($umrah->refresh(), $owner, now()->toDateString(), now()->addDays(14)->toDateString());

        // Top-ups so travellers/visa_applications/bookings/booking_segments/
        // hotel_stays/package_itinerary_items each clear three rows.
        $third = app(RegisterTraveller::class)->handle(
            new TravellerData(
                customerId: $customer->getKey(),
                fullName: 'Karim Hossain',
                gender: TravellerGender::Male,
                dateOfBirth: '1995-01-20',
                nationality: 'Bangladeshi',
                passportNumber: 'BD0789012',
                passportExpiry: now()->addYears(5)->format('Y-m-d'),
                passportIssueCountry: 'Bangladesh',
                phone: '+8801711000002',
                email: null,
                address: 'Dhaka',
                notes: null,
            ),
            $owner,
        );

        app(OpenVisaApplication::class)->handle(
            new VisaApplicationData(
                travellerId: $spouse->getKey(),
                customerId: $customer->getKey(),
                assignedEmployeeId: null,
                destinationCountry: 'Saudi Arabia',
                visaType: 'umrah',
                mission: 'Saudi Embassy Dhaka',
                referenceNo: 'VA-1002',
                applicationNo: null,
                governmentFee: '3000.00',
                serviceCharge: '1000.00',
                expectedTravelDate: now()->addMonths(2)->format('Y-m-d'),
            ),
            $owner,
            [
                new VisaRequirementData('Passport', true, true, now()->subDays(1)->toDateString(), null),
            ],
        );

        app(OpenVisaApplication::class)->handle(
            new VisaApplicationData(
                travellerId: $third->getKey(),
                customerId: $customer->getKey(),
                assignedEmployeeId: null,
                destinationCountry: 'Malaysia',
                visaType: 'tourist',
                mission: 'Malaysian High Commission Dhaka',
                referenceNo: 'VA-1003',
                applicationNo: null,
                governmentFee: '2000.00',
                serviceCharge: '800.00',
                expectedTravelDate: now()->addMonths(3)->format('Y-m-d'),
            ),
            $owner,
            [
                new VisaRequirementData('Passport', true, true, now()->subDays(1)->toDateString(), null),
            ],
        );

        $secondTicket = app(CreateBooking::class)->handle(
            BookingData::fromArray([
                'customer_id' => $customer->getKey(),
                'type' => 'air_ticket',
                'title' => 'DAC–KUL return, Malindo',
                'supplier_name' => 'Air Consolidator BD',
                'airline' => 'Malindo Air',
                'origin' => 'DAC',
                'destination' => 'KUL',
                'depart_on' => now()->addMonths(3)->format('Y-m-d'),
                'return_on' => now()->addMonths(3)->addDays(6)->format('Y-m-d'),
                'cost_amount' => '38000.00',
                'sell_amount' => '43000.00',
                'commission_amount' => '1200.00',
                'passengers' => [
                    ['traveller_id' => $third->getKey(), 'baggage' => '20kg'],
                ],
                'segments' => [
                    ['flight_number' => 'OD162', 'airline' => 'Malindo Air', 'from_airport' => 'DAC', 'to_airport' => 'KUL', 'depart_at' => now()->addMonths(3)->format('Y-m-d').' 11:00:00'],
                    ['flight_number' => 'OD161', 'airline' => 'Malindo Air', 'from_airport' => 'KUL', 'to_airport' => 'DAC', 'depart_at' => now()->addMonths(3)->addDays(6)->format('Y-m-d').' 15:30:00'],
                ],
            ]),
            $owner,
        );
        app(IssueBooking::class)->handle($secondTicket, 'MH9K3L', now()->toDateString());

        app(CreateBooking::class)->handle(
            BookingData::fromArray([
                'customer_id' => $customer->getKey(),
                'type' => 'hajj',
                'title' => 'Hajj package — Makkah + Madinah',
                'supplier_name' => 'Al-Haramain Travels',
                'depart_on' => now()->addMonths(5)->format('Y-m-d'),
                'return_on' => now()->addMonths(5)->addDays(21)->format('Y-m-d'),
                'cost_amount' => '380000.00',
                'sell_amount' => '430000.00',
                'commission_amount' => '0.00',
                'passengers' => [
                    ['traveller_id' => $spouse->getKey()],
                    ['traveller_id' => $third->getKey()],
                ],
                'hotel_stays' => [
                    ['hotel_name' => 'Makkah Towers', 'city' => 'Makkah', 'country' => 'Saudi Arabia', 'check_in' => now()->addMonths(5)->format('Y-m-d'), 'check_out' => now()->addMonths(5)->addDays(10)->format('Y-m-d'), 'room_type' => 'Quad', 'guests' => 2, 'board_basis' => 'breakfast'],
                    ['hotel_name' => 'Madinah Oasis', 'city' => 'Madinah', 'country' => 'Saudi Arabia', 'check_in' => now()->addMonths(5)->addDays(10)->format('Y-m-d'), 'check_out' => now()->addMonths(5)->addDays(21)->format('Y-m-d'), 'room_type' => 'Quad', 'guests' => 2, 'board_basis' => 'half_board'],
                ],
                'itinerary' => [
                    ['day_number' => 1, 'title' => 'Arrival at Jeddah, transfer to Makkah', 'city' => 'Makkah'],
                    ['day_number' => 11, 'title' => 'Transfer to Madinah by bus', 'city' => 'Madinah'],
                ],
            ]),
            $owner,
        );
    }
}
