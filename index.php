<?php
// エラー出力
ini_set('error_reporting', E_ALL); // エラー出力レベル
ini_set('display_startup_errors', 0); // 起動シーケンスエラー出力（実運用時は0に）
ini_set('display_errors', 0); // エラーの画面出力（実運用時は0に）
ini_set('log_errors', 'on');  // エラーログ記録
ini_set('error_log', 'php.log');  //ログファイル指定

//デバッグログ関数
$debug_flg = false; //デバッグフラグ（実運用時はfalseに）
function debug($str)
{
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log($str);
  }
}
//セッションスタート
session_start();

// 変数の宣言
$players = array();
$animals = array();
$results = array();

// 方向クラス
class Direction
{
  // クラス定数
  const UP = 1;
  const RIGHT = 2;
  const DOWN = 3;
  const LEFT = 4;
}

// 抽象クラス
abstract class Creature
{
  protected $name;
  protected $hp;
  protected $attackMin; // 最大攻撃力
  protected $attackMax; // 最少攻撃力
  // 抽象メソッド
  abstract public function playAction();

  // セッター・ゲッター
  public function getName()
  {
    debug('Creature_getName()');
    return $this->name;
  }
  public function setHp($num)
  {
    debug('Creature_setHp()');
    $this->hp = $num;
  }
  public function getHp()
  {
    debug('Creature_getHP()');
    return $this->hp;
  }

  // 攻撃メソッド（共通部分を定義）
  public function attack($targetObj)
  {
    debug('Creature_attack()');
    // 攻撃力をランダムでセット
    $attackPoint = mt_rand($this->attackMin, $this->attackMax);
    if (!mt_rand(0, 9)) {
      // 10分の1の確率で攻撃力を1.5倍に（0の場合のみ実行） 
      $attackPoint = $attackPoint * 1.5;
      $attackPoint = (int) $attackPoint;
      History::set($targetObj->getName() . 'に命中した!!<br>');
    }
    $targetObj->setHp($targetObj->getHp() - $attackPoint);
    History::set($attackPoint . 'ポイントのダメージ！<br>');
  }
}
///////////////////////////////////////////////////////////////////////
// プレイヤークラス（抽象クラスを継承）
class Player extends Creature
{

  // コンストラクタ
  public function __construct($name, $hp, $attackMin, $attackMax)
  {
    $this->name = $name;
    $this->hp = $hp;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
  }

  // 抽象メソッドをオーバーライド
  public function playAction()
  {
    debug('Player_playAction()');
    $actionFlg = mt_rand(0, 5);
    switch ($actionFlg) {
      case 0:
        $action = 'くさい缶詰を開けた！';
        break;
      case 1:
        $action = 'サムいギャグを言った！';
        break;
      case 2:
        $action = 'でこぴんをした！';
        break;
      case 3:
        $action = 'くすぐった！';
        break;
      case 4:
        $action = 'モノマネをした！';
        break;
      case 5:
        $action = '子守歌を歌った！';
        break;
    }
    // 履歴を表示
    History::set($this->getName() . 'は' . $action);
  }
  // 掛け声メソッド
  public function sayStart()
  {
    debug('Player_sayStart()');
    History::set($this->getName() . '「あっち向いてー！」');
    History::set('どっちをゆびさす？');
  }
}
///////////////////////////////////////////////////////////////////////
// 動物クラス（抽象クラスを継承）
class Animal extends Creature
{
  // プロパティ
  protected $img;   // 動物画像
  protected $direction = Direction::RIGHT;   // 画像の向き
  protected $rotate = '';   // 回転角度（CSSクラスとして出力）
  protected $actionImg = ''; // アクション時の画像

  // コンストラクタ
  public function __construct($name, $hp, $img, $attackMin, $attackMax)
  {
    $this->name = $name;
    $this->hp = $hp;
    $this->img = $img;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
  }
  // ゲッター、セッター
  public function getImg()
  {
    debug('Animal_getImg()');
    if (empty($this->img)) {
      return 'img/no-img.png';
    }
    return $this->img;
  }
  public function setImg($str)
  {
    debug('Animal_setImg()');
    $this->img = $str;
  }
  public function getActionImg()
  {
    return $this->actionImg;
  }
  public function setActionImg($str)
  {
    $this->actionImg = $str;
  }

  public function getDirection()
  {
    debug('Animal_getDirection()');
    return $this->direction;
  }
  public function setDirection($num)
  {
    debug('Animal_setDirection()');
    $this->direction = $num;
  }

  public function setRotate($str)
  {
    debug('Animal_setRotate()');
    $this->rotate = $str;
  }
  public function getRotate()
  {
    debug('Animal_getRotate()');
    return $this->rotate;
  }
  //抽象メソッドをオーバーライド
  public function playAction()
  {
    debug('Animal_playAction()');
    $actionFlg = mt_rand(0, 6);
    switch ($actionFlg) {
      case 0:
        $action = '不思議な踊りを踊った！';
        break;
      case 1:
        $action = '謎の呪文を唱えた！';
        break;
      case 2:
        $action = '石ころを投げた！';
        break;
      case 3:
        $action = 'くちぶえを吹いた！';
        break;
      case 4:
        $action = '笑い出した！';
        break;
      case 5:
        $action = 'ため息をついた…';
        break;
      case 6:
        $action = 'あくびをした…';
        break;
    }
    History::set($this->getName() . 'は' . $action);
  }
  // 向きを変えるアクション
  public function changeDirection()
  {
    $this->setActionImg('');
    debug('Animal_changeDirection()');
    $beforeDirection = $this->getDirection();
    debug('最初の向き：' . $beforeDirection);

    // ランダムで向きをセット
    $this->setDirection(mt_rand(1, 4));
    $afterDirection = $this->getDirection();
    debug('回転後の向き：' . $afterDirection);

    switch ($afterDirection) {
      case Direction::UP:
        History::set($this->getName() . 'は上を向いた！');
        $this->setRotate('rotate270');
        break;
      case Direction::RIGHT:
        History::set($this->getName() . 'は右を向いた！');
        $this->setRotate('');
        break;
      case Direction::DOWN:
        History::set($this->getName() . 'は下を向いた！');
        $this->setRotate('rotate90');
        break;
      case Direction::LEFT:
        History::set($this->getName() . 'は左を向いた！');
        $this->setRotate('rotateY');
        break;
    }
  }
}
///////////////////////////////////////////////////////////////////////
// 特殊アクションをする動物クラス（親クラスを継承）
class MagicAnimal extends Animal
{
  //特殊アクション用のプロパティ（継承しないためprivate）
  private $magicAttack;

  // 子クラスのコンストラクタ
  function __construct($name, $hp, $img, $attackMin, $attackMax, $magicAttack)
  {
    // parent::で親クラスのコンストラクタを呼び出す
    parent::__construct($name, $hp, $img, $attackMin, $attackMax);
    // 子クラスのプロパティ
    $this->magicAttack = $magicAttack;
  }

  // 子クラスのゲッター、セッター
  public function getMagicAttack()
  {
    debug('MagicAnimal_getMagicAttack()');
    return $this->magicAttack;
  }

  // オーバーライド
  public function playAction()
  {
    debug('MagicAnimal_playAction()');
    $actionFlg = mt_rand(0, 3);
    if (!$actionFlg) {
      // 0の場合は親クラスのメソッドを呼び出す
      parent::playAction();
    } else {
      // 0以外の場合は特殊アクション
      switch ($actionFlg) {
        case 1:
          $action = '汗を拭きはじめた！';
          $this->setActionImg('img/action02.png');
          break;
        case 2:
          $action = 'なにかを考え始めた…';
          $this->setActionImg('img/action03.png');
          break;
        case 3:
          $action = 'たいこを叩きだした！';
          $this->setActionImg('img/action01.png');
          break;
      }
      History::set('かえるは' . $action);
    }
  }
  public function attack($targetObj)
  {
    debug('MagicAnimal_attack()');
    if (!mt_rand(0, 4)) {
      //0の場合は特殊攻撃
      History::set('泡が飛んできた！');
      $targetObj->setHp($targetObj->getHp() - $this->magicAttack);
      History::set($targetObj->getName() . 'に' . $this->magicAttack . 'ポイントのダメージ！<br>');
    } else {
      // 0以外の場合は 親クラスのメソッドを呼び出
      parent::attack($targetObj);
    }
  }
}
///////////////////////////////////////////////////////////////////////
// インターフェイス（メソッドの実装を強制）
interface HistoryInterface
{
  // 抽象メソッドを定義
  public static function set($str);
  public static function reset();
  public static function clear();
}
///////////////////////////////////////////////////////////////////////
// 履歴管理クラス
class History implements HistoryInterface
{
  // インターフェイスの抽象メソッドをオーバーライド
  public static function set($str)
  {
    debug('History_set()');
    // セッションhistoryを定義
    if (empty($_SESSION['history'])) $_SESSION['history'] = '';
    // 引数の文字列をセッションhistoryへ格納
    $_SESSION['history'] .= $str . '<br>';
    debug('history：' . $_SESSION['history']);
  }
  public static function reset()
  {
    debug('History_reset()');
    // セッションhistoryをリセット
    if (!empty($_SESSION['history'])) $_SESSION['history'] = '';
    debug('history：' . $_SESSION['history']);
  }
  public static function clear()
  {
    debug('clear()');
    // セッションhistoryをクリア
    unset($_SESSION['history']);
    debug('history：' . $_SESSION['history']);
  }
}
///////////////////////////////////////////////////////////////////////
// プレイヤーインスタンス生成
$players[] = new Player('勇者', 500, 30, 150);
$players[] = new Player('魔法使い', 600, 50, 110);
$players[] = new Player('旅人', 450, 40, 120);

// 動物インスタンス生成
$animals[] = new Animal('ひしゃくを持ったうさぎ', mt_rand(100, 120), 'img/01.png', 20, 40);
$animals[] = new Animal('買い物中のきつね', mt_rand(100, 130), 'img/02.png', 20, 60);
$animals[] = new MagicAnimal('観戦中のかえる', mt_rand(150, 180), 'img/03.png', 30, 50, mt_rand(50, 100));
$animals[] = new Animal('きつねの親子', mt_rand(150, 180), 'img/04.png', 50, 80, mt_rand(60, 120));
$animals[] = new Animal('高貴なねこ', mt_rand(100, 120), 'img/05.png', 30, 50);
$animals[] = new Animal('がちょう博士', mt_rand(100, 120), 'img/06.png', 10, 30);
$animals[] = new Animal('鹿を引くうさぎ', mt_rand(100, 130), 'img/07.png', 20, 30);
$animals[] = new Animal('収穫中のうさぎ', mt_rand(100, 130), 'img/08.png', 30, 50);
$animals[] = new MagicAnimal('巻物を持ったかえる', mt_rand(200, 250), 'img/09.png', 30, 50, mt_rand(50, 100));
$animals[] = new Animal('舞踏中のきつね', mt_rand(130, 150), 'img/10.png', 30, 50);
$animals[] = new Animal('ボクシング中のきつね', mt_rand(160, 180), 'img/11.png', 30, 50);
$animals[] = new Animal('うさぎ和尚', mt_rand(160, 180), 'img/12.png', 30, 50);
$animals[] = new Animal('はしゃいだうさぎ', mt_rand(100, 120), 'img/13.png', 30, 50);
$animals[] = new MagicAnimal('仏になったかえる', mt_rand(200, 250), 'img/14.png', 30, 50, mt_rand(50, 100));
$animals[] = new Animal('ツッコミを入れるうさぎ', mt_rand(100, 120), 'img/15.png', 30, 50);


///////////////////////////////////////////////////////////////////////
// 動物を作成
function createAnimal()
{
  debug('createAnimal()');
  global $animals;
  // 生成したインスタンスからランダムで１つ格納
  $animal = $animals[mt_rand(0, 14)];
  History::set('');
  History::set($animal->getName() . 'が現れた！');
  History::set('');
  // インスタンスをセッションに入れる
  $_SESSION['animal'] = $animal;
  // ゲームオーバー後の結果表示用
  $_SESSION['record'][] = array(
    'name' => $_SESSION['animal']->getName(),
    'img' => $_SESSION['animal']->getImg()
  );
}
// プレイヤーを作成
function createPlayer()
{
  debug('createPlayer()');
  global $players;
  $player = $players[mt_rand(0, 2)];
  // インスタンスをセッションに入れる
  $_SESSION['player'] = $player;
}
// 初期化
function init()
{
  History::clear();
  $_SESSION['winCount'] = 0;
  $_SESSION['knockDownCount'] = 0;
  $_SESSION['record'] = array();
  createPlayer();
  createAnimal();
}

// ゲームオーバー時
function gameOver()
{
  debug(print_r($_SESSION, true));
  global $results;
  global $playername;
  global $uniqueAnimal;
  $results = $_SESSION;
  $playername = $_SESSION['player']->getName();
  $uniqueAnimal = array();

  // セッションを初期化
  $_SESSION = array();
  // クッキーを削除
  if (isset($_COOKIE["PHPSESSID"])) {
    setcookie("PHPSESSID", '', time() - 1800, '/');
  }
  // セッションを破棄
  session_destroy();

  debug('$_SESSION:' . print_r($_SESSION, true));
  debug('$results:' . print_r($results, true));

  // 重複チェック用の配列（重複しなければ名前を入れる）
  $tmp = array();
  if (!empty($results['record'])) {
    foreach ($results['record'] as $val) {
      if (!in_array($val['name'], $tmp)) {
        $tmp[] = $val['name'];
        $uniqueAnimal[] = $val;
      }
    }
  }
  debug('$uniqueAnimal:' . print_r($uniqueAnimal, true));
  debug('$tmp:' . print_r($tmp, true));
}

///////////////////////////////////////////////////////////////////////
// post送信されていた場合
if (!empty($_POST)) {
  // POSTの値を代入
  if (!empty($_POST['up'])) {
    $selectDir = Direction::UP;
  } elseif (!empty($_POST['right'])) {
    $selectDir = Direction::RIGHT;
  } elseif (!empty($_POST['down'])) {
    $selectDir = Direction::DOWN;
  } elseif (!empty($_POST['left'])) {
    $selectDir = Direction::LEFT;
  } else {
    $selectDir = '';
  }
  $isStart = (!empty($_POST['start'])) ? true : false;
  $isEnd = (!empty($_POST['end'])) ? true : false;
  $isEscape = (!empty($_POST['escape'])) ? true : false;

  if ($isStart) {
    History::set('ゲームスタート！');
    debug('ゲームスタート！');
    // 初期化
    init();
    $_SESSION['player']->sayStart();
  } elseif ($isEnd) {
    debug('「やめる」が押された！');
    gameOver();
  } elseif ($isEscape) {
    debug('「逃げる」が押された！');
    History::reset();
    History::set($_SESSION['player']->getName() . 'は逃げた！');
    createAnimal();
    $_SESSION['player']->sayStart();
  } elseif (!empty($selectDir)) {
    History::reset();
    switch ($selectDir) {
      case Direction::UP:
        History::set($_SESSION['player']->getName() . 'は上を指した！');
        break;
      case Direction::RIGHT:
        History::set($_SESSION['player']->getName() . 'は右を指した！');
        break;
      case Direction::DOWN:
        History::set($_SESSION['player']->getName() . 'は下を指した！');
        break;
      case Direction::LEFT:
        History::set($_SESSION['player']->getName() . 'は左を指した！');
        break;
    }
    // 動物の向きを変更
    $_SESSION['animal']->changeDirection();
    // プレイヤーが指した向きと一致したらプレイヤーの勝ち
    if ($selectDir == $_SESSION['animal']->getDirection()) {
      History::set($_SESSION['player']->getName() . 'の勝ち！');
      History::set('');
      $_SESSION['winCount'] += 1;
      // プレイヤーの攻撃
      $_SESSION['player']->playAction();
      $_SESSION['player']->attack($_SESSION['animal']);
    } else {
      History::set($_SESSION['animal']->getName() . 'の勝ち！');
      History::set('');
      // 動物が攻撃
      $_SESSION['animal']->playAction();
      $_SESSION['animal']->attack($_SESSION['player']);
    }

    // プレイヤーのhpが0以下になったらゲームオーバー
    if ($_SESSION['player']->getHp() <= 0) {
      gameOver();
    } else {
      // 動物のhpが0以下になったら、別の動物を出現させる
      if ($_SESSION['animal']->getHp() <= 0) {
        // 履歴を表示
        History::set($_SESSION['animal']->getName() . 'を倒した！');
        createAnimal();
        $_SESSION['knockDownCount'] = $_SESSION['knockDownCount'] + 1;
        $_SESSION['player']->sayStart();
      } else {
        $_SESSION['player']->sayStart();
      }
    }
  } else {
    // 別の動物を表示
    History::set($_SESSION['animal']->getName() . 'は去っていった');
    createAnimal();
    $_SESSION['player']->sayStart();
  }
  $_POST = array();
}

?>

<!DOCTYPE html>
<html>

<head>
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-157357671-2"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'UA-157357671-2');
  </script>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta name="description" content="鳥獣たちが向く方向を当てて戦おう！" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:site" content="@harukarist" />
  <meta property="og:url" content="https://portfolio.harukarist.work/choju-acchi/" />
  <meta property="og:title" content="鳥獣戯画とあっち向いてほい！" />
  <meta property="og:description" content="鳥獣たちが向く方向を当てて戦おう！" />
  <meta property="og:image" content="https://portfolio.harukarist.work/choju-acchi/img/twitter-card.png" />
  <title>鳥獣戯画とあっち向いてほい！</title>
  <link rel="stylesheet" type="text/css" href="dist/style.css">
  <link rel="stylesheet" type="text/css" href="dist/responsive.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script type="text/javascript" async src="//platform.twitter.com/widgets.js"></script>
</head>

<body>
  <div class="container">
    <!-- トップ画面表示 -->
    <?php if (empty($_SESSION)) { ?>
      <div class="top-page">
        <h1>鳥獣戯画とあっち向いてほい！</h1>
        <!-- ゲームオーバー時 -->
        <?php if (!empty($results)) { ?>
          <div class="gameover-wrapper">
            <h2 class="top gameover">GAME OVER</h2>
            <p>PLAYER：<span class="result-num"><?php echo $playername; ?></span></p>
            <p>勝った回数：<span class="result-num"><?php echo $results['winCount']; ?></span>回</p>
            <p>倒した動物：<span class="result-num"><?php echo $results['knockDownCount']; ?></span>匹</p>

            <a href="http://twitter.com/intent/tweet?url=https://portfolio.harukarist.work/choju-acchi/&text=鳥獣戯画とあっち向いてほい！で遊んだよ → PLAYER：<?php echo $playername; ?>、勝った回数：<?php echo $results['winCount']; ?>回、倒した動物：<?php echo $results['knockDownCount']; ?>匹 &related=harukarist&hashtags=鳥獣戯画とあっち向いてほい,ダ鳥獣戯画" class="button">結果をツイート</a>

            <h2 class="top">もういちど始める？</h2>
            <form method="post">
              <input type="submit" name="start" value="ゲームスタート！" class="button">
            </form>
            <h2 class="result-title">出てきた鳥獣</h2>
            <div class="result-img-wrapper">
              <?php
              if (!empty($uniqueAnimal)) :
                foreach ($uniqueAnimal as $key => $val) :
              ?>
                  <div class="result-animal">
                    <img src="<?php echo $val['img'] ?>" alt="<?php echo $val['name'] ?>" class="result-img">
                    <p><?php echo $val['name']; ?></p>
                  </div>
              <?php
                endforeach;
              endif;
              ?>
            </div>
          </div>

          <!-- ゲームスタート時 -->
        <?php } else { ?>
          <div class="start-wrapper">
            <h2 class="top">ゲームを始める？</h2>
            <form method="post">
              <input type="submit" name="start" value="ゲームスタート！" class="button">
            </form>
            <img src="img/10.png" alt="きつね">
          </div>
        <?php } ?>
      </div>

      <!-- プレイ画面表示 -->
    <?php } else { ?>
      <div class="contents-wrapper">
        <div class="contents-left">
          <div class="animal-left">
            <div class="animal-detail">
              <h2><?php echo $_SESSION['animal']->getName(); ?></h2>
              <p>HP：<?php echo $_SESSION['animal']->getHp(); ?></p>
            </div>

            <div class="animal-img-wrapper">
              <img src="<?php echo (!empty($_SESSION['animal']->getActionImg())) ? $_SESSION['animal']->getActionImg() : $_SESSION['animal']->getImg(); ?>" class="animal-img <?php echo $_SESSION['animal']->getRotate(); ?>">
            </div>
          </div>
          <div class="animal-right">
            <div class="scroll" id="js-auto-scroll">
              <p class="history-text"><?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?></p>
            </div>
          </div>
        </div>
        <div class="contents-right">
          <div class="controller-left">
            <form method="post">
              <div class="arrow-wrapper">
                <input type="submit" name="up" value="うえ" class="arrow">
                <input type="submit" name="right" value="みぎ" class="arrow">
                <input type="submit" name="down" value="した" class="arrow">
                <input type="submit" name="left" value="ひだり" class="arrow">
                <input type="submit" name="escape" value="逃げる" class="button">
              </div>
          </div>
          <div class="controller-right">
            <div class="bottom-nav">
              <div class="player-detail">
                <h2><?php echo $_SESSION['player']->getName(); ?></h2>
                <p>残りHP：<?php echo $_SESSION['player']->getHp(); ?></p>
                <p>勝った数：<?php echo $_SESSION['winCount']; ?></p>
                <p>倒した数：<?php echo $_SESSION['knockDownCount']; ?></p>
              </div>
              <div class="menu-wrapper">
                <input type="submit" name="start" value="最初から" class="button">
                <input type="submit" name="end" value="やめる" class="button">
              </div>
            </div>
          </div>
          </form>
        </div>
      </div>
    <?php } ?>
  </div>
  <footer>
    <p class="thanks"><a href="https://chojugiga.com/" target="_blank">Special Thanks to ダ鳥獣戯画</a></p>
    <p class="copyright"><a href="https://portfolio.harukarist.work/" target="_blank">© 2020 harukarist.</a></p>
  </footer>
  <script>
    $(function() {
      // 履歴テキストをフェードイン表示
      $('.history-text').hide().fadeIn('slow');
      // animate関数で一番下の要素[0]の高さ(scrollHeight)まで自動スクロール
      $('#js-auto-scroll').animate({
        scrollTop: $('#js-auto-scroll')[0].scrollHeight
      }, 'fast');
    });
  </script>
</body>

</html>
