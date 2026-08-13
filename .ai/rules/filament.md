# Filament v5 Conventions & Rules

## Application Structure

Filament v5 introduces a modular structure separating resource classes, schemas, and tables into dedicated folders:

```
app/Filament/Resources/<ModelPlural>/
├── <Model>Resource.php
├── Pages/
│   ├── Create<Model>.php
│   ├── Edit<Model>.php
│   └── List<ModelPlural>.php
├── Schemas/
│   └── <Model>Form.php
└── Tables/
    └── <ModelPlural>Table.php
```

## Resource Definition

Resource classes inherit from `Filament\Resources\Resource` and delegate form and table creation to the modular classes:

```php
namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\Tables\ServicesTable;
use App\Models\Service;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
    }
}
```

## Schemas & Forms (`Filament\Schemas\Schema`)

- **Schema Class**: Use `Filament\Schemas\Schema` instead of `Filament\Forms\Form`.
- **Method Signature**: `public static function configure(Schema $schema): Schema`
- **Layout Containers**: Use `Filament\Schemas\Components\*` (`Section`, `Grid`, `Group`, `Fieldset`, `Tabs`, `Wizard`).
- **Inputs & Fields**: Use `Filament\Forms\Components\*` (`TextInput`, `Select`, `FileUpload`, `DatePicker`, `Textarea`, `Toggle`).
- **Utilities (`Get` / `Set`)**: Use `Filament\Schemas\Components\Utilities\Get` and `Filament\Schemas\Components\Utilities\Set` (replaces legacy `Filament\Forms\Get` / `Filament\Forms\Set`).
- **Relationships**: Always use `Select::make('foreignId')->relationship('relationName', 'titleAttribute')->searchable()->preload()` for foreign key inputs.

Example:
```php
namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                Select::make('categoryId')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                FileUpload::make('photo')->image()->directory('services'),
            ]);
    }
}
```

## Tables & Actions (`Filament\Tables\Table`)

- **Table Class**: Use `Filament\Tables\Table`.
- **Method Signature**: `public static function configure(Table $table): Table`
- **Actions Namespace**: `Filament\Actions\*` (`EditAction`, `DeleteAction`, `BulkActionGroup`, `DeleteBulkAction`).
- **Record Actions**: Configured via `->recordActions([...])` (replaces legacy `->actions([...])`).
- **Toolbar Actions**: Configured via `->toolbarActions([...])` (replaces legacy `->bulkActions([...])`).
- **Columns**:
  - Format monetary values: `TextColumn::make('price')->money('EUR')`
  - Relationship columns: `TextColumn::make('category.name')`
  - Badges with dynamic colors: `TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) ...)`
  - Images: `ImageColumn::make('photo')->circular()`

Example:
```php
namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')->circular(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

## Navigation Icons

Navigation icons use BackedEnums from `Filament\Support\Icons\Heroicon`:
```php
use Filament\Support\Icons\Heroicon;

protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
```
