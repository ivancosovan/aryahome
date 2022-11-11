<? CMax::checkRestartBuffer(); ?>
<? IncludeTemplateLangFile(__FILE__); ?>
<? if (!$isIndex): ?>
    <? if ($isHideLeftBlock && !$isWidePage): ?>
        </div> <? // .maxwidth-theme?>
    <? endif; ?>
    </div> <? // .container?>
<? else: ?>
    <? CMax::ShowPageType('indexblocks'); ?>
<? endif; ?>
<? CMax::get_banners_position('CONTENT_BOTTOM'); ?>
</div> <? // .middle?>
<? //if(($isIndex && $isShowIndexLeftBlock) || (!$isIndex && !$isHideLeftBlock) && !$isBlog):?>
<? if (($isIndex && ($isShowIndexLeftBlock || $bActiveTheme)) || (!$isIndex && !$isHideLeftBlock)): ?>
    </div> <? // .right_block?>
    <? if ($APPLICATION->GetProperty("HIDE_LEFT_BLOCK") != "Y" && !defined("ERROR_404")): ?>
        <? CMax::ShowPageType('left_block'); ?>
    <? endif; ?>
<? endif; ?>
</div> <? // .container_inner?>
<? if ($isIndex): ?>
    </div>
<? elseif (!$isWidePage): ?>
    </div> <? // .wrapper_inner?>
<? endif; ?>
</div> <? // #content?>
<? CMax::get_banners_position('FOOTER'); ?>
</div><? // .wrapper?>

<footer id="footer">
    <? include_once(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . '/' . SITE_DIR . 'include/footer_include/under_footer.php')); ?>
    <? include_once(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . '/' . SITE_DIR . 'include/footer_include/top_footer.php')); ?>
</footer>


<script data-skip-moving="true">
    (function (w, d, u, i, o, s, p) {
        if (d.getElementById(i)) {
            return;
        }
        w['MangoObject'] = o;
        w[o] = w[o] || function () {
            (w[o].q = w[o].q || []).push(arguments)
        };
        w[o].u = u;
        w[o].t = 1 * new Date();
        s = d.createElement('script');
        s.async = 1;
        s.id = i;
        s.src = u;
        s.charset = 'utf-8';
        p = d.getElementsByTagName('script')[0];
        p.parentNode.insertBefore(s, p);
    }(window, document, '//widgets.mango-office.ru/widgets/mango.js', 'mango-js', 'mgo'));
    mgo({multichannel: {id: 12005}});
</script>

<? include_once(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . '/' . SITE_DIR . 'include/footer_include/bottom_footer.php')); ?>


</body>
</html>
