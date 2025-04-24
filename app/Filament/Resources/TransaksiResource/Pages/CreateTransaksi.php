<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use App\Models\transaksi;
use Filament\Actions;
use Filament\Actions\Action;

use Filament\Forms\Components\Section;
use Filament\Forms\Form;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateTransaksi extends CreateRecord
{
    use HasWizard;

    protected static string $resource = TransaksiResource::class;

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false),
            ])
            ->columns(null);
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Detail Pemesanan')
                ->schema([
                    Section::make()->schema(TransaksiResource::getDetailsFormSchema())->columns(),
                    ...TransaksiResource::getShippingAndServiceSchema(),
                ]),

            Step::make('Detail Item')
                ->schema([
                    Section::make()->schema([
                        
                        TransaksiResource::getItemsRepeater(),
                    ]),
                ]),
        ];
    }
}
