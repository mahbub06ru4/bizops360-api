<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Authorization\Actions\ProvisionTenantRbac;
use App\Modules\Authorization\Roles;
use App\Modules\CRM\Actions\CreateCustomer;
use App\Modules\CRM\Data\CustomerData;
use App\Modules\CRM\Models\Customer;
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

            if ($spec['industry'] === 'travel') {
                $this->seedTravel($tenant, $spec['slug']);
            }

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
    }
}
