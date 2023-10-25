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
        $original_text = '※写真はリフォーム施行中です※・2週間以内の契約開始でFR1か月付与。その場合火災保険18，000円必須＆FR分の初回契約期間延長。';
        // $original_text = '
        // ☆早いもの勝ちキャンペーン☆ 家賃大幅値下げ致しました！！！！ 先着３名様のみ！！限定キャンペーン中です！！  １ヶ月目賃料６，０００円共益費９，０００円 ２ヶ月目賃料１６，０００円共益費９，０００円 ３ヶ月目賃料３１，０００円共益費９，０００円 （２階の部屋は、３ヶ月目だけ賃料２，０００円UPです） 
        // ※６ヶ月以上入居するのが条件になります！！！！  ぜひ１度お問い合わせ下さい☆ 初期費用クレジット決済可能になりました！  人気の川崎エリアに２０１３年オープンしたシェアハウスです！最寄りの川崎新町駅からは徒歩6分、川崎駅へのバス停から徒歩２分！都心にもアクセスが便利！ 周辺にはコンビ二やマクドナルド、ショッピングセンター（徒歩１０分）があり生活環境は十分です。
        // 内装、外装も２０１３年にフルリフォームしているので奇麗！自転車も置けます！（無料） 布団・ベッド備えているので即入居可能！☆早いもの勝ちキャンペーン☆ 家賃大幅値下
        // '; // 898 tokens
        // $original_text = '
        // ☆早いもの勝ちキャンペーン☆ 家賃大幅値下げ致しました！！！！ 先着３名様のみ！！限定キャンペーン中です！！  １ヶ月目賃料６，０００円共益費９，０００円 ２ヶ月目賃料１６，０００円共益費９，０００円 ３ヶ月目賃料３１，０００円共益費９，０００円 （２階の部屋は、３ヶ月目だけ賃料２，０００円UPです） 
        // ※６ヶ月以上入居するのが条件になります！！！！  ぜひ１度お問い合わせ下さい☆ 初期費用クレジット決済可能になりました！  人気の川崎エリアに２０１３年オープンしたシェアハウスです！最寄りの川崎新町駅からは徒歩6分、川崎駅へのバス停から徒歩２分！都心にもアクセスが便利！ 周辺にはコンビ二やマクドナルド、ショッピングセンター（徒歩１０分）があり生活環境は十分です。
        // 内装、外装も２０１３年にフルリフォームしているので奇麗！自転車も置けます！（無料） 布団・ベッド備えているので即入居可能！※写真はリフォーム施行中です※・2週間以内の契約開始でFR1か月付与。|||その場合火災保険18，000円必須＆FR分の初回契約期間延長。・違約金は初回契約期間中の解約解除で違約金賃共1か月分とFR分・外国籍は敷金1ヵ月追加＆退去時にクリーニング費10万＋税・カーテン幅約2，750×高さ約1，900mm・二重窓で電車の騒音対策済み。
        // 線路の眺望良く撮り鉄さんにもオススメ・洗濯機置き場D640×W660×H1，030mm（水栓まで）・クローゼット内幅約840、ハンガーポール芯まで高さ約1，500mm、上部収納天井まで約820mm・鍵交換費：入居者負担なし毎年3月中に解約する場合は解約日を3/1〜15の間に設定するものとする。
        // ';
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
