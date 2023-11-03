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
    public function handle(TranslateService $translateService) {

        // $param = '{
        //     "original_text": "text to translate",
        //     "langs": [
        //         {
        //             "code": "en",
        //             "lang": "English"
        //         },
        //         {
        //             "code": "vi",
        //             "lang": "Vietnam"
        //         }
        //     ]
        // }';
        
        // $inputData = json_decode($param, true);
        // $original_text = $inputData['original_text'];
        // $arrLang = [];
        // if (isset($inputData['langs']) && is_array($inputData['langs'])) {
        //     foreach ($inputData['langs'] as $langData) {
        //         if (isset($langData['code']) && isset($langData['lang'])) {
        //             $arrLang[$langData['code']] = $langData['lang'];
        //         }
        //     }
        // }
        // Log::info($original_text);
        // Log::info($arrLang);

        $arrLang = array(
            // 'en'=>'English',
            // 'vi'=>'Vietnamese',
            // 'zh'=>'Chinese',
            'ko'=>'Korean',
            'tw'=>'Taiwan',
            // 'pt'=>'Portugal'
        );
        // $original_text = '
        // ※写真はリフォーム施行中です※・2週間以内の契約開始でFR1か月付与。その場合火災保険18，000円必須＆FR分の初回契約期間延長。
        // '; // 138 tokens
        // $original_text = '
        // ☆早いもの勝ちキャンペーン☆ 家賃大幅値下げ致しました！！！！ 先着３名様のみ！！限定キャンペーン中です！！  １ヶ月目賃料６，０００円共益費９，０００円 ２ヶ月目賃料１６，０００円共益費９，０００円 ３ヶ月目賃料３１，０００円共益費９，０００円 （２階の部屋は、３ヶ月目だけ賃料２，０００円UPです） 
        // ※６ヶ月以上入居するのが条件になります！！！！  ぜひ１度お問い合わせ下さい☆ 初期費用クレジット決済可能になりました！  人気の川崎エリアに２０１３年オープンしたシェアハウスです！最寄りの川崎新町駅からは徒歩6分、川崎駅へのバス停から徒歩２分！都心にもアクセスが便利！ 周辺にはコンビ二やマクドナルド、ショッピングセンター（徒歩１０分）があり生活環境は十分です。
        // 内装、外装も２０１３年にフルリフォームしているので奇麗！自転車も置けます！（無料） 布団・ベッド備えているので即入居可能！☆早いもの勝ちキャンペーン☆ 家賃大幅値下
        // '; // 568 tokens
        $original_text = '
        1. 保証会社加入要(初回1.5万円、月額総支払額の2%/月)。2年以内の解約は違約金1ヶ月分発生。敷地内、全面禁煙。連帯保証人要。犬・猫計2匹まで飼育可。戸建賃貸物件。TVインターホン付き。
        2. 2階角部屋。収納たっぷり。買い物便利。閑静な住宅街。当店のお薦め物件。女性限定。2面採光。南向きで日当り良好。コンビニまで210mはうれしいね。スーパーへ600m 自転車での買物も便利。都市ガス使用。
        ■１０月中旬入居可能予定■解約時費用：ハウスクリーニング（1,100円／平米）・畳（琉球風畳、カラー畳など、特殊なものは除く）表替（6,050円／枚）・襖張替（3,850円／枚）・天袋張替（2,200円／枚）・障子張替（2,750円／枚）■浄水器(浄活水器)カートリッジ交換：入居中の費用は借主様負担■ペット飼育の場合
        3. ☆早いもの勝ちキャンペーン☆ 家賃大幅値下げ致しました！！！！ 先着３名様のみ！！限定キャンペーン中です！！  １ヶ月目賃料６，０００円共益費９，０００円 ２ヶ月目賃料１６，０００円共益費９，０００円 ３ヶ月目賃料３１，０００円共益費９，０００円 （２階の部屋は、３ヶ月目だけ賃料２，０００円UPです）
        ※６ヶ月以上入居するのが条件になります！！！！  ぜひ１度お問い合わせ下さい☆ 初期費用クレジット決済可能になりました！  人気の川崎エリアに２０１３年オープンしたシェアハウスです！最寄りの川崎新町駅からは徒歩6分、川崎駅へのバス停から徒歩２分！都心にもアクセスが便利！ 周辺にはコンビ二やマクドナルド
        '; // 878 tokens
        // $original_text = '
        // ☆早いもの勝ちキャンペーン☆ 家賃大幅値下げ致しました！！！！ 先着３名様のみ！！限定キャンペーン中です！！  １ヶ月目賃料６，０００円共益費９，０００円 ２ヶ月目賃料１６，０００円共益費９，０００円 ３ヶ月目賃料３１，０００円共益費９，０００円 （２階の部屋は、３ヶ月目だけ賃料２，０００円UPです） 
        // ☆早いもの勝ちキャンペーン☆ 家賃大幅値下げ致しました！！！！ 先着３名様のみ！！限定キャンペーン中です！！  １ヶ月目賃料６，０００円共益費９，０００円 ２ヶ月目賃料１６，０００円共益費９，０００円 ３ヶ月目賃料３１，０００円共益費９，０００円 （２階の部屋は、３ヶ月目だけ賃料２，０００円UPです） 
        // ※６ヶ月以上入居するのが条件になります！！！！  ぜひ１度お問い合わせ下さい☆ 初期費用クレジット決済可能になりました！  人気の川崎エリアに２０１３年オープンしたシェアハウスです！最寄りの川崎新町駅からは徒歩6分、川崎駅へのバス停から徒歩２分！都心にもアクセスが便利！ 周辺にはコンビ二やマクドナルド、ショッピングセンター（徒歩１０分）があり生活環境は十分です。
        // 内装、外装も２０１３年にフルリフォームしているので奇麗！自転車も置けます！（無料） 布団・ベッド備えているので即入居可能！※写真はリフォーム施行中です※・2週間以内の契約開始でFR1か月付与。|||その場合火災保険18，000円必須＆FR分の初回契約期間延長。・違約金は初回契約期間中の解約解除で違約金賃共1か月分とFR分・外国籍は敷金1ヵ月追加＆退去時にクリーニング費10万＋税・カーテン幅約2，750×高さ約1，900mm・二重窓で電車の騒音対策済み。
        // 線路の眺望良く撮り鉄さんにもオススメ・洗濯機置き場D640×W660×H1，030mm（水栓まで）・クローゼット内幅約840、ハンガーポール芯まで高さ約1，500mm、上部収納天井まで約820mm・鍵交換費：入居者負担なし毎年3月中に解約する場合は解約日を3/1〜15の間に設定するものとする。
        // ☆早いもの勝ちキャンペーン☆ 家賃大幅値下げ致しました！！！！ 先着３名様のみ！！限定キャンペーン中です！！  １ヶ月目賃料６，０００円共益費９，０００円 ２ヶ月目賃料１６，０００円共益費９，０００円 ３ヶ月目賃料３１，０００円共益費９，０００円 （２階の部屋は、３ヶ月目だけ賃料２，０００円UPです） 
        // ※６ヶ月以上入居するのが条件になります！！！！  ぜひ１度お問い合わせ下さい☆ 初期費用クレジット決済可能になりました！  人気の川崎エリアに２０１３年オープンしたシェアハウスです！最寄りの川崎新町駅からは徒歩6分、川崎駅へのバス停から徒歩２分！都心にもアクセスが便利！ 周辺にはコンビ二やマクドナルド、ショッピングセンター（徒歩１０分）があり生活環境は十分です。
        // 内装、外装も２０１３年にフルリフォームしているので奇麗！自転車も置けます！（無料） 布団・ベッド備えているので即入居可能！※写真はリフォーム施行中です※・2週間以内の契約開始でFR1か月付与。|||その場合火災保険18，000円必須＆FR分の初回契約期間延長。・違約金は初回契約期間中の解約解除で違約金賃共1か月分とFR分・外国籍は敷金1ヵ月追加＆退去時にクリーニング費10万＋税・カーテン幅約2，750×高さ約1，900mm・二重窓で電車の騒音対策済み。
        // '; // 1796 tokens
        try {
            $result = $translateService->translate($original_text, $arrLang);
            Log::info("*** Translated ***\n\n". json_encode($result));
        } catch (ErrorCallAPIException $e) {
            Log::info('ErrorCallAPIException.');
        } catch (ParsingAPIResponseException $e) {
            Log::info('ParsingAPIResponseException.');
        } catch (GetTranslatedTextException $e) {
            Log::info('GetTranslatedTextException.');
        }
    }
}
