<?php

namespace App\Filament\Resources\Appointments\Widgets;

use App\Filament\Resources\Appointments\Schemas\AppointmentForm;
use App\Models\Appointment;
use App\Models\Barber;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class AppointmentCalendarWidget extends CalendarWidget
{
    protected CalendarViewType $calendarView = CalendarViewType::ResourceTimeGridDay;

    protected bool $eventClickEnabled = true;

    protected bool $dateClickEnabled = true;

    protected bool $dateSelectEnabled = true;

    protected bool $eventDragEnabled = true;

    protected bool $eventResizeEnabled = true;

    protected ?string $defaultEventClickAction = 'edit';

    protected ?string $locale = 'fr';

    protected string|HtmlString|bool|null $heading = null;

    #[On('refreshAppointmentPlanner')]
    #[On('appointment-updated')]
    #[On('appointment-created')]
    #[On('filament-tables::refresh')]
    #[On('close-modal')]
    public function refreshCalendar(): void
    {
        $this->refreshRecords();
        $this->refreshResources();
    }

    public function getOptions(): array
    {
        return [
            'slotMinTime' => '07:00:00',
            'slotMaxTime' => '22:00:00',
            'slotDuration' => '00:30:00',
            'slotHeight' => 72,
            'slotLabelInterval' => '01:00:00',
            'allDaySlot' => false,
            'scrollTime' => '07:00:00',
            'views' => [
                'resourceTimeGridDay' => [
                    'slotMinTime' => '07:00:00',
                    'slotMaxTime' => '22:00:00',
                    'slotDuration' => '00:30:00',
                    'slotHeight' => 72,
                    'allDaySlot' => false,
                ],
                'resourceTimeGridWeek' => [
                    'slotMinTime' => '07:00:00',
                    'slotMaxTime' => '22:00:00',
                    'slotDuration' => '00:30:00',
                    'slotHeight' => 72,
                    'allDaySlot' => false,
                ],
                'timeGridDay' => [
                    'slotMinTime' => '07:00:00',
                    'slotMaxTime' => '20:00:00',
                    'slotDuration' => '00:30:00',
                    'slotHeight' => 72,
                    'allDaySlot' => false,
                ],
                'timeGridWeek' => [
                    'slotMinTime' => '07:00:00',
                    'slotMaxTime' => '22:00:00',
                    'slotDuration' => '00:30:00',
                    'slotHeight' => 72,
                    'allDaySlot' => false,
                ],
            ],
        ];
    }

    public function getResources(): Collection|array|Builder
    {
        return Barber::canPerformServices()->get();
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        return Appointment::with(['client', 'barber', 'service'])
            ->whereDate('date', '>=', $info->start->toDateString())
            ->whereDate('date', '<=', $info->end->toDateString());
    }

    public function appointmentSchema(Schema $schema): Schema
    {
        return AppointmentForm::configure($schema);
    }

    public function defaultSchema(Schema $schema): Schema
    {
        return AppointmentForm::configure($schema);
    }

    public function createAppointmentAction(): CreateAction
    {
        return $this->createAction(Appointment::class)
            ->label('Nouveau rendez-vous')
            ->slideOver()
            ->mountUsing(function (?DateClickInfo $dateClick, ?DateSelectInfo $dateSelect, ?Schema $schema): void {
                $data = [];

                if ($dateClick) {
                    $start = $dateClick->date;
                    $data['date'] = $start->toDateString();
                    $data['startTime'] = $start->format('H:i');
                    if ($dateClick->resource) {
                        $data['barberId'] = $dateClick->resource->getId();
                    }
                } elseif ($dateSelect) {
                    $start = $dateSelect->start;
                    $end = $dateSelect->end;
                    $data['date'] = $start->toDateString();
                    $data['startTime'] = $start->format('H:i');
                    $data['endTime'] = $end->format('H:i');
                    if ($dateSelect->resource) {
                        $data['barberId'] = $dateSelect->resource->getId();
                    }
                }

                $schema?->fill($data);
            })
            ->after(function (): void {
                Notification::make()
                    ->title('Rendez-vous créé avec succès')
                    ->success()
                    ->send();

                $this->dispatch('refreshAppointmentPlanner');
                $this->dispatch('filament-tables::refresh');
            });
    }

    protected function onDateClick(DateClickInfo $info): void
    {
        $this->mountAction('createAppointment');
    }

    protected function onDateSelect(DateSelectInfo $info): void
    {
        $this->mountAction('createAppointment');
    }

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        $newStart = $info->event->getStart();
        $newEnd = $info->event->getEnd();
        $newResourceIds = $info->event->getResourceIds();

        $updates = [
            'date' => $newStart->toDateString(),
            'startTime' => $newStart->format('H:i'),
            'endTime' => $newEnd->format('H:i'),
        ];

        if (! empty($newResourceIds)) {
            $updates['barberId'] = $newResourceIds[0];
        }

        $event->update($updates);

        Notification::make()
            ->title('Rendez-vous replanifié avec succès')
            ->success()
            ->send();

        $this->dispatch('refreshAppointmentPlanner');
        $this->dispatch('filament-tables::refresh');

        return true;
    }

    protected function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        $newStart = $info->event->getStart();
        $newEnd = $info->event->getEnd();

        $event->update([
            'date' => $newStart->toDateString(),
            'startTime' => $newStart->format('H:i'),
            'endTime' => $newEnd->format('H:i'),
        ]);

        Notification::make()
            ->title('Durée du rendez-vous ajustée')
            ->success()
            ->send();

        $this->dispatch('refreshAppointmentPlanner');
        $this->dispatch('filament-tables::refresh');

        return true;
    }

    protected function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction()->slideOver(),
            $this->editAction()->slideOver(),
            $this->deleteAction(),
        ];
    }
}
