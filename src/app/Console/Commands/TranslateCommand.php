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
        ※写真はリフォーム施行中です※・2週間以内の契約開始でFR1か月付与。その場合火災保険18，000円必須＆FR分の初回契約期間延長。　　・違約金は初回契約期間中の解約解除で違約金賃共1か月分とFR分・外国籍は敷金1ヵ月追加＆退去時にクリーニング費10万＋税・カーテン幅約2，750×高さ約1，900mm・二重窓で電車の騒音対策済み。線路の眺望良く撮り鉄さんにもオススメ・洗濯機置き場D640×W660×H1，030mm（水栓まで）・クローゼット内幅約840、ハンガーポール芯まで高さ約1，500mm、上部収納天井まで約820mm・鍵交換費：入居者負担なし毎年3月中に解約する場合は解約日を3/1〜15の間に設定するものとする。
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
