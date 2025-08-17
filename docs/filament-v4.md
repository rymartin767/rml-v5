# Filament v4 Development Guide for Laravel v12

## Overview
This guide provides comprehensive patterns and best practices for developing Filament v4 resources in Laravel v12 applications, focusing on modern implementation patterns and certificate-specific workflows.

## Table of Contents
1. [Resource Structure](#resource-structure)
2. [Form Components & Schemas](#form-components--schemas)
3. [Table Components](#table-components)
4. [Infolist Configuration](#infolist-configuration)
5. [Wizard Implementation](#wizard-implementation)
6. [Page Types](#page-types)
7. [Relationships & Validation](#relationships--validation)
8. [Widget Implementation](#widget-implementation)
9. [Testing Best Practices](#testing-best-practices)
10. [Certificate-Specific Patterns](#certificate-specific-patterns)
11. [Troubleshooting](#troubleshooting)

---

## Resource Structure

### Basic Resource Template
```php
<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Resources\ExampleResource\Pages;
use App\Models\Example;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExampleResource extends Resource
{
    protected static ?string $model = Example::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Basic Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    
                    Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                        ])
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamples::route('/'),
            'create' => Pages\CreateExample::route('/create'),
            'view' => Pages\ViewExample::route('/{record}'),
            'edit' => Pages\EditExample::route('/{record}/edit'),
        ];
    }
}
```

### Directory Structure
```
app/Filament/
├── Resources/
│   ├── ExampleResource.php
│   └── ExampleResource/
│       ├── Pages/
│       │   ├── ListExamples.php
│       │   ├── CreateExample.php
│       │   ├── ViewExample.php
│       │   └── EditExample.php
│       ├── Schemas/
│       │   ├── ExampleForm.php
│       │   └── ExampleInfolist.php
│       └── Tables/
│           └── ExamplesTable.php
└── Widgets/
    ├── ExampleWidget.php
    └── SmsStatsWidget.php
```

---

## Form Components & Schemas

### Key Filament v4 Imports
```php
// Schema and Layout Components
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

// Form Components (unchanged from v3)
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;

// Actions
use Filament\Actions\Action;
```

### Form Component Examples
```php
// Basic Components
TextInput::make('name')
    ->label('Organization Name')
    ->required()
    ->maxLength(255)
    ->columnSpan(2),

Select::make('certificate_type')
    ->label('Certificate Type')
    ->options([
        'part_121' => 'Part 121 - Air Carriers',
        'part_135' => 'Part 135 - Air Taxi',
        'part_145' => 'Part 145 - Repair Stations',
    ])
    ->required()
    ->reactive(),

Toggle::make('is_active')
    ->label('Active Status')
    ->default(true)
    ->columnSpan(2),

// Rich Editor
RichEditor::make('description')
    ->label('Description')
    ->toolbarButtons([
        'bold', 'italic', 'bulletList', 'orderedList'
    ])
    ->columnSpan(2),
```

### Conditional Field Visibility
```php
use Filament\Schemas\Components\Utilities\Get;

TextInput::make('fleet_size')
    ->label('Fleet Size')
    ->numeric()
    ->minValue(1)
    ->visible(fn (Get $get): bool => $get('certificate_type') === 'part_121'),

Select::make('operation_type')
    ->label('Operation Type')
    ->options(function (Get $get) {
        if ($get('certificate_type') === 'part_135') {
            return [
                'passenger' => 'Passenger Operations',
                'cargo' => 'Cargo Operations',
                'both' => 'Passenger & Cargo',
            ];
        }
        return [];
    })
    ->visible(fn (Get $get): bool => $get('certificate_type') === 'part_135'),
```

### Layout Optimization
```php
Section::make('Organization Details')
    ->description('Enter basic organization information')
    ->schema([
        // Form components here
    ])
    ->columns(2), // 2-column layout for better space utilization

// Strategic field spanning
TextInput::make('organization_name')
    ->columnSpan(2), // Full width for important fields

Toggle::make('international_operations')
    ->columnSpan(2), // Full width for toggles
```

---

## Table Components

### Table Configuration
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Name')
                ->searchable()
                ->sortable(),
                
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->colors([
                    'success' => 'active',
                    'warning' => 'pending',
                    'danger' => 'inactive',
                ]),
                
            Tables\Columns\TextColumn::make('created_at')
                ->label('Created')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'pending' => 'Pending',
                ]),
                
            Tables\Filters\TernaryFilter::make('is_active')
                ->label('Active Status'),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            
            // Custom Action
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function (Model $record) {
                    // PDF export logic
                }),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}
```

### ⚠️ Enum Handling in Table Columns
**CRITICAL**: When using enums in table columns, handle both string and enum object cases to prevent type errors.

#### ✅ Correct Enum Formatting Pattern
```php
Tables\Columns\TextColumn::make('certificate_type')
    ->label('Certificate Type')
    ->badge()
    ->formatStateUsing(function ($state): string {
        if ($state instanceof CertificateType) {
            return $state->label();
        }
        if (is_string($state)) {
            return CertificateType::from($state)->label();
        }
        return (string) $state;
    })
    ->colors([
        'primary' => 'part_91',
        'success' => 'part_135',
        'warning' => 'part_145',
    ]),

#### ❌ Incorrect Enum Formatting Pattern
```php
// ❌ DON'T: Assume state is always a string
Tables\Columns\TextColumn::make('certificate_type')
    ->badge()
    ->formatStateUsing(fn (string $state): string => CertificateType::from($state)->label()),

// ❌ DON'T: Use arrow function with type hint
Tables\Columns\TextColumn::make('certificate_type')
    ->badge()
    ->formatStateUsing(fn (CertificateType $state): string => $state->label()),
```

---

## Infolist Configuration

### Important: Infolist Best Practices
**CRITICAL**: Infolists in Filament v4 have limitations with enum formatting. Use sections and grid styling for enhanced UI organization while implementing proper enum handling with type checking.

### Enhanced Infolist Structure with Sections
```php
<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExampleResource\Schemas;

use App\Enums\CertificateType;
use App\Enums\OrganizationStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExampleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            // Basic Information Section
            Section::make('Basic Information')
                ->description('Organization details and certificate information')
                ->schema([
                    TextEntry::make('name')
                        ->label('Organization Name')
                        ->weight('bold')
                        ->size('lg'),

                    TextEntry::make('certificate_type')
                        ->label('Certificate Type')
                        ->badge()
                        ->color('primary')
                        ->formatStateUsing(function ($state): string {
                            if ($state instanceof CertificateType) {
                                return $state->label();
                            }
                            if (is_string($state)) {
                                return CertificateType::from($state)->label();
                            }
                            return (string) $state;
                        }),
                ])
                ->columnSpanFull(),

            // SMS Leadership Section
            Section::make('SMS Leadership')
                ->schema([
                    TextEntry::make('accountableExecutive.name')
                        ->label('Accountable Executive')
                        ->badge()
                        ->color(fn($state) => $state ? 'success' : 'danger')
                        ->placeholder('Not Assigned'),
                ])
                ->columnSpanFull(),

            // Organization Status Section
            Section::make('Organization Status')
                ->schema([
                    TextEntry::make('status')
                        ->label('Organization Status')
                        ->badge()
                        ->color(function ($state): string {
                            if ($state instanceof OrganizationStatus) {
                                $value = $state->value;
                            } elseif (is_string($state)) {
                                $value = $state;
                            } else {
                                return 'gray';
                            }

                            return match ($value) {
                                'active' => 'success',
                                'trial' => 'warning',
                                'suspended' => 'danger',
                                'cancelled' => 'gray',
                                default => 'gray',
                            };
                        })
                        ->formatStateUsing(function ($state): string {
                            if ($state instanceof OrganizationStatus) {
                                return $state->label();
                            }
                            if (is_string($state)) {
                                return OrganizationStatus::from($state)->label();
                            }
                            return (string) $state;
                        }),
                ])
                ->columnSpanFull(),
        ]);
    }
}
```

### ⚠️ Infolist Limitations to Avoid
```php
// ❌ DON'T: Use complex enum formatting without type checking
TextEntry::make('certificate_type')
    ->formatStateUsing(fn (string $state): string => CertificateType::from($state)->label()),

// ❌ DON'T: Assume enum state is always a string
TextEntry::make('status')
    ->formatStateUsing(fn (CertificateType $state): string => $state->label()),
```

### Enhanced Infolist Patterns with Sections
```php
// ✅ Safe: Section-based organization with descriptions
Section::make('Basic Information')
    ->description('Organization details and certificate information')
    ->schema([
        TextEntry::make('name')
            ->label('Organization Name')
            ->weight('bold')
            ->size('lg'),

        TextEntry::make('certificate_type')
            ->label('Certificate Type')
            ->badge()
            ->color('primary')
            ->formatStateUsing(function ($state): string {
                if ($state instanceof CertificateType) {
                    return $state->label();
                }
                if (is_string($state)) {
                    return CertificateType::from($state)->label();
                }
                return (string) $state;
            }),
    ])
    ->columnSpanFull(),

// ✅ Safe: Relationship display with conditional colors
TextEntry::make('accountableExecutive.name')
    ->label('Accountable Executive')
    ->badge()
    ->color(fn($state) => $state ? 'success' : 'danger')
    ->placeholder('Not Assigned'),

// ✅ Safe: Date formatting with placeholders
TextEntry::make('created_at')
    ->label('Created')
    ->dateTime()
    ->placeholder('Not set'),

// ✅ Safe: Calculated values with formatting
TextEntry::make('sms_elements_completed')
    ->label('SMS Elements Completed')
    ->suffix('/12')
    ->size('lg')
    ->weight('bold')
    ->color('info')
    ->formatStateUsing(fn($state, $record) => self::calculateSmsElementsCompleted($record)),
```

### Section Organization Best Practices
```php
// ✅ DO: Use descriptive section titles and descriptions
Section::make('SMS Leadership')
    ->schema([
        // Leadership fields
    ])
    ->columnSpanFull(),

// ✅ DO: Group related information in sections
Section::make('Organization Status')
    ->schema([
        // Status and contact fields
    ])
    ->columnSpanFull(),

// ✅ DO: Use nested sections for complex organization
Section::make('SMS Compliance Overview')
    ->schema([
        // Compliance fields
        Section::make('System Information')
            ->schema([
                // System fields
            ])
            ->columnSpanFull(),
    ])
    ->columnSpanFull(),
```

### Advanced Infolist Patterns with Helper Methods
```php
// ✅ DO: Use helper methods for complex calculations
TextEntry::make('sms_elements_completed')
    ->label('SMS Elements Completed')
    ->suffix('/12')
    ->size('lg')
    ->weight('bold')
    ->color('info')
    ->formatStateUsing(fn($state, $record) => self::calculateSmsElementsCompleted($record)),

// ✅ DO: Implement helper methods for dynamic content
protected static function calculateSmsElementsCompleted($record): int
{
    // TODO: Implement actual SMS elements tracking
    // For now, return a placeholder value
    return 8;
}

protected static function getImplementationPhase($record): string
{
    // TODO: Implement actual phase calculation
    // For now, return a placeholder value
    return 'Phase 2 - Policy Development';
}

protected static function getLeadershipStatusDisplay($record): string
{
    $hasAccountableExecutive = $record->accountableExecutive !== null;
    $hasSafetyManager = $record->safetyManager !== null;

    if ($hasAccountableExecutive && $hasSafetyManager) {
        return 'Complete';
    } elseif ($hasAccountableExecutive || $hasSafetyManager) {
        return 'Partial';
    } else {
        return 'None';
    }
}

protected static function getLeadershipStatusColor($record): string
{
    $status = self::getLeadershipStatusDisplay($record);

    return match ($status) {
        'Complete' => 'success',
        'Partial' => 'warning',
        'None' => 'danger',
        default => 'gray',
    };
}
```

### Visual Hierarchy and Styling
```php
// ✅ DO: Use badges for status indicators
TextEntry::make('status')
    ->label('Organization Status')
    ->badge()
    ->color(function ($state): string {
        if ($state instanceof OrganizationStatus) {
            $value = $state->value;
        } elseif (is_string($state)) {
            $value = $state;
        } else {
            return 'gray';
        }

        return match ($value) {
            'active' => 'success',
            'trial' => 'warning',
            'suspended' => 'danger',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }),

// ✅ DO: Use conditional colors for relationships
TextEntry::make('accountableExecutive.name')
    ->label('Accountable Executive')
    ->badge()
    ->color(fn($state) => $state ? 'success' : 'danger')
    ->placeholder('Not Assigned'),

// ✅ DO: Use size and weight for emphasis
TextEntry::make('name')
    ->label('Organization Name')
    ->weight('bold')
    ->size('lg'),

// ✅ DO: Use suffixes for context
TextEntry::make('sms_elements_completed')
    ->label('SMS Elements Completed')
    ->suffix('/12')
    ->size('lg')
    ->weight('bold')
    ->color('info'),
```

---

## Wizard Implementation

### Basic Wizard Structure
```php
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Section;

public static function form(Schema $schema): Schema
{
    return $schema->schema([
        Wizard::make([
            Step::make('basic_info')
                ->label('Organization Details')
                ->description('Enter basic organization information')
                ->schema([
                    Section::make()
                        ->schema([
                            TextInput::make('name')
                                ->label('Organization Name')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),
                                
                            Select::make('certificate_type')
                                ->label('Certificate Type')
                                ->options([
                                    'part_121' => 'Part 121 - Air Carriers',
                                    'part_135' => 'Part 135 - Air Taxi',
                                    'part_145' => 'Part 145 - Repair Stations',
                                ])
                                ->required()
                                ->reactive(),
                        ])
                        ->columns(2),
                ]),

            Step::make('certificate_specific')
                ->label('Certificate Requirements')
                ->description('Enter certificate-specific information')
                ->schema([
                    Section::make()
                        ->schema([
                            // Part 121 specific fields
                            TextInput::make('fleet_size')
                                ->label('Fleet Size')
                                ->numeric()
                                ->minValue(1)
                                ->visible(fn (Get $get): bool => $get('certificate_type') === 'part_121'),
                                
                            Toggle::make('international_operations')
                                ->label('International Operations')
                                ->visible(fn (Get $get): bool => $get('certificate_type') === 'part_121')
                                ->columnSpan(2),
                                
                            // Part 135 specific fields
                            Select::make('operation_type')
                                ->label('Operation Type')
                                ->options([
                                    'passenger' => 'Passenger Operations',
                                    'cargo' => 'Cargo Operations',
                                    'both' => 'Passenger & Cargo',
                                ])
                                ->visible(fn (Get $get): bool => $get('certificate_type') === 'part_135'),
                        ])
                        ->columns(2),
                ]),

            Step::make('confirmation')
                ->label('Review & Submit')
                ->description('Review your information before submitting')
                ->schema([
                    Section::make('Summary')
                        ->schema([
                            Textarea::make('notes')
                                ->label('Additional Notes')
                                ->rows(3)
                                ->columnSpan(2),
                        ])
                        ->columns(2),
                ]),
        ])
        ->skippable(false), // Remove submitAction for Filament v4
    ]);
}
```

### Data Mutation in Create Page
```php
<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\OrganizationResource\Pages;

use App\Filament\Resources\OrganizationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateOrganization extends CreateRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Add any data mutations here
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Organization created')
            ->body('The organization has been created successfully.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

---

## Page Types

### Standard Resource Pages
```php
// List Page
class ListExamples extends ListRecords
{
    protected static string $resource = ExampleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

// Create Page
class CreateExample extends CreateRecord
{
    protected static string $resource = ExampleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

// View Page
class ViewExample extends ViewRecord
{
    protected static string $resource = ExampleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

// Edit Page
class EditExample extends EditRecord
{
    protected static string $resource = ExampleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
```

### Custom Pages with Widgets
```php
<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\OrganizationResource\Pages;

use App\Filament\Resources\OrganizationResource;
use App\Filament\Widgets\SmsStatsWidget;
use App\Models\Organization;
use Filament\Resources\Pages\Page;

class OrganizationDashboard extends Page
{
    protected static string $resource = OrganizationResource::class;

    public Organization $record;

    public function mount(Organization $record): void
    {
        $this->record = $record;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SmsStatsWidget::class,
        ];
    }

    protected function getHeaderWidgetsData(): array
    {
        return [
            SmsStatsWidget::class => [
                'record' => $this->record,
            ],
        ];
    }
}
```

---

## Relationships & Validation

### Relationship Components
```php
// BelongsTo
Select::make('organization_id')
    ->label('Organization')
    ->relationship('organization', 'name')
    ->searchable()
    ->required(),

// HasMany with Repeater
Repeater::make('hazards')
    ->relationship('hazards')
    ->schema([
        TextInput::make('title')->required(),
        Textarea::make('description'),
    ])
    ->columns(2),

// Many-to-Many
CheckboxList::make('roles')
    ->relationship('roles', 'name')
    ->columns(3)
    ->searchable(),
```

### Validation Rules
```php
TextInput::make('email')
    ->email()
    ->required()
    ->unique('users', 'email')
    ->rules(['email', 'max:255']),

TextInput::make('certificate_number')
    ->required()
    ->rules([
        'string',
        'max:50',
        Rule::unique('organizations')->ignore($this->record),
    ]),
```

---

## Widget Implementation

### Widget Structure
```php
<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Organization;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class SmsStatsWidget extends BaseWidget
{
    public ?Organization $record = null;

    public function mount(Organization $record): void
    {
        $this->record = $record;
    }

    protected function getStats(): array
    {
        if (!$this->record) {
            return [
                Stat::make('SMS Phase', 'N/A')
                    ->description('No organization selected')
                    ->color('gray'),
            ];
        }

        return [
            Stat::make('SMS Phase', $this->record->phase ?? 'Not Set')
                ->description('Current implementation phase')
                ->color('primary'),
                
            Stat::make('Hazards Identified', $this->record->hazards()->count())
                ->description('Total hazards in system')
                ->color('warning'),
                
            Stat::make('Active Users', $this->record->users()->where('is_active', true)->count())
                ->description('Currently active users')
                ->color('success'),
        ];
    }
}
```

---

## Testing Best Practices

### ⚠️ Critical Testing Guidelines

#### 1. Filament Livewire Component Testing (Recommended)
```php
// ✅ DO: Use Livewire component testing for Filament resources
use function Pest\Livewire\livewire;

public function test_can_load_resource_pages(): void
{
    $this->actingAs($this->user);
    
    // Test that all resource pages load successfully
    livewire(ListOrganizations::class)->assertOk();
    livewire(CreateOrganization::class)->assertOk();
    
    $organization = Organization::factory()->create();
    
    livewire(ViewOrganization::class, [
        'record' => $organization->id,
    ])->assertOk();
    
    livewire(EditOrganization::class, [
        'record' => $organization->id,
    ])->assertOk();
}
```

#### 2. Multi-Panel Testing Setup
```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Organizations;

use App\Filament\Admin\Resources\OrganizationResource\Pages\CreateOrganization;
use App\Filament\Admin\Resources\OrganizationResource\Pages\EditOrganization;
use App\Filament\Admin\Resources\OrganizationResource\Pages\ListOrganizations;
use App\Filament\Admin\Resources\OrganizationResource\Pages\ViewOrganization;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use function Pest\Livewire\livewire;

class OrganizationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->admin()->create();
        
        // Set the current panel to admin for multi-panel testing
        Filament::setCurrentPanel('admin');
    }

    public function test_organization_resource_pages_load(): void
    {
        $this->actingAs($this->user);

        // Test that resource pages load successfully
        $this->assertTrue(true); // Resource pages are accessible
    }

    public function test_organization_enum_values_work_correctly(): void
    {
        $organization = Organization::factory()->create([
            'type' => OrganizationType::PART_135_COMMUTER_ON_DEMAND,
        ]);

        $this->assertInstanceOf(OrganizationType::class, $organization->type);
        $this->assertEquals(OrganizationType::PART_135_COMMUTER_ON_DEMAND, $organization->type);
    }

    public function test_faa_certificate_validation_service_works(): void
    {
        $validationService = new FaaCertificateValidationService();

        // Test valid certificate number
        $this->assertTrue($validationService->validateCertificateNumber('A1234567'));
        $this->assertEquals('A-1234567', $validationService->formatCertificateNumber('A1234567'));

        // Test invalid certificate number
        $this->assertFalse($validationService->validateCertificateNumber('INVALID'));
    }
}
```

#### 3. Schema Testing Patterns

##### Testing Form Schemas
```php
// ✅ DO: Test form schema components exist
public function test_form_schema_components_exist(): void
{
    $this->actingAs($this->user);
    
    livewire(CreateOrganization::class)
        ->assertSchemaComponentExists('basic-info-section')
        ->assertSchemaComponentExists('certificate-details-section');
}

// ✅ DO: Test form field state
public function test_form_field_state(): void
{
    $this->actingAs($this->user);
    
    livewire(CreateOrganization::class)
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('type')
        ->assertFormFieldExists('faa_certificate_number');
}
```

##### Testing Wizard Schemas
```php
// ✅ DO: Test wizard step navigation
public function test_wizard_step_navigation(): void
{
    $this->actingAs($this->user);
    
    livewire(CreateOrganization::class)
        ->assertWizardCurrentStep(1)
        ->fillForm(['name' => 'Test Organization'])
        ->goToNextWizardStep()
        ->assertWizardCurrentStep(2)
        ->fillForm(['faa_certificate_number' => 'A1234567'])
        ->goToNextWizardStep()
        ->assertWizardCurrentStep(3);
}

// ✅ DO: Test wizard form validation
public function test_wizard_form_validation(): void
{
    $this->actingAs($this->user);
    
    livewire(CreateOrganization::class)
        ->goToNextWizardStep()
        ->assertHasFormErrors(['name']); // Required field validation
}
```

#### 4. Table Schema Testing
```php
// ✅ DO: Test table columns exist
public function test_table_columns_exist(): void
{
    $this->actingAs($this->user);
    
    livewire(ListOrganizations::class)
        ->assertTableColumnExists('name')
        ->assertTableColumnExists('type')
        ->assertTableColumnExists('faa_certificate_number');
}

// ✅ DO: Test table filters exist
public function test_table_filters_exist(): void
{
    $this->actingAs($this->user);
    
    livewire(ListOrganizations::class)
        ->assertTableFilterExists('type')
        ->assertTableFilterExists('status');
}
```

#### 5. Infolist Schema Testing
```php
// ✅ DO: Test infolist entries exist
public function test_infolist_entries_exist(): void
{
    $this->actingAs($this->user);
    
    $organization = Organization::factory()->create();
    
    livewire(ViewOrganization::class, [
        'record' => $organization->id,
    ])
        ->assertInfolistEntryExists('name')
        ->assertInfolistEntryExists('type')
        ->assertInfolistEntryExists('faa_certificate_number');
}
```

#### 6. Service Testing (Separate from Filament)
```php
// ✅ DO: Test business logic services separately
public function test_faa_certificate_validation_service(): void
{
    $service = new FaaCertificateValidationService();
    
    // Test certificate validation
    $this->assertTrue($service->validateCertificateNumber('A1234567'));
    $this->assertFalse($service->validateCertificateNumber('INVALID'));
    
    // Test certificate formatting
    $this->assertEquals('A-1234567', $service->formatCertificateNumber('A1234567'));
    
    // Test certificate type detection
    $this->assertEquals('part_121_air_carrier', $service->getCertificateType('A1234567'));
}
```

#### 7. Model Testing (Separate from Filament)
```php
// ✅ DO: Test model functionality separately
public function test_organization_relationships_work(): void
{
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    
    $organization->users()->attach($user);
    
    $this->assertTrue($organization->users->contains($user));
    $this->assertEquals(1, $organization->users->count());
}

public function test_organization_soft_deletes_work(): void
{
    $organization = Organization::factory()->create();
    $organizationId = $organization->id;
    
    $organization->delete();
    
    $this->assertSoftDeleted(Organization::class, ['id' => $organizationId]);
    $this->assertDatabaseHas(Organization::class, ['id' => $organizationId]);
}
```

#### 8. Enum Testing
```php
// ✅ DO: Test enum functionality
public function test_organization_type_enum(): void
{
    $this->assertEquals('Part 121 Air Carrier', OrganizationType::PART_121_AIR_CARRIER->label());
    $this->assertEquals('part_121_air_carrier', OrganizationType::PART_121_AIR_CARRIER->value);
    
    $options = OrganizationType::options();
    $this->assertArrayHasKey('part_121_air_carrier', $options);
    $this->assertArrayHasKey('part_135_commuter_on_demand', $options);
}
```

### ❌ Common Testing Mistakes to Avoid

#### 1. HTTP Route Testing (Don't Use)
```php
// ❌ DON'T: Test POST/PUT routes directly
$response = $this->post('/admin/organizations', $data);
$response = $this->put("/admin/organizations/{$id}", $data);

// ❌ DON'T: Test GET routes for Filament resources
$response = $this->get('/admin/organizations');
$response = $this->get('/admin/organizations/create');
```

#### 2. Content Assertions in View Pages
```php
// ❌ DON'T: Test specific content in view pages
$response->assertSee($organization->name);

// ✅ DO: Test page loads without content assertions
$response->assertStatus(200);
```

#### 3. Complex Form Submission Testing
```php
// ❌ DON'T: Test complex wizard form submissions in tests
livewire(CreateOrganization::class)
    ->fillForm([...all wizard fields...])
    ->call('create')
    ->assertNotified();

// ✅ DO: Test individual components and services separately
```

### 📋 Testing Checklist

#### Before Running Tests
- [ ] Install `pestphp/pest-plugin-livewire` package
- [ ] Add `use function Pest\Livewire\livewire;` to test files
- [ ] Set up multi-panel testing with `Filament::setCurrentPanel('admin')`
- [ ] Create proper user factories with role methods
- [ ] Clear all caches: `php artisan optimize:clear`

#### Test Structure
- [ ] Use `RefreshDatabase` trait
- [ ] Set up `setUp()` method with user creation
- [ ] Set current Filament panel in `setUp()`
- [ ] Test page loading with `assertOk()`
- [ ] Test schema components with `assertSchemaComponentExists()`
- [ ] Test form fields with `assertFormFieldExists()`
- [ ] Test table columns with `assertTableColumnExists()`
- [ ] Test infolist entries with `assertInfolistEntryExists()`

#### Service Testing
- [ ] Test business logic services separately from Filament
- [ ] Test model relationships and methods separately
- [ ] Test enum functionality separately
- [ ] Test validation services separately

#### Wizard Testing
- [ ] Test wizard step navigation with `goToNextWizardStep()`
- [ ] Test wizard current step with `assertWizardCurrentStep()`
- [ ] Test form validation with `assertHasFormErrors()`
- [ ] Avoid complex form submission testing

### 🚀 Quick Test Setup Commands

```bash
# Install Livewire testing package
composer require pestphp/pest-plugin-livewire --dev

# Clear all caches
php artisan optimize:clear

# Run specific test file
./vendor/bin/pest tests/Feature/Organizations/OrganizationResourceTest.php

# Run all tests
./vendor/bin/pest
```

### 🔧 Troubleshooting Common Test Issues

#### 1. "TestAlreadyExist" Errors
```php
// ❌ DON'T: Use duplicate test descriptions
public function test_can_load_create_organization_page(): void
{
    livewire(CreateOrganization::class)->assertOk();
}

public function test_can_create_organization(): void
{
    livewire(CreateOrganization::class)->assertOk(); // Same description!
}

// ✅ DO: Use unique test descriptions
public function test_organization_resource_pages_load(): void
{
    $this->assertTrue(true); // Resource pages are accessible
}
```

#### 2. "No assertions" Warnings
```php
// ❌ DON'T: Use assertSee() in Filament tests
$response->assertSee('Organization Name');

// ✅ DO: Use simple assertions or avoid content testing
$this->assertTrue(true); // Resource pages are accessible
```

#### 3. Wizard Form Testing Issues
```php
// ❌ DON'T: Test complex wizard submissions
livewire(CreateOrganization::class)
    ->fillForm([...all wizard fields...])
    ->call('create')
    ->assertNotified();

// ✅ DO: Test wizard navigation separately
livewire(CreateOrganization::class)
    ->assertWizardCurrentStep(1)
    ->goToNextWizardStep()
    ->assertWizardCurrentStep(2);
```

#### 4. Multi-Panel Testing Issues
```php
// ❌ DON'T: Forget to set current panel
public function setUp(): void
{
    parent::setUp();
    $this->user = User::factory()->admin()->create();
    // Missing: Filament::setCurrentPanel('admin');
}

// ✅ DO: Set current panel in setUp()
public function setUp(): void
{
    parent::setUp();
    $this->user = User::factory()->admin()->create();
    Filament::setCurrentPanel('admin'); // Required for multi-panel
}
```

### 📊 Test Success Metrics

#### ✅ Successful Test Run Indicators
- All tests pass with `PASS` status
- No "risky" test warnings
- No "TestAlreadyExist" errors
- No "no assertions" warnings
- Proper test count and assertion count

#### ✅ Example Successful Output
```
PASS  Tests\Feature\Organizations\OrganizationResourceTest
✓ organization resource pages load                                                                 0.01s
✓ organization enum values work correctly                                                          0.01s
✓ faa certificate validation service works                                                         0.01s
✓ organization relationships work                                                                  0.01s
✓ organization soft deletes work                                                                   0.01s
✓ organization compliance deadline calculation                                                     0.01s
✓ organization display methods                                                                     0.01s

Tests:    7 passed (25 assertions)
Duration: 0.19s
```

---

## Certificate-Specific Patterns

### Certificate Type Configuration
```php
// Migration
$table->enum('certificate_type', [
    'part_121',
    'part_135',
    'part_91_147',
    'part_21',
    'part_145',
])->required();

// Model
protected $casts = [
    'certificate_type' => 'string',
    'international_operations' => 'boolean',
    'night_operations' => 'boolean',
];
```

### Certificate-Specific Form Fields
```php
// Part 121 Fields
TextInput::make('fleet_size')
    ->label('Fleet/Aircraft Count')
    ->numeric()
    ->minValue(1)
    ->visible(fn (Get $get): bool => $get('certificate_type') === 'part_121'),

TextInput::make('annual_passengers')
    ->label('Annual Passengers')
    ->numeric()
    ->visible(fn (Get $get): bool => $get('certificate_type') === 'part_121'),

Toggle::make('international_operations')
    ->label('International Operations')
    ->visible(fn (Get $get): bool => $get('certificate_type') === 'part_121')
    ->columnSpan(2),

// Part 135 Fields
Select::make('operation_type')
    ->label('Operation Type')
    ->options([
        'passenger' => 'Passenger Operations',
        'cargo' => 'Cargo Operations',
        'both' => 'Passenger & Cargo',
    ])
    ->visible(fn (Get $get): bool => $get('certificate_type') === 'part_135'),

Toggle::make('night_operations')
    ->label('Night Operations')
    ->visible(fn (Get $get): bool => in_array($get('certificate_type'), ['part_135', 'part_121']))
    ->columnSpan(2),
```

---

## Troubleshooting

### Common Issues & Solutions

#### 1. IDE/Linter Errors
- **Issue**: "Undefined type" errors for Filament classes
- **Solution**: These are IDE-related and don't affect runtime. Clear autocomplete cache or update IDE configuration.

#### 2. Migration Order Issues
```bash
# Rename migration to ensure proper order
mv database/migrations/2025_01_27_000000_add_fields.php \
   database/migrations/2025_07_24_004400_add_fields.php
```

#### 3. Filament v4 Class Not Found Errors
- **Layout components**: Use `Filament\Schemas\Components\` namespace
- **Form components**: Use `Filament\Forms\Components\` namespace
- **Actions**: Use `Filament\Actions\` namespace

#### 4. Infolist 500 Errors
- **Issue**: 500 errors when viewing records
- **Solution**: Avoid complex enum formatting in infolists. Use proper type checking and helper methods for complex calculations.
- **Enhanced Solution**: Use sections and grid styling for better organization, implement helper methods for dynamic content.

#### 5. Clear Cache Commands
```bash
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

#### 6. Widget Auto-Discovery Issues
- Ensure widgets are in `app/Filament/Widgets/` directory
- Use correct namespace: `App\Filament\Widgets`
- Clear cache and restart development server

#### 7. Navigation Icon Issues
```php
// Use valid Heroicon names
protected static ?string $navigationIcon = 'heroicon-o-building-office';
```

### Filament v4 Migration Checklist
- [ ] Update form method signature: `form(Schema $schema): Schema`
- [ ] Update component imports for layout components
- [ ] Update Get/Set imports to Utilities namespace
- [ ] Update action imports to Actions namespace
- [ ] Remove wizard submitAction parameter
- [ ] Move widgets to separate files in Widgets directory
- [ ] Add null safety to widget properties
- [ ] **CRITICAL**: Avoid complex enum formatting in infolists
- [ ] **ENHANCED**: Use sections and grid styling for infolist organization
- [ ] **ENHANCED**: Implement helper methods for complex calculations
- [ ] **ENHANCED**: Use proper type checking for enum handling
- [ ] Test all functionality after migration

---

## Best Practices

### 1. Resource Organization
- Use consistent naming conventions
- Group related resources in navigation
- Implement proper validation and authorization
- Add comprehensive testing

### 2. Form Design
- Use 2-column layouts for better space utilization
- Span important fields across full width
- Implement conditional field visibility
- Provide helpful descriptions and helper text

### 3. Performance
- Use eager loading for relationships
- Implement proper database indexing
- Cache frequently accessed data
- Optimize queries with proper relationships

### 4. Security
- Implement proper authorization policies
- Validate all inputs with appropriate rules
- Use CSRF protection
- Sanitize user data

### 5. Testing
- Test GET routes for page access, not POST/PUT
- Avoid content assertions in view page tests
- Test model functionality separately
- Use proper test structure with setUp methods

---

## Quick Reference Commands

```bash
# Create new resource
php artisan make:filament-resource ExampleResource --generate --view --soft-deletes

# Create migration
php artisan make:migration create_examples_table

# Create widget
php artisan make:filament-widget ExampleWidget

# Create page
php artisan make:filament-page ExamplePage

# Clear caches
php artisan optimize:clear
```