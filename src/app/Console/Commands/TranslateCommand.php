<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Services\TranslateService;
use App\Exceptions\ErrorCallAPIException;
use App\Exceptions\ParsingAPIResponseException;
use App\Exceptions\GetTranslatedTextException;
use Illuminate\Support\Facades\Log;

class TranslateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:gpt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(TranslateService $translateService)
    {
        $original_text = '
        ※写真はリフォーム施行中です※・2週間以内の契約開始でFR1か月付与。その場合火災保険18，000円必須＆FR分の初回契約期間延長。
        ';
        // $original_text = '1.保証会社加入要(初回1.5万円、月額総支払額の2%/月)、2年以内の解約は違約金1ヶ月分発生、敷地内、全面禁煙。連帯保証人要、犬・猫計2匹まで飼育可、戸建賃貸物件、TVインターホン付き。';
        // $original_text = '※写真はリフォーム施行中です※・2週間以内の契約開始でFR1か月付与。1.保証会社加入要(初回1.5万円、月額総支払額の2%/月)、2年以内の解約は違約金1ヶ月分発生、';
        try {
            $translateService->translate($original_text);
        } catch (ErrorCallAPIException $e) {
            Log::info('ErrorCallAPIException.');
        } catch (ParsingAPIResponseException $e) {
            Log::info('ParsingAPIResponseException.');
        } catch (GetTranslatedTextException $e) {
            Log::info('GetTranslatedTextException.');
        }
    }
}
