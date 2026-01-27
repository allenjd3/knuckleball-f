<?php

namespace App\Livewire;

use App\Forms\Schema\FeeForm;
use App\Models\Fee;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\EditAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class EditFee extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Fee $fee;

    public function render()
    {
        return view('livewire.edit-fee');
    }

    public function editFeeAction(): Action
    {
        return EditAction::make('editFee')
            ->link()
            ->authorize(fn () => request()->user()?->can('update', $this->fee))
            ->record($this->fee)
            ->schema(FeeForm::schema())
            ->using(function (Fee $record, $data) {
                $record->update($data);

                $this->dispatch('feeUpdated');
            });
    }
}
