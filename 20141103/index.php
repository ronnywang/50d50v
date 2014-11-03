<?php
$list = array(
    array("連勝文", "陳汝斌", "柯文哲", "馮光遠", "陳永昌", "李宏信", "趙衍慶",),
    array("朱立倫", "游錫堃", "李進順",),
    array("吳志揚", "鄭文燦", "許睿智",),
    array("胡志強", "林佳龍",),
    array("黃秀霜", "賴清德",),
    array("楊秋興", "陳菊", "周可盛",),
);
$sources = array(
    1 => '蘋果',
    2 => '中時',
    3 => '中央社',
    4 => '東森',
    5 => '自由',
    6 => '新頭殼',
    7 => 'NowNews',
    8 => '聯合',
    9 => 'TVBS',
    10 => '中廣新聞網',
    11 => '公視新聞網',
    12 => '台視',
    13 => '華視',
    14 => '民視',
    //            15 => '三立',
    16 => '風傳媒',
);
$cities = array(
    "臺北市", "新北市", "桃園市", "臺中市", "臺南市", "高雄市",
);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>[50d50v] 各新聞媒體關鍵字標籤雲</title>
<meta property="og:title" content="[50d50v] 六都候選人新聞標籤雲">
<meta property="og:description" content="這是 50 天 50 張選舉圖表 在 2014/11/03 的圖表，將各媒體在針對單一候選人的新聞的標題關鍵字取出來做的標籤雲">
<meta property="og:image" content="http://pic.pimg.tw/ronnywang/1415011000-3937590392.png">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="http://timdream.org/wordcloud/assets/wordfreq/src/wordfreq.worker.js?_=i1zux04c"></script>
<script src="http://timdream.org/wordcloud2.js/src/wordcloud2.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.0/css/bootstrap.css">
<style>
.choosed {
    background-color: orange;
}
</style>
</head>
<body style="padding-top: 50px;">
<?php if (getenv('GOOGLEANALYTICS_ACCOUNT')) { ?>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', <?= json_encode(getenv('GOOGLEANALYTICS_ACCOUNT')) ?>]);
_gaq.push(['_trackPageview']);

(function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
<?php } ?>
<script>
$(function(){
    $(window).resize(function(){
        var canvas = $('#my_canvas')[0];
        canvas.width = $('#left-col').width();
        canvas.height = Math.floor(3 * $('#left-col').width() / 4);
    }).resize();

    $(window).bind('hashchange', function(){
        var terms = document.location.hash.substr(1).split('/');
        if (terms.length < 3) {
            return;
        }
        $.get('<?= getenv('API_PREFIX') ?>/api.php?name=' + encodeURIComponent(terms[0]) + '&time=' + parseInt(terms[1]) + '&source=' + parseInt(terms[2]), function(res){
            var worker = WordFreqSync();
            var list = worker.process(res.results.map(function(a){ return a.title; }).join("\n"));
            $('.source').each(function(){
                $('#count-' + $(this).data('source')).text(parseInt('0' + res.sources[$(this).data('source')]));
            });
            var max = list.length ? list[0][1] : 0;
            $('#keywords').html('');
            for (var i = 0; i < Math.min(10, list.length); i ++) {
                $('#keywords').append($('<li></li>').text(list[i][0] + ':' + list[i][1]));
            }
            $('#my_canvas')[0].getContext('2d').clearRect(0, 0, 640, 480);
            WordCloud(document.getElementById('my_canvas'), { list: list, clearCanvas: true, weightFactor: function(s){
                return s * 100 / max;
            } } );
        }, 'json');
    });

    if (document.location.hash) {
        var terms = document.location.hash.substr(1).split('/');
        if (terms.length == 3) {
            $('.user, .date, .source').removeClass('choosed');
            $('#user-' + terms[0]).addClass('choosed');
            $('#date-' + terms[1]).addClass('choosed');
            $('#source-' + terms[2]).addClass('choosed');
            $(window).trigger('hashchange');
        }
    }

    $('.user,.date,.source').click(function(e){
        e.preventDefault();
        if ($(this).is('.user')) {
            $('.user').removeClass('choosed');
            $(this).addClass('choosed');
        } else if ($(this).is('.date')) {
            $('.date').removeClass('choosed');
            $(this).addClass('choosed');
        } else if ($(this).is('.source')) {
            $('.source').removeClass('choosed');
            $(this).addClass('choosed');
        }
        if (!$('.date.choosed').size() || !$('.user.choosed').size()) {
            return;
        }
        document.location.hash = '#' + $('.user.choosed').text() + '/' + $('.date.choosed').text() + '/' + $('.source.choosed').data('source');
    });
});
</script>
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="/">六都候選人新聞標籤雲</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="/">首頁</a></li>
                <li><a href="https://www.facebook.com/50d50v">50 天 50 張選舉圖表</a></li>
                <li><a href="https://github.com/ronnywang/50d50v/tree/master/20141103">程式碼與資料</a></li>
                <li><a href="http://ronny.tw/data">About Ronny</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</div>
<div class="container">
<div class="row">
    <div class="col-md-6" id="left-col">
        <h2>候選人</h2>
        <table class="table">
            <?php foreach ($cities as $index => $city) { ?>
            <tr>
                <td><?= $city ?></td>
                <?php foreach ($list[$index] as $name) { ?>
                <td class="user" id="user-<?= $name ?>"><?= $name ?></td>
                <?php } ?>
            </tr>
            <?php } ?>
        </table>
        <canvas id="my_canvas"></canvas>
    </div>
    <div class="col-md-3">
        <h1>月份</h1>
        <ul>
            <?php foreach (range(201405, 201410) as $i => $date) { ?>
            <li><span class="date<?= 201410 == $date ? ' choosed' : '' ?>" id="date-<?= $date ?>"><?= $date ?></span></li>
            <?php } ?>
        </ul>
        <hr>
        <h1>關鍵字出現次數</h1>
        <ul id="keywords">
        </ul>
    </div>
    <div class="col-md-3">
        <h1>媒體來源</h1>
        <ul>
            <li><span class="source choosed" data-source="0">全部</span>(<span id="count-0"></span>)</li>
            <?php foreach ($sources as $id => $source) { ?>
            <li><span class="source" data-source="<?= $id ?>" id="source-<?= $id ?>"><?= $source ?></span>(<span id="count-<?= $id ?>"></span>)</li>
            <?php } ?>
        </ul>
    </div>
</div>
</div>
</body>
</html> 
