<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Authorization\Actions\ProvisionTenantRbac;
use App\Modules\Authorization\Roles;
use App\Modules\CRM\Actions\CreateCustomer;
use App\Modules\CRM\Data\CustomerData;
use App\Modules\Finance\Actions\CreateInvoice;
use App\Modules\Finance\Actions\RecordExpense;
use App\Modules\Finance\Actions\RecordIncome;
use App\Modules\Finance\Actions\RecordInvoicePayment;
use App\Modules\Finance\Actions\SendInvoice;
use App\Modules\Finance\Data\ExpenseData;
use App\Modules\Finance\Data\IncomeData;
use App\Modules\Finance\Data\InvoiceData;
use App\Modules\Finance\Data\InvoicePaymentData;
use App\Modules\Finance\Domain\ExpenseCategory;
use App\Modules\Finance\Domain\IncomeCategory;
use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Organization\Actions\CreateBranch;
use App\Modules\Organization\Actions\CreateDepartment;
use App\Modules\Organization\Actions\CreateDesignation;
use App\Modules\Organization\Actions\CreateEmployee;
use App\Modules\Organization\Data\BranchData;
use App\Modules\Organization\Data\DepartmentData;
use App\Modules\Organization\Data\DesignationData;
use App\Modules\Organization\Data\EmployeeData;
use App\Modules\Organization\Domain\EmploymentStatus;
use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
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

            if (Employee::query()->where('tenant_id', $tenant->getKey())->doesntExist()) {
                $staff = User::where('email', "staff@{$spec['slug']}.test")->first();
                $salesDept = Department::query()->where('code', 'SAL')->first();

                app(CreateEmployee::class)->handle(new EmployeeData(
                    userId: $staff?->getKey(),
                    branchId: null,
                    departmentId: $salesDept?->getKey(),
                    designationId: null,
                    employeeCode: 'EMP-0001',
                    firstName: 'Sample',
                    lastName: 'Employee',
                    email: "employee@{$spec['slug']}.test",
                    phone: null,
                    hireDate: now()->subYear()->format('Y-m-d'),
                    employmentStatus: EmploymentStatus::Active,
                ));
            }

            $this->seedFinance($tenant, $spec['slug']);

            $context->clear();
            $registrar->setPermissionsTeamId(null);
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
    }
}
