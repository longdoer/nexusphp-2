<?php

namespace App\Filament\Resources\System\SettingResource\Pages;

use App\Filament\OptionsTrait;
use App\Filament\Resources\System\SettingResource;
use App\Models\HitAndRun;
use App\Models\Setting;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\DB;
use Nexus\Database\NexusDB;

class EditSetting extends Page implements Forms\Contracts\HasForms
{
    use InteractsWithForms, OptionsTrait;

    protected static string $resource = SettingResource::class;

    protected static string $view = 'filament.resources.system.setting-resource.pages.edit-hit-and-run';

    protected function getTitle(): string
    {
        return __('label.setting.nav_text');
    }

    public function mount()
    {
        $settings = Setting::get();
        $this->form->fill($settings);

    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make('Heading')
                ->tabs([
                    Forms\Components\Tabs\Tab::make(__('label.setting.hr.tab_header'))
                        ->schema([
                            Forms\Components\Radio::make('hr.mode')->options(HitAndRun::listModes(true))->inline(true)->label(__('label.setting.hr.mode')),
                            Forms\Components\TextInput::make('hr.inspect_time')->helperText(__('label.setting.hr.inspect_time_help'))->label(__('label.setting.hr.inspect_time'))->integer(),
                            Forms\Components\TextInput::make('hr.seed_time_minimum')->helperText(__('label.setting.hr.seed_time_minimum_help'))->label(__('label.setting.hr.seed_time_minimum'))->integer(),
                            Forms\Components\TextInput::make('hr.ignore_when_ratio_reach')->helperText(__('label.setting.hr.ignore_when_ratio_reach_help'))->label(__('label.setting.hr.ignore_when_ratio_reach'))->integer(),
                            Forms\Components\TextInput::make('hr.ban_user_when_counts_reach')->helperText(__('label.setting.hr.ban_user_when_counts_reach_help'))->label(__('label.setting.hr.ban_user_when_counts_reach'))->integer(),
                        ])->columns(2),
                    Forms\Components\Tabs\Tab::make(__('label.setting.backup.tab_header'))
                        ->schema([
                            Forms\Components\Radio::make('backup.enabled')->options(self::$yesOrNo)->inline(true)->label(__('label.setting.backup.enabled'))->helperText(__('label.setting.backup.enabled_help')),
                            Forms\Components\Radio::make('backup.frequency')->options(['daily' => 'daily', 'hourly' => 'hourly'])->inline(true)->label(__('label.setting.backup.frequency'))->helperText(__('label.setting.backup.frequency_help')),
                            Forms\Components\Select::make('backup.hour')->options(range(0, 23))->label(__('label.setting.backup.hour'))->helperText(__('label.setting.backup.hour_help')),
                            Forms\Components\Select::make('backup.minute')->options(range(0, 59))->label(__('label.setting.backup.minute'))->helperText(__('label.setting.backup.minute_help')),
                            Forms\Components\TextInput::make('backup.google_drive_client_id')->label(__('label.setting.backup.google_drive_client_id')),
                            Forms\Components\TextInput::make('backup.google_drive_client_secret')->label(__('label.setting.backup.google_drive_client_secret')),
                            Forms\Components\TextInput::make('backup.google_drive_refresh_token')->label(__('label.setting.backup.google_drive_refresh_token')),
                            Forms\Components\TextInput::make('backup.google_drive_folder_id')->label(__('label.setting.backup.google_drive_folder_id')),
                            Forms\Components\Radio::make('backup.via_ftp')->options(self::$yesOrNo)->inline(true)->label(__('label.setting.backup.via_ftp'))->helperText(__('label.setting.backup.via_ftp_help')),
                            Forms\Components\Radio::make('backup.via_sftp')->options(self::$yesOrNo)->inline(true)->label(__('label.setting.backup.via_sftp'))->helperText(__('label.setting.backup.via_sftp_help')),
                        ])->columns(2),
                ])
        ];
    }

    public function submit()
    {
        $formData = $this->form->getState();
        $notAutoloadNames = ['donation_custom'];
        $data = [];
        foreach ($formData as $prefix => $parts) {
            foreach ($parts as $name => $value) {
                if (is_null($value)) {
                    continue;
                }
                if (in_array($name, $notAutoloadNames)) {
                    $autoload = 'no';
                } else {
                    $autoload = 'yes';
                }
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $data[] = [
                    'name' => "$prefix.$name",
                    'value' => $value,
                    'autoload' => $autoload,
                ];

            }
        }

        Setting::query()->upsert($data, ['name'], ['value']);
        NexusDB::cache_del('nexus_settings_in_laravel');
        NexusDB::cache_del('nexus_settings_in_nexus');

        $this->notify('success', __('filament::resources/pages/edit-record.messages.saved'));
    }

}
