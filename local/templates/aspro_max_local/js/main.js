var basketTimeoutSlide;
var resizeEventTimer;

var clicked_tab = 0;

if (typeof arAsproOptions === "undefined") {
  var arAsproOptions = {
    SITE_DIR: "/",
  };
  if ($("body").data("site") !== undefined) arAsproOptions["SITE_DIR"] = $("body").data("site");
}

function readyDOM(callback) {
  if (document.readyState !== "loading") callback();
  else document.addEventListener("DOMContentLoaded", callback);
}

var typeofExt = function (item) {
  const _toString = Object.prototype.toString;
  return _toString.call(item).slice(8, -1).toLowerCase();
};

var InitLazyLoad = function () {};

$(document).on("change", ".uploader input[type=file]", function () {
  if (!$(this).next().length || !$(this).next().hasClass("resetfile")) {
    $(
      '<span class="resetfile"><svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11"><path d="M1345.19,376.484l4.66,4.659a0.492,0.492,0,0,1,0,.707,0.5,0.5,0,0,1-.71,0l-4.66-4.659-4.65,4.659a0.5,0.5,0,0,1-.71,0,0.492,0.492,0,0,1,0-.707l4.66-4.659-4.66-4.659a0.492,0.492,0,0,1,0-.707,0.5,0.5,0,0,1,.71,0l4.65,4.659,4.66-4.659a0.5,0.5,0,0,1,.71,0,0.492,0.492,0,0,1,0,.707Z" transform="translate(-1339 -371)"/></svg></span>'
    ).insertAfter($(this));
  }
});

$(document).on("click", ".uploader.files_add input[type=file] + .resetfile", function () {
  var $input = $(this).prev();
  $input.val("");
  $.uniform.update($input);
  $(this).remove();
});

$(document).on("click", ".top_block h3", function () {
  if (window.matchMedia("(max-width: 550px)").matches) {
    var $this = $(this);
    var a = $this.siblings("a");
    if (a.length) {
      a[0].click();
    }
  }
});

$(document).on("click", ".bx-yandex-view-layout .yandex-map__mobile-opener", function () {
  if ($(this).hasClass("closer")) {
    closeYandexMap();
  } else {
    openYandexMap(this);
  }
});

function openYandexMap(element) {
  var $this = $(element);
  if ($this.hasClass("closer")) return;
  var currentMap = $this.parents(".bx-yandex-view-layout");
  var mapId = currentMap.find(".bx-yandex-map").attr("id");
  window.openedYandexMapFrame = mapId;
  var mapContainer = $('<div data-mapId="' + mapId + '"></div>');
  if (!$("div[data-mapId=" + mapId + "]").length) {
    currentMap.after(mapContainer);
  }
  var yandexMapFrame = $('<div class="yandex-map__frame"></div>');
  $("body .wrapper1").append(yandexMapFrame);
  currentMap.appendTo(yandexMapFrame);
  currentMap.find(".yandex-map__mobile-opener").addClass("closer");
  window.map.container.fitToViewport();
}

function closeYandexMap() {
  var yandexMapFrame = $(".yandex-map__frame");
  if (yandexMapFrame.length) {
    var currentMap = yandexMapFrame.find(".bx-yandex-view-layout");
    var yandexMapContainer = $("div[data-mapId=" + window.openedYandexMapFrame + "]");

    currentMap.appendTo(yandexMapContainer);
    yandexMapFrame.remove();
    currentMap.find(".yandex-map__mobile-opener").removeClass("closer");
    if (window.map) {
      window.map.container.fitToViewport();
    }
  }
}

function throttle(f, t) {
  let throttled, saveThis, saveArgs;
  const wrapper = function () {
    if (throttled) {
      saveThis = this;
      saveArgs = arguments;
      return;
    }
    throttled = true;
    f.apply(this, arguments);
    setTimeout(function () {
      throttled = false;
      if (saveArgs) {
        wrapper.apply(saveThis, saveArgs);
        saveThis = saveArgs = null;
      }
    }, t);
  };
  return wrapper;
}

function debounce(f, t) {
  return function (args) {
    var previousCall = this.lastCall;
    this.lastCall = Date.now();
    if (previousCall && this.lastCall - previousCall <= t) {
      clearTimeout(this.lastCallTimer);
    }
    this.lastCallTimer = setTimeout(function () {
      f(args);
    }, t);
  };
}

$(document).on("click", ".flex-control-nav.flex-control-js-click li a:not(.flex-active)", function (e) {
  var _this = $(this);
  var itemIndex = _this.data("index") ? _this.data("index") : _this.closest("li").index();

  var block = "";
  var blocks = _this.closest(".items").find("> .item");
  var activeBlock = _this.closest(".items").find(".item.active");

  var activeLi = _this.closest(".flex-control-nav").find(".flex-active");

  activeBlock.fadeOut(function () {
    activeBlock.removeClass("active");

    block = blocks.eq(itemIndex);
    if (block.length) {
      block.fadeIn(function () {
        block.addClass("active");
      });
    }

    _this.addClass("flex-active");
    activeLi.removeClass("flex-active");
  });

  e.preventDefault();
});

$(document).on("mouseenter", "#headerfixed .menu-item.wide_menu", function () {
  var _this = $(this);
  setTimeout(function () {
    var dropdownMenu = _this.find(".wrap > .dropdown-menu");
    var menuOffset = dropdownMenu[0].getBoundingClientRect();
    var headerOffset = document.getElementById("headerfixed").getBoundingClientRect();
    var windowHeight = document.documentElement.clientHeight;

    if (menuOffset.height + headerOffset.height > windowHeight) {
      var maxHeight = windowHeight - headerOffset.height;
      dropdownMenu.css({
        "max-height": maxHeight,
      });
      dropdownMenu.find(".menu-navigation").css({ "max-height": maxHeight });
      dropdownMenu.find(".customScrollbar").css({ "max-height": maxHeight });
      dropdownMenu.find(".menu-wrapper.menu-type-4 > li").css({ "min-height": "auto" });
    } else {
      dropdownMenu.css({
        "max-height": "",
      });
      dropdownMenu.find(".menu-navigation").css({ "max-height": "" });
      dropdownMenu.find(".customScrollbar").css({ "max-height": "" });
      dropdownMenu.find(".menu-wrapper.menu-type-4 > li").css({ "min-height": "" });
    }
  }, 300);
});

BX.addCustomEvent("onAjaxSuccess", function (data) {
  if (typeof data === "object" && data !== null) {
    if (
      typeof data.BASKET_REFRESHED !== undefined &&
      data.BASKET_REFRESHED === true &&
      typeof data.BASKET_DATA === "object" &&
      data.BASKET_DATA
    ) {
      var basketCounts = data.BASKET_DATA.BASKET_ITEMS_COUNT;
      reloadBasketCounters(basketCounts);
    }
  }
});

BX.addCustomEvent("onHeaderProgressBarChange", function (data) {
  if (typeof updateProgressBar === "function") {
    updateProgressBar();
  }
});

if (!funcDefined("parseUrlQuery")) {
  parseUrlQuery = function () {
    var data = {};
    if (location.search) {
      var pair = location.search.substr(1).split("&");
      for (var i = 0; i < pair.length; i++) {
        var param = pair[i].split("=");
        data[param[0]] = param[1];
      }
    }
    return data;
  };
}

if (!funcDefined("setLocationSKU")) {
  function setLocationSKU(ID, urlParam) {
    if (urlParam === undefined) urlParam = "oid";

    var objUrl = parseUrlQuery(),
      j = 0,
      prefix = "",
      query_string = "",
      url = "";

    objUrl[urlParam] = ID;

    for (var i in objUrl) {
      if (parseInt(j) > 0) prefix = "&";
      query_string = query_string + prefix + i + "=" + objUrl[i];
      j++;
    }
    if (query_string) {
      url = location.pathname + "?" + query_string + location.hash;
    }
    try {
      //history.pushState(null, null, url);
      history.replaceState(null, null, url);
      return;
    } catch (e) {}
    location.hash = "#" + url.substr(1);
  }
}

if (!funcDefined("ShowOverlay")) {
  ShowOverlay = function () {
    $('<div class="jqmOverlay waiting"></div>').appendTo("body");
  };
}

if (!funcDefined("HideOverlay")) {
  HideOverlay = function () {
    $(".jqmOverlay").detach();
  };
}

if (!funcDefined("trimPrice")) {
  var trimPrice = function trimPrice(s) {
    s = s.split(" ").join("");
    s = s.split("&nbsp;").join("");
    return s;
  };
}

if (!funcDefined("pauseYmObserver")) {
  // pause ya metrika webvisor MutationObserver callback (DOM indexer)
  // use before insert html with some animation
  pauseYmObserver = function () {
    if (
      typeof MutationObserver === "function" &&
      typeof MutationObserver.observers === "object" &&
      typeof MutationObserver.observers.ym === "object"
    ) {
      if (typeof pauseYmObserver.cnt === "undefined") {
        pauseYmObserver.cnt = 0;
      }

      ++pauseYmObserver.cnt;

      if (!MutationObserver.observers.ym.paused) {
        MutationObserver.observers.ym.pause();
      }
    }
  };
}

if (!funcDefined("resumeYmObserver")) {
  // resume ya metrika webvisor MutationObserver callback
  // use when animation finished
  resumeYmObserver = function () {
    if (
      typeof MutationObserver === "function" &&
      typeof MutationObserver.observers === "object" &&
      typeof MutationObserver.observers.ym === "object"
    ) {
      if (typeof pauseYmObserver.cnt === "undefined") {
        pauseYmObserver.cnt = 1;
      }

      pauseYmObserver.cnt -= pauseYmObserver.cnt > 0 ? 1 : 0;

      if (!pauseYmObserver.cnt && MutationObserver.observers.ym.paused) {
        MutationObserver.observers.ym.resume();
      }
    }
  };
}

if (!funcDefined("markProductRemoveBasket")) {
  var markProductRemoveBasket = function markProductRemoveBasket(id) {
    $(".in-cart[data-item=" + id + "]").hide();
    $(".to-cart[data-item=" + id + "]").show();
    if (
      $(".to-cart[data-item=" + id + "]")
        .closest(".counter_wrapp")
        .find(".counter_block").length
    ) {
      $(".to-cart[data-item=" + id + "]")
        .closest(".button_block")
        .removeClass("wide");
    }
    $(".to-cart[data-item=" + id + "]")
      .closest(".counter_wrapp")
      .find(".counter_block")
      .show();
    $(".counter_block[data-item=" + id + "]")
      .closest(".counter_block_inner")
      .show();
    $(".counter_block[data-item=" + id + "]").show();
    $(".in-subscribe[data-item=" + id + "]").hide();
    $(".to-subscribe[data-item=" + id + "]").show();
    //$('.wish_item[data-item='+id+']').removeClass("added");
    $(".wish_item[data-item=" + id + "] .value:not(.added)").show();
    $(".wish_item[data-item=" + id + "] .value.added").hide();
    $(".wish_item.to[data-item=" + id + "]").show();
    $(".wish_item.in[data-item=" + id + "]").hide();
    $(".banner_buttons.with_actions .wraps_buttons[data-id=" + id + "] .basket_item_add").removeClass("added");
    $(".banner_buttons.with_actions .wraps_buttons[data-id=" + id + "] .wish_item_add").removeClass("added");

    if ($("#headerfixed .but-cell .type_block").length) {
      $("#headerfixed .but-cell .type_block span").text(BX.message("MORE_INFO_SKU"));
      $("#headerfixed .but-cell .type_block .svg-inline-fw").remove();
    }
  };
}

if (!funcDefined("markProductAddBasket")) {
  var markProductAddBasket = function markProductAddBasket(id) {
    $(".to-cart[data-item=" + id + "]").hide();
    $(".to-cart[data-item=" + id + "]")
      .closest(".counter_wrapp")
      .find(".counter_block_inner")
      .hide();
    $(".to-cart[data-item=" + id + "]")
      .closest(".counter_wrapp")
      .find(".counter_block")
      .hide();
    $(".to-cart[data-item=" + id + "]")
      .closest(".button_block")
      .addClass("wide");
    $(".in-cart[data-item=" + id + "]").show();
    //$('.wish_item[data-item='+id+']').removeClass("added");
    $(".wish_item[data-item=" + id + "] .value:not(.added)").show();
    $(".wish_item[data-item=" + id + "] .value.added").hide();

    $(".wish_item.to[data-item=" + id + "]").show();
    $(".wish_item.in[data-item=" + id + "]").hide();
    $(".banner_buttons.with_actions .wraps_buttons[data-id=" + id + "] .basket_item_add").addClass("added");

    if ($("#headerfixed .but-cell .type_block").length) {
      $("#headerfixed .but-cell .type_block").html($(".in-cart[data-item=" + id + "]").html());
    }
  };
}

if (!funcDefined("markProductDelay")) {
  var markProductDelay = function markProductDelay(id) {
    $(".in-cart[data-item=" + id + "]").hide();
    $(".to-cart[data-item=" + id + "]").show();
    $(".to-cart[data-item=" + id + "]")
      .closest(".counter_wrapp")
      .find(".counter_block_inner")
      .show();
    $(".to-cart[data-item=" + id + "]")
      .closest(".counter_wrapp")
      .find(".counter_block")
      .show();
    if (
      $(".to-cart[data-item=" + id + "]")
        .closest(".counter_wrapp")
        .find(".counter_block").length
    ) {
      $(".to-cart[data-item=" + id + "]")
        .closest(".button_block")
        .removeClass("wide");
    }
    //$('.wish_item[data-item='+id+']').addClass("added");
    $(".wish_item[data-item=" + id + "] .value:not(.added)").hide();
    $(".wish_item[data-item=" + id + "] .value.added").css("display", "block");

    $(".wish_item.to[data-item=" + id + "]").hide();
    $(".wish_item.in[data-item=" + id + "]").show();

    $(".banner_buttons.with_actions .wraps_buttons[data-id=" + id + "] .wish_item_add").addClass("added");

    if ($("#headerfixed .but-cell .type_block").length) {
      $("#headerfixed .but-cell .type_block span").text(BX.message("MORE_INFO_SKU"));
      $("#headerfixed .but-cell .type_block .svg-inline-fw").remove();
    }
  };
}

if (!funcDefined("markProductSubscribe")) {
  var markProductSubscribe = function markProductSubscribe(id) {
    $(".to-subscribe[data-item=" + id + "]").hide();
    $(".in-subscribe[data-item=" + id + "]").css("display", "block");
  };
}

if (!funcDefined("updateBottomIconsPanel")) {
  var updateBottomIconsPanel = function updateBottomIconsPanel(options) {
    if (options && $(".bottom-icons-panel").length) {
      var bBasketUpdate = "READY" in options || "BASKET_COUNT" in options;
      var basketCount = "READY" in options ? options.READY.COUNT : "BASKET_COUNT" in options ? options.BASKET_COUNT : 0;
      var basketTitle =
        "READY" in options ? options.READY.TITLE : "BASKET_SUMM_TITLE" in options ? options.BASKET_SUMM_TITLE : "";
      var bDelayUpdate = "DELAY" in options || "DELAY_COUNT" in options;
      var delayCount = "DELAY" in options ? options.DELAY.COUNT : "DELAY_COUNT" in options ? options.DELAY_COUNT : 0;
      var delayTitle =
        "DELAY" in options ? options.DELAY.TITLE : "DELAY_SUMM_TITLE" in options ? options.DELAY_SUMM_TITLE : "";
      var bCompareUpdate = "COMPARE" in options;
      var compareCount =
        "COMPARE" in options
          ? typeof options.COMPARE === "object" && "COUNT" in options.COMPARE
            ? options.COMPARE.COUNT
            : Object.keys(options.COMPARE).length
          : 0;

      //basket
      if (bBasketUpdate) {
        if (+basketCount > 0) {
          $(".bottom-icons-panel .basket.counter-state").removeClass("counter-state--empty");
        } else {
          $(".bottom-icons-panel .basket.counter-state").addClass("counter-state--empty");
        }
        $(".bottom-icons-panel .basket .counter-state__content-item-value").text(basketCount);
        $(".bottom-icons-panel .basket")
          .closest(".bottom-icons-panel__content-link")
          .attr("title", $("<div/>").html(basketTitle).text());
      }
      //

      //delay
      if (bDelayUpdate && bBasketUpdate) {
        if (+delayCount > 0) {
          $(".bottom-icons-panel .delay.counter-state").removeClass("counter-state--empty");
        } else {
          $(".bottom-icons-panel .delay.counter-state").addClass("counter-state--empty");
        }
        $(".bottom-icons-panel .delay .counter-state__content-item-value").text(delayCount);
        $(".bottom-icons-panel .delay")
          .closest(".bottom-icons-panel__content-link")
          .attr("title", $("<div/>").html(delayTitle).text());
      }
      //

      //compare
      if (bCompareUpdate) {
        if (compareCount > 0) {
          $(".bottom-icons-panel .compare.counter-state").removeClass("counter-state--empty");
        } else {
          $(".bottom-icons-panel .compare.counter-state").addClass("counter-state--empty");
        }
        $(".bottom-icons-panel .compare .counter-state__content-item-value").text(compareCount);
      }
    }
  };
}
if (!funcDefined("basketFly")) {
  var basketFly = function basketFly(action, opener) {
    if (typeof obMaxPredictions === "object") {
      obMaxPredictions.updateAll();
    }

    /*if(arAsproOptions['PAGES']['BASKET_PAGE'])
      return;*/
    $.post(
      arAsproOptions["SITE_DIR"] + "ajax/basket_fly.php",
      "PARAMS=" + $("#basket_form").find("input#fly_basket_params").val(),
      $.proxy(function (data) {
        var small = $(".opener .basket_count").hasClass("small"),
          basket_count = $(data).find(".basket_count").find(".items div").text();
        $("#basket_line .basket_fly").addClass("loaded").html(data);

        if (action == "refresh") $("li[data-type=AnDelCanBuy]").trigger("click");

        if (typeof opener == "undefined" || $("#basket_line .basket_fly").hasClass("loading")) {
          if (window.matchMedia("(min-width: 769px)").matches) {
            if (action == "open") {
              if (small) {
                if (arAsproOptions["THEME"]["SHOW_BASKET_ONADDTOCART"] !== "N") {
                  $(".opener .basket_count").click();
                }
              } else {
                $(".opener .basket_count").removeClass("small");
                $('.tabs_content.basket li[item-section="AnDelCanBuy"]').addClass("cur");
                $('#basket_line ul.tabs li[item-section="AnDelCanBuy"]').addClass("cur");

                $("#basket_line .basket_fly .opener > div:eq(0)").addClass("cur");
              }
            } else if (action == "wish") {
              if (small) {
                if (arAsproOptions["THEME"]["SHOW_BASKET_ONADDTOCART"] !== "N") $(".opener .wish_count").click();
              } else {
                $(".opener .wish_count").removeClass("small");
                $('.tabs_content.basket li[item-section="DelDelCanBuy"]').addClass("cur");
                $('#basket_line ul.tabs li[item-section="DelDelCanBuy"]').addClass("cur");
              }
            } else {
              if (arAsproOptions["THEME"]["SHOW_BASKET_ONADDTOCART"] !== "N") {
                $(".opener .basket_count").click();
              }
            }
          }
        } else if (opener === "SHOW") {
          if (window.matchMedia("(min-width: 769px)").matches) {
            $(".opener .basket_count").removeClass("small");
            $('.tabs_content.basket li[item-section="AnDelCanBuy"]').addClass("cur");
            $('#basket_line ul.tabs li[item-section="AnDelCanBuy"]').addClass("cur");
            $("#basket_line .basket_fly .opener > div:eq(0)").addClass("cur");
          }
        }
      })
    );
  };
}

if (!funcDefined("basketTop")) {
  var basketTop = function basketTop(action, hoverBlock) {
    if (action == "reload") {
      if ($(".basket_hover_block:hover").length) {
        hoverBlock = $(".basket_hover_block:hover");
      }
    }

    if (action == "open") {
      if (arAsproOptions["THEME"]["SHOW_BASKET_ONADDTOCART"] !== "N") {
        if ($("#headerfixed").hasClass("fixed")) {
          hoverBlock = $("#headerfixed .basket_hover_block");
        } else {
          hoverBlock = $(".top_basket .basket_hover_block");
        }
      }
    }

    if (hoverBlock === undefined) {
      console.log("Undefined hoverBlock");
      console.trace();
      return false;
    }

    if (action == "close") {
      if (hoverBlock.length) {
        hoverBlock.css({
          opacity: "",
          visibility: "",
        });
        return true;
      }
    }

    hoverBlock.removeClass("loaded");
    var firstTime = hoverBlock.find("div").length ? "false" : "true";
    var params = $("#basket_form").find("input#fly_basket_params").val();
    var postData = {
      firstTime: firstTime,
    };
    if (params !== undefined) {
      postData.PARAMS = params;
    }

    $.post(
      arAsproOptions["SITE_DIR"] + "ajax/showBasketHover.php",
      postData,
      $.proxy(function (data) {
        var ob = BX.processHTML(data);

        // inject
        $("#headerfixed .basket_hover_block, .top_basket .basket_hover_block").html(ob.HTML);
        BX.ajax.processScripts(ob.SCRIPT);

        if (window.matchMedia("(min-width: 992px)").matches) {
          hoverBlock.addClass("loaded");

          if (action == "open") {
            if (arAsproOptions["THEME"]["SHOW_BASKET_ONADDTOCART"] !== "N") {
              if ($("#headerfixed").hasClass("fixed")) {
                hoverBlock = $("#headerfixed .basket_hover_block");
              } else {
                hoverBlock = $(".top_basket .basket_hover_block");
              }

              hoverBlock.css({
                opacity: "1",
                visibility: "visible",
              });

              setTimeout(function () {
                hoverBlock.css({
                  opacity: "",
                  visibility: "",
                });
              }, 2000);
            }
          }
        }
      })
    );
  };
}

//work with hash start
var lastHash = location.hash;
if ("onhashchange" in window) {
  $(window).bind("hashchange", function () {
    var newHash = location.hash;
    if (newHash == "#delayed") {
      if ($("#basket_toolbar_button_delayed").length) $("#basket_toolbar_button_delayed").trigger("click");
    } else {
      if ($("#basket_toolbar_button").length) $("#basket_toolbar_button").trigger("click");
    }
    // Do something
    var diff = compareHash(newHash, lastHash);
    // alert("Difference between old and new hash:\n"+diff[0]+"\n\n"+diff[1]);
    //At the end of the func:
    lastHash = newHash;

    if (lastHash) {
      if ("scrollRestoration" in history) {
        history.scrollRestoration = "manual";
      }
      if ($('.ordered-block .tabs .nav a[href="' + lastHash + '"]').length) {
        $('.ordered-block .tabs .nav a[href="' + lastHash + '"]').trigger("click");
        var content_offset = $(".ordered-block .tabs").offset();
        $("html, body").animate({ scrollTop: content_offset.top - 90 }, 400);
      }
    }
  });

  function compareHash(current, previous) {
    for (var i = 0, len = Math.min(current.length, previous.length); i < len; i++) {
      if (current.charAt(0) != previous.charAt(0)) break;
    }
    current = current.substr(i);
    previous = previous.substr(i);
    for (var i = 0, len = Math.min(current.length, previous.length); i < len; i++) {
      if (current.substr(-1) != previous.substr(-1)) break;
    }

    //Array: Current = New hash, previous = old hash
    return [current, previous];
  }
}

$(document).on("click", "#basket_toolbar_button", function () {
  if (lastHash) location.hash = "cart";
});
$(document).on("click", "#basket_toolbar_button_delayed", function () {
  if (lastHash) location.hash = "delayed";
});
if (location.hash) {
  var hash = location.hash;
}
//work with hash end

$(document).on("click", "#basket_line .basket_fly .opener > div.clicked", function () {
  if (arAsproOptions["PAGES"]["BASKET_PAGE"]) return;
  function onOpenFlyBasket(_this) {
    $("#basket_line .basket_fly .tabs li").removeClass("cur");
    $("#basket_line .basket_fly .tabs_content li").removeClass("cur");
    // $("#basket_line .basket_fly .remove_all_basket").removeClass("cur");
    if (!$(_this).is(".wish_count.empty")) {
      $("#basket_line .basket_fly .tabs_content li[item-section=" + $(_this).data("type") + "]").addClass("cur");
      $("#basket_line .basket_fly .tabs li:eq(" + $(_this).index() + ")").addClass("cur");
      // $("#basket_line .basket_fly .remove_all_basket."+$(_this).data("type")).addClass("cur");
    } else {
      $("#basket_line .basket_fly .tabs li").first().addClass("cur").siblings().removeClass("cur");
      $("#basket_line .basket_fly .tabs_content li").first().addClass("cur").siblings().removeClass("cur");
      // $("#basket_line .basket_fly .remove_all_basket").first().addClass("cur");
    }
    $("#basket_line .basket_fly .opener > div.clicked").removeClass("small");

    $("#basket_line .basket_fly .opener > div").siblings().removeClass("cur");
    $("#basket_line .basket_fly .opener > div:eq(" + $(_this).index() + ")").addClass("cur");
  }

  if (window.matchMedia("(min-width: 769px)").matches) {
    var _this = this;

    $(_this).siblings().removeClass("cur");
    $(_this).addClass("cur");

    if (parseInt($("#basket_line .basket_fly").css("right")) < 0) {
      $("#basket_line .basket_fly")
        .stop()
        .addClass("loading")
        .animate({ right: "0" }, 333, function () {
          if ($(_this).closest(".basket_fly.loaded").length) {
            onOpenFlyBasket(_this);
          } else {
            $.ajax({
              url: arAsproOptions["SITE_DIR"] + "ajax/basket_fly.php",
              type: "post",
              success: function (html) {
                $("#basket_line .basket_fly").removeClass("loading").addClass("loaded").html(html);
                onOpenFlyBasket(_this);
              },
            });
          }
        });
      $("#basket_line .basket_fly").addClass("swiped");
    } else if (
      $(this).is(".wish_count:not(.empty)") &&
      !$("#basket_line .basket_fly .basket_sort ul.tabs li.cur").is("[item-section=DelDelCanBuy]")
    ) {
      $("#basket_line .basket_fly .tabs li").removeClass("cur");
      $("#basket_line .basket_fly .tabs_content li").removeClass("cur");
      // $("#basket_line .basket_fly .remove_all_basket").removeClass("cur");
      $("#basket_line .basket_fly .tabs_content li[item-section=" + $(this).data("type") + "]").addClass("cur");
      $("#basket_line  .basket_fly .tabs li:eq(" + $(this).index() + ")")
        .first()
        .addClass("cur");
      // $("#basket_line .basket_fly .remove_all_basket."+$(this).data("type")).first().addClass("cur");
    } else if (
      $(this).is(".basket_count") &&
      $("#basket_line .basket_fly .basket_sort ul.tabs li.cur").length &&
      !$("#basket_line .basket_fly .basket_sort ul.tabs li.cur").is("[item-section=AnDelCanBuy]")
    ) {
      $("#basket_line .basket_fly .tabs li").removeClass("cur");
      $("#basket_line .basket_fly .tabs_content li").removeClass("cur");
      // $("#basket_line .basket_fly .remove_all_basket").removeClass("cur");
      $("#basket_line  .basket_fly .tabs_content li:eq(" + $(this).index() + ")").addClass("cur");
      $("#basket_line  .basket_fly .tabs li:eq(" + $(this).index() + ")")
        .first()
        .addClass("cur");
      // $("#basket_line .basket_fly .remove_all_basket."+$(this).data("type")).first().addClass("cur");
    } else {
      $("#basket_line .basket_fly")
        .stop()
        .animate({ right: -$("#basket_line .basket_fly").outerWidth() }, 150);
      $("#basket_line .basket_fly .opener > div.clicked").addClass("small");
      $("#basket_line .basket_fly").removeClass("swiped");
      $("#basket_line .basket_fly .opener > div").removeClass("cur");
    }
  }
});

if (!funcDefined("clearViewedProduct")) {
  function clearViewedProduct() {
    try {
      var siteID = arAsproOptions.SITE_ID;
      var localKey = "MAX_VIEWED_ITEMS_" + siteID;
      var cookieParams = { path: "/", expires: 30 };
      if (typeof BX.localStorage !== "undefined") {
        // remove local storage
        BX.localStorage.set(localKey, {}, 0);
      }
      // remove cookie
      $.removeCookie(localKey, cookieParams);
    } catch (e) {
      console.error(e);
    }
  }
}

if (!funcDefined("setViewedProduct")) {
  function setViewedProduct(id, arData) {
    try {
      // save $.cookie option
      var bCookieJson = $.cookie.json;
      $.cookie.json = true;

      var siteID = arAsproOptions.SITE_ID;
      var localKey = "MAX_VIEWED_ITEMS_" + siteID;
      var cookieParams = { path: "/", expires: 30 };

      if (typeof BX.localStorage !== "undefined" && typeof id !== "undefined" && typeof arData !== "undefined") {
        var PRODUCT_ID = typeof arData.PRODUCT_ID !== "undefined" ? arData.PRODUCT_ID : id;
        var arViewedLocal = BX.localStorage.get(localKey) ? BX.localStorage.get(localKey) : {};
        var arViewedCookie = $.cookie(localKey) ? $.cookie(localKey) : {};
        var count = 0;

        // delete some items (sync cookie & local storage)
        for (var _id in arViewedLocal) {
          arViewedLocal[_id].IS_LAST = false;
          if (typeof arViewedCookie[_id] === "undefined") {
            delete arViewedLocal[_id];
          }
        }
        for (var _id in arViewedCookie) {
          if (typeof arViewedLocal[_id] === "undefined") {
            delete arViewedCookie[_id];
          }
        }

        for (var _id in arViewedCookie) {
          count++;
        }

        // delete item if other item (offer) of that PRODUCT_ID is exists
        if (typeof arViewedLocal[PRODUCT_ID] !== "undefined") {
          if (arViewedLocal[PRODUCT_ID].ID != id) {
            delete arViewedLocal[PRODUCT_ID];
            delete arViewedCookie[PRODUCT_ID];
          }
        }

        var time = new Date().getTime();
        arData.ID = id;
        arData.ACTIVE_FROM = time;
        arData.IS_LAST = true;
        arViewedLocal[PRODUCT_ID] = arData;
        arViewedCookie[PRODUCT_ID] = [time.toString(), arData.PICTURE_ID];

        $.cookie(localKey, arViewedCookie, cookieParams);
        BX.localStorage.set(localKey, arViewedLocal, 2592000); // 30 days
      }
    } catch (e) {
      console.error(e);
    } finally {
      // restore $.cookie option
      $.cookie.json = bCookieJson;
    }
  }
}

if (!funcDefined("initSelects")) {
  function initSelects(target) {
    var iOS = navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false;
    if (iOS && !$(".wrapper1.iks-on-ios").length) return;
    if ($("#bx-soa-order").length) return;

    if (!$(".wrapper1.iks-ignore").length) {
      const $target = $(target).find(
        ".wrapper1 select:visible:not(.iks-ignore), .jqmWindow select:visible:not(.iks-ignore)"
      );

      if ($target.length) {
        // SELECT STYLING
        $target.ikSelect({
          syntax:
            '<div class="ik_select_link"> \
              <span class="ik_select_link_text"></span> \
              <div class="trigger"></div> \
            </div> \
            <div class="ik_select_dropdown"> \
              <div class="ik_select_list"> \
              </div> \
            </div>',
          dynamicWidth: true,
          ddMaxHeight: 112,
          customClass: "common_select",
          //equalWidths: true,
          onShow: function (inst) {
            inst.$dropdown.css("top", parseFloat(inst.$dropdown.css("top")) - 5 + "px");
            if (inst.$dropdown.outerWidth() < inst.$link.outerWidth()) {
              inst.$dropdown.css("width", inst.$link.outerWidth());
            }
            if (inst.$dropdown.outerWidth() > inst.$link.outerWidth()) {
              inst.$dropdown.css("width", inst.$link.outerWidth());
            }
            var count = 0,
              client_height = 0;
            inst.$dropdown.css("left", inst.$link.offset().left);
            $(inst.$listInnerUl)
              .find("li")
              .each(function () {
                if (!$(this).hasClass("ik_select_option_disabled")) {
                  ++count;
                  client_height += $(this).outerHeight();
                }
              });
            if (client_height < 112) {
              inst.$listInner.css("height", "auto");
            } else {
              inst.$listInner.css("height", "112px");
            }
            inst.$link.addClass("opened");
            inst.$listInner.addClass("scroller scrollblock");
            if ($(".confirm_region").length) $(".confirm_region").remove();
          },
          onHide: function (inst) {
            inst.$link.removeClass("opened");
          },
        });
        // END OF SELECT STYLING

        var timeout;
        $(window).on("resize", function () {
          ignoreResize.push(true);
          clearTimeout(timeout);
          timeout = setTimeout(function () {
            //$('select:visible').ikSelect('redraw');
            var inst = "";
            if ((inst = $(".common_select-link.opened + select").ikSelect().data("plugin_ikSelect"))) {
              inst.$dropdown.css("left", inst.$link.offset().left + "px");
            }
          }, 20);
          ignoreResize.pop();
        });
      }
    }
  }
}

if (!funcDefined("CheckTopMenuFullCatalogSubmenu")) {
  CheckTopMenuFullCatalogSubmenu = function () {
    if (arAsproOptions["THEME"] && arAsproOptions["THEME"]["MENU_TYPE_VIEW"] != "HOVER") return;

    var $menu = $(".left_block .menu_top_block");
    if ($menu.length) {
      var $wrapmenu = $menu.parents(".wrap_menu");

      var wrapMenuWidth = $menu.closest(".wrapper_inner").actual("width");
      if (!wrapMenuWidth) wrapMenuWidth = $menu.closest(".wraps").actual("width");

      var bCatalogFirst = $menu.hasClass("catalogfirst");
      var findMenuLi = $(".left_block .menu_top_block:visible li.full");
      var parentSubmenuWidth = $menu.actual("outerWidth");
      var wrapMenuLeft = 0;
      var wrapMenuRight = 0;

      if ($wrapmenu.length) {
        wrapMenuWidth = $wrapmenu.actual("outerWidth");
        wrapMenuLeft = $wrapmenu.offset().left;
        wrapMenuRight = wrapMenuLeft + wrapMenuWidth;
      }

      if ($(".left_block .catalog_block.menu_top_block").length) {
        if ($(".left_block .catalog_block.menu_top_block").is(":visible"))
          findMenuLi = $(".left_block .menu_top_block.catalog_block li.full");
      }
      findMenuLi.each(function () {
        var $this = $(this);
        var $submenu = $this.find(">.dropdown");

        if ($submenu.length) {
          // $submenu.css({left: parentSubmenuWidth + 'px', width: (wrapMenuWidth - parentSubmenuWidth) + 'px', 'padding-left': '0px', 'padding-right': '0px', 'opacity': 1});
          $submenu.css({ width: wrapMenuWidth - parentSubmenuWidth + "px" });

          if (!isOnceInited && arAsproOptions["THEME"]["MENU_POSITION"] == "TOP") {
            $this.on("mouseenter", function () {
              $submenu.css("min-height", $this.closest(".dropdown").actual("outerHeight") + "px");
            });
          }
        }
      });
    }
  };
}

$.fn.getMaxHeights = function (outer, classNull, minHeight) {
  var maxHeight = this.map(function (i, e) {
    var calc_height = 0;
    $(e).css("height", "");

    if (outer == true) calc_height = $(e).actual("outerHeight");
    else calc_height = $(e).actual("height");

    return calc_height;
  }).get();
  for (var i = 0, c = maxHeight.length; i < c; ++i) {
    if (maxHeight[i] % 2) --maxHeight[i];
  }
  return Math.max.apply(this, maxHeight);
};

$.fn.equalizeHeights = function (outer, classNull, options) {
  var maxHeight = [];
  var items = [];

  for (var i = 0, itemKey = 0; i < this.length; itemKey++) {
    var item = this[itemKey],
      _item = $(item),
      minus_height = 0,
      calc_height = 0;

    i++;

    if (options.blockNull !== undefined) {
      if (options.blockNull.class !== undefined) {
        if (_item.hasClass(options.blockNull.class) || _item.closest("." + options.blockNull.class).length) {
          continue;
        }
      }
    }

    items.push(this[itemKey]);

    if (classNull !== false) {
      if (!isMobile) {
        var nulled = _item.find(classNull);
        if (nulled.length) {
          minus_height = parseInt(nulled[0].offsetHeight);
          //minus_height=parseInt(_item.find(classNull).outerHeight(false));
          //minus_height=parseInt(_item.find(classNull).actual('outerHeight'));
        }
      }
    }

    if (minus_height) minus_height += 12;

    _item.css("height", "");

    calc_height = item.offsetHeight - minus_height;

    if (options.minHeight !== false) {
      if (calc_height < options.minHeight) calc_height += options.minHeight - calc_height;

      if (window.matchMedia("(max-width: 520px)").matches) calc_height = 300;

      if (window.matchMedia("(max-width: 400px)").matches) calc_height = 200;
    }

    if (!calc_height) calc_height = 0;

    maxHeight.push(calc_height);
  }

  for (var i = 0, c = maxHeight.length; i < c; ++i) {
    if (maxHeight[i] % 2) {
      --maxHeight[i];
    }
  }

  var result = $(items).height(Math.max.apply(this, maxHeight));

  return result;
};

$.fn.getFloatWidth = function () {
  var width = 0;
  if ($(this).length) {
    var rect = $(this)[0].getBoundingClientRect();
    if (!(width = rect.width)) width = rect.right - rect.left;
  }

  $(this).data("floatWidth", width);

  return width;
};

function extendDepthObject(target, FromObj) {
  var to = Object.assign({}, target);
  for (var key in FromObj) {
    if (typeof FromObj[key] == "object") {
      to[key] = extendDepthObject(to[key], FromObj[key]);
    } else {
      to[key] = FromObj[key];
    }
  }

  return to;
}

$.fn.sliceHeight = function (options) {
  function _slice(el) {
    var arBreakpoints = Object.keys(options.breakpoint);
    var resizeOptionsTmp = {};
    if (arBreakpoints.length) {
      for (var key in arBreakpoints) {
        if (window.matchMedia(arBreakpoints[key].toString()).matches) {
          resizeOptionsTmp = options.breakpoint[arBreakpoints[key]];
        }
      }
    }

    var resizeOptions = extendDepthObject(options, resizeOptionsTmp);

    var blockNullClass =
      resizeOptions.blockNull !== undefined && resizeOptions.blockNull.class !== undefined
        ? resizeOptions.blockNull.class
        : false;
    var parent =
      typeof resizeOptions.row !== "undefined" && resizeOptions.row.length
        ? el.first().parents(resizeOptions.row)
        : el.first().parents(".items");
    var item = "";

    if (typeof resizeOptions.item !== "undefined" && resizeOptions.item.length) {
      if (blockNullClass) {
        $(resizeOptions.item).each(function (i, element) {
          _element = $(element);
          if (!_element.hasClass(blockNullClass)) {
            item = _element;
          }
        });
        if (!item)
          // if only ignored blocks
          return false;
      } else {
        item = $(resizeOptions.item).first();
      }
    } else {
      if (el.first().hasClass("item")) {
        item = el.first();
      } else {
        item = el.first().parents(".item");
      }
    }

    if (typeof resizeOptions.autoslicecount == "undefined" || resizeOptions.autoslicecount !== false) {
      var elsw = parent.getFloatWidth(),
        elw = item.getFloatWidth();
      if (!elsw) {
        elsw = el.first().parents(".row").getFloatWidth();
      }
      if (!elw) {
        if (typeof resizeOptions.item !== "undefined" && resizeOptions.item.length)
          elw = $(resizeOptions.item + ":eq(1)").getFloatWidth()
            ? $(resizeOptions.item + ":eq(1)").getFloatWidth()
            : $(resizeOptions.item + ":eq(2)").getFloatWidth();
        else elw = $(el[1]).getFloatWidth() ? $(el[1]).getFloatWidth() : $(el[2]).getFloatWidth();
      }
      if (elw && resizeOptions.fixWidth) elw -= resizeOptions.fixWidth;

      elw = parseInt(elw * 100) / 100;

      if (elsw && elw) {
        resizeOptions.slice = Math.floor(elsw / elw);
      }
    }

    if (resizeOptions.customSlice) {
      //manual slice count
      var bSliceNext = false;
      if (resizeOptions.length) {
        elw =
          typeof resizeOptions.item !== "undefined" && resizeOptions.item.length
            ? $(resizeOptions.item).last().getFloatWidth()
            : el.last().hasClass("item")
            ? el.last().getFloatWidth()
            : el.last().parents(".item").getFloatWidth();
        if (elw) resizeOptions.sliceNext = Math.floor(elsw / elw);

        bSliceNext = true;
      }
    }

    elements = [];
    if (resizeOptions.classes !== undefined && resizeOptions.classes.length) {
      for (var i = 0; i < resizeOptions.classes.length; i++) {
        var items = $(resizeOptions.item).find(resizeOptions.classes[i]);
        elements.push(items);
      }
    }
    var elIndex = elements.push(el) - 1;

    if (resizeOptions.mobile == true) {
      if (window.matchMedia("(max-width: 500px)").matches) {
        for (var block = 0; block < elements.length; block++) {
          var items = $(elements[block]);
          items.css({
            "line-height": "",
            height: "",
          });
        }
        return;
      }
    }

    if (typeof resizeOptions.typeResize == "undefined" || resizeOptions.typeResize == false) {
      if (resizeOptions.slice) {
        for (var block = 0; block < elements.length; block++) {
          if (resizeOptions.currentRow) {
            var itemNumber = el.index(resizeOptions.currentItem);
            var rowCount = resizeOptions.slice;
            var rowNumber = Math.floor(itemNumber / rowCount);

            var items = $(elements[block].slice(rowNumber * rowCount, rowNumber * rowCount + rowCount));
            var classNull = block == elIndex ? resizeOptions.classNull : false;
            items.css({
              "line-height": "",
              height: "",
            });
            items.equalizeHeights(resizeOptions.outer, classNull, resizeOptions);
          } else {
            for (var i = 0; i < elements[block].length; ) {
              if (resizeOptions.customSlice && resizeOptions.sliceNext && bSliceNext && i)
                //manual slice count
                resizeOptions.slice = resizeOptions.sliceNext;

              var slice = resizeOptions.slice;
              var items = $(elements[block].slice(i, i + slice));
              if (resizeOptions.blockNull !== undefined) {
                if (resizeOptions.blockNull.class !== undefined) {
                  var counts = 0;
                  items.each(function (i, item) {
                    var _item = $(item);
                    if (
                      _item.hasClass(resizeOptions.blockNull.class) ||
                      _item.closest("." + resizeOptions.blockNull.class).length
                    ) {
                      counts++;
                    }
                  });
                }
              }
              if (counts) {
                slice -= (resizeOptions.blockNull.width - 1) * counts;
              }

              items = $(elements[block].slice(i, i + slice));
              if (items) {
                var classNull = block == elIndex ? resizeOptions.classNull : false;

                items.css({
                  "line-height": "",
                  height: "",
                });
                items.equalizeHeights(resizeOptions.outer, classNull, resizeOptions);
              }
              i += slice || 1;
            }
          }
        }
      }
      if (resizeOptions.lineheight) {
        var lineheightAdd = parseInt(resizeOptions.lineheight);
        if (isNaN(lineheightAdd)) {
          lineheightAdd = 0;
        }
        el.each(function () {
          $(this).css("line-height", $(this).actual("height") + lineheightAdd + "px");
        });
      }
    }
  }
  var options = $.extend(
    {
      slice: null,
      sliceNext: null,
      outer: false,
      lineheight: false,
      autoslicecount: true,
      classNull: false,
      minHeight: false,
      row: false,
      item: false,
      typeResize: false,
      typeValue: false,
      fixWidth: 0,
      resize: true,
      mobile: false,
      customSlice: false,
      breakpoint: {},
      classes: [],
    },
    options
  );

  var el = $(this);

  ignoreResize.push(true);
  _slice(el);
  ignoreResize.pop();

  if (options.resize) {
    BX.addCustomEvent("onWindowResize", function (eventdata) {
      try {
        ignoreResize.push(true);
        _slice(el);
      } catch (e) {
      } finally {
        ignoreResize.pop();
      }
    });
  } else {
    if (!ignoreResize.length) {
      // ignoreResize.push(true);
      _slice(el);
      // ignoreResize.pop();
    }
  }
};

$.fn.sliceHeightNoResize = function (options) {
  function _slice(el) {
    el.each(function () {
      $(this).css("line-height", "");
      $(this).css("height", "");
    });
    if (options.mobile == true) {
      if (window.matchMedia("(max-width: 550px)").matches) return;
    }
    if (typeof options.autoslicecount == "undefined" || options.autoslicecount !== false) {
      var elsw =
          typeof options.row !== "undefined" && options.row.length
            ? el.first().parents(options.row).getFloatWidth()
            : el.first().parents(".items").getFloatWidth(),
        elw =
          typeof options.item !== "undefined" && options.item.length
            ? $(options.item).first().getFloatWidth()
            : el.first().hasClass("item")
            ? el.first().getFloatWidth()
            : el.first().parents(".item").getFloatWidth();

      if (!elsw) {
        elsw = el.first().parents(".row").getFloatWidth();
      }
      if (elw && options.fixWidth) elw -= options.fixWidth;

      if (elsw && elw) {
        options.slice = Math.floor(elsw / elw);
      }
    }
    if (options.customSlice) {
      //manual slice count
      var arBreakpoints = Object.keys(options.breakpoint),
        bSliceNext = false;
      if (arBreakpoints.length) {
        elw =
          typeof options.item !== "undefined" && options.item.length
            ? $(options.item).last().getFloatWidth()
            : el.last().hasClass("item")
            ? el.last().getFloatWidth()
            : el.last().parents(".item").getFloatWidth();
        if (elw) options.sliceNext = Math.floor(elsw / elw);
        for (var key in arBreakpoints) {
          if (window.matchMedia(arBreakpoints[key].toString()).matches) {
            bSliceNext = true;
            options.slice = options.breakpoint[arBreakpoints[key]];
          }
        }
      }
    }
    if (typeof options.typeResize == "undefined" || options.typeResize == false) {
      if (options.slice) {
        for (var i = 0; i < el.length; i += options.slice) {
          if (options.customSlice && options.sliceNext && bSliceNext && i)
            //manual slice count
            options.slice = options.sliceNext;
          $(el.slice(i, i + options.slice)).equalizeHeights(
            options.outer,
            options.classNull,
            options.minHeight,
            options.typeResize,
            options.typeValue
          );
        }
      }
      if (options.lineheight) {
        var lineheightAdd = parseInt(options.lineheight);
        if (isNaN(lineheightAdd)) {
          lineheightAdd = 0;
        }
        el.each(function () {
          $(this).css("line-height", $(this).actual("height") + lineheightAdd + "px");
        });
      }
    }
  }
  var options = $.extend(
    {
      slice: null,
      sliceNext: null,
      outer: false,
      lineheight: false,
      autoslicecount: true,
      classNull: false,
      minHeight: false,
      row: false,
      item: false,
      typeResize: false,
      typeValue: false,
      fixWidth: 0,
      resize: true,
      mobile: false,
      customSlice: false,
      breakpoint: {},
    },
    options
  );

  var el = $(this);

  ignoreResize.push(true);
  _slice(el);
  ignoreResize.pop();
};

if (!funcDefined("initHoverBlock")) {
  function initHoverBlock(target) {
    /*$(target).find('.catalog_item.item_wrap').on('mouseenter', function(){
      $(this).addClass('hover');
    })
    $(target).find('.catalog_item.item_wrap').on('mouseleave', function(){
      $(this).removeClass('hover');
    })*/
  }
}
if (!funcDefined("setStatusButton")) {
  function setStatusButton() {
    if (!funcDefined("setItemButtonStatus")) {
      setItemButtonStatus = function (data) {
        if (data.BASKET) {
          for (var i in data.BASKET) {
            var id = data.BASKET[i];
            if (typeof id === "number" || typeof id === "string") {
              $(".to-cart[data-item=" + id + "]").hide();
              $(".counter_block[data-item=" + id + "]")
                .closest(".counter_block_inner")
                .hide();
              $(".counter_block[data-item=" + id + "]").hide();
              $(".in-cart[data-item=" + id + "]").show();
              $(".in-cart[data-item=" + id + "]")
                .closest(".button_block")
                .addClass("wide");
            }
          }
        }
        if (data.DELAY) {
          for (var i in data.DELAY) {
            var id = data.DELAY[i];
            if (typeof id === "number" || typeof id === "string") {
              $(".wish_item.to[data-item=" + id + "]").hide();
              $(".wish_item.in[data-item=" + id + "]").show();
              if ($(".wish_item[data-item=" + id + "]").find(".value.added").length) {
                //$('.wish_item[data-item='+id+']').addClass("added");
                $(".wish_item[data-item=" + id + "]")
                  .find(".value")
                  .hide();
                $(".wish_item[data-item=" + id + "]")
                  .find(".value.added")
                  .show();
              }
            }
          }
        }
        if (data.SUBSCRIBE) {
          for (var i in data.SUBSCRIBE) {
            var id = data.SUBSCRIBE[i];
            if (typeof id === "number" || typeof id === "string") {
              $(".to-subscribe[data-item=" + id + "]").hide();
              $(".in-subscribe[data-item=" + id + "]").show();
            }
          }
        }
        if (data.COMPARE) {
          for (var i in data.COMPARE) {
            var id = data.COMPARE[i];
            if (typeof id === "number" || typeof id === "string") {
              $(".compare_item.to[data-item=" + id + "]").hide();
              $(".compare_item.in[data-item=" + id + "]").show();
              if ($(".compare_item[data-item=" + id + "]").find(".value.added").length) {
                $(".compare_item[data-item=" + id + "]")
                  .find(".value")
                  .hide();
                $(".compare_item[data-item=" + id + "]")
                  .find(".value.added")
                  .show();
              }
            }
          }
        }
      };
    }
    if (!Object.keys(arStatusBasketAspro).length) {
      if (typeof arAsproOptions === "undefined") {
        var arAsproOptions = {
          SITE_DIR: "/",
        };
        if ($("body").data("site") !== undefined) arAsproOptions["SITE_DIR"] = $("body").data("site");
      }
      $.ajax({
        url: arAsproOptions["SITE_DIR"] + "ajax/getAjaxBasket.php",
        type: "POST",
        success: function (data) {
          arStatusBasketAspro = data;
          setItemButtonStatus(arStatusBasketAspro);
        },
      });
    } else setItemButtonStatus(arStatusBasketAspro);
  }
}

if (!funcDefined("onLoadjqm")) {
  var onLoadjqm = function (name, hash, requestData, selector, requestTitle, isButton, thButton) {
    if (hash.c.noOverlay === undefined || (hash.c.noOverlay !== undefined && !hash.c.noOverlay)) {
      $("body").addClass("jqm-initied");
    }
    if (window.matchMedia("(min-width: 768px)").matches) {
      $("body").addClass("swipeignore");
    }

    if (typeof $(hash.t).data("ls") !== " undefined" && $(hash.t).data("ls")) {
      var ls = $(hash.t).data("ls"),
        ls_timeout = 0,
        v = "";

      if ($(hash.t).data("ls_timeout")) ls_timeout = $(hash.t).data("ls_timeout");

      ls_timeout = ls_timeout ? Date.now() + ls_timeout * 1000 : "";

      if (typeof localStorage !== "undefined") {
        var val = localStorage.getItem(ls);
        try {
          v = JSON.parse(val);
        } catch (e) {
          v = val;
        }
        if (v != null) {
          localStorage.removeItem(ls);
        }
        v = {};
        v["VALUE"] = "Y";
        v["TIMESTAMP"] = ls_timeout; // default: seconds for 1 day

        localStorage.setItem(ls, JSON.stringify(v));
      } else {
        var val = $.cookie(ls);
        if (!val) $.cookie(ls, "Y", { expires: ls_timeout }); // default: seconds for 1 day
      }

      var dopClasses = hash.w.find(".marketing-popup").data("classes");
      if (dopClasses) {
        hash.w.addClass(dopClasses);
      }
    }

    //show password eye
    if (hash.w.hasClass("auth_frame")) {
      hash.w.find(".form-control:not(.eye-password-ignore) [type=password]").each(function (item) {
        $(this).closest(".form-control").addClass("eye-password");
      });
    }

    $.each($(hash.t).get(0).attributes, function (index, attr) {
      if (/^data\-autoload\-(.+)$/.test(attr.nodeName)) {
        var key = attr.nodeName.match(/^data\-autoload\-(.+)$/)[1];
        var el = $('input[data-sid="' + key.toUpperCase() + '"]');
        el.val(BX.util.htmlspecialcharsback($(hash.t).data("autoload-" + key))).attr("readonly", "readonly");
        el.closest(".form-group").addClass("input-filed");
        el.attr("title", el.val());
      }
    });

    //show gift block
    if (hash.w.hasClass("send_gift_frame")) {
      var imgHtml = (priceHtml = propsHtml = "");
      if ($(".offers_img a").length) imgHtml = $(".offers_img a").html();
      else if ($(".product-detail-gallery__container .first_sku_picture").length)
        imgHtml = "<img src=" + $(".product-detail-gallery__container link.first_sku_picture").attr("href") + " />";
      else if ($(".product-detail-gallery__container").length)
        imgHtml = "<img src=" + $('.product-detail-gallery__container link[itemprop="image"]').attr("href") + " />";

      if ($('.product-container *[itemprop="offers"]').length) {
        //show price
        if ($(".sku-view").length) {
          if ($(".prices_block .price").length)
            priceHtml = $(".prices_block .cost.prices").html().replace("id", "data-id");
        } else {
          var mainContainer = $(hash.t).closest(".product-main");
          if (mainContainer.find(".prices_block .js_price_wrapper").length) {
            if (mainContainer.find(".prices_block .js_price_wrapper .with_matrix").length) {
              priceHtml =
                '<div class="with_matrix">' +
                mainContainer.find(".prices_block .js_price_wrapper .with_matrix").html() +
                "</div>";
            } else if (mainContainer.find(".prices_block .with_matrix:visible").length) {
              priceHtml =
                '<div class="with_matrix">' + mainContainer.find(".prices_block .with_matrix").html() + "</div>";
            } else if (mainContainer.find(".prices_block .price_group.min").length) {
              priceHtml =
                '<div class="with_matrix">' + mainContainer.find(".prices_block .price_group.min").html() + "</div>";
            } else {
              priceHtml =
                '<div class="with_matrix">' + mainContainer.find(".prices_block .js_price_wrapper").html() + "</div>";
            }
          } else if (mainContainer.find(".prices_block .with_matrix").length)
            priceHtml =
              '<div class="with_matrix">' + mainContainer.find(".prices_block .with_matrix").html() + "</div>";
          else if (mainContainer.find(".prices_block .price_group.min").length)
            priceHtml = mainContainer.find(".prices_block .price_group.min").html();
          else if (mainContainer.find(".prices_block .price_matrix_wrapper").length)
            priceHtml = mainContainer.find(".prices_block .price_matrix_wrapper").html();
        }
      }

      if ($(".buy_block .sku_props").length) {
        propsHtml = '<div class="props_item">';
        $(".buy_block .sku_props .bx_catalog_item_scu > div").each(function () {
          var title = $(this).find(".bx_item_section_name > span").html();
          var props = "";
          var ikSelect = $(this).find(".ik_select_link_text");
          if (ikSelect.length) {
            props = ikSelect.text();
          } else {
            var activeSku = $(this).find("ul li.active");
            var isPicture = activeSku.find(" > i");
            if (isPicture.length && isPicture.attr("title")) {
              var propTitle = isPicture.attr("title").split(":");
              if (propTitle.length) {
                propTitle = propTitle[1].trim();
              } else {
                propTitle = isPicture.attr("title");
              }
              props = propTitle;
            } else {
              props = activeSku.find(" > span").text();
            }
          }

          propsHtml +=
            '<div class="prop_item">' +
            "<span>" +
            title +
            // '<span class="val">' +
            // props +
            // "</span>" +
            "</span>" +
            "</div>";
        });
        propsHtml += "</div>";
      }
      $(
        '<div class="custom_block">' +
          '<div class="title">' +
          BX.message("POPUP_GIFT_TEXT") +
          "</div>" +
          '<div class="item_block">' +
          '<table class="item_list"><tr>' +
          '<td class="image">' +
          "<div>" +
          imgHtml +
          "</div>" +
          "</td>" +
          '<td class="text">' +
          '<div class="name">' +
          $("h1").text() +
          "</div>" +
          priceHtml +
          propsHtml +
          "</td>" +
          "</tr></table>" +
          "</div>" +
          "</div>"
      ).prependTo(hash.w.find(".form_body"));
    }

    if (
      arAsproOptions["THEME"]["REGIONALITY_SEARCH_ROW"] == "Y" &&
      (hash.w.hasClass("city_chooser_frame ") || hash.w.hasClass("city_chooser_small_frame"))
    ) {
      hash.w.addClass("small_popup_regions");
      hash.w.addClass("no_scroll");
    }

    /*if (hash.w.hasClass("city_chooser_frame ") || hash.w.hasClass("city_chooser_small_frame ")) {
      $(".popup_regions .items .items_block").mCustomScrollbar({
        mouseWheel: {
          scrollAmount: 150,
          preventDefault: true,
        },
      });
    }*/


	

    if (name == "fast_view" && $(".smart-filter-filter").length) {
      var navButtons =
        '<div class="navigation-wrapper-fast-view">' +
        '<div class="fast-view-nav prev colored_theme_hover_bg" data-fast-nav="prev">' +
        '<i class="svg left">' +
        //'<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6.969" viewBox="0 0 12 6.969"><path id="Rounded_Rectangle_702_copy_24" data-name="Rounded Rectangle 702 copy 24" class="cls-1" d="M361.691,401.707a1,1,0,0,1-1.414,0L356,397.416l-4.306,4.291a1,1,0,0,1-1.414,0,0.991,0.991,0,0,1,0-1.406l5.016-5a1.006,1.006,0,0,1,1.415,0l4.984,5A0.989,0.989,0,0,1,361.691,401.707Z" transform="translate(-350 -395.031)"/></svg>' +
        '<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M26.57 13.614a1.587 1.587 0 0 1-2.076 0L14 4.184l-10.494 9.43a1.587 1.587 0 0 1-2.076 0 1.226 1.226 0 0 1 0-1.865L12.962 1.386a1.587 1.587 0 0 1 2.076 0L26.57 11.75c.573.515.573 1.35 0 1.865Z" fill="#313132"/><path d="M12.375 25.375a1.625 1.625 0 1 0 3.25 0V2.625a1.625 1.625 0 1 0-3.25 0v22.75Z" fill="#313132"/><path fill-rule="evenodd" clip-rule="evenodd" d="M15.628 5.63v19.765a1.628 1.628 0 1 1-3.256 0V5.63l-8.885 7.984a1.59 1.59 0 0 1-2.08 0 1.229 1.229 0 0 1 0-1.868L12.785 1.52A1.632 1.632 0 0 1 14 .977a1.621 1.621 0 0 1 1.215.544l11.378 10.224a1.229 1.229 0 0 1 0 1.868 1.59 1.59 0 0 1-2.08 0l-8.885-7.984Zm.275-4.803 11.342 10.191a2.205 2.205 0 0 1 0 3.322c-.945.85-2.44.85-3.385 0l-7.255-6.52v17.575a2.605 2.605 0 1 1-5.21 0V7.82L4.14 14.34c-.946.85-2.44.85-3.385 0a2.205 2.205 0 0 1 0-3.322L12.096.827A2.598 2.598 0 0 1 14 0c.751 0 1.428.318 1.903.827Z" fill="#fff"/></svg>'+
		"</i>" +
        "</div>" +
        '<div class="fast-view-nav next colored_theme_hover_bg" data-fast-nav="next">' +
        '<i class="svg right">' +
        //'<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6.969" viewBox="0 0 12 6.969"><path id="Rounded_Rectangle_702_copy_24" data-name="Rounded Rectangle 702 copy 24" class="cls-1" d="M361.691,401.707a1,1,0,0,1-1.414,0L356,397.416l-4.306,4.291a1,1,0,0,1-1.414,0,0.991,0.991,0,0,1,0-1.406l5.016-5a1.006,1.006,0,0,1,1.415,0l4.984,5A0.989,0.989,0,0,1,361.691,401.707Z" transform="translate(-350 -395.031)"/></svg>' +
        '<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M26.57 13.614a1.587 1.587 0 0 1-2.076 0L14 4.184l-10.494 9.43a1.587 1.587 0 0 1-2.076 0 1.226 1.226 0 0 1 0-1.865L12.962 1.386a1.587 1.587 0 0 1 2.076 0L26.57 11.75c.573.515.573 1.35 0 1.865Z" fill="#313132"/><path d="M12.375 25.375a1.625 1.625 0 1 0 3.25 0V2.625a1.625 1.625 0 1 0-3.25 0v22.75Z" fill="#313132"/><path fill-rule="evenodd" clip-rule="evenodd" d="M15.628 5.63v19.765a1.628 1.628 0 1 1-3.256 0V5.63l-8.885 7.984a1.59 1.59 0 0 1-2.08 0 1.229 1.229 0 0 1 0-1.868L12.785 1.52A1.632 1.632 0 0 1 14 .977a1.621 1.621 0 0 1 1.215.544l11.378 10.224a1.229 1.229 0 0 1 0 1.868 1.59 1.59 0 0 1-2.08 0l-8.885-7.984Zm.275-4.803 11.342 10.191a2.205 2.205 0 0 1 0 3.322c-.945.85-2.44.85-3.385 0l-7.255-6.52v17.575a2.605 2.605 0 1 1-5.21 0V7.82L4.14 14.34c-.946.85-2.44.85-3.385 0a2.205 2.205 0 0 1 0-3.322L12.096.827A2.598 2.598 0 0 1 14 0c.751 0 1.428.318 1.903.827Z" fill="#fff"/></svg>'+
		"</i>" +
        "</div>" +
        "</div>";

		
      hash.w.closest("#popup_iframe_wrapper").append(navButtons);
    }

    hash.w.addClass("scrollblock");
    hash.w.addClass("show").css({
      // 'top': (($(window).height() > hash.w.height()) ? Math.floor(($(window).height() - hash.w.height()) / 2) : 0) + 'px',
      // 'margin-left': ($(window).width() > hash.w.outerWidth() ? '-' + hash.w.outerWidth() / 2 + 'px' : '-' + $(window).width() / 2 + 'px'),
      opacity: 1,
    });

    /*if (!hash.w.hasClass("no_custom_scroll")) {
      hash.w.mCustomScrollbar({
        mouseWheel: {
          scrollAmount: 150,
          preventDefault: true,
        },
      });
    }*/

    if (hash.c.noOverlay === undefined || (hash.c.noOverlay !== undefined && !hash.c.noOverlay)) {
      $("body").css({ overflow: "hidden", height: "100vh" });
      hash.w.closest("#popup_iframe_wrapper").css({ "z-index": 3000, display: "flex" });
    }

    var eventdata = { action: "loadForm" };
    BX.onCustomEvent("onCompleteAction", [eventdata, $(hash.t)[0]]);

    if (typeof requestData == "undefined") {
      requestData = "";
    }
    if (typeof selector == "undefined") {
      selector = false;
    }

    var width = $("." + name + "_frame").width();
    //$('.'+name+'_frame').css('margin-left', '-'+width/2+'px');

    if (name == "order-popup-call") {
    } else if (name == "order-button") {
      $(".order-button_frame")
        .find("div[product_name]")
        .find("input")
        .val(hash.t.title)
        .attr("readonly", "readonly")
        .css({ overflow: "hidden", "text-overflow": "ellipsis" });
    } else if (name == "basket_error") {
      $(".basket_error_frame .pop-up-title").text(requestTitle);
      $(".basket_error_frame .ajax_text").html(requestData);

      if (window.matchMedia("(max-width: 991px)").matches) {
        $("body").addClass("all_viewed");
      }

      initSelects(document);
      if (isButton == "Y" && thButton)
        $(
          "<div class='popup_button_basket_wr'><span class='popup_button_basket big_btn button' data-item=" +
            thButton.data("item") +
            "><span class='btn btn-default'>" +
            BX.message("ERROR_BASKET_BUTTON") +
            "</span></span></div>"
        ).insertAfter($(".basket_error_frame .ajax_text"));
    } else if (name === "fast_view") {
      initSelects(document);
    }

    $("." + name + "_frame").show();
  };
}

if (!funcDefined("onHidejqm")) {
  var onHidejqm = function (name, hash) {
    if (hash.w.find(".one_click_buy_result_success").is(":visible") && name == "one_click_buy_basket") {
      window.location.href = window.location.href;
    }

    if ($(".xzoom-source").length) $(".xzoom-source").remove();
    if ($(".xzoom-preview").length) $(".xzoom-preview").remove();

    // hash.w.css('opacity', 0).hide();
    hash.w.animate({ opacity: 0 }, 200, function () {
      // hash.w.removeClass("scroll-init").mCustomScrollbar("destroy");
      hash.w.hide();
      hash.w.empty();
      hash.o.remove();
      hash.w.removeClass("show");

      $("body").css({ overflow: "", height: "" });

      if (!hash.w.closest("#popup_iframe_wrapper").find(".jqmOverlay").length) {
        hash.w.closest("#popup_iframe_wrapper").css({ "z-index": "", display: "" });
      }

      if (window.matchMedia("(max-width: 991px)").matches) {
        $("body").removeClass("all_viewed");
      }
      if (!$(".jqmOverlay:not(.mobp)").length || $(".jqmOverlay.waiting").length) {
        $("body").removeClass("jqm-initied");
      }

      if (window.matchMedia("(min-width: 768px)").matches) {
        $("body").removeClass("swipeignore");
      }

      if (name == "fast_view") {
        $(".fast_view_popup").remove();

        var navButtons = hash.w.closest("#popup_iframe_wrapper").find(".navigation-wrapper-fast-view");
        navButtons.remove();
      }
    });

    window.b24form = false;
  };
}

$.fn.jqmEx = function () {
  // $(this).each(function(){
  var _this = $(this);
  var name = _this.data("name");

  if (name.length && _this.attr("disabled") != "disabled") {
    var extClass = "",
      paramsStr = "",
      trigger = "",
      arTriggerAttrs = {};

    // call counter
    if (typeof $.fn.jqmEx.counter === "undefined") {
      $.fn.jqmEx.counter = 0;
    } else {
      ++$.fn.jqmEx.counter;
    }

    // trigger attrs and params
    $.each(_this.get(0).attributes, function (index, attr) {
      var attrName = attr.nodeName;
      var attrValue = _this.attr(attrName);
      if (attrName !== "onclick") {
        trigger += "[" + attrName + '="' + attrValue + '"]';
        arTriggerAttrs[attrName] = attrValue;
      }
      if (/^data\-param\-(.+)$/.test(attrName)) {
        var key = attrName.match(/^data\-param\-(.+)$/)[1];
        paramsStr += key + "=" + attrValue + "&";
      }
    });
    var triggerAttrs = JSON.stringify(arTriggerAttrs);
    var encTriggerAttrs = encodeURIComponent(triggerAttrs);

    // popup url
    var script = arAsproOptions["SITE_DIR"] + "ajax/form.php";
    if (name == "auth") {
      script += "?" + paramsStr + "auth=Y";
    } else {
      script += "?" + paramsStr + "data-trigger=" + encTriggerAttrs;
    }

    // ext frame class
    if (_this.closest("#fast_view_item").length) {
      extClass = "fast_view_popup";
    }

    // use overlay?
    var noOverlay = _this.data("noOverlay") == "Y";

    // unique frame to each trigger
    if (noOverlay) {
      var frame = $(
        '<div class="' +
          name +
          "_frame " +
          extClass +
          ' jqmWindow popup" data-popup="' +
          $.fn.jqmEx.counter +
          '" data-trigger="' +
          encTriggerAttrs +
          '"></div>'
      ).appendTo("body");
    } else {
      var frame = $(
        '<div class="' +
          name +
          "_frame " +
          extClass +
          ' jqmWindow popup" data-popup="' +
          $.fn.jqmEx.counter +
          '" data-trigger="' +
          encTriggerAttrs +
          '"></div>'
      ).appendTo("#popup_iframe_wrapper");
    }

    frame.jqm({
      ajax: script,
      trigger: trigger,
      noOverlay: noOverlay,
      onLoad: function (hash) {
        onLoadjqm(name, hash);
      },
      onHide: function (hash) {
        onHidejqm(name, hash);
      },
    });
  }
  // });
};

window.addEventListener("keydown", function (e) {
  if (e.keyCode == 27) {
    $(".jqm-init.show").last().jqmHide();

    if ($(".inline-search-block.show").length) {
      $(".inline-search-block").toggleClass("show");
      $(".jqmOverlay.search").detach();
    }

    $(".mega_fixed_menu").fadeOut(animationTime);
  }
});

if (!funcDefined("scroll_block")) {
  function scroll_block(block, clickedItem) {
    if (block.length) {
      if (clickedItem !== undefined && clickedItem.length) {
        clickedItem.trigger("click");
      } else {
        $(".prices_tab").addClass("active").siblings().removeClass("active");
        if ($(".prices_tab .opener").length && !$(".prices_tab .opener .opened").length) {
          var item = $(".prices_tab .opener").first();
          item.find(".opener_icon").addClass("opened");
          item.parents("tr").addClass("nb");
          item.parents("tr").next(".offer_stores").find(".stores_block_wrap").slideDown(200);
        }
      }
      var topPos = block.offset().top,
        headerH = $("header").outerHeight(true, true);

      $("html,body").animate({ scrollTop: topPos - 88 }, 150);
    }
  }
}

if (!funcDefined("jqmEd")) {
  var jqmEd = function (name, form_id, open_trigger, requestData, selector, requestTitle, isButton, thButton) {
    if (typeof requestData == "undefined") {
      requestData = "";
    }
    if (typeof selector == "undefined") {
      selector = false;
    }
    $("body #popup_iframe_wrapper")
      .find("." + name + "_frame")
      .remove();
    $("body #popup_iframe_wrapper").append('<div class="' + name + '_frame jqmWindow popup"></div>');
    if (typeof open_trigger == "undefined") {
      $("." + name + "_frame").jqm({
        trigger: "." + name + "_frame.popup",
        onHide: function (hash) {
          onHidejqm(name, hash);
        },
        onLoad: function (hash) {
          onLoadjqm(name, hash, requestData, selector);
        },
        ajax:
          arAsproOptions["SITE_DIR"] +
          "ajax/form.php?form_id=" +
          form_id +
          (requestData.length ? "&" + requestData : ""),
      });
    } else {
      if (name == "enter") {
        $("." + name + "_frame").jqm({
          trigger: open_trigger,
          onHide: function (hash) {
            onHidejqm(name, hash);
          },
          onLoad: function (hash) {
            onLoadjqm(name, hash, requestData, selector);
          },
          ajax: arAsproOptions["SITE_DIR"] + "ajax/auth.php",
        });
      } else if (name == "basket_error") {
        $("." + name + "_frame").jqm({
          trigger: open_trigger,
          onHide: function (hash) {
            onHidejqm(name, hash);
          },
          onLoad: function (hash) {
            onLoadjqm(name, hash, requestData, selector, requestTitle, isButton, thButton);
          },
          ajax: arAsproOptions["SITE_DIR"] + "ajax/basket_error.php",
        });
      } else {
        $("." + name + "_frame").jqm({
          trigger: open_trigger,
          onHide: function (hash) {
            onHidejqm(name, hash);
          },
          onLoad: function (hash) {
            onLoadjqm(name, hash, requestData, selector);
          },
          ajax:
            arAsproOptions["SITE_DIR"] +
            "ajax/form.php?form_id=" +
            form_id +
            (requestData.length ? "&" + requestData : ""),
        });
      }
      $(open_trigger).dblclick(function () {
        return false;
      });
    }
    return true;
  };
}

if (!funcDefined("replaceBasketPopup")) {
  function replaceBasketPopup(hash) {
    if (typeof hash != "undefined") {
      hash.w.hide();
      hash.o.hide();
    }
  }
}

if (!funcDefined("waitLayer")) {
  function waitLayer(delay, callback) {
    if (typeof dataLayer !== "undefined" && typeof callback === "function") {
      callback();
    } else {
      setTimeout(function () {
        waitLayer(delay, callback);
      }, delay);
    }
  }
}

if (!funcDefined("checkCounters")) {
  function checkCounters(name) {
    if (typeof name !== "undefined") {
      if (
        name == "google" &&
        arAsproOptions["COUNTERS"]["GOOGLE_ECOMERCE"] == "Y" &&
        arAsproOptions["COUNTERS"]["GOOGLE_COUNTER"] > 0
      ) {
        return true;
      } else if (
        name == "yandex" &&
        arAsproOptions["COUNTERS"]["YANDEX_ECOMERCE"] == "Y" &&
        arAsproOptions["COUNTERS"]["YANDEX_COUNTER"] > 0
      ) {
        return true;
      } else {
        return false;
      }
    } else if (
      (arAsproOptions["COUNTERS"]["YANDEX_ECOMERCE"] == "Y" && arAsproOptions["COUNTERS"]["YANDEX_COUNTER"] > 0) ||
      (arAsproOptions["COUNTERS"]["GOOGLE_ECOMERCE"] == "Y" && arAsproOptions["COUNTERS"]["GOOGLE_COUNTER"] > 0)
    ) {
      return true;
    } else {
      return false;
    }
  }
}

if (!funcDefined("addBasketCounter")) {
function addBasketCounter(id) {
    if (arAsproOptions["COUNTERS"]["USE_BASKET_GOALS"] !== "N") {
      var eventdata = { goal: "goal_basket_add", params: { id: id } };
      BX.onCustomEvent("onCounterGoals", [eventdata]);
    }
    if (checkCounters()) {
      $.ajax({
        url: arAsproOptions["SITE_DIR"] + "ajax/goals.php",
        dataType: "json",
        type: "POST",
        data: { ID: id },
        success: function (item) {
          if (!!item && !!item.ID) {
            let ecommerce =  {
              items: [
                {
                  item_name: item.NAME, // Name or ID is required.
                  item_id: item.ID,
                  price: parseFloat(item.PRICE),
                  item_brand: item.BRAND,
                  item_category: item.CATEGORY,
                  item_list_name: "List Results",
                  item_list_id: item.IBLOCK_SECTION_ID,
                  affiliation: item.SHOP_NAME,
                  index: 1,
                  quantity: parseFloat(item.QUANTITY),
                },
              ],
            }
            if (arAsproOptions["COUNTERS"]["GA_VERSION"] === "v3") {
              ecommerce = {
                currencyCode: item.CURRENCY,
                add: {
                  products: [
                    {
                      id: item.ID,
                      name: item.NAME,
                      price: parseFloat(item.PRICE),
                      brand: item.BRAND,
                      category: item.CATEGORY,
                      quantity: parseFloat(item.QUANTITY),
                    },
                  ],
                },
              }
            }
            waitLayer(100, function () {
//               dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
//               dataLayer.push({
//                 // event: 'add_to_cart',
//                 event: arAsproOptions["COUNTERS"]["GOOGLE_EVENTS"]["ADD2BASKET"],
//                 currency: item.CURRENCY,
//                 value: parseFloat(item.PRICE),
//                 ecommerce: ecommerce,
//               });
            });
          }
        },
      });
    }
  }
}

if (!funcDefined("purchaseCounter")) {
  function purchaseCounter(order_id, type, callback) {
    if (checkCounters()) {
      $.ajax({
        url: arAsproOptions["SITE_DIR"] + "ajax/goals.php",
        dataType: "json",
        type: "POST",
        data: { ORDER_ID: order_id, TYPE: type },
        success: function (order) {
          var products = [];
          const items = [];
          if (order.ITEMS) {
            for (var i in order.ITEMS) {
              products.push({
                id: order.ITEMS[i].ID,
                sku: order.ITEMS[i].ID,
                name: order.ITEMS[i].NAME,
                price: order.ITEMS[i].PRICE,
                brand: order.ITEMS[i].BRAND,
                category: order.ITEMS[i].CATEGORY,
                quantity: order.ITEMS[i].QUANTITY,
              });
              items.push({
                item_id: order.ITEMS[i].ID,
                item_name: order.ITEMS[i].NAME,
                price: parseFloat(order.ITEMS[i].PRICE),
                item_brand: order.ITEMS[i].BRAND,
                item_category: order.ITEMS[i].CATEGORY,
                affiliation: order.SHOP_NAME,
                quantity: parseFloat(order.ITEMS[i].QUANTITY),
              });
            }
          }
          if (order.ID) {
            let ecommerce =  {
              transaction_id: order.ACCOUNT_NUMBER,
              affiliation: order.SHOP_NAME,
              value: order.PRICE,
              tax: order.TAX_VALUE,
              shipping: order.PRICE_DELIVERY,
              currency: order.CURRENCY,
              items: items,
            }
            if (arAsproOptions["COUNTERS"]["GA_VERSION"] === "v3") {
              ecommerce = {
                purchase: {
                  actionField: {
                    id: order.ACCOUNT_NUMBER,
                    shipping: order.PRICE_DELIVERY,
                    tax: order.TAX_VALUE,
                    list: type,
                    revenue: order.PRICE,
                  },
                  products: products,
                }
              }
            }
            waitLayer(100, function () {
//               dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
//               dataLayer.push({
//                 event: arAsproOptions["COUNTERS"]["GOOGLE_EVENTS"]["PURCHASE"],
//                 ecommerce: ecommerce,
//               });

              if (typeof callback !== "undefined") {
                callback(ecommerce);
              }
            });
          } else {
            if (typeof callback !== "undefined") {
              callback();
            }
          }
        },
        error: function () {
          if (typeof callback !== "undefined") {
            callback();
          }
        },
      });
    }
  }
}

if (!funcDefined("viewItemCounter")) {
  function viewItemCounter(id, price_id) {
    if (checkCounters()) {
      $.ajax({
        url: arAsproOptions["SITE_DIR"] + "ajax/goals.php",
        dataType: "json",
        type: "POST",
        data: { PRODUCT_ID: id, PRICE_ID: price_id },
        success: function (item) {
          if (item.ID) {
            let ecommerce =  {
              items: [
                {
                  item_name: item.NAME, // Name or ID is required.
                  item_id: item.ID,
                  price: parseFloat(item.PRICE),
                  item_brand: item.BRAND,
                  item_category: item.CATEGORY,
                  item_list_name: "List Results",
                  item_list_id: item.IBLOCK_SECTION_ID,
                  affiliation: item.SHOP_NAME,
                  index: 1,
                  quantity: parseFloat(item.QUANTITY),
                },
              ],
            }
            if (arAsproOptions["COUNTERS"]["GA_VERSION"] === "v3") {
              ecommerce = {
                detail: {
                  products: [
                    {
                      id: item.ID,
                      name: item.NAME,
                      price: parseFloat(item.PRICE),
                      brand: item.BRAND,
                      category: item.CATEGORY,
                    },
                  ],
                },
              }
            }
//             waitLayer(100, function () {
//               dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
//               dataLayer.push({
//                 "event": "view_item",
//                 currency: item.CURRENCY,
//                 value: parseFloat(item.PRICE),
//                 ecommerce: ecommerce,
//               });
//             });
          };
        },
      });
    }
  }
}

if (!funcDefined("checkoutCounter")) {
  function checkoutCounter(step, option, callback) {
    if (checkCounters("google")) {
      $.ajax({
        url: arAsproOptions["SITE_DIR"] + "ajax/goals.php",
        dataType: "json",
        type: "POST",
        data: { BASKET: "Y" },
        success: function (basket) {
          var products = [];
          const items = [];
          let summ = 0;
          let currency = 'RUB';
          if (basket.ITEMS) {
            for (var i in basket.ITEMS) {
              products.push({
                id: basket.ITEMS[i].ID,
                name: basket.ITEMS[i].NAME,
                price: basket.ITEMS[i].PRICE,
                brand: basket.ITEMS[i].BRAND,
                category: basket.ITEMS[i].CATEGORY,
                quantity: basket.ITEMS[i].QUANTITY,
              });
              items.push({
                item_id: basket.ITEMS[i].ID,
                item_name: basket.ITEMS[i].NAME,
                price: parseFloat(basket.ITEMS[i].PRICE),
                item_brand: basket.ITEMS[i].BRAND,
                item_category: basket.ITEMS[i].CATEGORY,
                affiliation: basket.SHOP_NAME,
                quantity: parseFloat(basket.ITEMS[i].QUANTITY),
              });
              summ += basket.ITEMS[i].PRICE;
              currency = basket.ITEMS[i].CURRENCY;
            }
          }
          if (products) {
            let ecommerce =  {
              items: items,
            }
            if (arAsproOptions["COUNTERS"]["GA_VERSION"] === "v3") {
              ecommerce = {
                checkout: {
                  actionField: {
                    step: step,
                    option: option,
                  },
                  products: products,
                },
              }
            }
            waitLayer(100, function () {
              dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
              dataLayer.push({
                event: arAsproOptions["COUNTERS"]["GOOGLE_EVENTS"]["CHECKOUT_ORDER"],
                currency: currency,
                value: parseFloat(summ),
                ecommerce: ecommerce,
                /*"eventCallback": function() {
                    if((typeof callback !== 'undefined') && (typeof callback === 'function')){
                      callback();
                    }
                 }*/
              });
              if((typeof callback !== 'undefined') && (typeof callback === 'function')){
                callback();
              }
            });
          }
        },
      });
    }
  }
}

if (!funcDefined("delFromBasketCounter")) {
  function delFromBasketCounter(id, callback) {
    if (checkCounters()) {
      $.ajax({
        url: arAsproOptions["SITE_DIR"] + "ajax/goals.php",
        dataType: "json",
        type: "POST",
        data: { ID: id },
        success: function (item) {
          if (item.ID) {
            let ecommerce =  {
              items: [
                {
                  item_name: item.NAME, // Name or ID is required.
                  item_id: item.ID,
                  price: parseFloat(item.PRICE),
                  item_brand: item.BRAND,
                  item_category: item.CATEGORY,
                  affiliation: item.SHOP_NAME,
                  item_list_name: "List Results",
                },
              ],
            }
            if (arAsproOptions["COUNTERS"]["GA_VERSION"] === "v3") {
              ecommerce = {
                remove: {
                  products: [
                    {
                      id: item.ID,
                      name: item.NAME,
                      category: item.CATEGORY,
                    },
                  ],
                },
              }
            }
            waitLayer(100, function () {
//               dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
//               dataLayer.push({
//                 event: arAsproOptions["COUNTERS"]["GOOGLE_EVENTS"]["REMOVE_BASKET"],
//                 currency: item.CURRENCY,
//                 value: parseFloat(item.PRICE),
//                 ecommerce: ecommerce,
//               });
              if (typeof callback == "function") {
                callback();
              }
            });
          }
        },
      });
    }
  }
}

if (!funcDefined("setHeightCompany")) {
  function setHeightCompany() {
    $(".md-50.img").height($(".md-50.big").outerHeight() - 35);
  }
}

if (!funcDefined("initSly")) {
  function initSly() {
    /*var $frame  = $(document).find('.frame');
    var $slidee = $frame.children('ul').eq(0);
    var $wrap   = $frame.parent();

    if(arAsproOptions["PAGES"]["CATALOG_PAGE"] && $frame.length){
      $frame.sly({
        horizontal: 1,
        itemNav: 'basic',
        smart: 1,
        mouseDragging: 0,
        touchDragging: 0,
        releaseSwing: 0,
        startAt: 0,
        scrollBar: $wrap.find('.scrollbar'),
        scrollBy: 1,
        speed: 300,
        elasticBounds: 0,
        easing: 'swing',
        dragHandle: 1,
        dynamicHandle: 1,
        clickBar: 1,

        // Buttons
        forward: $wrap.find('.forward'),
        backward: $wrap.find('.backward'),
      });
      $frame.sly('reload');
    }*/
  }
}

if (!funcDefined("createTableCompare")) {
  function createTableCompare(originalTable, appendDiv, cloneTable) {
    try {
      var clone = originalTable.clone().removeAttr("id").addClass("clone");
      if (cloneTable.length) {
        cloneTable.remove();
        appendDiv.html("");
        appendDiv.html(clone);
      } else {
        appendDiv.append(clone);
      }
    } catch (e) {
    } finally {
    }
  }
}

if (!funcDefined("fillBasketPropsExt")) {
  fillBasketPropsExt = function (that, prop_code, basket_prop_div) {
    var i = 0,
      propCollection = null,
      foundValues = false,
      basketParams = {},
      obBasketProps = null;

    obBasketProps = BX(basket_prop_div);
    if (!obBasketProps && that.closest(".item").find(".basket_props_block").length && that.data("offers") !== "Y")
      obBasketProps = that.closest(".item").find(".basket_props_block")[0];

    if (!!obBasketProps) {
      propCollection = obBasketProps.getElementsByTagName("select");
      if (!!propCollection && !!propCollection.length) {
        for (i = 0; i < propCollection.length; i++) {
          if (!propCollection[i].disabled) {
            switch (propCollection[i].type.toLowerCase()) {
              case "select-one":
                basketParams[propCollection[i].name] = propCollection[i].value;
                foundValues = true;
                break;
              default:
                break;
            }
          }
        }
      }
      propCollection = obBasketProps.getElementsByTagName("input");
      if (!!propCollection && !!propCollection.length) {
        for (i = 0; i < propCollection.length; i++) {
          if (!propCollection[i].disabled) {
            switch (propCollection[i].type.toLowerCase()) {
              case "hidden":
                basketParams[propCollection[i].name] = propCollection[i].value;
                foundValues = true;
                break;
              case "radio":
                if (propCollection[i].checked) {
                  basketParams[propCollection[i].name] = propCollection[i].value;
                  foundValues = true;
                }
                break;
              default:
                break;
            }
          }
        }
      }
    }
    if (!foundValues) {
      basketParams[prop_code] = [];
      basketParams[prop_code][0] = 0;
    }
    return basketParams;
  };
}
if (!funcDefined("showBasketError")) {
  showBasketError = function (mess, title, addButton, th, callback) {
    var title_set = title ? title : BX.message("ERROR_BASKET_TITLE"),
      isButton = "N",
      thButton = "";
    if (typeof addButton !== undefined) {
      isButton = "Y";
    }
    if (typeof th !== undefined) {
      thButton = th;
    }
    $("body").append("<span class='add-error-bakset' style='display:none;'></span>");
   // jqmEd("basket_error", "error-bakset", ".add-error-bakset", mess, this, title_set, isButton, thButton);
    $("body .add-error-bakset").click();
    $("body .add-error-bakset").remove();

    if (typeof callback === "function") {
      callback();
    }
  };
}

CheckTopVisibleMenu = function (that) {
  var dropdownMenu = $(".dropdown-menu:visible");
  if (that !== undefined) {
    dropdownMenu.push(that);
  }

  if (dropdownMenu.length) {
    dropdownMenu.each(function (i, el) {
      var dropdownMenuCurrent = $(el);
      dropdownMenuCurrent.find("a").css("white-space", "");
      dropdownMenuCurrent.css("left", "");
      dropdownMenuCurrent.css("right", "");
      dropdownMenuCurrent.removeClass("toright");

      var dropdownMenuCurrent_left = dropdownMenuCurrent.offset().left;

      if (typeof dropdownMenuCurrent_left != "undefined") {
        var menu = dropdownMenuCurrent.parents(".mega-menu");
        if (!menu.length) menu = dropdownMenuCurrent.closest(".logo-row");
        var menu_width = menu.outerWidth();
        var menu_left = menu.offset().left;
        var menu_right = menu_left + menu_width;
        var isToRight = dropdownMenuCurrent.parents(".toright").length > 0;
        var parentsdropdownMenuCurrents = dropdownMenuCurrent.parents(".dropdown-menu");
        var isHasParentdropdownMenuCurrent = parentsdropdownMenuCurrents.length > 0;
        if (isHasParentdropdownMenuCurrent) {
          var parentdropdownMenuCurrent_width = parentsdropdownMenuCurrents.first().outerWidth();
          var parentdropdownMenuCurrent_left = parentsdropdownMenuCurrents.first().offset().left;
          var parentdropdownMenuCurrent_right = parentdropdownMenuCurrent_width + parentdropdownMenuCurrent_left;
        }

        if (parentdropdownMenuCurrent_right + dropdownMenuCurrent.outerWidth() > menu_right) {
          dropdownMenuCurrent.find("a").css("white-space", "normal");
        }

        var dropdownMenuCurrent_width = dropdownMenuCurrent.outerWidth();
        var dropdownMenuCurrent_right = dropdownMenuCurrent_left + dropdownMenuCurrent_width;

        if (dropdownMenuCurrent_right > menu_right || isToRight) {
          var addleft = 0;
          addleft = menu_right - dropdownMenuCurrent_right;
          if (isHasParentdropdownMenuCurrent || isToRight) {
            dropdownMenuCurrent.css("left", "auto");
            dropdownMenuCurrent.css("right", "100%");
            dropdownMenuCurrent.addClass("toright");
          } else {
            var dropdownMenuCurrent_curLeft = parseInt(dropdownMenuCurrent.css("left"));
            dropdownMenuCurrent.css("left", dropdownMenuCurrent_curLeft + addleft + "px");
          }
        }
      }
    });
  }
};

if (!funcDefined("isRealValue")) {
  function isRealValue(obj) {
    return obj && obj !== "null" && obj !== "undefined";
  }
}

if (!funcDefined("rightScroll")) {
  function rightScroll(prop, id) {
    var el = BX("prop_" + prop + "_" + id);
    if (el) {
      var curVal = parseInt(el.style.marginLeft);
      if (curVal >= 0) el.style.marginLeft = curVal - 20 + "%";
    }
  }
}

if (!funcDefined("leftScroll")) {
  function leftScroll(prop, id) {
    var el = BX("prop_" + prop + "_" + id);
    if (el) {
      var curVal = parseInt(el.style.marginLeft);
      if (curVal < 0) el.style.marginLeft = curVal + 20 + "%";
    }
  }
}

if (!funcDefined("InitOrderCustom")) {
  InitOrderCustom = function () {
    $(".ps_logo img").wrap('<div class="image"></div>');

    $("#bx-soa-order .radio-inline").each(function () {
      if ($(this).find("input").attr("checked") == "checked") {
        $(this).addClass("checked");
      }
    });

    $("#bx-soa-order .checkbox input[type=checkbox]").each(function () {
      if ($(this).attr("checked") == "checked") $(this).parent().addClass("checked");
    });

    $("#bx-soa-order .bx-authform-starrequired").each(function () {
      var html = $(this).html();
      $(this)
        .closest("label")
        .append('<span class="bx-authform-starrequired"> ' + html + "</span>");
      $(this).detach();
    });
    $(".bx_ordercart_coupon").each(function () {
      if ($(this).find(".bad").length) $(this).addClass("bad");
      else if ($(this).find(".good").length) $(this).addClass("good");
    });
    /*if (typeof(propsMap) !== 'undefined') {
      $(propsMap).on('click', function () {
        var value = $('#orderDescription').val();
        if ($('#orderDescription')) {
          if (value != '') {
            $('#orderDescription').closest('.form-group').addClass('value_y');
          }
        }
      });
    }*/
  };
}

if (!funcDefined("InitLabelAnimation")) {
  InitLabelAnimation = function (className) {
    // Fix order labels
    if (!$(className).length) {
      return;
    }
    $(className)
      .find(".form-group")
      .each(function () {
        if (
          $(this).find("input[type=text], textarea").length &&
          !$(this).find(".dropdown-block").length &&
          $(this).find("input[type=text], textarea").val() != ""
        ) {
          $(this).addClass("value_y");
        }
      });

    $(document).on("click", className + " .form-group:not(.bx-soa-pp-field) label", function () {
      $(this).parent().find("input, textarea").focus();
    });

    $(document).on(
      "focusout",
      className +
        " .form-group:not(.bx-soa-pp-field) input, " +
        className +
        " .form-group:not(.bx-soa-pp-field) textarea",
      function () {
        var value = $(this).val();
        if (
          value != "" &&
          !$(this).closest(".form-group").find(".dropdown-block").length &&
          !$(this).closest(".form-group").find("#profile_change").length
        ) {
          $(this).closest(".form-group").addClass("value_y");
        } else {
          $(this).closest(".form-group").removeClass("value_y");
        }
      }
    );

    $(document).on(
      "focus",
      className +
        " .form-group:not(.bx-soa-pp-field) input, " +
        className +
        " .form-group:not(.bx-soa-pp-field) textarea",
      function () {
        if (
          !$(this).closest(".form-group").find(".dropdown-block").length &&
          !$(this).closest(".form-group").find("#profile_change").length &&
          !$(this).closest(".form-group").find("[name=PERSON_TYPE_OLD]").length
        ) {
          $(this).closest(".form-group").addClass("value_y");
        }
      }
    );
  };
}

checkPopupWidth = function () {
  $(".popup.show").each(function () {
    var width_form = $(this).actual("width");
    $(this).css({
      "margin-left": $(window).width() > width_form ? "-" + width_form / 2 + "px" : "-" + $(window).width() / 2 + "px",
    });
  });
};

checkCaptchaWidth = function () {
  $(".captcha-row").each(function () {
    var width = $(this).actual("width");
    if ($(this).hasClass("b")) {
      if (width > 320) {
        $(this).removeClass("b");
      }
    } else {
      if (width <= 320) {
        $(this).addClass("b");
      }
    }
  });
};

checkFormWidth = function () {
  $(".form .form_left").each(function () {
    var form = $(this).parents(".form");
    var width = form.actual("width");
    if (form.hasClass("b")) {
      if (width > 417) {
        form.removeClass("b");
      }
    } else {
      if (width <= 417) {
        form.addClass("b");
      }
    }
  });
};

checkFormControlWidth = function () {
  $(".form-control").each(function () {
    var width = $(this).actual("width");
    var labelWidth = $(this).find("label:not(.error) > span").actual("width");
    var errorWidth = $(this).find("label.error").actual("width");
    if (errorWidth > 0) {
      if ($(this).hasClass("h")) {
        if (width > labelWidth + errorWidth + 5) {
          $(this).removeClass("h");
        }
      } else {
        if (width <= labelWidth + errorWidth + 5) {
          $(this).addClass("h");
        }
      }
    } else {
      $(this).removeClass("h");
    }
  });
};

scrollToTop = function () {
  if (arAsproOptions["THEME"]["SCROLLTOTOP_TYPE"] !== "NONE") {
    var _isScrolling = false;
    // Append Button
    $("body").append(
      $("<a />")
        .addClass(
          "scroll-to-top " +
            arAsproOptions["THEME"]["SCROLLTOTOP_TYPE"] +
            " " +
            arAsproOptions["THEME"]["SCROLLTOTOP_POSITION"]
        )
        .attr({ href: "#", id: "scrollToTop" })
    );

    if (arAsproOptions["THEME"]["SCROLLTOTOP_POSITION_BOTTOM"]) {
      $("#scrollToTop").css("bottom", +arAsproOptions["THEME"]["SCROLLTOTOP_POSITION_BOTTOM"] + "px");
    }

    if (arAsproOptions["THEME"]["SCROLLTOTOP_POSITION_RIGHT"]) {
      $("#scrollToTop").css("right", +arAsproOptions["THEME"]["SCROLLTOTOP_POSITION_RIGHT"] + "px");
    }

    $("#scrollToTop").click(function (e) {
      e.preventDefault();
      $("body, html").animate({ scrollTop: 0 }, 500);
      return false;
    });
    // Show/Hide Button on Window Scroll event.
    $(window).scroll(function () {
      if (!_isScrolling) {
        _isScrolling = true;
        if ($(window).scrollTop() > 150) {
          $("#scrollToTop").stop(true, true).addClass("visible");
          _isScrolling = false;
        } else {
          $("#scrollToTop").stop(true, true).removeClass("visible");
          _isScrolling = false;
        }
        checkScrollToTop();
      }
    });
  }
};

checkScrollToTop = function () {
  if (arAsproOptions["THEME"] && arAsproOptions["THEME"]["SCROLLTOTOP_POSITION_BOTTOM"])
    var bottom = +arAsproOptions["THEME"]["SCROLLTOTOP_POSITION_BOTTOM"];
  else var bottom = 55;

  var scrollVal = $(window).scrollTop(),
    windowHeight = $(window).height(),
    footerOffset = 0;
  if ($("footer").length) footerOffset = $("footer .footer-inner").offset().top;

  if (arAsproOptions["THEME"] && arAsproOptions["THEME"]["SCROLLTOTOP_POSITION"] == "CONTENT") {
    warpperWidth = $("body > .wrapper > .wrapper_inner").width();
    $("#scrollToTop").css("margin-left", Math.ceil(warpperWidth / 2) + 23);
  }

  if (scrollVal + windowHeight > footerOffset) {
    $("#scrollToTop").css("bottom", Math.round(bottom + scrollVal + windowHeight - footerOffset) + "px");
  } else if (parseInt($("#scrollToTop").css("bottom")) > bottom) {
    $("#scrollToTop").css("bottom", Math.round(bottom));
  }
};

CheckObjectsSizes = function () {
  $(".container iframe,.container object,.container video").each(function () {
    var height_attr = $(this).attr("height");
    var width_attr = $(this).attr("width");
    if (height_attr && width_attr) {
      $(this).css("height", ($(this).outerWidth() * height_attr) / width_attr);
    }
  });
};

if (!funcDefined("reloadTopBasket")) {
  var reloadTopBasket = function reloadTopBasket(action, basketWindow, speed, delay, slideDown, item, sync) {
    var obj = {
      PARAMS: $("#top_basket_params").val(),
      ACTION: action,
    };
    if (typeof item !== "undefined" && item) {
      obj.delete_top_item = "Y";
      obj.delete_top_item_id = item.data("id");
    }
    // $.post( arAsproOptions['SITE_DIR']+"ajax/show_basket_popup.php", obj, $.proxy(function( data ){
    $.post(
      arAsproOptions["SITE_DIR"] + "ajax/show_basket_actual.php",
      obj,
      $.proxy(function (data) {
        $(basketWindow).html(data);

        //getActualBasket('', '', sync);

        getActualBasket("", "Compare", sync); //need for actual compare count when do opt action

        var eventdata = { action: "loadBasket" };
        BX.onCustomEvent("onCompleteAction", [eventdata]);

        /*if(arAsproOptions['THEME']['SHOW_BASKET_ONADDTOCART'] !== 'N'){
        if($(window).outerWidth() > 520){
          if(slideDown=="Y")
            $(basketWindow).find('.basket_popup_wrapp').stop(true,true).slideDown(speed);
          clearTimeout(basketTimeoutSlide);
          basketTimeoutSlide = setTimeout(function() {
            var _this = $('#basket_line').find('.basket_popup_wrapp');
            if (_this.is(':hover')) {
              _this.show();
            }else{
              $('#basket_line').find('.basket_popup_wrapp').slideUp(speed);
            }
          },delay);
        }
      }*/
      })
    );
  };
}

CheckTabActive = function () {
  if (typeof clicked_tab && clicked_tab) {
    if (window.matchMedia("(min-width: 768px)").matches) {
      clicked_tab--;
      $(".nav.nav-tabs li").each(function () {
        if ($(this).index() == clicked_tab) $(this).addClass("active");
      });
      // $('.nav.nav-tabs li:eq('+clicked_tab+')').addClass('active');
      $(".catalog_detail .tab-content .tab-pane:eq(" + clicked_tab + ")").addClass("active");
      $(".catalog_detail .tab-content .tab-pane .title-tab-heading").next().removeAttr("style");
      clicked_tab = 0;
    }
  }
};

/*countdown start*/
if (!funcDefined("initCountdown")) {
  var initCountdown = function initCountdown() {
    if ($(".view_sale_block").length) {
      $(".view_sale_block").each(function () {
        var _this = $(this);
        if (_this.hasClass("init-if-visible") && !_this.is(":visible")) return;

        var activeTo = _this.find(".active_to").text(),
          dateTo = new Date(activeTo.replace(/(\d+)\.(\d+)\.(\d+)/, "$3/$2/$1"));
        if (_this.hasClass("compact"))
          _this.find(".countdown").countdown(
            {
              until: dateTo,
              format: "dHMS",
              compact: true,
              padZeroes: true,
              layout:
                '{d<}<span class="days item">{dn}<div class="text">{dl}</div></span>{d>} <span class="hours item">{hn}<div class="text">{hl}</div></span> <span class="minutes item">{mn}<div class="text">{ml}</div></span> <span class="sec item">{sn}<div class="text">{sl}</div></span>',
            },
            $.countdown.regionalOptions["ru"]
          );
        else
          _this.find(".countdown").countdown(
            {
              until: dateTo,
              format: "dHMS",
              padZeroes: true,
              layout:
                '{d<}<span class="days item">{dnn}<div class="text">{dl}</div></span>{d>} <span class="hours item">{hnn}<div class="text">{hl}</div></span> <span class="minutes item">{mnn}<div class="text">{ml}</div></span> <span class="sec item">{snn}<div class="text">{sl}</div></span>',
            },
            $.countdown.regionalOptions["ru"]
          );
      });
    }
  };
}

if (!funcDefined("initCountdownTime")) {
  var initCountdownTime = function initCountdownTime(block, time) {
    if (time) {
      var dateTo = new Date(time.replace(/(\d+)\.(\d+)\.(\d+)/, "$3/$2/$1"));
      block.find(".countdown").countdown("destroy");
      if (block.hasClass("compact"))
        block.find(".countdown").countdown(
          {
            until: dateTo,
            format: "dHM",
            compact: true,
            padZeroes: true,
            layout:
              '{d<}<span class="days item">{dn}<div class="text">{dl}</div></span>{d>} <span class="hours item">{hn}<div class="text">{hl}</div></span> <span class="minutes item">{mn}<div class="text">{ml}</div></span> <span class="sec item">{sn}<div class="text">{sl}</div></span>',
          },
          $.countdown.regionalOptions["ru"]
        );
      else
        block.find(".countdown").countdown(
          {
            until: dateTo,
            format: "dHMS",
            padZeroes: true,
            layout:
              '{d<}<span class="days item">{dnn}<div class="text">{dl}</div></span>{d>} <span class="hours item">{hnn}<div class="text">{hl}</div></span> <span class="minutes item">{mnn}<div class="text">{ml}</div></span> <span class="sec item">{snn}<div class="text">{sl}</div></span>',
          },
          $.countdown.regionalOptions["ru"]
        );
      block.find(".view_sale_block").show();
    } else {
      block.find(".view_sale_block").hide();
    }
  };
}
/*countdown end*/

waitCounter = function (idCounter, delay, callback) {
  var obCounter = window["yaCounter" + idCounter];
  if (typeof obCounter == "object") {
    if (typeof callback == "function") callback();
  } else {
    setTimeout(function () {
      waitCounter(idCounter, delay, callback);
    }, delay);
  }
};

var isOnceInited = (insertFilter = false);
var animationTime = 200;
var delayTime = 200;
var topMenuEnterTimer = false;
var previewMode = window != window.top;
var isMobile =
  ((jQuery.browser.mobile || previewMode) && window.matchMedia("(max-width:767px)").matches) ||
  window.matchMedia("(max-width:400px)").matches;

if (isMobile) document.documentElement.className += " mobile";
if (previewMode) document.documentElement.className += " previewMode";

if (navigator.userAgent.indexOf("Edge") != -1) document.documentElement.className += " bx-ie-edge";

/*filter start*/
if (!funcDefined("checkVerticalMobileFilter")) {
  var checkVerticalMobileFilter = function checkVerticalMobileFilter() {
    /*if($('.right_block1.catalog.vertical').length && !$('.left_block.filter_ajax').length)
    {
      if(typeof window['trackBarOptions'] !== 'undefined')
      {
        window['trackBarValues'] = {}
        for(key in window['trackBarOptions'])
        {
          window['trackBarValues'][key] = {
            'leftPercent': window['trackBar' + key].leftPercent,
            'leftValue': window['trackBar' + key].minInput.value,
            'rightPercent': window['trackBar' + key].rightPercent,
            'rightValue': window['trackBar' + key].maxInput.value,
          }
        }
      }

      if(window.matchMedia('(max-width: 991px)').matches)
      {
        if(!insertFilter)
        {
          $('.js_filter .bx_filter.bx_filter_vertical').html($('.left_block .bx_filter.bx_filter_vertical').html());
          $('.left_block .bx_filter.bx_filter_vertical .bx_filter_section').remove();
          insertFilter=true;
        }
      }
      else
      {
        if(insertFilter)
        {
          $('.left_block .bx_filter.bx_filter_vertical').html($('.js_filter .bx_filter.bx_filter_vertical').html());
          $('.js_filter .bx_filter.bx_filter_vertical .bx_filter_section').remove();
          insertFilter=false;
        }
      }

      if(typeof window['trackBarOptions'] !== 'undefined')
      {
        for(key in window['trackBarOptions'])
        {
          window['trackBarOptions'][key].leftPercent = window['trackBarValues'][key].leftPercent;
          window['trackBarOptions'][key].rightPercent = window['trackBarValues'][key].rightPercent;
          window['trackBarOptions'][key].curMinPrice = window['trackBarValues'][key].leftValue;
          window['trackBarOptions'][key].curMaxPrice = window['trackBarValues'][key].rightValue;
          window['trackBar' + key] = new BX.Iblock.SmartFilter(window['trackBarOptions'][key]);
          window['trackBar' + key].minInput.value = window['trackBarValues'][key].leftValue;
          window['trackBar' + key].maxInput.value = window['trackBarValues'][key].rightValue;
        }
      }
    }
    else if($('.visible_mobile_filter').length)
    {
      var posTopBlock = BX.pos($('.detail.partners')[0]),
        posBlock = $('.ajax_load').position();
      // $('.visible_mobile_filter').css('top', posBottomBlock.top-posTopBlock.top);
      $('.visible_mobile_filter').css('top', posBlock.top);

    }*/
  };
}
/*filter end*/

// ONE CLICK
if (!funcDefined("oneClickBuy")) {
  var oneClickBuy = function (elementID, iblockID, that) {
    var name = "one_click_buy";
    var elementQuantity = 1;
    var offerProps = false;
    var buy_btn = $(that).closest(".buy_block").find(".to-cart");
    var buy_btn2 = $(that).closest("tr").find(".to-cart");

    if (typeof that !== "undefined") {
      elementQuantity = $(that).attr("data-quantity");
      offerProps = $(that).attr("data-props");
    }

    if (elementQuantity < 0) {
      elementQuantity = 1;
    }

    var tmp_props = buy_btn.data("props"),
      tmp_props2 = buy_btn2.data("props"),
      props = "",
      part_props = "",
      add_props = "N",
      fill_prop = {},
      iblockid = buy_btn.data("iblockid"),
      item = buy_btn.attr("data-item");

    if (tmp_props) {
      props = tmp_props.split(";");
    } else if (tmp_props2) {
      props = tmp_props2.split(";");
    }
    if (buy_btn.data("part_props")) {
      part_props = buy_btn.data("part_props");
    }
    if (buy_btn.data("add_props")) {
      add_props = buy_btn.data("add_props");
    }

    fill_prop = fillBasketPropsExt(buy_btn, "prop", buy_btn.data("bakset_div"));
    fill_prop.iblockID = iblockid;
    fill_prop.part_props = part_props;
    fill_prop.add_props = add_props;
    fill_prop.props = JSON.stringify(props);
    fill_prop.item = item;
    fill_prop.ocb_item = "Y";

    // if (!isMobile) {
    if (!$(that).hasClass("clicked")) {
      $(that).addClass("clicked");
      $("body")
        .find("." + name + "_frame")
        .remove();
      $("body")
        .find("." + name + "_trigger")
        .remove();
      $("body #popup_iframe_wrapper").append('<div class="' + name + '_frame popup"></div>');
      $("body #popup_iframe_wrapper").append('<div class="' + name + '_trigger"></div>');
      $("." + name + "_frame").jqm({
        trigger: "." + name + "_trigger",
        onHide: function (hash) {
          onHidejqm(name, hash);
        },
        toTop: false,
        onLoad: function (hash) {
          onLoadjqm(name, hash);
        },
        ajax:
          arAsproOptions["SITE_DIR"] +
          "ajax/one_click_buy.php?ELEMENT_ID=" +
          elementID +
          "&IBLOCK_ID=" +
          iblockID +
          "&ELEMENT_QUANTITY=" +
          elementQuantity +
          "&OFFER_PROPS=" +
          fill_prop.props,
      });
      $("." + name + "_trigger").click();
    }
    // } else {
    //   var script = arAsproOptions["SITE_DIR"] + "form/";
    //   script +=
    //     "?name=" +
    //     name +
    //     "&form_id=ocb&path=" +
    //     window.location.pathname +
    //     "&ELEMENT_ID=" +
    //     elementID +
    //     "&IBLOCK_ID=" +
    //     iblockID +
    //     "&ELEMENT_QUANTITY=" +
    //     elementQuantity +
    //     "&OFFER_PROPS=" +
    //     fill_prop.props;
    //   location.href = script;
    // }
  };
}

if (!funcDefined("oneClickBuyBasket")) {
  var oneClickBuyBasket = function () {
    name = "one_click_buy_basket";
    if (!isMobile) {
      if (!$(".fast_order").hasClass("clicked")) {
        $(".fast_order").addClass("clicked");
        $("body")
          .find("." + name + "_frame")
          .remove();
        $("body")
          .find("." + name + "_trigger")
          .remove();
        $("body #popup_iframe_wrapper").append('<div class="' + name + '_frame popup"></div>');
        $("body #popup_iframe_wrapper").append('<div class="' + name + '_trigger"></div>');
        $("." + name + "_frame").jqm({
          trigger: "." + name + "_trigger",
          onHide: function (hash) {
            onHidejqm(name, hash);
          },
          onLoad: function (hash) {
            onLoadjqm(name, hash);
          },
          ajax: arAsproOptions["SITE_DIR"] + "ajax/one_click_buy_basket.php",
        });
        $("." + name + "_trigger").click();
      }
    } else {
      var script = arAsproOptions["SITE_DIR"] + "form/";
      script += "?name=" + name + "&form_id=ocb&path=" + window.location.pathname + "&buy_basket=y";
      location.href = script;
    }
  };
}

// TOP MENU ANIMATION
$(document).on("click", ".menu_top_block>li .more a", function () {
  $this = $(this);
  $this.parents(".dropdown").first().find(">.hidden").removeClass("hidden");
  $this.parent().addClass("hidden");
  setTimeout(function () {
    $this.parent().remove();
  }, 500);
});

$(document).on("mouseenter", ".menu_top_block.catalogfirst>li>.dropdown>li.full", function () {
  var $submenu = $(this).find(">.dropdown");

  if ($submenu.length) {
    if (topMenuEnterTimer) {
      clearTimeout(topMenuEnterTimer);
      topMenuEnterTimer = false;
    }
  }
});

$(document).on("mouseenter", ".menu_top_block>li:not(.full)", function () {
  var $submenu = $(this).find(">.dropdown");

  if ($submenu.length && !$submenu.hasClass("visible")) {
    var $menu = $(this).parents(".menu");
    var $wrapmenu = $menu.parents(".wrap_menu");
    var wrapMenuWidth = $wrapmenu.actual("outerWidth");
    var wrapMenuLeft = $wrapmenu.offset().left;
    var wrapMenuRight = wrapMenuLeft + wrapMenuWidth;
    var left = wrapMenuRight - ($(this).offset().left + $submenu.actual("outerWidth"));
    if (
      window.matchMedia("(min-width: 951px)").matches &&
      $(this).hasClass("catalog") &&
      ($(".banner_auto").hasClass("catalog_page") || $(".banner_auto").hasClass("front_page"))
    ) {
      return;
    }
    if (left < 0) {
      $submenu.css({ left: left + "px" });
    }
    $submenu.stop().slideDown(animationTime, function () {
      $submenu.css({ height: "", overflow: "visible" });
    });

    $(this).on("mouseleave", function () {
      var leaveTimer = setTimeout(function () {
        $submenu.stop().slideUp(animationTime, function () {
          $submenu.css({ left: "" });
        });
      }, delayTime);

      $(this).on("mouseenter", function () {
        if (leaveTimer) {
          clearTimeout(leaveTimer);
          leaveTimer = false;
        }
      });
    });
  }
});

$(document).on("mouseenter", ".menu_top_block>li .dropdown>li", function () {
  var $this = $(this);
  var $submenu = $this.find(">.dropdown");

  if (
    $submenu.length &&
    ((!$this.parents(".full").length && !$this.hasClass("full")) || $this.parents(".more").length)
  ) {
    var $menu = $this.parents(".menu");
    var $wrapmenu = $menu.parents(".wrap_menu");
    var arParentSubmenuForOpacity = [];
    topMenuEnterTimer = setTimeout(function () {
      var wrapMenuWidth = $wrapmenu.actual("outerWidth");
      var wrapMenuLeft = $wrapmenu.offset().left;
      var wrapMenuRight = wrapMenuLeft + wrapMenuWidth;
      var $parentSubmenu = $this.parent();
      var bToLeft = $parentSubmenu.hasClass("toleft") ? true : false;
      if (!bToLeft) {
        bToLeft = $this.offset().left + $this.actual("outerWidth") + $submenu.actual("outerWidth") > wrapMenuRight;
      } else {
        bToLeft = $this.offset().left + $this.actual("outerWidth") - $submenu.actual("outerWidth") < wrapMenuLeft;
      }

      if (bToLeft) {
        $this.find(">.dropdown").addClass("toleft").show();
      } else {
        $this.find(">.dropdown").removeClass("toleft").show();
      }
      var submenuLeft = $submenu.offset().left;
      var submenuRight = submenuLeft + $submenu.actual("outerWidth");

      $this.parents(".dropdown").each(function () {
        var $this = $(this);
        var leftOffset = $this.offset().left;
        var rightOffset = leftOffset + $this.actual("outerWidth");
        if (
          (leftOffset >= submenuLeft && leftOffset < submenuRight - 1) ||
          (rightOffset > submenuLeft + 1 && rightOffset <= submenuRight)
        ) {
          arParentSubmenuForOpacity.push($this);
          $this.find(">li>a").css({ opacity: "0.1" });
        }
      });
    }, delayTime);

    $this.unbind("mouseleave");
    $this.on("mouseleave", function () {
      var leaveTimer = setTimeout(function () {
        $this.find(".dropdown").removeClass("toleft").hide();
        if (arParentSubmenuForOpacity.length) {
          for (i in arParentSubmenuForOpacity) {
            arParentSubmenuForOpacity[i].find(">li>a").css({ opacity: "" });
          }
        }
      }, delayTime);

      $this.unbind("mouseenter");
      $this.on("mouseenter", function () {
        if (leaveTimer) {
          clearTimeout(leaveTimer);
          leaveTimer = false;
        }
      });
    });
  }
});

/*hover animate*/
//breadcrumbs
$(document).on("mouseenter", ".breadcrumbs .breadcrumbs__item, .hover-block .hover-block__item", function () {
  var _this = $(this),
    menu = _this.find("> .breadcrumbs__dropdown-wrapper, > .hover-block__item-wrapper");

  menu.velocity("stop").velocity("transition.slideUpIn", {
    duration: 300,
    delay: 100,
  });

  _this.one("mouseleave", function () {
    menu.velocity("stop").velocity("fadeOut", {
      duration: 100,
      // delay: 0
    });
  });
});

//top menu
$(document).on("mouseenter", ".menu .mega-menu table td, .menu-row .mega-menu table td", function () {
  var _this = $(this),
    menu = _this.find("> .wrap > .dropdown-menu");

  if (!_this.hasClass("wide_menu")) {
    menu.show();
    CheckTopVisibleMenu();
  }

  var bDarkness = $(".wrapper1.dark-hover-overlay").length > 0;

  menu.velocity("stop");

  if (menu.css("opacity") != 0) {
    menu.css("opacity", "1");
    if (bDarkness) {
      $(".shadow-block").css("opacity", "1");
    }
  } else {
    // menu.velocity('stop').velocity('transition.perspectiveDownIn', {
    // menu.velocity('stop').velocity('transition.slideUpBigIn', {
    // menu.velocity('transition.expandIn', {
    menu.velocity("fadeIn", {
      begin: function (elements) {
        InitMenuNavigationAim();
        CheckTopVisibleMenu();
      },
      duration: 150,
      delay: 250,
      complete: function () {
        if (bDarkness) {
          $("body").addClass("menu-hovered");
          if (!$(".shadow-block").length) $('<div class="shadow-block"></div>').appendTo($("body"));
          $(".shadow-block").velocity("stop").velocity("fadeIn", 200);
        }

        var bannerMenu = $(".dropdown-menu.with_right_block .owl-carousel-hover");
        if (bannerMenu.length) {
          bannerMenu.removeClass("owl-carousel-hover").addClass("owl-carousel");
          setTimeout(function () {
            InitOwlSlider();
            bannerMenu.removeClass("loader_circle");
          }, 1);
        }
      },
    });
  }

  _this.one("mouseleave", function () {
    menu.velocity("stop").velocity("fadeOut", {
      duration: 50,
      delay: 300,
      complete: function () {
        if (bDarkness) {
          $(".shadow-block")
            .velocity("stop")
            .velocity("fadeOut", {
              duration: 200,
              // delay: 100,
              complete: function () {
                $("body").removeClass("menu-hovered");
              },
            });
        }
      },
    });
  });
});
/**/

$(document).on("mouseenter", ".menu-item:not(.wide_menu) .dropdown-menu .dropdown-submenu", function () {
  var _this = $(this),
    menu = _this.find("> .dropdown-menu");

  menu.velocity("stop");

  if (menu.css("opacity") != 0) {
    menu.css("opacity", "1");
  } else {
    menu.velocity("transition.fadeIn", {
      begin: function (element) {
        $(element).css("display", "block");
        CheckTopVisibleMenu();
      },
      duration: 300,
      delay: 250,
    });
  }

  _this.one("mouseleave", function () {
    menu.velocity("stop").velocity("fadeOut", {
      duration: 150,
      delay: 300,
    });
  });
});

/*register custom velocity animate*/
if (typeof $.Velocity !== undefined && "RegisterEffect" in $.Velocity) {
  var effects = {
    "transition.slideDownFullIn": {
      defaultDuration: 900,
      calls: [[{ opacity: [1, 0], translateY: [0, "-100%"], translateZ: 0 }]],
    },
    "transition.slideDownFullOut": {
      defaultDuration: 900,
      calls: [[{ opacity: [0.4, 1], translateY: ["-100%", 0], translateZ: 0 }]],
    },
  };

  for (var effectName in effects) {
    if (effects.hasOwnProperty(effectName)) {
      $.Velocity.RegisterEffect(effectName, effects[effectName]);
    }
  }
}
/**/

getGridSize = function (counts, slider) {
  var counts_item = 1;
  //wide
  if (window.matchMedia("(min-width: 1200px)").matches) {
    counts_item = counts[0];
    if (typeof slider.data("lg_count") !== "undefined" && slider.data("lg_count") && $(".front.wide_page").length)
      counts_item = slider.data("lg_count");
  }

  //large
  if (window.matchMedia("(max-width: 1200px)").matches) {
    counts_item = counts[1];
  }

  //middle
  if (window.matchMedia("(max-width: 992px)").matches) {
    counts_item = counts[2];
  }

  //small
  if (counts[3]) {
    if (window.matchMedia("(max-width: 600px)").matches) {
      counts_item = counts[3];
    }
  }

  //exsmall
  if (counts[4]) {
    if (window.matchMedia("(max-width: 400px)").matches) {
      counts_item = counts[4];
    }
  }
  return counts_item;
};

CheckFlexSlider = function () {
  $(".flexslider:not(.thmb)").each(function () {
    var slider = $(this);
    if (typeof slider.data("flexslider") != "undefined") {
      if ("vars" in slider.data("flexslider")) {
        slider.resize();

        var counts = slider.data("flexslider").vars.counts;
        if (typeof counts != "undefined" && slider.is(":visible")) {
          var cnt = getGridSize(counts, slider);
          var to0 =
            cnt != slider.data("flexslider").vars.minItems ||
            cnt != slider.data("flexslider").vars.maxItems ||
            cnt != slider.data("flexslider").vars.move;
          if (to0) {
            slider.data("flexslider").vars.minItems = cnt;
            slider.data("flexslider").vars.maxItems = cnt;
            slider.data("flexslider").vars.move = cnt;
            slider.flexslider(0);
            slider.resize();
            slider.resize(); // twise!
          }
        }
      }
    }
  });
};

$.fn.mCustomScrollbarDeferred = function (config) {
  $(this).addClass("scroll-init");

  if ($(this).hasClass("destroyed")) {
    return;
  }

  $(this).hover(
    function (e) {
      var $this = $(this);
      if (!$this.hasClass("mCustomScrollbar") && !$(this).hasClass("destroyed") && !$(this).hasClass("mobile-scroll")) {
        $this.data(
          "scrollTimer",
          setTimeout(function () {
            $this.mCustomScrollbar(config);
            $this.off("touchstart touchmove touchend mousewheel mouseenter mouseleave");
          }, 200)
        );
      }
    },
    function (e) {
      clearTimeout($(this).data("scrollTimer"));
    }
  );

  $(this).on("touchstart touchmove", function (e) {
    var $this = $(this);
    if (!$this.hasClass("mCustomScrollbar") && !$(this).hasClass("destroyed") && !$(this).hasClass("mobile-scroll")) {
      $this.mCustomScrollbar(config);
    }

    var $scrollContainer = $this.find(">.mCustomScrollBox>.mCSB_container");
    if ($scrollContainer.length) {
      var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];

      var newTouch = new Touch({
        identifier: 42,
        target: $scrollContainer[0],
        clientX: touch.clientX,
        clientY: touch.clientY,
        screenX: touch.screenX,
        screenY: touch.screenY,
        pageX: touch.pageX,
        pageY: touch.pageY,
        radiusX: 1,
        radiusY: 1,
      });

      var newEvent = new TouchEvent(e.type, {
        cancelable: true,
        bubbles: false,
        composed: true,
        touches: [newTouch],
        targetTouches: [newTouch],
        changedTouches: [newTouch],
      });

      $scrollContainer[0].dispatchEvent(newEvent);
    }
  });

  $(this).on("touchend", function (e) {
    $(this).off("touchstart touchmove touchend mousewheel mouseenter mouseleave");
  });

  if ($.event.special.mousewheel) {
    $(this).on("mousewheel", function (e) {
      var $this = $(this);
      if (!$this.hasClass("mCustomScrollbar") && !$(this).hasClass("destroyed") && !$(this).hasClass("mobile-scroll")) {
        $this.mCustomScrollbar(config);
        $this.off("touchstart touchmove touchend mousewheel mouseenter mouseleave");
      }
    });
  }
};

InitScrollBar = function (el, initOptions) {
  var block;
  if (typeof el === "undefined") {
    block = $(".srollbar-custom:not(.mobile-scroll):not(.scroll-init)");
  } else {
    block = el.filter(":not(.scroll-init)");
  }

  if (block.length) {
    block.addClass("scroll-init");

    var options,
      defaults = {
        mouseWheel: {
          scrollAmount: 150,
          preventDefault: true,
        },
      };
    var config = $.extend({}, defaults, options, block.data("plugin-options"), initOptions);

    config.callbacks = {
      onScroll: function () {
        if ($(this).find(".mCSB_buttonLeft").hasClass("disabled"))
          $(this).find(".mCSB_buttonLeft").removeClass("disabled");
        if ($(this).find(".mCSB_buttonRight").hasClass("disabled"))
          $(this).find(".mCSB_buttonRight").removeClass("disabled");
      },
      onTotalScrollBack: function () {
        $(this).find(".mCSB_buttonLeft").addClass("disabled");
      },
      onTotalScroll: function () {
        $(this).find(".mCSB_buttonRight").addClass("disabled");
      },
      onInit: function () {
        $(this).find(".mCSB_buttonLeft").addClass("disabled");
      },
    };

    block.filter(":not(.scroll-deferred)").mCustomScrollbar(config);
    block.filter(".scroll-deferred").mCustomScrollbarDeferred(config);
  }
};

InitCustomScrollBar = function (el) {
  var block;
  if (typeof el === "undefined") {
    block = $(".scrollbar:not(.mobile-scroll):not(.scroll-init)");
  } else {
    block = el.filter(":not(.scroll-init)");
  }

  if (block.length) {
    block.addClass("scroll-init");

    var options,
      defaults = {
        effect: "fadeIn",
        effectTime: 300,
        threshold: 0,
        // threshold: 30
      };

    var config = $.extend({}, defaults, options, block.data("plugin-options"));

    block.filter(":not(.scroll-deferred)").mCustomScrollbar(config);
    block.filter(".scroll-deferred").mCustomScrollbarDeferred(config);
  }
};

InitFancyBox = function () {
  if (typeof $.fn.fancybox !== "function") {
    return;
  }

  if (!$(".fancy").length) {
    return;
  }
  $(".fancy").fancybox({
    padding: [40, 40, 64, 40],
    openEffect: "fade",
    closeEffect: "fade",
    nextEffect: "fade",
    prevEffect: "fade",
    opacity: true,
    tpl: {
      closeBtn:
        '<span title="' +
        BX.message("FANCY_CLOSE") +
        '" class="fancybox-item fancybox-close inline svg"><svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 24L24 2M2 2L24 24" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>',
      next:
        '<a title="' +
        BX.message("FANCY_NEXT") +
        '" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
      prev:
        '<a title="' +
        BX.message("FANCY_PREV") +
        '" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>',
    },
    btnTpl: {
      close:
        '<button data-fancybox-close class="fancybox-button fancybox-button--close" title="{{CLOSE}}">' +
        '<i class="svg"><svg width="26" height="26" viewBox="0 0 26 26" xmlns="http://www.w3.org/2000/svg"><path d="M2 24L24 2M2 2L24 24" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" /></svg></i>' +
        "</button>",

      arrowLeft:
        '<button data-fancybox-prev class="fancybox-button fancybox-button--arrow_left" title="{{PREV}}">' +
        '<div><i class="svg left"><svg width="16" height="26" viewBox="0 0 15 26" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 13L0.585787 14.4142C-0.195262 13.6332 -0.195262 12.3668 0.585787 11.5858L2 13ZM11.5858 0.585786C12.3668 -0.195262 13.6332 -0.195262 14.4142 0.585786C15.1953 1.36683 15.1953 2.63317 14.4142 3.41421L11.5858 0.585786ZM14.4142 22.5858C15.1953 23.3668 15.1953 24.6332 14.4142 25.4142C13.6332 26.1953 12.3668 26.1953 11.5858 25.4142L14.4142 22.5858ZM0.585787 11.5858L11.5858 0.585786L14.4142 3.41421L3.41421 14.4142L0.585787 11.5858ZM3.41421 11.5858L14.4142 22.5858L11.5858 25.4142L0.585787 14.4142L3.41421 11.5858Z" fill="#999999"/></svg></i></div>' +
        "</button>",

      arrowRight:
        '<button data-fancybox-next class="fancybox-button fancybox-button--arrow_right" title="{{NEXT}}">' +
        '<div><i class="svg right"><svg width="16" height="26" viewBox="0 0 15 26" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 13L14.4142 14.4142C15.1953 13.6332 15.1953 12.3668 14.4142 11.5858L13 13ZM3.41421 0.585786C2.63317 -0.195262 1.36683 -0.195262 0.585786 0.585786C-0.195262 1.36683 -0.195262 2.63317 0.585786 3.41421L3.41421 0.585786ZM0.585786 22.5858C-0.195262 23.3668 -0.195262 24.6332 0.585786 25.4142C1.36683 26.1953 2.63317 26.1953 3.41421 25.4142L0.585786 22.5858ZM14.4142 11.5858L3.41421 0.585786L0.585786 3.41421L11.5858 14.4142L14.4142 11.5858ZM11.5858 11.5858L0.585786 22.5858L3.41421 25.4142L14.4142 14.4142L11.5858 11.5858Z" fill="white"/></svg></i></div>' +
        "</button>",
    },
    touch: "enabled",
    buttons: [
      // "zoom",
      //"share",
      // "slideShow",
      //"fullScreen",
      //"download",
      // "thumbs",
      "close",
    ],
    thumbs: {
      autoStart: true,
    },
    backFocus: false,
    onActivate: function (instance) {
      if (
        $(".fancybox-thumbs") &&
        $(".fancybox-thumbs__list a").length > 1 &&
        !$(".fancybox-thumbs .swiper-container").length &&
        typeof initSwiperSlider === "function"
      ) {
        // update preview when lazyloaded
        instance.Thumbs.$grid.find(".fancybox-thumbs__list a").each(function (i, el) {
          $(el).css("background-image", "url(" + location.origin + instance.group[i].src + ")");
        });
        const $swiperSlides = $(".fancybox-thumbs__list a");
        const $swiperWrapper = $(".fancybox-thumbs__list");

        const options = {
          direction: "vertical",
          slidesPerView: 5,
          mousewheelControl: true,
          setWrapperSize: true,
          centerInsufficientSlides: true,
          spaceBetween: 16,
        };

        if ($swiperSlides.length > 5) {
          options.navigation = {
            nextEl: ".fancybox-thumbs__wrapper .swiper-button-next",
            prevEl: ".fancybox-thumbs__wrapper .swiper-button-prev",
          };
        }

        $(".fancybox-thumbs").wrapInner(
          "<div class='fancybox-thumbs__wrapper'><div class='swiper-container' data-plugin-options='" +
            JSON.stringify(options) +
            "' /></div>"
        );
        const $swiperContainerWrapper = $(".fancybox-thumbs__wrapper");

        if ($swiperSlides.length > 5) {
          $swiperContainerWrapper.append(
            "<div class='swiper-button-next'><i class='svg right'><svg xmlns='http://www.w3.org/2000/svg' width='12' height='6.969' viewBox='0 0 12 6.969'><path id='Rounded_Rectangle_702_copy_24' data-name='Rounded Rectangle 702 copy 24' class='cls-1' d='M361.691,401.707a1,1,0,0,1-1.414,0L356,397.416l-4.306,4.291a1,1,0,0,1-1.414,0,0.991,0.991,0,0,1,0-1.406l5.016-5a1.006,1.006,0,0,1,1.415,0l4.984,5A0.989,0.989,0,0,1,361.691,401.707Z' transform='translate(-350 -395.031)'></path></svg></i></div>"
          );
          $swiperContainerWrapper.prepend(
            "<div class='swiper-button-prev'><i class='svg left'><svg xmlns='http://www.w3.org/2000/svg' width='12' height='6.969' viewBox='0 0 12 6.969'><path id='Rounded_Rectangle_702_copy_24' data-name='Rounded Rectangle 702 copy 24' class='cls-1' d='M361.691,401.707a1,1,0,0,1-1.414,0L356,397.416l-4.306,4.291a1,1,0,0,1-1.414,0,0.991,0.991,0,0,1,0-1.406l5.016-5a1.006,1.006,0,0,1,1.415,0l4.984,5A0.989,0.989,0,0,1,361.691,401.707Z' transform='translate(-350 -395.031)'></path></svg></i></div>"
          );
        }
        $swiperWrapper.addClass("swiper-wrapper");
        $swiperSlides.addClass("swiper-slide");

        initSwiperSlider(".fancybox-thumbs .swiper-container");
        const swiper = $(".fancybox-thumbs .swiper-container").data("swiper");
        swiper.slideTo(instance.curPos, 10);
      }
    },
    beforeShow: function (instance, current) {
      if ($(".fancybox-thumbs .swiper-container").length) {
        const swiper = $(".fancybox-thumbs .swiper-container").data("swiper");

        swiper.slideTo(current.index);
      }
      if (!$(".cd-modal-bg").hasClass("is-visible")) {
        BX.loadScript(arAsproOptions["SITE_TEMPLATE_PATH"] + "/js/aspro_animate_open_fancy.js", function () {
          var scaleValue = retrieveScale($(".cd-modal-bg"));

          $(".cd-modal-bg")
            .show()
            .addClass("is-visible")
            .one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function () {
              animateLayer($(".cd-modal-bg"), scaleValue, true);
            });
        });
      }
      var video_block = $(".company-block source.video-block");
      if (video_block.length) {
        if (video_block.attr("src") == "#") {
          var video_block_wrapper = video_block.closest("video");
          var video_block_clone = video_block_wrapper.clone();

          video_block_clone.find("source").attr("src", video_block_clone.find("source").data("src"));
          video_block.attr("src", video_block.data("src"));
          video_block_clone.insertAfter(video_block_wrapper);
          video_block_clone.siblings("video").remove();
        }
      }
      var video_block_frame = $(".company-block #company_video_iframe");
      if (video_block_frame.length) {
        var data_src_iframe = video_block_frame.attr("data-src");
        video_block_frame.attr("src", data_src_iframe);
        video_block_frame.attr("allow", "autoplay");
      }
      // $('.detail .galery .overlay_form').hide();
    },
    afterShow: function () {
      if ($(".fancybox-overlay").css("opacity") == 0) {
        setTimeout(function () {
          $(".fancybox-overlay").css("opacity", 1);
          $("html").addClass("overflow_html");
        }, 200);
      }

      $(".fancybox-nav").css("opacity", 0);
      setTimeout(function () {
        $(".fancybox-nav").css("opacity", 1);
      }, 150);
      if ($(".fancybox-inner #company_video").length) {
        // var fancyHeight = $('.fancybox-wrap').height();
        // $('.fancybox-inner').height(fancyHeight);
        setTimeout(function () {
          $(".fancybox-wrap video").resize();
          setTimeout(function () {
            $(".fancybox-wrap").addClass("show_video");
            document.getElementById("company_video").currentTime = 0;
            document.getElementById("company_video").play();
          }, 300);
        }, 150);
      } else if ($(".fancybox-wrap iframe").length) {
        $(".fancybox-inner").height("100%");
      }
    },
    beforeClose: function () {
      BX.loadScript(arAsproOptions["SITE_TEMPLATE_PATH"] + "/js/aspro_animate_open_fancy.js", function () {
        closeModal();
        $(".fancybox-overlay").fadeOut();
        if ($("#company_video").length) {
          document.getElementById("company_video").currentTime = 0;
        }
        $("html").removeClass("overflow_html");
        var video_block_frame = $(".company-block .video-block");
        if (video_block_frame.length) {
          $("#company_video_iframe").attr("src", "");
        }
      });
    },
    onClosed: function () {
      if ($(".fancybox-wrap #company_video").length) {
        document.getElementById("company_video").pause();
      }
    },
  });
};

InitFancyBoxVideo = function () {
  if (typeof $.fn.fancybox !== "function") {
    return;
  }

  if (!$(".video_link").length) {
    return;
  }
  $(".video_link").fancybox({
    type: "iframe",
    maxWidth: 800,
    maxHeight: 600,
    fitToView: false,
    width: "70%",
    height: "70%",
    autoSize: false,
    closeClick: false,
    opacity: true,
    tpl: {
      closeBtn:
        '<span title="' +
        BX.message("FANCY_CLOSE") +
        '" class="fancybox-item fancybox-close inline svg"><svg class="svg svg-close" width="14" height="14" viewBox="0 0 14 14"><path data-name="Rounded Rectangle 568 copy 16" d="M1009.4,953l5.32,5.315a0.987,0.987,0,0,1,0,1.4,1,1,0,0,1-1.41,0L1008,954.4l-5.32,5.315a0.991,0.991,0,0,1-1.4-1.4L1006.6,953l-5.32-5.315a0.991,0.991,0,0,1,1.4-1.4l5.32,5.315,5.31-5.315a1,1,0,0,1,1.41,0,0.987,0.987,0,0,1,0,1.4Z" transform="translate(-1001 -946)"></path></svg></span>',
      next:
        '<a title="' +
        BX.message("FANCY_NEXT") +
        '" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
      prev:
        '<a title="' +
        BX.message("FANCY_PREV") +
        '" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>',
    },
    beforeShow: function (event) {
      if (!$(".cd-modal-bg").hasClass("is-visible")) {
        BX.loadScript(arAsproOptions["SITE_TEMPLATE_PATH"] + "/js/aspro_animate_open_fancy.js", function () {
          var scaleValue = retrieveScale($(".cd-modal-bg"));

          $(".cd-modal-bg")
            .show()
            .addClass("is-visible")
            .one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function () {
              animateLayer($(".cd-modal-bg"), scaleValue, true);
            });
        });
      }
    },
    afterShow: function () {
      if ($(".fancybox-overlay").css("opacity") == 0) {
        setTimeout(function () {
          $(".fancybox-overlay").css("opacity", 1);
          $("html").addClass("overflow_html");
        }, 200);
      }

      $(".fancybox-nav").css("opacity", 0);
      setTimeout(function () {
        $(".fancybox-nav").css("opacity", 1);
      }, 150);
      if ($(".fancybox-wrap iframe").length) {
        $(".fancybox-inner").height("100%");
      }
    },
    beforeClose: function () {
      BX.loadScript(arAsproOptions["SITE_TEMPLATE_PATH"] + "/js/aspro_animate_open_fancy.js", function () {
        closeModal();
        $(".fancybox-overlay").fadeOut();
        $("html").removeClass("overflow_html");
      });
    },
  });
};

InitStickySideBar = function (el, container_el) {
  var block = ".sticky-sidebar",
    container_catalog = ".wraps .wrapper_inner .container_inner .main-catalog-wrapper",
    container = ".wraps .wrapper_inner .container_inner";
  if (typeof el !== "undefined") block = el;

  if ($(container_catalog).length) container = container_catalog;

  if (typeof container_el !== "undefined") container = container_el;

  if ($(block).length && arAsproOptions["THEME"]["STICKY_SIDEBAR"] != "N") {
    if (typeof window["stickySidebar"] !== "undefined") {
      window["stickySidebar"].destroy();
    }

    window["stickySidebar"] = new StickySidebar(block, {
      topSpacing: 60,
      bottomSpacing: 20,
      containerSelector: container,
      resizeSensor: true,
      innerWrapperSelector: ".sticky-sidebar__inner",
    });

    if ($(".sticky-sidebar .sticky-sidebar__inner .banner img").length) {
      $(".sticky-sidebar .sticky-sidebar__inner .banner img").load(function () {
        if (typeof window["stickySidebar"] !== "undefined") {
          window["stickySidebar"].updateSticky();
        }
      });
    }
  }
};

InitOwlSlider = function () {
  if (typeof $.fn.owlCarousel !== "function") {
    return;
  }
  $(".owl-carousel:not(.owl-loaded):not(.appear-block)").each(function () {
    var slider = $(this);
    var options;
    var svg =
      '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6.969" viewBox="0 0 12 6.969"><path id="Rounded_Rectangle_702_copy_24" data-name="Rounded Rectangle 702 copy 24" class="cls-1" d="M361.691,401.707a1,1,0,0,1-1.414,0L356,397.416l-4.306,4.291a1,1,0,0,1-1.414,0,0.991,0.991,0,0,1,0-1.406l5.016-5a1.006,1.006,0,0,1,1.415,0l4.984,5A0.989,0.989,0,0,1,361.691,401.707Z" transform="translate(-350 -395.031)"/></svg>';
    var defaults = {
      navText: [
        '<i class="svg left colored_theme_hover_text">' + svg + "</i>",
        '<i class="svg right colored_theme_hover_text">' + svg + "</i>",
      ],
    };
    var config = $.extend({}, defaults, options, slider.data("plugin-options"));



    slider.on("initialized.owl.carousel", function (event) {
      var eventdata = { slider: event };
      BX.onCustomEvent("onSliderInitialized", [eventdata]);

      $(event.target).removeClass("loading-state");
      $(event.target).find(".owl-item:first").addClass("current");

      if ($(event.target).hasClass("catalog_block")) {
        if (typeof sliceItemBlockSlide === "function") {
          sliceItemBlockSlide();
        }
      }

      if (typeof window["stickySidebar"] !== "undefined") {
        window["stickySidebar"].updateSticky();
      }
    });
    slider.owlCarousel(config);
    setTimeout(function(){
      $(".owl-stage").owlCarousel("reset");
    },300);
    slider.on("change.owl.carousel", function (event) {
      var eventdata = { slider: event };
      BX.onCustomEvent("onSlideChange", [eventdata]);
    });
    slider.on("resized.owl.carousel", function (event) {
      if (typeof sliceItemBlockSlide === "function") {
        sliceItemBlockSlide({ resize: false });
      }
    });

    slider.on("changed.owl.carousel", function (event) {
      var eventdata = { slider: event };
      BX.onCustomEvent("onSlideChanged", [eventdata]);

      if ($(event.target).data("pluginOptions")) {
        if ("index" in $(event.target).data("pluginOptions")) {
          if ($(".switch-item-block").length) {
            $(".switch-item-block__count-wrapper--big .switch-item-block__count-value").text(
              event.item.index + 1 + "/" + event.item.count
            );
          }
        }

        if ("relatedTo" in $(event.target).data("pluginOptions")) {
          var relatedClass = $(event.target).data("pluginOptions").relatedTo,
            relatedBlock = $(relatedClass);

          if (relatedBlock.length && $(event.target).data("owl.carousel")) {
            if (!$(event.target).data("owl.carousel").loop) {
              var current = event.item.index;
            } else {
              var count = event.item.count - 1;
              var current = Math.round(event.item.index - event.item.count / 2 - 0.5);

              if (current < 0) current = count;

              if (current > count) current = 0;
            }

            relatedBlock.find(".owl-item").removeClass("current").eq(current).addClass("current");

            var onscreen = relatedBlock.find(".owl-item.active").length - 1;
            var start = relatedBlock.find(".owl-item.active").first().index();
            var end = relatedBlock.find(".owl-item.active").last().index();

            if (current > end) relatedBlock.data("owl.carousel").to(current, 100, true);

            if (current < start) relatedBlock.data("owl.carousel").to(current - onscreen, 100, true);
            // $(".owl-slider-"+id).trigger('to.owl.carousel', [itemCarousel])
          }
        }
      }
    });

    slider.on("translated.owl.carousel", function (event) {});

    if ("clickTo" in config) {
      var relatedClass = config.clickTo,
        magnifier = "magnifier" in config;

      slider.on("click", ".owl-item", function (e) {
        e.preventDefault();
        var _this = $(this),
          number = _this.index();

        if (magnifier) {
          const $parent = $(relatedClass).closest(".product-container");
          if ($parent.find(".zoom_picture").length) {
            const $zoomImg = $parent.find(".zoom_picture");
            $zoomImg.attr("data-large", _this.find(".product-detail-gallery__item").data("big"));
            $zoomImg.attr("xoriginal", _this.find(".product-detail-gallery__item").data("big"));
            $zoomImg.attr("src", _this.find(".product-detail-gallery__item img").attr("src"));
            $parent
              .find(".product-detail-gallery__link")
              .attr("href", _this.find(".product-detail-gallery__item img").attr("src"));
          }
          _this.siblings("").removeClass("current");
          _this.addClass("current");
        } else {
          $(relatedClass).data("owl.carousel").to(number, 300, true);
        }
      });
    }
  });
};

InitFlexSlider = function () {
  if (typeof $.fn.flexslider !== "function") {
    return;
  }
  $(".flexslider:not(.thmb):not(.flexslider-init):not(.appear-block)").each(function () {
    var slider = $(this);
    var options;
    var defaults = {
      animationLoop: false,
      controlNav: false,
      keyboard: false,
      pauseOnAction: false,
      pauseInvisible: false,
      directionNav: true,
      useCSS: false,
      animation: "slide",
    };
    var config = $.extend({}, defaults, options, slider.data("plugin-options"));
    if (!slider.parent().hasClass("top_slider_wrapp") && slider.is(":visible")) {
      if (typeof config.counts != "undefined" && config.direction !== "vertical") {
        config.maxItems = getGridSize(config.counts, slider);
        config.minItems = getGridSize(config.counts, slider);
        config.itemWidth = 200;
      }
      if (typeof config.move == "undefined") config.move = 1;

      config.start = function (slider) {
        var eventdata = { slider: slider };
        BX.onCustomEvent("onSlideInit", [eventdata]);
      };

      config.after = function (slider) {
        var eventdata = { slider: slider };
        BX.onCustomEvent("onSlideComplete", [eventdata]);
      };

      config.end = function (slider) {
        var eventdata = { slider: slider };
        BX.onCustomEvent("onSlideEnd", [eventdata]);
      };
      slider.flexslider(config).addClass("flexslider-init");
      if (config.controlNav) slider.addClass("flexslider-control-nav");
      if (config.directionNav) slider.addClass("flexslider-direction-nav");
    }
  });
};

InitFlexSliderByClass = function ($class) {
  if ($class.hasClass("flexslider-init")) return;
  var slider = $class;
  var options;
  var defaults = {
    animationLoop: false,
    controlNav: false,
    keyboard: false,
    pauseOnAction: false,
    pauseInvisible: false,
    directionNav: true,
    useCSS: false,
    animation: "slide",
  };
  var config = $.extend({}, defaults, options, slider.data("plugin-options"));
  if (!slider.parent().hasClass("top_slider_wrapp") && slider.is(":visible")) {
    if (typeof config.counts != "undefined" && config.direction !== "vertical") {
      config.maxItems = getGridSize(config.counts, slider);
      config.minItems = getGridSize(config.counts, slider);
      config.itemWidth = 200;
    }
    if (typeof config.move == "undefined") config.move = 1;

    config.start = function (slider) {
      var eventdata = { slider: slider };
      BX.onCustomEvent("onSlideInit", [eventdata]);
    };

    config.after = function (slider) {
      var eventdata = { slider: slider };
      BX.onCustomEvent("onSlideComplete", [eventdata]);
    };

    config.end = function (slider) {
      var eventdata = { slider: slider };
      BX.onCustomEvent("onSlideEnd", [eventdata]);
    };

    slider.flexslider(config).addClass("flexslider-init");
    if (config.controlNav) slider.addClass("flexslider-control-nav");
    if (config.directionNav) slider.addClass("flexslider-direction-nav");
  }
};

inIframe = function () {
  try {
    return window.self !== window.top;
  } catch (e) {
    return true;
  }
};

InitZoomPict = function (el) {
  const zoomer = typeof el !== "undefined" ? el : $(".zoom_picture");

  if (zoomer.length) {
    var options;
    const defaults = {
      title: true,
      scroll: false,
      Xoffset: 15,
      tint: "#333",
      defaultScale: -1,
    };

    const config = $.extend({}, defaults, options, zoomer.data("plugin-options"));
    zoomer.xzoom(config);

    // checks if zoomer doesn't have the event listenners
    const zoomerEventsListeners = $._data(zoomer.get(0), "events");

    if (!zoomerEventsListeners.mouseout && !zoomerEventsListeners.load) {
      zoomer.on("mouseleave", function () {
        zoomer.data("xzoom").movezoom;
      });

      zoomer.on("load", function () {
        const zoomerParentWidth = zoomer.parent().width();
        const zoomerHeight = zoomer.height();
        const zoomerWidth = zoomer.width();
        const sizeMultiplier = 1.5;

        const zoomWidth =
          zoomerWidth < zoomerParentWidth / 2 && zoomerHeight > zoomerWidth ? zoomerWidth * sizeMultiplier : "auto";

        zoomer.data("xzoom").destroy();
        zoomer.removeData("xzoom");
        zoomer.width("auto");

        config.zoomWidth = zoomWidth;
        zoomer.xzoom(config);
      });
    }
  }
};

var arBasketAsproCounters = (arStatusBasketAspro = arBasketPrices = {});
SetActualBasketFlyCounters = function (sync) {
  if (arBasketAsproCounters.DEFAULT == true) {
    $.ajax({
      url: arAsproOptions["SITE_DIR"] + "ajax/basket_fly.php",
      type: "post",
      success: function (html) {
        $("#basket_line .basket_fly").removeClass("loaded").html(html);

        if (typeof sync !== "undefined") {
          $.ajax({
            type: "GET",
            url: arAsproOptions["SITE_DIR"] + "ajax/actualBasket.php",
            // data:data,
            success: function (data) {
              if (!$(".js_ajax").length) $("body").append('<div class="js_ajax"></div>');
              $(".js_ajax").html(data);

              setBasketStatusBtn(true);
            },
          });
        }
      },
    });
  } else {
    // insert currency &#8381; by this hack!: $('<div/>').html(arBasketAsproCounters.READY.TITLE).text()
    $(".basket_fly .opener .basket_count .count")
      .attr("class", "count" + (arBasketAsproCounters.READY.COUNT > 0 ? "" : " empty_items"))
      .find(".items span")
      .text(arBasketAsproCounters.READY.COUNT);
    $(".basket_fly .opener .basket_count + a").attr("href", arBasketAsproCounters["READY"]["HREF"]);
    $(".basket_fly .opener .basket_count")
      .attr("title", $("<div/>").html(arBasketAsproCounters.READY.TITLE).text())
      .attr(
        "class",
        "colored_theme_hover_text basket_count small clicked" + (arBasketAsproCounters.READY.COUNT > 0 ? "" : " empty")
      );

    $(".basket_fly .opener .wish_count .count")
      .attr("class", "count" + (arBasketAsproCounters.DELAY.COUNT > 0 ? "" : " empty_items"))
      .find(".items span")
      .text(arBasketAsproCounters.DELAY.COUNT);
    $(".basket_fly .opener .wish_count + a").attr("href", arBasketAsproCounters.DELAY.HREF);
    $(".basket_fly .opener .wish_count")
      .attr("title", $("<div/>").html(arBasketAsproCounters.DELAY.TITLE).text())
      .attr(
        "class",
        "colored_theme_hover_text wish_count small clicked" + (arBasketAsproCounters.DELAY.COUNT > 0 ? "" : " empty")
      );

    $(".basket_fly .opener .compare_count .wraps_icon_block").attr(
      "class",
      "wraps_icon_block compare" + (arBasketAsproCounters.COMPARE.COUNT > 0 ? "" : " empty_block")
    );
    $(".basket_fly .opener .compare_count .count")
      .attr("class", "count" + (arBasketAsproCounters.COMPARE.COUNT > 0 ? "" : " empty_items"))
      .find(".items span")
      .text(arBasketAsproCounters.COMPARE.COUNT);
    $(".basket_fly .opener .compare_count + a").attr("href", arBasketAsproCounters.COMPARE.HREF);

    updateBottomIconsPanel(arBasketAsproCounters);
  }
};

CheckHeaderFixed = function () {
  var header = $("header, body.simple_basket_mode #header").first(),
    header_fixed = $("#headerfixed, body.simple_basket_mode #header"),
    header_simple = $("body.simple_basket_mode #header");

  if (header_fixed.length) {
    if (header.length) {
      var isHeaderFixed = false,
        isTabsFixed = false,
        headerCanFix = true,
        headerFixedHeight = header_fixed.actual("outerHeight"),
        headerNormalHeight = header.actual("outerHeight"),
        headerDiffHeight = headerNormalHeight - headerFixedHeight,
        mobileBtnMenu = $(".btn.btn-responsive-nav"),
        headerTop = $("#panel:visible").actual("outerHeight"),
        topBlock = $(".TOP_HEADER").first(),
        $headerFixedNlo = header_fixed.find("[data-nlo]"),
        isNloLoaded = !$headerFixedNlo.length,
        OnHeaderFixedScrollHandler;

      if (headerDiffHeight <= 0) headerDiffHeight = 0;

      if (topBlock.length) headerTop += topBlock.actual("outerHeight");

      $(window).scroll(
        (OnHeaderFixedScrollHandler = function () {
          var tabs_fixed = $(".product-item-detail-tabs-container-fixed");

          if (window.matchMedia("(min-width:992px)").matches) {
            var scrollTop = $(window).scrollTop(),
              current_is = $(".search-wrapper .search-input:visible"),
              tabs = $(".ordered-block .nav.nav-tabs"),
              headerCanFix = !mobileBtnMenu.is(":visible"); /* && !$('.dropdown-menu:visible').length*/

            if (!isHeaderFixed) {
              if (headerCanFix && scrollTop > headerNormalHeight + headerTop) {
                if (!isNloLoaded) {
                  if (!$headerFixedNlo.hasClass("nlo-loadings")) {
                    $headerFixedNlo.addClass("nlo-loadings");
                    setTimeout(function () {
                      $.ajax({
                        data: { nlo: $headerFixedNlo.attr("data-nlo") },
                        success: function (response) {
                          // stop ya metrika webvisor DOM indexer
                          pauseYmObserver();

                          isNloLoaded = true;
                          $headerFixedNlo[0].insertAdjacentHTML("beforebegin", $.trim(response));
                          $headerFixedNlo.remove();

                          InitMenuNavigationAim();
                          OnHeaderFixedScrollHandler();

                          // init custom scroll
                          /*$("#headerfixed .mega-menu table td.wide_menu .customScrollbar").mCustomScrollbarDeferred({
                            mouseWheel: {
                              scrollAmount: 150,
                              preventDefault: true,
                            },
                          });*/

                          // resume ya metrika webvisor
                          // (300ms transition) + (100ms scroll handler)
                          setTimeout(resumeYmObserver, 400);
                        },
                        error: function () {
                          $headerFixedNlo.removeClass("nlo-loadings");
                        },
                      });
                    }, 300);
                  }
                } else {
                  isHeaderFixed = true;
                  if (header_simple.length) {
                    headerSimpleHeight = header_simple.actual("outerHeight");
                    header_simple.closest(".header_wrap").css({ "margin-top": headerSimpleHeight });
                  }
                  header_fixed.addClass("fixed");

                  $("nav.mega-menu.sliced.initied").removeClass("initied");
                  CheckTopMenuDotted();
                }
              }
            }
            if (isHeaderFixed || !headerCanFix) {
              if (!headerCanFix || scrollTop <= headerDiffHeight + headerTop) {
                isHeaderFixed = false;
                header_fixed.removeClass("fixed");
                if (header_simple.length) {
                  header_simple.closest(".header_wrap").css({ "margin-top": "" });
                }
              }
            }

            //fixed tabs
            if (tabs_fixed.length && tabs.length) {
              var tabs_offset = $(".ordered-block .nav.nav-tabs").offset();
              if (scrollTop + headerFixedHeight > tabs_offset.top) {
                tabs_fixed.css({ top: header_fixed.actual("outerHeight") });
                tabs_fixed.addClass("fixed");

                header_fixed.addClass("tabs-fixed");
              } else if (tabs_fixed.hasClass("fixed")) {
                tabs_fixed.removeAttr("style");
                tabs_fixed.removeClass("fixed");

                header_fixed.removeClass("tabs-fixed");
              }
            }
          }
        })
      );
    }
  }

  //mobile fixed
  var headerSimple = $("body.simple_basket_mode .wrapper1.mfixed_Y #header");
  var mfixed = headerSimple.length ? headerSimple : $(".wrapper1.mfixed_Y #mobileheader");
  if (mfixed.length && isMobile) {
    var isMHeaderFixed = false,
      mheaderCanFix = true,
      mheaderFixedHeight = mfixed.actual("outerHeight"),
      // mheaderFixedHeight = 0,
      mheaderTop = $("#panel:visible").actual("outerHeight"),
      mHeaderScrollTop = $(".wrapper1").hasClass("mfixed_view_scroll_top");
    $(window).scroll(function () {
      var scrollTop = $(window).scrollTop();
      if (window.matchMedia("(max-width:991px)").matches) {
        if (mHeaderScrollTop) {
          if (scrollTop > startScroll) {
            if (!$("#mobilePhone.show").length) {
              mfixed.removeClass("fixed");
              if (headerSimple.length) {
                headerSimple.closest(".header_wrap").css({ "margin-top": "" });
              }
            }
          } else if (scrollTop > mheaderFixedHeight + mheaderTop) {
            mfixed.addClass("fixed");
            if (headerSimple.length) {
              headerSimple.closest(".header_wrap").css({ "margin-top": mheaderFixedHeight });
            }
          } else if (scrollTop <= mheaderFixedHeight + mheaderTop) {
            mfixed.removeClass("fixed");
            if (headerSimple.length) {
              headerSimple.closest(".header_wrap").css({ "margin-top": "" });
            }
          }
          startScroll = scrollTop;
        } else {
          if (!isMHeaderFixed) {
            if (scrollTop > mheaderFixedHeight + mheaderTop) {
              isMHeaderFixed = true;
              mfixed.addClass("fixed");
              if (headerSimple.length) {
                headerSimple.closest(".header_wrap").css({ "margin-top": mheaderFixedHeight });
              }
            }
          } else if (isMHeaderFixed) {
            if (scrollTop <= mheaderFixedHeight + mheaderTop) {
              isMHeaderFixed = false;
              mfixed.removeClass("fixed");
              if (headerSimple.length) {
                headerSimple.closest(".header_wrap").css({ "margin-top": "" });
              }
            }
          }
        }
      } else {
        mfixed.removeClass("fixed");
        if (headerSimple.length) {
          headerSimple.closest(".header_wrap").css({ "margin-top": "" });
        }
      }
    });
  }
};

CheckHeaderFixedMenu = function () {
  if (
    arAsproOptions["THEME"] &&
    arAsproOptions["THEME"]["HEADER_FIXED"] == 2 &&
    $("#headerfixed .js-nav").length &&
    window.matchMedia("(min-width: 992px)").matches
  ) {
    $("#headerfixed .js-nav").css("width", "0");
    var all_width = 0,
      cont_width = $("#headerfixed .maxwidth-theme").actual("width"),
      padding_menu =
        $("#headerfixed .logo-row.v2 .menu-block").actual("outerWidth") -
        $("#headerfixed .logo-row.v2 .menu-block").actual("width");
    $("#headerfixed .logo-row.v2 > .inner-table-block").each(function () {
      if (!$(this).hasClass("menu-block")) all_width += $(this).actual("outerWidth");
    });
    $("#headerfixed .js-nav").width(cont_width - all_width - padding_menu);
  }
};

CheckSearchWidth = function () {
  if ($(".logo_and_menu-row .search_wrap").length) {
    var searchPosition = $(".logo_and_menu-row .search_wrap").position().left,
      maxWidth = $(".logo_and_menu-row .maxwidth-theme").width() - 2;
    width = 0;
    if ($(".logo_and_menu-row .subtop .search_wrap").length) {
      maxWidth = $(".logo_and_menu-row .subtop").width() - 2;
      $(".logo_and_menu-row .subtop > .row >div >div")
        .each(function () {
          if (!$(this).hasClass("search_wrap")) {
            var elementWidth = $(this).outerWidth();
            if (!$(this).is(":visible") || !$(this).height()) elementWidth = 0;
            width = width ? width - elementWidth : maxWidth - elementWidth;
          }
        })
        .promise()
        .done(function () {
          if ($(".logo_and_menu-row .search_wrap.wide_search").length)
            $(".logo_and_menu-row .search_wrap .search-block").outerWidth(width);
          else $(".logo_and_menu-row .search_wrap").outerWidth(width);
          $(".logo_and_menu-row .search_wrap").css({ opacity: 1, visibility: "visible" });
        });
    } else {
      if ($(".logo_and_menu-row .subbottom .search_wrap").length) {
        maxWidth = $(".logo_and_menu-row .subbottom").width() - 2;
        $(".logo_and_menu-row .subbottom >div")
          .each(function () {
            if (!$(this).hasClass("search_wrap")) {
              var elementWidth = $(this).outerWidth();
              if (!$(this).is(":visible") || !$(this).height()) elementWidth = 0;
              width = width ? width - elementWidth : maxWidth - elementWidth;
            }
          })
          .promise()
          .done(function () {
            if ($(".logo_and_menu-row .search_wrap.wide_search").length)
              $(".logo_and_menu-row .search_wrap .search-block").outerWidth(width);
            else $(".logo_and_menu-row .search_wrap").outerWidth(width);
            $(".logo_and_menu-row .search_wrap").css({ opacity: 1, visibility: "visible" });
          });
      } else {
        $(".logo_and_menu-row .maxwidth-theme > .row >div >div")
          .each(function () {
            if (!$(this).hasClass("search_wrap")) {
              var elementWidth = $(this).outerWidth();
              if (!$(this).is(":visible") || !$(this).height()) elementWidth = 0;
              width = width ? width - elementWidth : maxWidth - elementWidth;
            }
          })
          .promise()
          .done(function () {
            if ($(".logo_and_menu-row .search_wrap.wide_search").length)
              $(".logo_and_menu-row .search_wrap .search-block").outerWidth(width);
            else $(".logo_and_menu-row .search_wrap").outerWidth(width);
            $(".logo_and_menu-row .search_wrap").css({ opacity: 1, visibility: "visible" });
          });
      }
    }
  }
};

lazyLoadPagenBlock = function () {
  setTimeout(function () {
    if ($(".with-load-block .ajax_load_btn:not(.appear-block)").length) {
      $(".with-load-block .ajax_load_btn:not(.appear-block)").appear(
        function () {
          var $this = $(this);
          $this.addClass("appear-block").trigger("click");
        },
        { accX: 0, accY: 200 }
      );
    }
  }, 200);
};

scrollPreviewBlock = function () {
  if (typeof $.cookie("scroll_block") != "undefined" && $.cookie("scroll_block")) {
    var scroll_block = $($.cookie("scroll_block"));
    if (scroll_block.length) {
      $("body, html").animate({ scrollTop: scroll_block.offset().top }, 500);
    }
    $.cookie("scroll_block", null);
  }
};

scrollToBlock = function (block) {
  if ($(block).length) {
    var offset = $(block).offset().top;
    if (typeof $(block).data("toggle") != "undefined") $(block).click();

    if (typeof $(block).data("offset") != "undefined") offset += $(block).data("offset");

    $("body, html").animate({ scrollTop: offset }, 500);
  }
};

checkMenuLines = function () {
  if ($(".front_page .menu-row .left_border").length || $(".front_page .menu-row .right_border").length) {
    var positionMenu = $(".centered .menu-row .mega-menu table").length
        ? $(".centered .menu-row .mega-menu table").offset().left
        : 0,
      varFixLineWidth =
        $("body").hasClass("with_decorate") && window.matchMedia("(min-width: 1100px)").matches ? 126 : 7;

    $(".menu-row .left_border, .menu-row .right_border").css("width", positionMenu - varFixLineWidth);
  }
};

SetFixedAskBlock = function () {
  if ($(".ask_a_question_wrapper").length) {
    var offset = $(".ask_a_question_wrapper").offset(),
      footer_offset = 0,
      block = $(".ask_a_question_wrapper").find(".ask_a_question"),
      block_offset = BX.pos(block[0]),
      block_height = block_offset.bottom - block_offset.top,
      diff_top_scroll = $("#headerfixed").height() + 20;

    if ($("footer").length) footer_offset = $("footer").offset().top;

    if ($(".banner.CONTENT_BOTTOM").length) footer_offset = $(".banner.CONTENT_BOTTOM").offset().top;

    /* removed for ISSUE NEXT-414
    if(block_height+130 > block.closest('.fixed_wrapper').height())
      block.addClass('nonfixed');
    else
      block.removeClass('nonfixed');
    */

    if (block_height + diff_top_scroll + documentScrollTopLast + 130 > footer_offset) {
      block.removeClass("fixed").css({ top: "auto", width: "auto", bottom: 0 });
      block.parent().css("position", "static");
      block.parent().parent().css("position", "static");
    } else {
      block.parent().removeAttr("style");
      block.parent().parent().removeAttr("style");

      if (documentScrollTopLast + diff_top_scroll > offset.top) {
        var fixed_width = $(".fixed_block_fix").width();
        block.addClass("fixed").css({ top: diff_top_scroll, bottom: "auto" });
        if (fixed_width) block.css({ width: $(".fixed_block_fix").width() });
      } else block.removeClass("fixed").css({ top: 0, width: "auto" });
    }
  }
};

MegaMenuFixed = function () {
  var animationTime = 150,
    $megaFixedNlo = $(".mega_fixed_menu").find("[data-nlo]");

  $("header .burger, #headerfixed .burger").on("click", function () {
    if ($megaFixedNlo.length) {
      if (!$megaFixedNlo.hasClass("nlo-loadings")) {
        $megaFixedNlo.addClass("nlo-loadings");
        setTimeout(function () {
          $.ajax({
            data: { nlo: $megaFixedNlo.attr("data-nlo") },
            success: function (response) {
              $megaFixedNlo[0].insertAdjacentHTML("beforebegin", $.trim(response));
              $megaFixedNlo.remove();
            },
            error: function () {
              $megaFixedNlo.removeClass("nlo-loadings");
            },
          });
        }, 300);
      }
    }

    $(".mega_fixed_menu").fadeIn(animationTime);
    //$('.header_wrap').toggleClass('zindexed');
    /*$(".mega_fixed_menu").mCustomScrollbarDeferred({
      mouseWheel: {
        scrollAmount: 150,
        preventDefault: true,
      },
    });*/
  });

  $(".mega_fixed_menu .svg.svg-close").on("click", function () {
    $(this).closest(".mega_fixed_menu").fadeOut(animationTime);
    //$('.header_wrap').toggleClass('zindexed');
  });

  $(".mega_fixed_menu .dropdown-menu .arrow").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).closest(".dropdown-submenu").find(".dropdown-menu").slideToggle(animationTime);
    $(this).closest(".dropdown-submenu").addClass("opened");
  });
};

CheckPopupTop = function () {
  // var popup = $('.jqmWindow.show:last');
  // if(!popup.length)
  // 	popup = $('.jqmWindow.show');
  // if(!popup.length)
  // 	popup = $('.jqm-init.show');
  // if(popup.length){
  // 	var documentScollTop = $(document).scrollTop();
  // 	var windowHeight = $(window).height();
  // 	var popupTop = parseInt(popup.css('top'));
  // 	var popupHeight = popup.height();
  // 	if(windowHeight >= popupHeight){
  // 		// center
  // 		popupTop = (windowHeight - popupHeight) / 2;
  // 	}
  // 	else{
  // 		if(documentScollTop > documentScrollTopLast){
  // 			// up
  // 			popupTop -= documentScollTop - documentScrollTopLast;
  // 		}
  // 		else if(documentScollTop < documentScrollTopLast){
  // 			// down
  // 			popupTop += documentScrollTopLast - documentScollTop;
  // 		}
  // 		if(popupTop + popupHeight < windowHeight){
  // 			// bottom
  // 			popupTop = windowHeight - popupHeight;
  // 		}
  // 		else if(popupTop > 0){
  // 			// top
  // 			popupTop = 0;
  // 		}
  // 	}
  // 	popup.css('top', popupTop + 'px');
  // }
};

AjaxClickLink = function (e) {
  var _this = "",
    container = $(".js-load-wrapper"),
    container_inner = $(".js-load-wrapper").find(".ajax_load"),
    dopAction = "";
  url = "";
  if ("preventDefault" in e) {
    e.preventDefault();
    _this = $(e.target).hasClass("js-load-link") ? $(e.target) : $(e.target).closest(".js-load-link");
  } else {
    _this = $(e);
    dopAction = "Y";
  }

  if (container.length) {
    var objUrl = parseUrlQuery(),
      obGetData = { ajax_get_filter: "Y", control_ajax: "Y" };

    obGetData.bitrix_include_areas = "N";

    if ("clear_cache" in objUrl) {
      if (objUrl.clear_cache == "Y") {
        obGetData.clear_cache = "Y";
      }
    }

    if (container_inner.length) {
      container_inner.addClass("loading-state");
    } else {
      container.addClass("loading-state");
    }

    if (_this.data("url")) {
      url = _this.data("url");
    }

    if (_this.data("click_block") && $(_this.data("click_block")).length) {
      if ($(_this.data("click_block")).data("url")) {
        url = $(_this.data("click_block")).data("url");
      }
    }

    if (dopAction) {
      BX.PopupWindowManager.getCurrentPopup().close();
      $(".bx_filter_select_popup ul li .sort_btn").removeClass("current");
      _this.addClass("current");
      _this.closest(".bx_filter_block").find(".bx_filter_select_text").text(_this.text());
    }

    $(".bx_filter .bx_sort_filter .bx_filter_select_text").text(_this.text());
    $(".bx_filter .bx_sort_filter .bx_filter_select_popup ul li span.current").removeClass("current");
    $(".bx_filter .bx_sort_filter .bx_filter_select_popup ul li")
      .eq(_this.parent().index())
      .find("span")
      .addClass("current");

    $.ajax({
      url: url,
      data: obGetData,
      success: function (html) {
        container.html(html);

        if (container_inner.length) container_inner.removeClass("loading-state");
        else container.removeClass("loading-state");

        initAnimateLoad();

        var eventdata = { action: "jsLoadBlock" };
        BX.onCustomEvent("onCompleteAction", [eventdata, _this]);

        BX.onCustomEvent("onHeaderProgressBarChange", [{}]);

        initSelects(document);
        InitCustomScrollBar();
        InitScrollBar();

        if (window.FilterHelper !== undefined) {
          FilterHelper.resultDiv = $("#filter-helper");
          FilterHelper.show();
        }
      },
    });
  }
};

initCalculatePreview = function () {
  $(".calculate-delivery.with_preview:not(.inited)").each(function () {
    var $this = $(this);
    var $calculateSpan = $this.find("span[data-event=jqm]");
    var $preview = $this.find(".calculate-delivery-preview");

    $this.addClass("inited");
    $this.appear(
      function () {
        if ($calculateSpan.length) {
          if (typeof window["calculate-delivery-preview-index"] === "undefined") {
            window["calculate-delivery-preview-index"] = 1001;
          } else {
            ++window["calculate-delivery-preview-index"];
          }

          var productId = $calculateSpan.attr("data-param-product_id") * 1;
          var quantity = $calculateSpan.attr("data-param-quantity") * 1;

          if (productId > 0) {
            var areaIndexSended = window["calculate-delivery-preview-index"];
            $calculateSpan.data({ areaIndex: areaIndexSended });
            console.log(quantity);
            $.ajax({
              url: arAsproOptions["SITE_DIR"] + "ajax/delivery.php",
              type: "POST",
              data: {
                is_preview: "Y",
                index: areaIndexSended,
                product_id: productId,
                quantity: quantity,
              },
              beforeSend: function () {
                $this.addClass("loadings");
              },
              success: function (response) {
                var areaIndex = $calculateSpan.data("areaIndex");
                if (typeof areaIndex !== "undefined" && areaIndex == areaIndexSended) {
                  $calculateSpan.hide();
                  $preview.html(response);
                  if (!$preview.find(".catalog-delivery-preview").length) {
                    $preview.empty();
                    $calculateSpan.show();
                  }
                }
              },
              error: function (xhr, ajaxOptions, thrownError) {},
              complete: function () {
                var areaIndex = $calculateSpan.data("areaIndex");
                if (typeof areaIndex !== "undefined" && areaIndex == areaIndexSended) {
                  $this.removeClass("loadings");
                }
              },
            });
          }
        }
      },
      { accX: 0, accY: 0 }
    );
  });
};

/*set price item*/
if (!funcDefined("setPriceItem")) {
  var setPriceItem = function setPriceItem(
    main_block,
    quantity,
    rewrite_price,
    check_quantity,
    is_sku,
    show_percent,
    percent
  ) {
    var old_quantity = main_block.find(".to-cart").attr("data-ratio"),
      value =
        typeof rewrite_price !== "undefined" && rewrite_price
          ? rewrite_price
          : main_block.find(".to-cart").attr("data-value"),
      currency = main_block.find(".to-cart").attr("data-currency"),
      total_block =
        '<div class="total_summ" style="display:none;"><div>' +
        BX.message("TOTAL_SUMM_ITEM") +
        "<span></span></div></div>",
      price_block = main_block.find(".cost.prices"),
      use_percent = typeof show_percent !== "undefined" && show_percent == "Y",
      percent_number = typeof percent !== "undefined" && percent,
      sku_checked = main_block.find(".has_offer_prop").length ? "Y" : "N",
      check = typeof check_quantity !== "undefined" && check_quantity;

    if (main_block.find(".counter_wrapp + .wrapp-one-click").length) {
      if (!main_block.find(".wrapp-one-click .total_summ").length && !is_sku)
        $(total_block).appendTo(main_block.find(".counter_wrapp + .wrapp-one-click"));
    } else if (main_block.find(".buy_block").length) {
      if (!main_block.find(".buy_block .total_summ").length && !is_sku)
        $(total_block).appendTo(main_block.find(".buy_block"));
    } else if (main_block.find(".counter_wrapp").length) {
      if (!main_block.find(".counter_wrapp .total_summ").length && !is_sku)
        $(total_block).appendTo(main_block.find(".counter_wrapp:first"));
    }
    if (main_block.find(".total_summ").length) {
      if (value && currency) {
        if ((1 == quantity && old_quantity == quantity) || (typeof is_sku !== "undefined" && is_sku && !check)) {
          main_block.find(".total_summ").slideUp(50);
        } else {
          main_block.find(".total_summ span").html(BX.Currency.currencyFormat(value * quantity, currency, true));
          if (main_block.find(".total_summ").is(":hidden") /* || sku_checked == 'Y'*/)
            main_block.find(".total_summ").slideDown(100);
        }
      } else {
        main_block.find(".total_summ").slideUp(100);
      }
    }
  };
}

if (!funcDefined("getCurrentPrice")) {
  var getCurrentPrice = function getCurrentPrice(price, currency, print_price) {
    var val = "";
    var format_value = BX.Currency.currencyFormat(price, currency);
    if (print_price.indexOf(format_value) >= 0) {
      val = print_price.replace(
        format_value,
        '<span class="price_value">' + format_value + '</span><span class="price_currency">'
      );
      val += "</span>";
    } else {
      val = print_price;
    }

    return val;
  };
}

if (!funcDefined("initAnimateLoad")) {
  var initAnimateLoad = function initAnimateLoad() {
    $(".animate-load").click(function () {
      if (!jQuery.browser.mobile) {
        $(this).parent().addClass("loadings");
      }
    });
  };
}

if (!funcDefined("showBasketShareBtn")) {
  var showBasketShareBtn = function () {
    if (arAsproOptions["THEME"]["SHOW_SHARE_BASKET"] === "Y") {
      if (!document.querySelector(".basket-checkout-block-btns")) {
        var checkout = document.querySelector(".basket-checkout-section-inner");
        if (checkout) {
          var btns = BX.create({
            tag: "div",
            attrs: {
              class: "basket-checkout-block basket-checkout-block-btns",
            },
            html: '<div class="basket-checkout-block-btns-wrap"></div>',
          });

          BX.insertAfter(btns, BX.lastChild(checkout));
          var btnsWrap = btns.querySelector(".basket-checkout-block-btns-wrap");

          var btnCheckout = checkout.querySelector(".basket-checkout-block-btn");
          if (btnCheckout) {
            btnsWrap.appendChild(btnCheckout);
          }

          var btnFastOrder = checkout.querySelector(".fastorder");
          if (btnFastOrder) {
            btnsWrap.appendChild(btnFastOrder);
          }

          if ($(".basket-btn-checkout").length && !$(".basket-btn-checkout").hasClass("disabled")) {
            var btnShareBasket = BX.create({
              tag: "div",
              attrs: {
                class: "basket-checkout-block basket-checkout-block-share colored_theme_hover_bg-block",
                title: arAsproOptions["THEME"]["EXPRESSION_FOR_SHARE_BASKET"],
              },
              html:
                '<span class="animate-load" data-event="jqm" data-param-form_id="share_basket" data-name="share_basket"><i class="svg colored_theme_hover_bg-el-svg"><svg class="svg svg-share" xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16"><path data-name="Ellipse 223 copy 8" d="M1613,203a2.967,2.967,0,0,1-1.86-.661l-3.22,2.01a2.689,2.689,0,0,1,0,1.3l3.22,2.01A2.961,2.961,0,0,1,1613,207a3,3,0,1,1-3,3,3.47,3.47,0,0,1,.07-0.651l-3.21-2.01a3,3,0,1,1,0-4.678l3.21-2.01A3.472,3.472,0,0,1,1610,200,3,3,0,1,1,1613,203Zm0,8a1,1,0,1,0-1-1A1,1,0,0,0,1613,211Zm-8-7a1,1,0,1,0,1,1A1,1,0,0,0,1605,204Zm8-5a1,1,0,1,0,1,1A1,1,0,0,0,1613,199Z" transform="translate(-1602 -197)" fill="#B8B8B8"></path></svg></i><span class="title">' +
                arAsproOptions["THEME"]["EXPRESSION_FOR_SHARE_BASKET"] +
                "</span></span>",
            });

            btnsWrap.appendChild(btnShareBasket);
            initAnimateLoad();
          }
        }
      }
    }
  };
}

if (!funcDefined("showBasketHeadingBtn")) {
  var showBasketHeadingBtn = function () {
    if (document.querySelector(".page-top h1")) {
      var topicHeading = document.querySelector(".page-top .topic .topic__heading");
      if (topicHeading) {
        if (arAsproOptions["THEME"]["SHOW_BASKET_PRINT"] === "Y") {
          if (!document.querySelector(".btn_basket_heading--print")) {
            var btnPrintBasket = BX.create({
              tag: "div",
              attrs: {
                class: "btn_basket_heading btn_basket_heading--print print-link colored_theme_hover_bg-block",
                title: arAsproOptions["THEME"]["EXPRESSION_FOR_PRINT_PAGE"],
              },
              html: '<i class="svg colored_theme_hover_bg-el-svg"><svg class="svg svg-print" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path id="Rectangle_665_copy_4" data-name="Rectangle 665 copy 4" class="cls-print" d="M1570,210h-2v3h-8v-3h-2a2,2,0,0,1-2-2v-5a2,2,0,0,1,2-2h2v-4h8v4h2a2,2,0,0,1,2,2v5A2,2,0,0,1,1570,210Zm-8,1h4v-4h-4v4Zm4-12h-4v2h4v-2Zm4,4h-12v5h2v-3h8v3h2v-5Z" transform="translate(-1556 -197)"></path></svg></i>',
            });
            BX.insertBefore(btnPrintBasket, topicHeading);
          }
        }

        if ($(".basket-btn-checkout").length && !$(".basket-btn-checkout").hasClass("disabled")) {
          // if(arAsproOptions['THEME']['SHOW_SEND_BASKET_2EMAIL'] === 'Y'){
          // if(!document.querySelector('.btn_basket_heading--send2email')){
          // 	var btnSendBasket2Email = BX.create({
          // 		tag: 'div',
          // 		attrs: {
          // 			class: 'btn_basket_heading btn_basket_heading--with_title btn_basket_heading--send2email colored_theme_hover_bg-block',
          // 			title: arAsproOptions['THEME']["EXPRESSION_FOR_SEND_BASKET_2EMAIL"],
          // 		},
          // 		html: '<i class="svg colored_theme_hover_bg-el-svg"><svg class="svg" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3 0H4H12H13V1V3.125L15.06 4.4125C15.6448 4.77798 16 5.41892 16 6.1085V6.98162C16.0002 6.99325 16.0002 7.00488 16 7.01649V14C16 15.1046 15.1046 16 14 16H2C0.895429 16 -1.43918e-06 15.1046 -1.43918e-06 14V7.01644C-0.000202747 7.00486 -0.000203195 6.99327 -1.43918e-06 6.98167V6.1085C-1.43918e-06 5.41892 0.355238 4.77798 0.940001 4.4125L3 3.125V1V0ZM5 4.2335L5 2H11V7L11 8.23381L9.62943 9.05615L8.64018 8.23178C8.26934 7.92274 7.73066 7.92274 7.35981 8.23178L6.37057 9.05615L5 8.23381V4.2335ZM2 6.1085L3 5.4835V7.03381L2 6.43381V6.1085ZM14 6.1085L13 5.4835V7.03381L14 6.43381V6.1085ZM12.4379 14L8 10.3017L3.56205 14H12.4379ZM14 12.6983V8.76619L11.2567 10.4122L14 12.6983ZM2 8.76619V12.6983L4.74333 10.4122L2 8.76619Z" fill="#B8B8B8"/></svg></i><span class="title">' + arAsproOptions['THEME']["EXPRESSION_FOR_SEND_BASKET_2EMAIL"] + '</span>',
          // 	});
          // 	BX.insertBefore(btnSendBasket2Email, topicHeading);
          // }
          // }

          if (arAsproOptions["THEME"]["SHOW_DOWNLOAD_BASKET"] === "Y") {
            if (!document.querySelector(".btn_basket_heading--download")) {
              var btnDownloadBasket = BX.create({
                tag: "div",
                attrs: {
                  class:
                    "btn_basket_heading btn_basket_heading--with_title btn_basket_heading--download colored_theme_hover_bg-block",
                  title: arAsproOptions["THEME"]["EXPRESSION_FOR_DOWNLOAD_BASKET"],
                },
                events: {
                  click: BX.proxy(function (e) {
                    if (!e) {
                      e = window.event;
                    }

                    BX.PreventDefault(e);

                    var button = e.target.closest(".btn_basket_heading");
                    if (button) {
                      if (BX.hasClass(button, "loadings")) {
                        return;
                      }

                      BX.addClass(button, "loadings");
                      setTimeout(function () {
                        BX.removeClass(button, "loadings");
                      }, 2000);
                    }

                    location.href =
                      "/ajax/download_basket.php?params[type]=" +
                      arAsproOptions["THEME"]["BASKET_FILE_DOWNLOAD_TEMPLATE"];
                  }, this),
                },
                html:
                  '<i class="svg colored_theme_hover_bg-el-svg"><svg class="svg" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5 14H14L14 6H11H10V5V2H5V6H7H9V8V10V12H7H5V14ZM13.6716 4L12 2.32843V4H13.6716ZM6 8H7V10H6H5H3H2V8H3H5H6ZM3 6H2H0V8V10V12H2H3V14V16H5H14H16V14V4.32843C16 3.79799 15.7893 3.28929 15.4142 2.91421L13.0858 0.585786C12.7107 0.210714 12.202 0 11.6716 0H5H3V2V6Z" fill="#B8B8B8"/></svg></i><span class="title">' +
                  arAsproOptions["THEME"]["EXPRESSION_FOR_DOWNLOAD_BASKET"] +
                  "</span>",
              });
              BX.insertBefore(btnDownloadBasket, topicHeading);
            }
          }
        } else {
          BX.remove(document.querySelector(".btn_basket_heading--send2email"));
          BX.remove(document.querySelector(".btn_basket_heading--download"));
        }

        var eventdata = { parent: topicHeading };
        BX.onCustomEvent("onShowBasketHeadingBtn", [eventdata]);
      }
    }
  };
}

$(window).on("load", function () {
  //check width for menu and search
  CheckSearchWidth();
});

$(document).ready(function () {
  MegaMenuFixed();
  InitScrollBar();

  /*ripple effect for buttons*/
  $.ripple(".btn", {
    debug: false, // Turn Ripple.js logging on/off
    on: "mouseenter", // The event to trigger a ripple effect

    opacity: 0.4, // The opacity of the ripple
    color: "auto", // Set the background color. If set to "auto", it will use the text color
    multi: true, // Allow multiple ripples per element

    duration: 0.6, // The duration of the ripple

    easing: "linear", // The CSS3 easing function of the ripple
  });
  /**/

  //ecommerce order
  if (arAsproOptions["PAGES"]["ORDER_PAGE"]) {
    var arUrl = parseUrlQuery();
    if ("ORDER_ID" in arUrl) {
      var _id = arUrl["ORDER_ID"];
      if (arAsproOptions["COUNTERS"]["USE_FULLORDER_GOALS"] !== "N") {
        var eventdata = { goal: "goal_order_success", result: _id };
        BX.onCustomEvent("onCounterGoals", [eventdata]);
      }
      if (checkCounters()) {
        if (typeof localStorage !== "undefined") {
          var val = localStorage.getItem("gtm_e_" + _id),
            d = "";
          try {
            d = JSON.parse(val);
          } catch (e) {
            d = val;
          }
          if (typeof d === "object") {
//             window.dataLayer = window.dataLayer || [];
//             dataLayer.push({ event: arAsproOptions["COUNTERS"]["GOOGLE_EVENTS"]["PURCHASE"], ecommerce: d });
          }

          if (typeof localStorage !== "undefined") {
            localStorage.removeItem("gtm_e_" + _id);
          }
        }
      }
    }
  }
  var bSafary = false;
  if (typeof jQuery.browser == "object") bSafary = jQuery.browser.safari;
  else if (typeof browser == "object") bSafary = browser.safari;
  if (!bSafary) {
    CheckTopMenuPadding();
    CheckTopMenuOncePadding();
    CheckTopMenuDotted();
    CheckHeaderFixed();
    checkMenuLines();
    //setTimeout(function() {$(window).resize();}, 150); // need to check resize flexslider & menu
    //setTimeout(function() {$(window).scroll();}, 250); // need to check position fixed ask block
  } else {
    setTimeout(function () {
      $(window).resize(); // need to check resize flexslider & menu
      setTimeout(function () {
        CheckTopMenuPadding();
        CheckTopMenuOncePadding();
        CheckTopMenuDotted();
        CheckHeaderFixed();
        checkMenuLines();

        setTimeout(function () {
          $(window).scroll();
        }, 50);
      }, 50);
    }, 350);
  }

  /*for filter scrol in mobile*/
  if (window.matchMedia("(max-width: 767px)").matches) {
    $(".bx_filter .scrollbar").addClass("mobile-scroll").removeClass("scroll-init");
    $(".bx_filter .srollbar-custom").addClass("mobile-scroll").removeClass("scroll-init");
    $(".bx_filter .bx_filter_section form .bx_filter_parameters").addClass("mobile-scroll").removeClass("scroll-init");
    if ($(".bx_filter .mobile-scroll.scrollbar").length)
      $(".bx_filter .mobile-scroll.scrollbar").mCustomScrollbar("destroy");
    if ($(".bx_filter .mobile-scroll.srollbar-custom").length)
      $(".bx_filter .mobile-scroll.srollbar-custom").mCustomScrollbar("destroy");
  }
  /**/

  if (arAsproOptions["THEME"]["USE_DEBUG_GOALS"] === "Y") $.cookie("_ym_debug", 1, { path: "/" });
  else $.cookie("_ym_debug", null, { path: "/" });

  /* change type2 menu for fixed */
  if ($("#headerfixed .js-nav").length) {
    if (arAsproOptions["THEME"]["HEADER_FIXED"] == 2) CheckHeaderFixedMenu();

    setTimeout(function () {
      $("#headerfixed .js-nav").addClass("opacity1");
    }, 350);
  }

  // -- scroll after apply option
  if ($(".instagram_ajax").length) {
    BX.addCustomEvent("onCompleteAction", function (eventdata) {
      if (eventdata.action === "instagrammLoaded") scrollPreviewBlock();
    });
  } else scrollPreviewBlock();

  scrollToTop();
  checkVerticalMobileFilter();

  $("[data-scroll-block]").click(function () {
    var _this = $(this);
    if (_this.data("scrollBlock")) {
      var target = $(_this.data("scrollBlock"));
      if (target.length) {
        scroll_block(target);
      }
    }
  });

  $.extend($.validator.messages, {
    required: BX.message("JS_REQUIRED"),
    email: BX.message("JS_FORMAT"),
    equalTo: BX.message("JS_PASSWORD_COPY"),
    minlength: BX.message("JS_PASSWORD_LENGTH"),
    remote: BX.message("JS_ERROR"),
  });

  $.validator.addMethod(
    "regexp",
    function (value, element, regexp) {
      var re = new RegExp(regexp);
      return this.optional(element) || re.test(value);
    },
    BX.message("JS_FORMAT")
  );

  $.validator.addMethod(
    "filesize",
    function (value, element, param) {
      return this.optional(element) || element.files[0].size <= param;
    },
    BX.message("JS_FILE_SIZE")
  );

  $.validator.addMethod(
    "date",
    function (value, element, param) {
      var status = false;
      if (!value || value.length <= 0) {
        status = false;
      } else {
        // html5 date allways yyyy-mm-dd
        var re = new RegExp("^([0-9]{4})(.)([0-9]{2})(.)([0-9]{2})$");
        var matches = re.exec(value);
        if (matches) {
          var composedDate = new Date(matches[1], matches[3] - 1, matches[5]);
          status =
            composedDate.getMonth() == matches[3] - 1 &&
            composedDate.getDate() == matches[5] &&
            composedDate.getFullYear() == matches[1];
        } else {
          // firefox
          var re = new RegExp("^([0-9]{2})(.)([0-9]{2})(.)([0-9]{4})$");
          var matches = re.exec(value);
          if (matches) {
            var composedDate = new Date(matches[5], matches[3] - 1, matches[1]);
            status =
              composedDate.getMonth() == matches[3] - 1 &&
              composedDate.getDate() == matches[1] &&
              composedDate.getFullYear() == matches[5];
          }
        }
      }
      return status;
    },
    BX.message("JS_DATE")
  );

  $.validator.addMethod(
    "extension",
    function (value, element, param) {
      param = typeof param === "string" ? param.replace(/,/g, "|") : "png|jpe?g|gif";
      return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
    },
    BX.message("JS_FILE_EXT")
  );

  $.validator.addMethod(
    "captcha",
    function (value, element, params) {
      return $.validator.methods.remote.call(this, value, element, {
        url: arAsproOptions["SITE_DIR"] + "ajax/check-captcha.php",
        type: "post",
        data: {
          captcha_word: value,
          captcha_sid: function () {
            return $(element).closest("form").find('input[name="captcha_sid"]').val();
          },
        },
      });
    },
    BX.message("JS_ERROR")
  );

  $.validator.addMethod(
    "recaptcha",
    function (value, element, param) {
      var id = $(element).closest("form").find(".g-recaptcha").attr("data-widgetid");
      if (typeof id !== "undefined") {
        return grecaptcha.getResponse(id) != "";
      } else {
        return true;
      }
    },
    BX.message("JS_RECAPTCHA_ERROR")
  );

  $.validator.addClassRules({
    phone: {
      regexp: arAsproOptions["THEME"]["VALIDATE_PHONE_MASK"],
    },
    confirm_password: {
      equalTo: 'input[name="REGISTER[PASSWORD]"]',
      minlength: 6,
    },
    password: {
      minlength: 6,
    },
    inputfile: {
      extension: arAsproOptions["THEME"]["VALIDATE_FILE_EXT"],
      filesize: 5000000,
    },
    captcha: {
      captcha: "",
    },
    recaptcha: {
      recaptcha: "",
    },
  });

  if (arAsproOptions["THEME"]["PHONE_MASK"]) {
    $("input.phone").inputmask("mask", { mask: arAsproOptions["THEME"]["PHONE_MASK"] });
  }

  // init calculate delivery with preview
  initCalculatePreview();

  /*city*/
  $("select.region").on("change", function () {
    var val = parseInt($(this).val());
    if ($("select.city").length) {
      if (val) {
        $("select.city option").hide();
        $("select.city option").prop("disabled", "disabled");
        $("select.city option[data-parent_section=" + val + "]").prop("disabled", "");
        $("select.city option:eq(0)").prop("disabled", "");
        $("select.city").ikSelect("reset");
        $("select.city option[data-parent_section=" + val + "]").show();
      } else $("select.city option").prop("disabled", "disabled");
      $("select.city option:eq(0)").prop("disabled", "");
      $("select.city").ikSelect("reset");
    }
  });

  $("select.city, select.region").on("change", function () {
    var _this = $(this),
      val = parseInt(_this.val());
    if (_this.hasClass("region")) {
      $("select.city option:eq(0)").show();
      $("select.city").val(0);
    }

    if ((_this.hasClass("region") && !val) || _this.hasClass("city")) {
      $.ajax({
        type: "POST",
        data: { ID: val },
      }).success(function (html) {
        var ob = BX.processHTML(html);
        $(".ajax_items")[0].innerHTML = ob.HTML;
        BX.ajax.processScripts(ob.SCRIPT);
      });
    }
  });

  $(document).on("mouseenter", ".section-gallery-wrapper .section-gallery-wrapper__item", function () {
    $(this).siblings().removeClass("_active");
    $(this).addClass("_active");
  });

  $(document).on("mouseleave", ".image_wrapper_block", function () {
    const $elements = $(this).find(".section-gallery-wrapper .section-gallery-wrapper__item");
    if ($elements.length) {
      $elements.removeClass("_active");
      $($elements[0]).addClass("_active");
    }
  });

  $(document).on("click", ".hint .icon", function (e) {
    var tooltipWrapp = $(this).closest(".hint");

    if (tooltipWrapp.hasClass("active")) {
      tooltipWrapp.removeClass("active").find(".tooltip").slideUp(200);
    } else {
      tooltipWrapp.addClass("active");
      tooltipWrapp.find(".tooltip").slideDown(200);
      tooltipWrapp.find(".tooltip_close").click(function (e) {
        e.stopPropagation();
        tooltipWrapp.removeClass("active").find(".tooltip").slideUp(100);
      });
    }
    e.stopPropagation();
  });

  $(".mobile_regions .city_item").on("click", function (e) {
    e.preventDefault();
    var _this = $(this);
    $.removeCookie("current_region");
    $.cookie("current_region", _this.data("id"), { path: "/", domain: arAsproOptions["SITE_ADDRESS"] });
    location.href = _this.attr("href");
  });

  /* toggle */
  var $this = this,
    previewParClosedHeight = 25;

  $("section.toggle > label").prepend($("<i />").addClass("fa fa-plus"));
  $("section.toggle > label").prepend($("<i />").addClass("fa fa-minus"));
  $("section.toggle.active > p").addClass("preview-active");
  $("section.toggle.active > div.toggle-content").slideDown(350, function () {});

  $("section.toggle > label").click(function (e) {
    var parentSection = $(this).parent(),
      parentWrapper = $(this).parents("div.toogle"),
      previewPar = false,
      isAccordion = parentWrapper.hasClass("toogle-accordion");

    if (isAccordion && typeof e.originalEvent != "undefined") {
      parentWrapper.find("section.toggle.active > label").trigger("click");
    }

    parentSection.toggleClass("active");

    // Preview Paragraph
    if (parentSection.find("> p").get(0)) {
      previewPar = parentSection.find("> p");
      var previewParCurrentHeight = previewPar.css("height");
      previewPar.css("height", "auto");
      var previewParAnimateHeight = previewPar.css("height");
      previewPar.css("height", previewParCurrentHeight);
    }

    // Content
    var toggleContent = parentSection.find("> div.toggle-content");

    if (parentSection.hasClass("active")) {
      $(previewPar).animate(
        {
          height: previewParAnimateHeight,
        },
        350,
        function () {
          $(this).addClass("preview-active");
        }
      );
      toggleContent.slideDown(350, function () {});
    } else {
      $(previewPar).animate(
        {
          height: previewParClosedHeight,
        },
        350,
        function () {
          $(this).removeClass("preview-active");
        }
      );
      toggleContent.slideUp(350, function () {});
    }
  });

  if (typeof $.fn.footable == "function") {
    $(".tables-responsive .responsive").footable(); //responsive table
  }

  $("a[rel=tooltip]").tooltip();
  $("span[data-toggle=tooltip]").tooltip();

  $(".toggle .more_items").on("click", function () {
    $(this).closest(".toggle").find(".collapsed").css("display", "inline-block").removeClass("collapsed");
    $(this).remove();
    if (typeof $(this).data("resize") !== "undefined" && $(this).data("resize")) $(window).resize();
  });
  $(".toggle_menu .more_items").on("click", function () {
    $(this).closest(".toggle_menu").find(".collapsed").addClass("clicked_exp");
    $(this).remove();
  });

  /* search sync */
  $(document).on("keyup", ".search-input-div input", function (e) {
    var inputValue = $(this).val();
    $(".search-input-div input:not(:focus").val(inputValue);

    if ($(this).closest("#headerfixed").length) {
      if (e.keyCode == 13) $(".search form").submit();
    }
  });
  $(document).on("click", ".search-button-div button", function (e) {
    if ($(this).closest("#headerfixed").length) $(".search form").submit();
  });

  $(".inline-search-show, .inline-search-hide").on("click", function (e) {
    CloseMobilePhone();
    // if(window.matchMedia('(min-width: 600px)').matches)
    // {
    if (typeof $(this).data("type_search") != "undefined" && $(this).data("type_search") == "fixed")
      $(".inline-search-block").addClass("fixed");

    if (arAsproOptions["THEME"]["TYPE_SEARCH"] == "fixed") {
      /*setTimeout(function(){
          $('.inline-search-block.fixed.big .search-input').focus();
        }, 200);*/
      $(".inline-search-block.fixed.big .search-input").focus();
    }
    if (arAsproOptions["THEME"]["TYPE_SEARCH"] != "fixed") {
      $("#title-search-input").focus();
    }

    $(".inline-search-block").toggleClass("show");
    if ($(".top-block").length) {
      if ($(".inline-search-block").hasClass("show"))
        $(".inline-search-block").css("background", $(".top-block").css("background-color"));
      else $(".inline-search-block").css("background", "#fff");
    }
    if (arAsproOptions["THEME"]["TYPE_SEARCH"] == "fixed") {
      if ($(".inline-search-block").hasClass("show")) $('<div class="jqmOverlay search"></div>').appendTo("body");
      else {
        $("#title-search-input").blur();
        $(".jqmOverlay").detach();
      }
    }
    // }
    // else
    // 	location.href = arAsproOptions['PAGES']['CATALOG_PAGE_URL']+'?q';
  });

  /* close search block */
  $("html, body").on("mousedown", function (e) {
    if (typeof e.target.className == "string" && e.target.className.indexOf("adm") < 0) {
      e.stopPropagation();
      var search_target = $(e.target).closest(".title-search-result");
      if (!$(e.target).hasClass("inline-search-block") && !$(e.target).hasClass("svg") && !search_target.length) {
        $(".inline-search-block").removeClass("show");
        $(".title-search-result").hide();
        if (arAsproOptions["THEME"]["TYPE_SEARCH"] == "fixed") $(".jqmOverlay.search").detach();
      }

      if ($("#mobilePhone").length) {
        CloseMobilePhone();
      }

      if ($("#basket_line .basket_fly").length && parseInt($("#basket_line .basket_fly").css("right")) >= 0) {
        if (!$(e.target).closest(".basket_wrapp").length) {
          $("#basket_line .basket_fly")
            .stop()
            .animate({ right: -$("#basket_line .basket_fly").outerWidth() }, 150);
          $("#basket_line .basket_fly .opener > div").removeClass("cur");
          $("#basket_line .basket_fly").removeClass("swiped");
        }
      }

      if (isMobile) {
        if (search_target.length) location.href = search_target.attr("href");
      }

      if (!$(e.target).closest(".js-info-block").length && !$(e.target).closest(".js-show-info-block").length) {
        $(".js-show-info-block").removeClass("opened");
        $(".js-info-block").fadeOut();
      }

      if (!$(e.target).closest(".hint.active").length) {
        $(".hint.active .icon").trigger("click");
      }

      var class_name = $(e.target).attr("class");
      if (typeof class_name == "undefined" || class_name.indexOf("tooltip") < 0)
        //tooltip link
        $(".tooltip-link").tooltip("hide");
    }
  });
  $(".inline-search-block")
    .find("*")
    .on("mousedown", function (e) {
      e.stopPropagation();
    });

  initAnimateLoad();

  /*check all opt table items*/
  $(document).on("change", "input#select_all_items", function () {
    var _this = $(this);
    var notOfferCount = 0;
    var bComplect = _this.closest(".complect_main_wrap").length;
    if (bComplect) {
      notOfferCount = _this
        .closest(".complect_main_wrap")
        .find(".catalog-block-view__item .counter_wrapp:not(.ce_cmp_visible) .button_block .to-cart").length;
    } else {
      notOfferCount = $(
        ".table-view__item:not([data-product_type=3]), .table-view-offer-tree .table-view__item[data-product_type=3] .counter_wrapp:not(.ce_cmp_visible)"
      ).find(".button_block .to-cart").length;
    }
    var wishCount = $(
      ".table-view__item:not([data-product_type=3]), .table-view-offer-tree .table-view__item[data-product_type=3]"
    ).find(".item-icons .wish_item_button .wish_item.to").length;
    var allGoodsCount = $(".table-view__item").length;

    if (_this.is(":checked")) {
      //buy && wish
      if (notOfferCount != 0) {
        $(".opt_action:not([data-action=compare])").removeClass("no-action");
      }

      //compare
      $(".opt_action[data-action=compare]").removeClass("no-action");

      $(".opt_action").addClass("animate-load");
      $(".opt_action .text").remove();

      //buy
      $('<div class="text">(<span>' + notOfferCount + "</span>)</div>").appendTo($(".opt_action[data-action=buy]"));

      //wish
      $('<div class="text muted">(<span>' + wishCount + "</span>)</div>").appendTo($(".opt_action[data-action=wish]"));

      //compare
      $('<div class="text muted">(<span>' + allGoodsCount + "</span>)</div>").appendTo(
        $(".opt_action[data-action=compare]")
      );

      $('input[name="chec_item"]').prop("checked", "checked");
    } else {
      $(".opt_action").addClass("no-action");
      $(".opt_action").removeClass("animate-load");
      $(".opt_action .text").remove();

      $('input[name="chec_item"]').removeAttr("checked");
    }

    if (_this.closest(".complect_header_block").length && typeof setNewPriceComplect === "function") {
      setNewPriceComplect();
    }
  });

  /*check opt table item*/
  $(document).on("change", "input[name='chec_item']", function () {
    var _this = $(this);

    var canBuy =
      _this.closest(".table-view__item").find(".button_block .to-cart").length ||
      _this.closest(".catalog-block-view__item").find(".button_block .to-cart").length;
    var isOffer = _this.closest(".main_item_wrapper").attr("data-product_type") == "3" && !canBuy;

    if (_this.is(":checked")) {
      $(".opt_action").each(function () {
        var _this = $(this);

        var isBuyAction = _this.attr("data-action") == "buy";
        var isWishAction = _this.attr("data-action") == "wish";

        if (isOffer && (isBuyAction || isWishAction)) {
          return true;
        }

        if (isBuyAction && !canBuy) {
          return true;
        }

        if (_this.find(".text").length) {
          var count = parseInt(_this.find(".text span").text());
          _this.find(".text span").text(++count);
        } else {
          _this.removeClass("no-action");
          _this.addClass("animate-load");
          $('<div class="text muted">(<span>1</span>)</div>').appendTo(_this);
        }
      });
    } else {
      $(".opt_action").each(function () {
        var _this = $(this);

        var isBuyAction = _this.attr("data-action") == "buy";
        var isWishAction = _this.attr("data-action") == "wish";

        if (isOffer && (isBuyAction || isWishAction)) {
          return true;
        }

        if (isBuyAction && !canBuy) {
          return true;
        }

        if (_this.find(".text").length) {
          var count = parseInt(_this.find(".text span").text());
          --count;
          _this.find(".text span").text(count);

          if (!count) {
            _this.addClass("no-action");
            _this.removeClass("animate-load");
            _this.find(".text").remove();
          }
        }
      });
    }
  });

  /*buy|wish|compare opt table items*/
  $(document).on("click", ".opt_action", function () {
    var _this = $(this),
      action = _this.data("action"),
      itemsAll = [],
      basketParams = {
        type: "multiple",
        iblock_id: _this.data("iblock_id"),
        action: action,
        items: {},
      };

    if (!_this.hasClass("no-action")) {
      $(".opt_action").addClass("no-action");
      _this.removeClass("no-action");

      $(".table-view__item, .catalog-block-view__item").each(function () {
        var _this = $(this);
        var canBuy = _this.find(".button_block .to-cart").length;
        var add = _this.find('input[name="chec_item"]').is(":checked") && (canBuy || action != "buy");
        var addOffer = _this.data("product_type") == "3" && _this.find(".button_block .to-cart").length;
        if (add) {
          var itemId = addOffer ? _this.find(".button_block .to-cart").data("item") : _this.data("id");
          itemsAll.push(_this[0]);
          basketParams["items"][itemId] = {};
          basketParams["items"][itemId]["id"] = itemId;
          basketParams["items"][itemId]["product_type"] = _this.data("product_type");
          basketParams["items"][itemId]["quantity"] = _this.find('input[name="quantity"]').val();
          if (addOffer) {
            basketParams["items"][itemId]["add_offer"] = "Y";
          }
        }
      });
      $.ajax({
        type: "POST",
        url: arAsproOptions["SITE_DIR"] + "ajax/item.php",
        data: basketParams,
        dataType: "json",
        success: function (data) {
          if ("STATUS" in data) {
            if (data.STATUS !== "OK") {
              showBasketError(BX.message(data.MESSAGE) + " <br/>" + data.MESSAGE_EXT);
            }

            //js notice
            if (typeof JNoticeSurface !== "undefined") {
              switch (action) {
                case "buy":
                  JNoticeSurface.get().onAdd2cart(itemsAll);
                  break;
                case "wish":
                  JNoticeSurface.get().onAdd2Delay(itemsAll);
                  break;
                case "compare":
                  JNoticeSurface.get().onAdd2Compare(itemsAll);
                  break;
                default:
                  console.log("nothing");
              }
            }
            //

            if ($(".header-cart.fly").length) {
              arBasketAsproCounters.DEFAULT = true;
              SetActualBasketFlyCounters(true);
            } else {
              if ($("#ajax_basket").length) {
                reloadTopBasket("add", $("#ajax_basket"), 200, 5000, "N", "", true);
              } else {
                reloadBasketCounters("", true);
              }
            }
          }
          _this.parent().removeClass("loadings");
          $(".opt_action").removeClass("no-action");
        },
      });
    }
  });

  //fix opt checked for back in browser
  setTimeout(function () {
    if ($('.with-opt-buy input[name="chec_item"], .with-opt-buy input[name="select_all_items"]').length) {
      $('.with-opt-buy input[name="chec_item"], .with-opt-buy input[name="select_all_items"]').prop("checked", false);
    }
  }, 1);

  /*check mobile device*/
  // if (isMobile) {
  //   $(document).on("click", '*[data-event="jqm"]', function (e) {
  //     e.preventDefault();
  //     e.stopPropagation();
  //     var _this = $(this);
  //     var name = _this.data("name");

  //     if (
  //       window.matchMedia("(min-width:768px)").matches ||
  //       (typeof _this.data("no-mobile") !== "undefinde" && _this.data("no-mobile") == "Y")
  //     ) {
  //       if (!$(this).hasClass("clicked")) {
  //         $(this).addClass("clicked");
  //         $(this).jqmEx();
  //         $(this).trigger("click");
  //       }
  //       return false;
  //     } else if (name.length) {
  //       var script = arAsproOptions["SITE_DIR"] + "form/";
  //       var paramsStr = "";
  //       var arTriggerAttrs = {};
  //       $.each(_this.get(0).attributes, function (index, attr) {
  //         var attrName = attr.nodeName;
  //         var attrValue = _this.attr(attrName);
  //         arTriggerAttrs[attrName] = attrValue;
  //         if (/^data\-param\-(.+)$/.test(attrName)) {
  //           var key = attrName.match(/^data\-param\-(.+)$/)[1];
  //           paramsStr += key + "=" + attrValue + "&";
  //         }
  //       });

  //       var triggerAttrs = JSON.stringify(arTriggerAttrs);
  //       var encTriggerAttrs = encodeURIComponent(triggerAttrs);
  //       script += "?name=" + name + "&" + paramsStr + "data-trigger=" + encTriggerAttrs;

  //       if (previewMode && _this.attr("href") !== undefined) {
  //         script = _this.attr("href");
  //       }

  //       location.href = script;
  //     }
  //   });

  //   $(".fancybox").removeClass("fancybox");
  // } else {
  $(document).on("click", '*[data-event="jqm"]', function (e) {
    e.preventDefault();
    e.stopPropagation();

    var _this = $(this);
    var name = _this.data("name");
    if (previewMode && ((name.length && name == "auth") || window.matchMedia("(max-width:400px)").matches)) {
      var script = arAsproOptions["SITE_DIR"] + "form/";
      var paramsStr = "";
      var arTriggerAttrs = {};
      $.each(_this.get(0).attributes, function (index, attr) {
        var attrName = attr.nodeName;
        var attrValue = _this.attr(attrName);
        arTriggerAttrs[attrName] = attrValue;
        if (/^data\-param\-(.+)$/.test(attrName)) {
          var key = attrName.match(/^data\-param\-(.+)$/)[1];
          paramsStr += key + "=" + attrValue + "&";
        }
      });

      var triggerAttrs = JSON.stringify(arTriggerAttrs);
      var encTriggerAttrs = encodeURIComponent(triggerAttrs);
      script += "?name=" + name + "&" + paramsStr + "data-trigger=" + encTriggerAttrs;

      if (_this.attr("href") !== undefined) {
        script = _this.attr("href");
      }

      location.href = script;
    } else {
      if (!$(this).hasClass("clicked")) {
        $(this).addClass("clicked");
        $(this).jqmEx();
        $(this).trigger("click");
      }
    }

    return false;
  });
  // }

  BX.addCustomEvent("onCompleteAction", function (eventdata, _this) {
    try {
      if (eventdata.action === "loadForm") {
        $(_this).parent().removeClass("loadings");
        $(_this).removeClass("clicked");

        if ($(_this).hasClass("one_click_buy_trigger")) {
          $(".wrapp_one_click > span").removeClass("clicked");
          $(".one_click").removeClass("clicked");
        } else if ($(_this).hasClass("one_click_buy_basket_trigger")) {
          $(".fast_order").removeClass("clicked");
        }
      } else if (eventdata.action === "loadBasket") {
        $(".basket-link.basket").attr("title", $("<div/>").html(arBasketPrices.BASKET_SUMM_TITLE).text());
        $(".basket-link.delay").attr("title", $("<div/>").html(arBasketPrices.DELAY_SUMM_TITLE).text());

        if (arBasketPrices.BASKET_COUNT > 0) {
          $(".basket-link.basket").addClass("basket-count");
          $(".basket-link.basket .count").removeClass("empted");
          if ($(".basket-link.basket .prices").length)
            $(".basket-link.basket .prices").html(arBasketPrices.BASKET_SUMM);
        } else {
          $(".basket-link.basket").removeClass("basket-count");
          $(".basket-link.basket .count").addClass("empted");
          if ($(".basket-link.basket .prices").length)
            $(".basket-link.basket .prices").html(arBasketPrices.BASKET_SUMM_TITLE_SMALL);
        }
        $(".basket-link.basket .count").text(arBasketPrices.BASKET_COUNT);
        if (arBasketPrices.DELAY_COUNT > 0) {
          $(".basket-link.delay").addClass("basket-count");
          $(".basket-link.delay .count").removeClass("empted");
        } else {
          $(".basket-link.delay").removeClass("basket-count");
          $(".basket-link.delay .count").addClass("empted");
        }
        $(".basket-link.delay .count").text(arBasketPrices.DELAY_COUNT);

        updateBottomIconsPanel(arBasketPrices);
      } else if (eventdata.action === "loadActualBasketCompare") {
        var compare_count = Object.keys(arBasketAspro.COMPARE).length;
        if (compare_count > 0) {
          $(".basket-link.compare").addClass("basket-count");
          $(".basket-link.compare .count").removeClass("empted");
          if ($("#compare_fly").length) $("#compare_fly").removeClass("empty_block");
        } else {
          $(".basket-link.compare").removeClass("basket-count");
          $(".basket-link.compare .count").addClass("empted");
          if ($("#compare_fly").length) $("#compare_fly").addClass("empty_block");
        }
        $(".basket-link.compare .count").text(compare_count);

        updateBottomIconsPanel(arBasketAspro);
      } else if (eventdata.action === "loadRSS") {
      } else if (eventdata.action === "ajaxContentLoaded") {
      } else if (eventdata.action === "jsLoadBlock") {
        initCountdown();
        setStatusButton();
        InitFlexSlider();
        setTimeout(function () {
          InitOwlSlider();
        }, 200);
        InitFancyBox();

        checkLinkedArticles();
        checkLinkedBlocks(".linked-banners-list");
        lazyLoadPagenBlock();

        if (typeof window["stickySidebar"] !== "undefined") {
          window["stickySidebar"].updateSticky();
        }
      }
    } catch (e) {
      console.error(e);
    }
  });

  /*slices*/
  if ($(".banners-small .item.normal-block").length) $(".banners-small .item.normal-block").sliceHeight();
  if ($(".teasers .item").length) $(".teasers .item").sliceHeight();
  if ($(".wrap-portfolio-front .row.items > div").length)
    $(".wrap-portfolio-front .row.items > div").sliceHeight({ row: ".row.items", item: ".item1" });

  /* bug fix in ff*/
  $("img").removeAttr("draggable");

  $(".title-tab-heading").on("click", function () {
    var container = $(this).parent(),
      slide_block = $(this).next(),
      bReviewTab = container.hasClass("media_review");

    clicked_tab = container.index() + 1;

    container.siblings().removeClass("active");

    $(".nav.nav-tabs li").removeClass("active");

    if (container.hasClass("active")) {
      if (bReviewTab) {
        $("#reviews_content").slideUp(200, function () {
          container.removeClass("active");
        });
      } else {
        slide_block.slideUp(200, function () {
          container.removeClass("active");
        });
      }
    } else {
      container.addClass("active");
      if (bReviewTab) {
        $("#reviews_content").slideDown();
      } else {
        if ($(".tabs_section + #reviews_content").length) $(".tabs_section + #reviews_content").slideUp();
        slide_block.slideDown();
        if (typeof container.attr("id") !== "undefined" && container.attr("id") == "descr") {
          var $gallery = $(".galerys-block");
          if ($gallery.length) {
            var bTypeBig = $gallery.find(".big_slider").length;
            var $slider = bTypeBig ? $gallery.find(".big_slider") : $gallery.find(".small_slider");
            InitFlexSlider();
            var interval = setInterval(function () {
              if ($slider.find(".slides .item").attr("style").indexOf("height") === -1) {
                $(window).resize();
              } else {
                clearInterval(interval);
              }
            }, 100);
          }
        }
      }
    }
  });

  InitFlexSlider();
  InitOwlSlider();
  InitStickySideBar();
  InitFancyBox();
  InitFancyBoxVideo();

  setTimeout(function () {
    InitTopestMenuGummi();
    isOnceInited = true;
  }, 50);

  InitZoomPict();

  $(document).on("click", ".captcha_reload", function (e) {
    var captcha = $(this).parents(".captcha-row");
    e.preventDefault();
    $.ajax({
      url: arAsproOptions["SITE_DIR"] + "ajax/captcha.php",
    }).done(function (text) {
      captcha.find("input[name=captcha_sid],input[name=captcha_code]").val(text);
      captcha.find("img").attr("src", "/bitrix/tools/captcha.php?captcha_sid=" + text);
      captcha.find("input[name=captcha_word]").val("").removeClass("error");
      captcha.find(".captcha_input").removeClass("error").find(".error").remove();
    });
  });

  /* show print */
  if (arAsproOptions["PAGES"]["BASKET_PAGE"]) {
    showBasketHeadingBtn();
  } else {
    if (arAsproOptions["THEME"]["PRINT_BUTTON"] === "Y") {
      setTimeout(function () {
        var topicHeading = document.querySelector(".period_wrapper.in-detail-news1 .period_wrapper_inner")
          ? document.querySelector(".detail-news1 .period_wrapper .period_wrapper_inner")
          : document.querySelector(".page-top .topic .topic__heading")
          ? document.querySelector(".page-top .topic .topic__heading")
          : null;
        if (topicHeading) {
          var btnPrint = BX.create({
            tag: "div",
            attrs: {
              class: "print-link colored_theme_hover_bg-block",
              title: arAsproOptions["THEME"]["EXPRESSION_FOR_PRINT_PAGE"],
            },
            html: '<i class="svg colored_theme_hover_bg-el-svg"><svg class="svg svg-print" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path id="Rectangle_665_copy_4" data-name="Rectangle 665 copy 4" class="cls-print" d="M1570,210h-2v3h-8v-3h-2a2,2,0,0,1-2-2v-5a2,2,0,0,1,2-2h2v-4h8v4h2a2,2,0,0,1,2,2v5A2,2,0,0,1,1570,210Zm-8,1h4v-4h-4v4Zm4-12h-4v2h4v-2Zm4,4h-12v5h2v-3h8v3h2v-5Z" transform="translate(-1556 -197)"></path></svg></i>',
          });
          BX.insertBefore(btnPrint, topicHeading);
        }
      }, 150);
    }
  }

  $(document).on("click", ".print-link", function () {
    window.print();
  });

  $(".head-block .item-link").on("click", function () {
    var _this = $(this);
    _this.siblings().removeClass("active");
    _this.addClass("active");
  });

  $("table.table").each(function () {
    var _this = $(this),
      first_td = _this.find("thead tr th");
    if (!first_td.length) first_td = _this.find("thead tr td");
    if (first_td.length) {
      var j = 0;
      _this.find("tbody tr td").each(function (i) {
        if (j > first_td.length - 1) j = 0;
        $('<div class="th-mobile">' + first_td[j].textContent + "</div>").appendTo($(this));
        j++;
      });
    }
  });

  if (window.matchMedia("(min-width: 768px)").matches) $(".wrapper_middle_menu.wrap_menu").removeClass("mobile");

  if (window.matchMedia("(max-width: 767px)").matches) $(".wrapper_middle_menu.wrap_menu").addClass("mobile");

  $(".menu_top_block .v_bottom a").on("click", function (e) {
    if ($(e.target).hasClass("toggle_block")) e.preventDefault();
  });
  $(".menu_top_block .v_bottom a .toggle_block").click(function (e) {
    var _this = $(this),
      menu = _this.closest(".has-child").find("> .dropdown"),
      bVisibleMeu = menu.is(":visible"),
      animate = bVisibleMeu ? "slideUp" : "slideDown";

    if (!_this.hasClass("clicked")) {
      _this.addClass("clicked");

      menu.velocity("stop").velocity(animate, {
        duration: 150,
        // delay: 250,
        begin: function () {
          _this.toggleClass("closed");
        },
        complete: function () {
          _this.removeClass("clicked");

          if (typeof window["stickySidebar"] !== "undefined") window["stickySidebar"].updateSticky();
        },
      });
    }

    $(this).closest(".has-child").toggleClass("opened");
  });

  $(document).on("click", ".show_props", function () {
    $(this).prev().stop().slideToggle(333);
    $(this).find(".char_title").toggleClass("opened");
  });

  $(".see_more").on("click", function (e) {
    e.preventDefault();
    var see_more = $(this).is(".see_more") ? $(this) : $(this).parents(".see_more").first();
    var see_moreText = see_more.find("> a").length ? see_more.find("> a") : see_more;
    var see_moreHiden = see_more.parent().find("> .d");
    if (see_more.hasClass("open")) {
      see_moreText.text(BX.message("CATALOG_VIEW_MORE"));
      see_more.removeClass("open");
      see_moreHiden.hide();
    } else {
      see_moreText.text(BX.message("CATALOG_VIEW_LESS"));
      see_more.addClass("open");
      see_moreHiden.show();
    }
    return false;
  });

  $(".button.faq_button").click(function (e) {
    e.preventDefault();
    $(this).toggleClass("opened");
    $(".faq_ask .form").slideToggle();
  });

  $(".staff.list .staff_section .staff_section_title a").click(function (e) {
    e.preventDefault();
    $(this).parents(".staff_section").toggleClass("opened");
    $(this).parents(".staff_section").find(".staff_section_items").stop().slideToggle(600);
    $(this).parents(".staff_section_title").find(".opener_icon").toggleClass("opened");
  });

  $(".jobs_wrapp .item .name").click(function (e) {
    $(this).closest(".item").toggleClass("opened");
    $(this).closest(".item").find(".description_wrapp").stop().slideToggle(600);
    $(this).closest(".item").find(".opener_icon").toggleClass("opened");
  });

  $(".faq.list .item .q a").on("click", function (e) {
    e.preventDefault();
    $(this).parents(".item").toggleClass("opened");
    $(this).parents(".item").find(".a").stop().slideToggle();
    $(this).parents(".item").find(".q .opener_icon").toggleClass("opened");
  });

  $(".opener_icon").click(function (e) {
    e.preventDefault();
    $(this).parent().find("a").trigger("click");
  });

  $(".dotdot").dotdotdot();

  $(".more_block span").on("click", function () {
    var content_offset = $(".catalog_detail .tabs_section").offset();
    $("html, body").animate({ scrollTop: content_offset.top - 43 }, 400);
  });

  $(document).on("click", ".counter_block:not(.basket) .plus", function () {
    if (!$(this).parents(".basket_wrapp").length || $(this).parents(".services_in_basket").length) {
      if ($(this).parent().data("offers") != "Y") {
        var isDetailSKU2 = $(this).parents(".counter_block_wr").length;
        input = $(this).parents(".counter_block").find("input[type=text]");
        (tmp_ratio = !isDetailSKU2
          ? $(this).parents(".counter_wrapp").find(".to-cart").data("ratio")
          : $(this).parents("tr").first().find("td.buy .to-cart").data("ratio")),
          (isDblQuantity = !isDetailSKU2
            ? $(this).parents(".counter_wrapp").find(".to-cart").data("float_ratio")
            : $(this).parents("tr").first().find("td.buy .to-cart").data("float_ratio")),
          (ratio = isDblQuantity ? parseFloat(tmp_ratio) : parseInt(tmp_ratio, 10)),
          (max_value = "");
        currentValue = input.val();

        if (isDblQuantity)
          ratio =
            Math.round(ratio * arAsproOptions.JS_ITEM_CLICK.precisionFactor) /
            arAsproOptions.JS_ITEM_CLICK.precisionFactor;

        curValue = isDblQuantity ? parseFloat(currentValue) : parseInt(currentValue, 10);

        curValue += ratio;
        if (isDblQuantity) {
          curValue =
            Math.round(curValue * arAsproOptions.JS_ITEM_CLICK.precisionFactor) /
            arAsproOptions.JS_ITEM_CLICK.precisionFactor;
        }

        if (parseFloat($(this).data("max")) > 0) {
          if (input.val() < $(this).data("max")) {
            if (curValue <= $(this).data("max")) input.val(curValue);

            input.change();
          }
        } else {
          input.val(curValue);
          input.change();
        }
      }
    }
  });
  $(document).on("click", ".counter_block:not(.basket) .minus", function () {
    if (!$(this).parents(".basket_wrapp").length || $(this).parents(".services_in_basket").length) {
      if ($(this).parent().data("offers") != "Y") {
        var isDetailSKU2 = $(this).parents(".counter_block_wr").length;
        input = $(this).parents(".counter_block").find("input[type=text]");
        (tmp_ratio = !isDetailSKU2
          ? $(this).parents(".counter_wrapp").find(".to-cart").data("ratio")
          : $(this).parents("tr").first().find("td.buy .to-cart").data("ratio")),
          (isDblQuantity = !isDetailSKU2
            ? $(this).parents(".counter_wrapp").find(".to-cart").data("float_ratio")
            : $(this).parents("tr").first().find("td.buy .to-cart").data("float_ratio")),
          (ratio = isDblQuantity ? parseFloat(tmp_ratio) : parseInt(tmp_ratio, 10)),
          (max_value = "");
        currentValue = input.val();

        if (isDblQuantity)
          ratio =
            Math.round(ratio * arAsproOptions.JS_ITEM_CLICK.precisionFactor) /
            arAsproOptions.JS_ITEM_CLICK.precisionFactor;

        curValue = isDblQuantity ? parseFloat(currentValue) : parseInt(currentValue, 10);

        curValue -= ratio;
        if (isDblQuantity) {
          curValue =
            Math.round(curValue * arAsproOptions.JS_ITEM_CLICK.precisionFactor) /
            arAsproOptions.JS_ITEM_CLICK.precisionFactor;
        }
        const minValue = parseFloat($(this).parents(".counter_block").find(".minus").data("min"));
        if (minValue) {
          ratio = minValue;
        }

        if (parseFloat($(this).parents(".counter_block").find(".plus").data("max")) > 0) {
          if (currentValue > ratio) {
            if (curValue < ratio) {
              input.val(ratio);
            } else {
              input.val(curValue);
            }
            input.change();
          }
        } else {
          if (curValue > ratio) {
            input.val(curValue);
          } else {
            if (ratio) {
              input.val(ratio);
            } else if (currentValue > 1) {
              input.val(curValue);
            }
          }
          input.change();
        }
      }
    }
  });

  $(".counter_block input[type=text]").numeric({ allow: "." });

  $(document).on("focus", ".counter_block input[type=text]", function (e) {
    $(this).addClass("focus");
  });

  $(document).on("blur", ".counter_block input[type=text]", function (e) {
    $(this).removeClass("focus");
  });

  var timerInitCalculateDelivery = false;
  $(document).on("change", ".counter_block input[type=text]", function (e) {
    if (!$(this).parents(".basket_wrapp").length) {
      var val = $(this).val(),
        tmp_ratio = $(this).parents(".counter_wrapp").find(".to-cart").data("ratio")
          ? $(this).parents(".counter_wrapp").find(".to-cart").data("ratio")
          : $(this).parents("tr").first().find("td.buy .to-cart").data("ratio"),
        isDblQuantity = $(this).parents(".counter_wrapp").find(".to-cart").data("float_ratio")
          ? $(this).parents(".counter_wrapp").find(".to-cart").data("float_ratio")
          : $(this).parents("tr").first().find("td.buy .to-cart").data("float_ratio"),
        ratio = isDblQuantity ? parseFloat(tmp_ratio) : parseInt(tmp_ratio, 10),
        diff = val % ratio;

      if (isDblQuantity) {
        ratio =
          Math.round(ratio * arAsproOptions.JS_ITEM_CLICK.precisionFactor) /
          arAsproOptions.JS_ITEM_CLICK.precisionFactor;
        if (
          Math.round(diff * arAsproOptions.JS_ITEM_CLICK.precisionFactor) /
            arAsproOptions.JS_ITEM_CLICK.precisionFactor ==
          ratio
        )
          diff = 0;
      }

      if ($(this).hasClass("focus")) {
        intCount =
          Math.round(
            Math.round((val * arAsproOptions.JS_ITEM_CLICK.precisionFactor) / ratio) /
              arAsproOptions.JS_ITEM_CLICK.precisionFactor
          ) || 1;
        val = intCount <= 1 ? ratio : intCount * ratio;
        // val -= diff;
        val =
          Math.round(val * arAsproOptions.JS_ITEM_CLICK.precisionFactor) / arAsproOptions.JS_ITEM_CLICK.precisionFactor;
      }

      if (parseFloat($(this).parents(".counter_block").find(".plus").data("max")) > 0) {
        if (val > parseFloat($(this).parents(".counter_block").find(".plus").data("max"))) {
          val = parseFloat($(this).parents(".counter_block").find(".plus").data("max"));
          // val -= (val % ratio);
        }
      }
      if (parseFloat($(this).parents(".counter_block").find(".minus").data("min")) > 0) {
        if (val < parseFloat($(this).parents(".counter_block").find(".minus").data("min"))) {
          val = parseFloat($(this).parents(".counter_block").find(".minus").data("min"));
          // val -= (val % ratio);
        }
      }
      if (val < ratio) {
        val = ratio;
      } else if (!parseFloat(val)) {
        val = 1;
      }

      $(this).parents(".counter_block").parent().parent().find(".to-cart").attr("data-quantity", val);
      $(this).parents(".counter_block").parent().parent().parent().find(".one_click").attr("data-quantity", val);
      $(this)
        .parents(".counter_block")
        .parent()
        .parent()
        .parent()
        .parent()
        .find(".one_click")
        .attr("data-quantity", val); //for offers in list
      $(this).parents(".catalog_item_wrapp").find(".inner_wrap.TYPE_1 .one_click").attr("data-quantity", val); //for first type buttons in catalog_block

      $(this).val(val);

      var $calculate = $(this).closest(".item").length
        ? $(this).closest(".item").find(".calculate-delivery")
        : $(this).closest(".catalog_detail").find(".calculate-delivery");
      if ($calculate.length && !$(this).closest(".buy_services_wrap").length) {
        $calculate.each(function () {
          var $calculateSpan = $(this).find("span[data-event=jqm]").first();

          if ($calculateSpan.length) {
            var $clone = $calculateSpan.clone();
            $clone.attr("data-param-quantity", val).removeClass("clicked");
            $clone.insertAfter($calculateSpan).on("click", function () {
              if (!jQuery.browser.mobile) {
                $(this).parent().addClass("loadings");
              }
            });
            $calculateSpan.remove();
          }

          if ($(this).hasClass("with_preview")) {
            $(this).removeClass("inited");

            if (timerInitCalculateDelivery) {
              clearTimeout(timerInitCalculateDelivery);
            }

            timerInitCalculateDelivery = setTimeout(function () {
              initCalculatePreview();
              timerInitCalculateDelivery = false;
            }, 1000);
          }
        });
      }

      var eventdata = { type: "change", params: { id: $(this), value: val } };
      BX.onCustomEvent("onCounterProductAction", [eventdata]);

      if ($(this).closest(".complect-block").length && typeof setNewPriceComplect === "function") {
        setNewPriceComplect();
      }
    }
  });

  BX.addCustomEvent("onCounterProductAction", function (eventdata) {
    if (typeof eventdata != "object") {
      eventdata = { type: "undefined" };
    }
    try {
      if (typeof eventdata.type != "undefined") {
        if (!eventdata.params.id.closest(".gifts").length) {
          // no gift
          var obProduct = eventdata.params.id.data("product");
          if (eventdata.params.id.closest(".has_offer_prop").length) {
            // type1 for offers in element list
            if (typeof window["obSkuQuantys"] === "undefined") window["obSkuQuantys"] = {};

            // if(typeof window['obOffers'] === 'undefined')
            window["obSkuQuantys"][eventdata.params.id.closest(".offer_buy_block").find(".to-cart").data("item")] =
              eventdata.params.value;
          }
          if (typeof window[obProduct] == "object") {
            if (obProduct == "obOffers") setPriceAction("", "", "Y");
            else window[obProduct].setPriceAction("Y");
            if ($(".detail_page").length) {
              setNewHeader();
            }
          } else if (eventdata.params.id.length) {
            if (
              eventdata.params.id.closest(".main_item_wrapper").length &&
              !eventdata.params.id.closest(".services-item__buy").length &&
              arAsproOptions["THEME"]["SHOW_TOTAL_SUMM"] == "Y"
            ) {
              setPriceItem(eventdata.params.id.closest(".main_item_wrapper"), eventdata.params.value);
            }
          }
          BX.onCustomEvent("onCounterProductActionResize");
        }
      }
    } catch (e) {
      console.error(e);
    }
  });

  /*show basket on hover */
  $(document).on("mouseenter", ".wrap_icon.top_basket, #headerfixed .basket-link.basket", function () {
    var _this = $(this);
    var parent = _this.closest("header, #headerfixed");
    var hover_block = parent.find(".basket_hover_block");

    if (!hover_block.hasClass("loaded")) {
      basketTop("", hover_block);
    }
  });

  /*show basket on click mobile */
  $(document).on("click", ".wrap_icon.wrap_basket.top_basket, #headerfixed .basket-link.basket", function (e) {
    var _this = $(this);
    if (isMobile) {
      if (!_this.hasClass("clicked")) {
        e.preventDefault();
        _this.addClass("clicked");
        setTimeout(function () {
          _this.removeClass("clicked");
        }, 3000);
      }
    }
  });

  /*slide cart*/
  $(document).on("mouseenter", "#basket_line .basket_normal:not(.empty_cart):not(.bcart) .basket_block ", function () {
    $(this).closest(".basket_normal").find(".popup").addClass("block");
    $(this).closest(".basket_normal").find(".basket_popup_wrapp").stop(true, true).slideDown(150);
  });
  $(document).on("mouseleave", "#basket_line .basket_normal .basket_block ", function () {
    var th = $(this);
    $(this)
      .closest(".basket_normal")
      .find(".basket_popup_wrapp")
      .stop(true, true)
      .slideUp(150, function () {
        th.closest(".basket_normal").find(".popup").removeClass("block");
      });
  });

  $(document).on("click", ".popup_button_basket", function () {
    var th = $(".to-cart[data-item=" + $(this).data("item") + "]");

    var val = th.attr("data-quantity");

    if (!val) $val = 1;

    var tmp_props = th.data("props"),
      props = "",
      part_props = "",
      add_props = "N",
      fill_prop = {},
      iblockid = th.data("iblockid"),
      offer = th.data("offers"),
      rid = "",
      basket_props = "",
      item = th.attr("data-item");

    if (offer != "Y") {
      offer = "N";
    } else {
      basket_props = th.closest(".prices_tab").find(".bx_sku_props input").val();
    }
    if (tmp_props) {
      props = tmp_props.split(";");
    }
    if (th.data("part_props")) {
      part_props = th.data("part_props");
    }
    if (th.data("add_props")) {
      add_props = th.data("add_props");
    }
    if ($(".rid_item").length) {
      rid = $(".rid_item").data("rid");
    } else if (th.data("rid")) {
      rid = th.data("rid");
    }

    fill_prop = fillBasketPropsExt(th, "prop", "bx_ajax_text");

    fill_prop.quantity = val;
    fill_prop.add_item = "Y";
    fill_prop.rid = rid;
    fill_prop.offers = offer;
    fill_prop.iblockID = iblockid;
    fill_prop.part_props = part_props;
    fill_prop.add_props = add_props;
    fill_prop.props = JSON.stringify(props);
    fill_prop.item = item;
    fill_prop.basket_props = basket_props;

    var isDetail = th.closest(".product-action").length || th.closest("#headerfixed.with-product");
    var isSku2 = th.closest(".list-offers").length;
    var bIsService = th.closest(".buy_services_wrap").length;

    if (!bIsService && (isDetail || isSku2)) {
      //var allServicesOn = th.closest('.product-container').find('.buy_services_wrap .services-item [name="buy_switch_services"]:checked');
      var allServicesOn = [];
      if (isSku2) {
        allServicesOn = th
          .closest(".product-container")
          .find('.buy_services_wrap .services-item [name="buy_switch_services"]:checked');
      } else {
        allServicesOn = $(".product-container").find(
          '.buy_services_wrap[data-parent_product="' + item + '"] .services-item [name="buy_switch_services"]:checked'
        );
      }
      var servicesNeedAdd = [];
      allServicesOn.each(function () {
        var _this = $(this);
        var buttonCart = _this.closest(".services-item").find(".to-cart");

        servicesNeedAdd[buttonCart.data("item")] = {};
        servicesNeedAdd[buttonCart.data("item")]["id"] = buttonCart.data("item");
        servicesNeedAdd[buttonCart.data("item")]["quantity"] = buttonCart.attr("data-quantity");
        servicesNeedAdd[buttonCart.data("item")]["iblock_id"] = buttonCart.data("iblockid");
      });
      servicesNeedAdd = array_values_js(servicesNeedAdd);
      fill_prop.services = servicesNeedAdd;
    }

    $.ajax({
      type: "POST",
      url: arAsproOptions["SITE_DIR"] + "ajax/item.php",
      data: fill_prop,
      dataType: "json",
      success: function (data) {
        $(".basket_error_frame").jqmHide();
        if ("STATUS" in data) {
          getActualBasket(fill_prop.iblockID);
          if (data.STATUS === "OK") {
            th.hide();
            th.closest(".counter_wrapp").find(".counter_block_inner").hide();
            th.closest(".counter_wrapp").find(".counter_block").hide();
            th.parents("tr").find(".counter_block_wr .counter_block").hide();
            th.closest(".button_block").addClass("wide");
            th.parent().find(".in-cart").show();

            addBasketCounter(item);
            //$('.wish_item[data-item='+item+']').removeClass("added");
            $(".wish_item[data-item=" + item + "]")
              .find(".value")
              .show();
            $(".wish_item[data-item=" + item + "]")
              .find(".value.added")
              .hide();

            if ($("#ajax_basket").length) reloadTopBasket("add", $("#ajax_basket"), 200, 5000, "Y");

            if ($("#basket_line .basket_fly").length) {
              if (th.closest(".fast_view_frame").length || window.matchMedia("(max-width: 767px)").matches)
                basketFly("open", "N");
              else basketFly("open");
            }
          } else {
            showBasketError(BX.message(data.MESSAGE));
          }
        } else {
          showBasketError(BX.message("CATALOG_PARTIAL_BASKET_PROPERTIES_ERROR"));
        }
      },
    });
  });

  $(document).on("click", ".to-cart:not(.read_more), .basket_item_add", function (e) {
    e.preventDefault();
    var th = $(this);
    if (!th.hasClass("clicked")) {
      th.addClass("clicked");
      var val = $(this).attr("data-quantity");
      var tmp_props = $(this).data("props"),
        props = "",
        part_props = "",
        add_props = "N",
        fill_prop = {},
        iblockid = $(this).data("iblockid"),
        offer = $(this).data("offers"),
        rid = "",
        bBannerAction = false,
        basket_props = "",
        item = $(this).attr("data-item");
      if (th.closest(".but-cell").length) {
        if ($('.counter_block[data-item="' + item + '"]').length)
          val = $('.counter_block[data-item="' + item + '"] input').val();
      }

      if (th.closest(".banner_buttons.with_actions").length) {
        //banner action
        var wrapper_btns = th.closest(".wraps_buttons");
        item = wrapper_btns.data("id");
        bBannerAction = true;
      }

      if (!val) val = 1;
      if (offer != "Y") {
        offer = "N";
      } else {
        basket_props = $(this).closest(".prices_tab").find(".bx_sku_props input").val();
      }
      if (tmp_props) {
        props = tmp_props.split(";");
      }
      if ($(this).data("part_props")) {
        part_props = $(this).data("part_props");
      }
      if ($(this).data("add_props")) {
        add_props = $(this).data("add_props");
      }
      if ($(".rid_item").length) {
        rid = $(".rid_item").data("rid");
      } else if ($(this).data("rid")) {
        rid = $(this).data("rid");
      }

      fill_prop = fillBasketPropsExt(th, "prop", th.data("bakset_div"));

      fill_prop.quantity = val;
      fill_prop.add_item = "Y";
      fill_prop.rid = rid;
      fill_prop.offers = offer;
      fill_prop.iblockID = iblockid;
      fill_prop.part_props = part_props;
      fill_prop.add_props = add_props;
      fill_prop.props = JSON.stringify(props);
      fill_prop.item = item;
      fill_prop.basket_props = basket_props;

      var isDetail = th.closest(".product-action").length || th.closest("#headerfixed.with-product");
      var isSku2 = th.closest(".list-offers").length;
      var bIsService = th.closest(".buy_services_wrap").length;

      if (!bIsService && (isDetail || isSku2)) {
        var allServicesOn = [];
        if (isSku2) {
          allServicesOn = th
            .closest(".product-container")
            .find('.buy_services_wrap .services-item [name="buy_switch_services"]:checked');
        } else {
          allServicesOn = $(".product-container").find(
            '.buy_services_wrap[data-parent_product="' + item + '"] .services-item [name="buy_switch_services"]:checked'
          );
        }
        var servicesNeedAdd = [];
        allServicesOn.each(function () {
          var _this = $(this);
          var buttonCart = _this.closest(".services-item").find(".to-cart");
          servicesNeedAdd[buttonCart.data("item")] = {};
          servicesNeedAdd[buttonCart.data("item")]["id"] = buttonCart.data("item");
          servicesNeedAdd[buttonCart.data("item")]["quantity"] = buttonCart.attr("data-quantity");
          servicesNeedAdd[buttonCart.data("item")]["iblock_id"] = buttonCart.data("iblockid");
        });
        servicesNeedAdd = array_values_js(servicesNeedAdd);
        fill_prop.services = servicesNeedAdd;
      }

      var servicesWrap = $(this).closest(".buy_services_wrap");
      if (bIsService) {
        var parent_product = servicesWrap.attr("data-parent_product");
        fill_prop["prop[BUY_PRODUCT_PROP]"] = parent_product;
        fill_prop["prop[ASPRO_BUY_PRODUCT_ID]"] = parent_product;
      }

      if (bBannerAction) {
        if (th.hasClass("added")) {
          location.href = th.data("href");
          return false;
        } else {
          th.attr("title", th.data("title2"));
          th.toggleClass("added");
        }

        if (wrapper_btns.find(".wish_item_add").length) wrapper_btns.find(".wish_item_add").removeClass("added");
      }

      if (th.data("empty_props") == "N" /* && fill_prop.part_props != 'Y'*/) {
        var basket_popup_html = $("#" + th.data("bakset_div")).html();
        if (!basket_popup_html && th.closest(".item").find(".basket_props_block").length)
          basket_popup_html = th.closest(".item").find(".basket_props_block").html();

        showBasketError(basket_popup_html, BX.message("ERROR_BASKET_PROP_TITLE"), "Y", th);
        BX.addCustomEvent("onCompleteAction", function (e) {
          if (!e.ignoreSelf) {
            var eventdata = { action: "loadForm", ignoreSelf: true };
            BX.onCustomEvent("onCompleteAction", [eventdata, th[0]]);
          }
        });
      } else {
        $.ajax({
          type: "POST",
          url: arAsproOptions["SITE_DIR"] + "ajax/item.php",
          data: fill_prop,
          dataType: "json",
          success: function (data) {
            getActualBasket(fill_prop.iblockID);

            var eventdata = { action: "loadForm" };
            BX.onCustomEvent("onCompleteAction", [eventdata, th[0]]);
            arStatusBasketAspro = {};

            if (data !== null) {
              if ("STATUS" in data) {
                if (data.MESSAGE_EXT === null) data.MESSAGE_EXT = "";
                if (data.STATUS === "OK") {
                  // th.hide();
                  $(".to-cart[data-item=" + item + "]").hide();
				  $(".products-product__incart[data-item=" + item + "]").removeClass('.to-cart').text('✓ В корзине').attr('onclick','window.location.href="/basket/"');
				  $(".products-product[data-product=" + item + "]").find('.wish_item').removeClass('active');
                  $(".to-cart[data-item=" + item + "]")
                    .closest(".counter_wrapp")
                    .find(".counter_block_inner")
                    .hide();
                  $(".to-cart[data-item=" + item + "]")
                    .closest(".counter_wrapp")
                    .find(".counter_block")
                    .hide();
                  $(".to-cart[data-item=" + item + "]")
                    .parents("tr")
                    .find(".counter_block_wr .counter_block")
                    .hide();
                  $(".to-cart[data-item=" + item + "]")
                    .closest(".button_block")
                    .addClass("wide");
                  // th.parent().find('.in-cart').show();
                  $(".in-cart[data-item=" + item + "]").show();

                  addBasketCounter(item);
                  //$('.wish_item[data-item='+item+']').removeClass("added");
                  $(".wish_item[data-item=" + item + "]")
                    .find(".value")
                    .show();
                  $(".wish_item[data-item=" + item + "]")
                    .find(".value.added")
                    .hide();
                  $(".wish_item.to[data-item=" + item + "]").show();
                  $(".wish_item.in[data-item=" + item + "]").hide();

                  //js notice
                  $bDontOpenBasket = false;
                  if (typeof JNoticeSurface !== "undefined") {
                    JNoticeSurface.get().onAdd2cart([th[0]]);
                    $bDontOpenBasket = true;
                  }
                  //

                  if ($("#ajax_basket").length) reloadTopBasket("add", $("#ajax_basket"), 200, 5000, "Y");

                  if ($("#basket_line .basket_fly").length) {
                    if (th.closest(".services_in_basket").length && !window.matchMedia("(max-width: 767px)").matches) {
                      basketFly("open", "SHOW"); //need for buy services in basket_fly
                    } else if (
                      th.closest(".fast_view_frame").length ||
                      window.matchMedia("(max-width: 767px)").matches ||
                      $("#basket_line .basket_fly.loaded").length
                    )
                      basketFly("open", "N");
                    else {
                      if ($bDontOpenBasket) basketFly("open", "N");
                      else basketFly("open");
                    }
                  }

                  //need for services on basket_page
                  if (th.closest(".services_in_basket_page").length) {
                    BX.Sale.BasketComponent.sendRequest("refreshAjax", {
                      fullRecalculation: "Y",
                      otherParams: {
                        param: "N",
                      },
                    });
                  }

                  if ($(".top_basket").length) {
                    basketTop($bDontOpenBasket ? "" : "open", $(".top_basket").find(".basket_hover_block"));
                  }

                  if (
                    $("#headerfixed .wproducts .ajax_load .btn").length &&
                    th.closest(".product-action").length &&
                    th.next(".in-cart").length
                  ) {
                    var buttonHtml =
                      //'<span class="buy_block"><span class="btn btn-default btn-sm slide_offer more type_block">' +
                      '<span class="buy_block"><span class="btn btn-default btn-sm more type_block">' +
                      th.next(".in-cart").html() +
                      "</span></span>";
                    $("#headerfixed .wproducts .ajax_load .item-buttons .but-cell").html(buttonHtml);
                  }
                } else {
                  showBasketError(BX.message(data.MESSAGE) + " <br/>" + data.MESSAGE_EXT);
                }
              } else {
                showBasketError(BX.message("CATALOG_PARTIAL_BASKET_PROPERTIES_ERROR"));
              }
            } else {
              // th.hide();
              $(".to-cart[data-item=" + item + "]").hide();
              $(".to-cart[data-item=" + item + "]")
                .closest(".counter_wrapp")
                .find(".counter_block_inner")
                .hide();
              $(".to-cart[data-item=" + item + "]")
                .closest(".counter_wrapp")
                .find(".counter_block")
                .hide();
              $(".to-cart[data-item=" + item + "]")
                .parents("tr")
                .find(".counter_block_wr .counter_block")
                .hide();
              $(".to-cart[data-item=" + item + "]")
                .closest(".button_block")
                .addClass("wide");
              // th.parent().find('.in-cart').show();
              $(".in-cart[data-item=" + item + "]").show();

              addBasketCounter(item);
              //$('.wish_item[data-item='+item+']').removeClass("added");
              $(".wish_item[data-item=" + item + "]")
                .find(".value")
                .show();
              $(".wish_item[data-item=" + item + "]")
                .find(".value.added")
                .hide();

              if ($("#ajax_basket").length) reloadTopBasket("add", $("#ajax_basket"), 200, 5000, "Y");

              if ($("#basket_line .basket_fly").length) {
                if (
                  th.closest(".fast_view_frame").length ||
                  window.matchMedia("(max-width: 767px)").matches ||
                  $("#basket_line .basket_fly.loaded").length
                )
                  basketFly("open", "N");
                else basketFly("open");
              }
            }
          },
        });
      }
    }
  });

  $(document).on("click", ".to-subscribe", function (e) {
    e.preventDefault();
    var th = $(this);
    if (!th.hasClass("clicked")) {
      th.addClass("clicked");
      if ($(this).is(".auth")) {
        if ($(this).hasClass("nsubsc")) {
          $(this).jqmEx();
          $(this).trigger("click");
        } else {
          location.href = arAsproOptions["SITE_DIR"] + "auth/?backurl=" + location.pathname;
        }
      } else {
        var item = $(this).attr("data-item"),
          iblockid = $(this).attr("data-iblockid");
        // $(this).hide();
        $(".to-subscribe[data-item=" + item + "]").hide();
        $(".to-subscribe[data-item=" + item + "]")
          .parent()
          .find(".in-subscribe")
          .show();
        $.get(
          arAsproOptions["SITE_DIR"] + "ajax/item.php?item=" + item + "&subscribe_item=Y",
          $.proxy(function (data) {
            arStatusBasketAspro = {};
            getActualBasket(iblockid);
          })
        );
        th.removeClass("clicked");
      }
    }
  });

  $(document).on("click", ".in-subscribe", function (e) {
    e.preventDefault();
    var item = $(this).attr("data-item"),
      iblockid = $(this).attr("data-iblockid");
    // $(this).hide();
    $(".in-subscribe[data-item=" + item + "]").hide();
    $(".in-subscribe[data-item=" + item + "]")
      .parent()
      .find(".to-subscribe")
      .removeClass("clicked");
    $(".in-subscribe[data-item=" + item + "]")
      .parent()
      .find(".to-subscribe")
      .show();
    $.get(
      arAsproOptions["SITE_DIR"] + "ajax/item.php?item=" + item + "&subscribe_item=Y",
      $.proxy(function (data) {
        getActualBasket(iblockid);
        arStatusBasketAspro = {};
      })
    );
  });

  $(document).on("click", ".wish_item, .wish_item_add", function (e) {
    e.preventDefault();
    var val = $(this).attr("data-quantity"),
      _this = $(this),
      offer = $(this).data("offers"),
      iblockid = $(this).data("iblock"),
      tmp_props = $(this).data("props"),
      props = "",
      bBannerAction = false,
      item = $(this).attr("data-item"),
      item2 = $(this).attr("data-item");

    if (_this.closest(".banner_buttons.with_actions").length) {
      //banner action
      var wrapper_btns = _this.closest(".wraps_buttons");
      item = item2 = wrapper_btns.data("id");
      bBannerAction = true;
    }

    if (!_this.hasClass("clicked")) {
      _this.addClass("clicked");
      if (!val) val = 1;
      if (offer != "Y") offer = "N";
      if (tmp_props) {
        props = tmp_props.split(";");
      }
      if (!$(this).hasClass("text")) {
        if ($(this).hasClass("added")) {
          if (!bBannerAction) {
            $(this).hide();
            $(this).closest(".wish_item_button").find(".to").show();
          }
          $(".like_icons").each(function () {
            if ($(this).find('.wish_item.text[data-item="' + item + '"]').length) {
              $(this)
                .find('.wish_item.text[data-item="' + item + '"]')
                .removeClass("added");
              $(this)
                .find('.wish_item.text[data-item="' + item + '"]')
                .find(".value")
                .show();
              $(this)
                .find('.wish_item.text[data-item="' + item + '"]')
                .find(".value.added")
                .hide();
            }
            if ($(this).find(".wish_item_button").length) {
              /*$(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').removeClass('added');
              $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').find('.value').show();
              $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').find('.value.added').hide();*/
              $(this)
                .find(".wish_item_button")
                .find('.wish_item[data-item="' + item + '"].to')
                .show();
              $(this)
                .find(".wish_item_button")
                .find('.wish_item[data-item="' + item + '"].in')
                .hide();
            }
          });
        } else {
          if (!bBannerAction) {
            $(this).hide();
            $(this).closest(".wish_item_button").find(".in").addClass("added").show();
          }

          $(".like_icons").each(function () {
            if ($(this).find('.wish_item.text[data-item="' + item + '"]').length) {
              $(this)
                .find('.wish_item.text[data-item="' + item + '"]')
                .addClass("added");
              $(this)
                .find('.wish_item.text[data-item="' + item + '"]')
                .find(".value")
                .hide();
              $(this)
                .find('.wish_item.text[data-item="' + item + '"]')
                .find(".value.added")
                .css({ display: "block" });
            }
            if ($(this).find(".wish_item_button").length) {
              /*$(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').addClass('added');
              $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').find('.value').hide();
              $(this).find('.wish_item_button').find('.wish_item[data-item="'+item+'"]').find('.value.added').show();*/
              $(this)
                .find(".wish_item_button")
                .find('.wish_item[data-item="' + item + '"].to')
                .hide();
              $(this)
                .find(".wish_item_button")
                .find('.wish_item[data-item="' + item + '"].in')
                .show();
            }
          });
        }
      } else {
        if (!$(this).hasClass("added")) {
          $(".wish_item[data-item=" + item + "]").addClass("added");
          $(".wish_item[data-item=" + item + "]")
            .find(".value")
            .hide();
          $(".wish_item[data-item=" + item + "]")
            .find(".value.added")
            .css("display", "block");

          $(".wish_item.to[data-item=" + item + "]").hide();
          $(".wish_item.in[data-item=" + item + "]").show();

          $(".like_icons").each(function () {
            if ($(this).find(".wish_item_button").length) {
              $(this)
                .find(".wish_item_button")
                .find('.wish_item[data-item="' + item + '"]')
                .addClass("added");
              $(this)
                .find(".wish_item_button")
                .find('.wish_item[data-item="' + item + '"]')
                .find(".value")
                .hide();
              $(this)
                .find(".wish_item_button")
                .find('.wish_item[data-item="' + item + '"]')
                .find(".value.added")
                .show();
            }
          });
        } else {
          $(".wish_item[data-item=" + item + "]").removeClass("added");
          $(".wish_item[data-item=" + item + "]")
            .find(".value")
            .show();
          $(".wish_item[data-item=" + item + "]")
            .find(".value.added")
            .hide();

          $(".wish_item.to[data-item=" + item + "]").show();
          $(".wish_item.in[data-item=" + item + "]").hide();

          $(".like_icons").each(function () {
            if ($(this).find(".wish_item_button").length) {
              $(this)
                .find(".wish_item_button")
                .find('.wish_item[data-item="' + item + '"]')
                .removeClass("added");
              $(this)
                .find(".wish_item_button")
                .find('.wish_item[data-item="' + item + '"]')
                .find(".value")
                .show();
              $(this)
                .find(".wish_item_button")
                .find('.wish_item[data-item="' + item + '"]')
                .find(".value.added")
                .hide();
            }
          });
        }
      }

      $(".in-cart[data-item=" + item + "]").hide();
      $(".to-cart[data-item=" + item + "]").removeClass("clicked");
      if (
        $(".to-cart[data-item=" + item + "]")
          .closest(".counter_wrapp")
          .find(".counter_block").length
      ) {
        $(".to-cart[data-item=" + item + "]")
          .parent()
          .removeClass("wide");
      }
      if (
        !$(".counter_block[data-item=" + item + "]")
          .closest(".counter_wrapp")
          .find(".to-order").length
      ) {
        $(".to-cart[data-item=" + item + "]").show();
        $(".counter_block[data-item=" + item + "]")
          .closest(".counter_block_inner")
          .show();
        $(".counter_block[data-item=" + item + "]").show();
      }

      if (bBannerAction) {
        _this.toggleClass("added");

        if (_this.hasClass("added")) _this.attr("title", _this.data("title2"));
        else _this.attr("title", _this.data("title"));

        if (wrapper_btns.find(".basket_item_add").length) wrapper_btns.find(".basket_item_add").removeClass("added");
      }

      if (!$(this).closest(".module-cart").size()) {
        $.ajax({
          type: "GET",
          url: arAsproOptions["SITE_DIR"] + "ajax/item.php",
          data:
            "item=" +
            item2 +
            "&quantity=" +
            val +
            "&wish_item=Y" +
            "&offers=" +
            offer +
            "&iblockID=" +
            iblockid +
            "&props=" +
            JSON.stringify(props),
          dataType: "json",
          success: function (data) {
            getActualBasket(iblockid);
            arStatusBasketAspro = {};
            if (data !== null) {
              if (data.MESSAGE_EXT === null) data.MESSAGE_EXT = "";
              if ("STATUS" in data) {
                if (data.STATUS === "OK") {
                  // js notice
                  $bDontOpenBasket = false;
                  if (
                    typeof JNoticeSurface !== "undefined" &&
                    ((!bBannerAction && !_this.hasClass("added")) || (bBannerAction && _this.hasClass("added")))
                  ) {
                    JNoticeSurface.get().onAdd2Delay([_this[0]]);
                    $bDontOpenBasket = true;
                  }
                  //

                  if (arAsproOptions["COUNTERS"]["USE_BASKET_GOALS"] !== "N") {
                    var eventdata = { goal: "goal_wish_add", params: { id: item2 } };
                    BX.onCustomEvent("onCounterGoals", [eventdata]);
                  }
                  if ($("#ajax_basket").length) reloadTopBasket("wish", $("#ajax_basket"), 200, 5000, "N");

                  if ($("#basket_line .basket_fly").length) {
                    if (
                      _this.closest(".fast_view_frame").length ||
                      window.matchMedia("(max-width: 767px)").matches ||
                      $("#basket_line .basket_fly.loaded").length
                    )
                      basketFly("wish", "N");
                    else {
                      if ($bDontOpenBasket) basketFly("wish", "N");
                      else basketFly("wish");
                    }
                  }
				  $('.products-product[data-product="'+item2+'"]').find('.wish_item').addClass('active');
				  $('.products-product[data-product="'+item2+'"]').find('.products-product__incart').addClass('to-cart').removeAttr('onclick').text('В корзину');
				  
                } else {
                  showBasketError(
                    BX.message(data.MESSAGE) + " <br/>" + data.MESSAGE_EXT,
                    BX.message("ERROR_ADD_DELAY_ITEM")
                  );
                }
              } else {
                showBasketError(
                  BX.message(data.MESSAGE) + " <br/>" + data.MESSAGE_EXT,
                  BX.message("ERROR_ADD_DELAY_ITEM")
                );
              }
            } else {
              if ($("#ajax_basket").length) reloadTopBasket("wish", $("#ajax_basket"), 200, 5000, "N");

              if ($("#basket_line .basket_fly").length) {
                if (
                  _this.closest(".fast_view_frame").length ||
                  window.matchMedia("(max-width: 767px)").matches ||
                  $("#basket_line .basket_fly.loaded").length
                )
                  basketFly("wish", "N");
                else basketFly("wish");
              }
            }
            _this.removeClass("clicked");
          },
        });
      }
    }
  });

  $(document).on("click", ".item_main_info .item_slider .flex-direction-nav li span", function (e) {
    if ($(".inner_slider .slides_block").length) {
      if ($(this).parent().hasClass("flex-nav-next")) $(".inner_slider .slides_block li.current").next().click();
      else $(".inner_slider .slides_block li.current").prev().click();
    }
  });

  $(document).on("click", ".compare_item, .compare_item_add", function (e) {
    e.preventDefault();
    var item = $(this).attr("data-item");
    var iblockID = $(this).attr("data-iblock"),
      bBannerAction = false,
      th = $(this);

    if (th.closest(".banner_buttons.with_actions").length) {
      //banner action
      var wrapper_btns = th.closest(".wraps_buttons");
      item = wrapper_btns.data("id");
      iblockID = wrapper_btns.data("iblockid");
      bBannerAction = true;

      th.toggleClass("added");

      if (th.hasClass("added")) th.attr("title", th.data("title2"));
      else th.attr("title", th.data("title"));
    }

    if (!th.hasClass("clicked")) {
      th.addClass("clicked");
      if (!$(this).hasClass("text")) {
        if ($(this).hasClass("added")) {
          if (!bBannerAction) {
            $(this).hide();
            $(this).closest(".compare_item_button").find(".to").show();
          }

          /*sync other button*/
          $(".like_icons").each(function () {
            if ($(this).find('.compare_item.text[data-item="' + item + '"]').length) {
              $(this)
                .find('.compare_item.text[data-item="' + item + '"]')
                .removeClass("added");
              $(this)
                .find('.compare_item.text[data-item="' + item + '"]')
                .find(".value")
                .show();
              $(this)
                .find('.compare_item.text[data-item="' + item + '"]')
                .find(".value.added")
                .hide();
            }
            if ($(this).find(".compare_item_button").length) {
              /*$(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').removeClass('added');
              $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').find('.value').show();
              $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').find('.value.added').hide();*/
              $(this)
                .find(".compare_item_button")
                .find('.compare_item[data-item="' + item + '"].in')
                .hide();
              $(this)
                .find(".compare_item_button")
                .find('.compare_item[data-item="' + item + '"].to')
                .show();
            }
          });
        } else {
          if (!bBannerAction) {
            $(this).hide();
            $(this).closest(".compare_item_button").find(".in").show();
          }

          /*sync other button*/
          $(".like_icons").each(function () {
            if ($(this).find('.compare_item.text[data-item="' + item + '"]').length) {
              $(this)
                .find('.compare_item.text[data-item="' + item + '"]')
                .addClass("added");
              $(this)
                .find('.compare_item.text[data-item="' + item + '"]')
                .find(".value")
                .hide();
              $(this)
                .find('.compare_item.text[data-item="' + item + '"]')
                .find(".value.added")
                .css({ display: "block" });
            }
            if ($(this).find(".compare_item_button").length) {
              /*$(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').addClass('added');
              $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').find('.value.added').show();
              $(this).find('.compare_item_button').find('.compare_item[data-item="'+item+'"]').find('.value').hide();*/
              $(this)
                .find(".compare_item_button")
                .find('.compare_item[data-item="' + item + '"].to')
                .hide();
              $(this)
                .find(".compare_item_button")
                .find('.compare_item[data-item="' + item + '"].in')
                .show();
            }
          });
        }
      } else {
        if (!$(this).hasClass("added")) {
          $(".compare_item[data-item=" + item + "]").addClass("added");
          $(".compare_item[data-item=" + item + "]")
            .find(".value")
            .hide();
          $(".compare_item[data-item=" + item + "]")
            .find(".value.added")
            .css("display", "block");

          /*sync other button*/
          $(".like_icons").each(function () {
            if ($(this).find(".compare_item_button").length) {
              $(this)
                .find(".compare_item_button")
                .find('.compare_item[data-item="' + item + '"]')
                .addClass("added");
              $(this)
                .find(".compare_item_button")
                .find('.compare_item[data-item="' + item + '"]')
                .find(".value.added")
                .show();
              $(this)
                .find(".compare_item_button")
                .find('.compare_item[data-item="' + item + '"]')
                .find(".value")
                .hide();
            }
          });
        } else {
          $(".compare_item[data-item=" + item + "]").removeClass("added");
          $(".compare_item[data-item=" + item + "]")
            .find(".value")
            .show();
          $(".compare_item[data-item=" + item + "]")
            .find(".value.added")
            .hide();

          /*sync other button*/
          $(".like_icons").each(function () {
            if ($(this).find(".compare_item_button").length) {
              $(this)
                .find(".compare_item_button")
                .find('.compare_item[data-item="' + item + '"]')
                .removeClass("added");
              $(this)
                .find(".compare_item_button")
                .find('.compare_item[data-item="' + item + '"]')
                .find(".value")
                .show();
              $(this)
                .find(".compare_item_button")
                .find('.compare_item[data-item="' + item + '"]')
                .find(".value.added")
                .hide();
            }
          });
        }
      }

      $.get(
        arAsproOptions["SITE_DIR"] + "ajax/item.php?item=" + item + "&compare_item=Y&iblock_id=" + iblockID,
        $.proxy(function (data) {
          getActualBasket(iblockID, "Compare");
          arStatusBasketAspro = {};

          // js notice
          if (
            typeof JNoticeSurface !== "undefined" &&
            ((!bBannerAction && !th.hasClass("added")) || (bBannerAction && th.hasClass("added")))
          ) {
            JNoticeSurface.get().onAdd2Compare([th[0]]);
          }
          //

          if ($("#compare_fly").length) {
            jsAjaxUtil.InsertDataToNode(
              arAsproOptions["SITE_DIR"] + "ajax/show_compare_preview_fly.php",
              "compare_fly",
              false
            );
          }
          th.removeClass("clicked");
        })
      );
    }
  });

  /*
  $(".fancybox").fancybox({
    backFocus: false,
  });
  */

  $(document).on("click", ".tabs>li", function () {
    var parent = $(this).parent();
    if (!$(this).hasClass("active")) {
      var sliderIndex = $(this).index(),
        curLiNav = $(this)
          .closest(".top_block")
          .find(".slider_navigation")
          .find(">li:eq(" + sliderIndex + ")"),
        curLi = $(this)
          .closest(".top_block")
          .siblings(".tabs_content")
          .find(">li:eq(" + sliderIndex + ")");
      $(this).addClass("active").addClass("cur").siblings().removeClass("active").removeClass("cur");
      curLi.addClass("cur").siblings().removeClass("cur");
      curLiNav.addClass("cur").siblings().removeClass("cur");

      if (parent.hasClass("ajax")) {
        // front tabs
        if (!$(this).hasClass("clicked")) {
          $.ajax({
            url: arAsproOptions["SITE_DIR"] + "include/mainpage/comp_catalog_ajax.php",
            type: "POST",
            data: {
              AJAX_POST: "Y",
              FILTER_HIT_PROP: $(this).data("code"),
              AJAX_PARAMS: $(this).closest(".tab_slider_wrapp").find(".request-data").data("value"),
              GLOBAL_FILTER: curLi.data("filter"),
            },
          }).success(function (html) {
            curLi.find(".tabs_slider").html(html);
            var container = curLi.closest(".content_wrapper_block");
            if (container.length) {
              var bNav = curLi.find(".bottom_nav .module-pagination").length > 0;
              if (bNav) {
                container.removeClass("without-border");
              } else {
                container.addClass("without-borded");
              }
            }
            setTimeout(function () {
              curLi.addClass("opacity1");
            }, 100);

            /*equal height*/
            // curLi.find('.catalog_block .catalog_item_wrapp .catalog_item .item_info:visible .item-title').sliceHeight({item:'.catalog_item:visible', mobile: true});
            // curLi.find('.catalog_block .catalog_item_wrapp .catalog_item .item_info:visible').sliceHeight({item:'.catalog_item:visible', mobile: true});
            // curLi.find('.catalog_block .catalog_item_wrapp .catalog_item:visible').sliceHeight({classNull: '.footer_button', item:'.catalog_item:visible', mobile: true});

            initCountdown();
          });
          $(this).addClass("clicked");
        } else {
          var container = curLi.closest(".content_wrapper_block");
          if (container.length) {
            var bNav = curLi.find(".bottom_nav .module-pagination").length > 0;
            if (bNav) {
              container.removeClass("without-border");
            } else {
              container.addClass("without-borded");
            }
          }
          /*$('.cur .catalog_block .catalog_item_wrapp.catalog_item:not(.big):visible').sliceHeight({
            classNull: '.footer_button',
            item:'.cur .catalog_block .catalog_item_wrapp.catalog_item:not(.big):visible',
            classes: [
              '.item_info .item-title',
              '.item_info .sa_block',
              '.item_info .cost.prices',
            ],
            mobile: true,
          });*/
        }
      }

      var eventdata = { index: sliderIndex, target: $(this) };
      BX.onCustomEvent("clickedTabsLi", [eventdata]);
    }
  });

  /*search click*/
  $(".search_block .icon").on("click", function () {
    var th = $(this);
    if ($(this).hasClass("open")) {
      $(this).closest(".center_block").find(".search_middle_block").removeClass("active");
      $(this).removeClass("open");
      $(this).closest(".center_block").find(".search_middle_block").find(".noborder").hide();
    } else {
      setTimeout(function () {
        th.closest(".center_block").find(".search_middle_block").find(".noborder").show();
      }, 100);
      $(this).closest(".center_block").find(".search_middle_block").addClass("active");
      $(this).addClass("open");
    }
  });

  $(document).on("click", ".no_goods .button", function () {
    $(".bx_filter .smartfilter .bx_filter_search_reset").trigger("click");
  });

  $(document).on("click", ".js-load-link", function (e) {
    AjaxClickLink(e);
  });

  $(document).on("click", ".svg-inline-bottom_nav-icon", function () {
    $(this).next().trigger("click");
  });
  $(document).on("click", ".ajax_load_btn", function () {
    var _this = $(this),
      url = _this.closest(".container").find(".module-pagination .flex-direction-nav .flex-next").attr("href"),
      th = _this.find(".more_text_ajax"),
      bottom_nav = _this.closest(".bottom_nav"),
      mobileBottomNav = bottom_nav.hasClass("mobile_slider"),
      bLoadingState = _this.closest(".animate-load-state").length;

    if (!th.hasClass("loading")) {
      th.addClass("loading");
      if (mobileBottomNav) {
        var icon = bottom_nav.find(".svg-inline-bottom_nav-icon");
        icon.css("display", "none");
      }
      if (bLoadingState) _this.addClass("loadings");

      var objUrl = parseUrlQuery(),
        add_url = "";
      obGetData = { ajax_get: "Y", AJAX_REQUEST: "Y" };

      if (_this.closest(".wrapper_inner.front").length) {
        //index page
        var class_block = th.closest(".drag-block").data("class").replace("_drag", "");
        class_block = class_block.replace(/\s/g, "");
        obGetData.BLOCK = class_block;
      }

      if (_this.closest(".tab").length) {
        //tabs block
        var parentTab = th.closest(".tab");
        obGetData.FILTER_HIT_PROP = parentTab.data("code");
        obGetData.GLOBAL_FILTER = parentTab.data("filter");
        url = _this.closest(".bottom_nav").find(".module-pagination .flex-direction-nav .flex-next").attr("href");
      }

      if (_this.closest(".content_linked_goods").length) {
        //linked_goods block in content
        var filterSections = th.closest(".content_linked_goods").attr("data-sections-ids");
        obGetData.ajax_section_id = decodeURIComponent(filterSections);

        url = _this.closest(".bottom_nav").find(".module-pagination .flex-direction-nav .flex-next").attr("href");
      }

      if ("clear_cache" in objUrl) {
        if (objUrl.clear_cache == "Y") add_url = "&clear_cache=Y";
      }
      if (_this.hasClass("ajax")) obGetData.ajax = "y";

      if (th.closest(".goods-block.ajax_load")) obGetData.bitrix_include_areas = "N";

      if (_this.closest(".nav-data_block").length) {
        //need data-block
        obGetData.data_block = _this.closest(".nav-data_block").attr("data-block");
        obGetData.pagen_data_block = "Y";
        url = _this.closest(".bottom_nav").find(".module-pagination .flex-direction-nav .flex-next").attr("href");
      }

      $.ajax({
        url: url + add_url,
        data: obGetData,
        success: function (html) {
          pasteAjaxPagination(html, th, bottom_nav, bLoadingState, _this);
        },
        error: function (html) {
          pasteAjaxPagination(html.responseText, th, bottom_nav, bLoadingState, _this);
        },
      });
    }
  });

  function pasteAjaxPagination(html, th, bottom_nav, bLoadingState, _this) {
    var mobileBottomClicked = bottom_nav.hasClass("mobile_slider");
    var hasMobileBottomNav = $(html).find(".bottom_nav.mobile_slider");
    var bottomNav = hasMobileBottomNav.length ? hasMobileBottomNav : $(html).find(".bottom_nav");
    var bottomNavHtml = bottomNav.html();
    var bottomNavScrollClass = bottomNav.data("scroll-class");
    var hasBottomNav = $(html).find(".ajax_load_btn").length;

    if (th.closest(".ajax_load").length && !mobileBottomClicked) {
      th.removeClass("loading");
      if (th.closest(".ajax_load").find(".js_append").length) {
        if (_this.closest(".catalog_in_content").length) {
          th.closest(".ajax_load").find(".js_append").append($(".inner_wrapper .js_wrapper_items>", html)); //need for brands & landings
        } else if (_this.closest(".catalog.search").length) {
          th.closest(".ajax_load").find(".js_append").append($(".js_append>", html));
        } else {
          th.closest(".ajax_load").find(".js_append").append(html);
        }
        th.closest(".ajax_load").find(".bottom_nav_wrapper").remove();
      } else {
        if ($(".display_list").length) {
          $(".display_list").append(html);
          th.closest(".display_list").find(".bottom_nav_wrapper").remove();
        } else if ($(".block_list").length) {
          $(".block_list").append(html);
          touchItemBlock(".catalog_item a");
          th.closest(".block_list").find(".bottom_nav_wrapper").remove();
        } else if ($(".module_products_list").length) {
          $(".module_products_list > tbody").append(html);
          th.closest(".module_products_list > tbody").find(".bottom_nav_wrapper").remove();
        }
      }

      setStatusButton();
      checkLinkedArticles();
      checkLinkedBlocks(".linked-banners-list");

      var eventdata = { action: "ajaxContentLoadedTab" };
      BX.onCustomEvent("onAjaxSuccess", [eventdata]);
      _this.closest(".bottom_nav").html(bottomNavHtml);
    } else {
      var eventdata = { action: "ajaxContentLoaded" };

      if ($(".banners-small.front").length) {
        $(".banners-small .items.row").append(html);
        $(".bottom_nav").html($(".banners-small .items.row .bottom_nav").html());
        $(".banners-small .items.row .bottom_nav").remove();
        if (hasBottomNav) {
          $(".banners-small .items.row").addClass("has-bottom-nav");
        } else {
          $(".banners-small .items.row").removeClass("has-bottom-nav");
        }
      } else {
        if (bottom_nav.data("append") !== undefined && bottom_nav.data("parent") !== undefined) {
          var target = html;
          if (bottom_nav.data("target") !== undefined) {
            target = $(html).find(bottom_nav.data("target"));
          }
          if (mobileBottomClicked || hasMobileBottomNav.length) {
            var mobileSliderNav = th.closest(bottom_nav.data("parent")).find(".bottom_nav.mobile_slider");
            if (mobileSliderNav.length) {
              mobileSliderNav.before(target);
            } else {
              bottom_nav.before(target);
            }
          } else {
            th.closest(bottom_nav.data("parent")).find(bottom_nav.data("append")).append(target);
          }
          th.closest(bottom_nav.data("parent")).find(bottom_nav.data("append")).find(".bottom_nav_wrapper").remove();

          if (hasBottomNav) {
            if (bottomNavScrollClass !== undefined) {
              th.closest(bottom_nav.data("parent")).find(bottomNavScrollClass).addClass("has-bottom-nav");
            }
            th.closest(bottom_nav.data("parent")).find(bottom_nav.data("append")).addClass("has-bottom-nav");
          } else {
            if (bottomNavScrollClass !== undefined) {
              th.closest(bottom_nav.data("parent")).find(bottomNavScrollClass).removeClass("has-bottom-nav");
            }
            th.closest(bottom_nav.data("parent")).find(bottom_nav.data("append")).removeClass("has-bottom-nav");
          }

          bottom_nav = th.closest(bottom_nav.data("parent")).find(".bottom_nav");
          bottom_nav.html(bottomNavHtml);
          var icon = bottom_nav.find(".svg-inline-bottom_nav-icon");
          icon.css("display", "");

          eventdata.container = th.closest(bottom_nav.data("parent"));
        } else {
          if (th.closest(".item-views").find(".items").length) {
            th.closest(".item-views").find(".items").append(html);
          } else {
            $(html).insertBefore($(".blog .bottom_nav"));
          }

          bottom_nav.html($(".bottom_nav:hidden").html());
          if (hasBottomNav) {
            th.closest(".item-views").find(".items").addClass("has-bottom-nav");
          } else {
            th.closest(".item-views").find(".items").removeClass("has-bottom-nav");
          }
          _this.closest(".item-views").find(".bottom_nav:hidden").remove();
        }
      }

      eventdata.content = html;
      eventdata.container = th;
      BX.onCustomEvent("onCompleteAction", [eventdata, th[0]]);

      setTimeout(function () {
        $(".banners-small .item.normal-block").sliceHeight({ resize: false });
        if ($(".item.slice-item").length) {
          $(".item.slice-item .title").sliceHeight({ resize: false });
          $(".item.slice-item").sliceHeight({ resize: false });
        }
        th.removeClass("loading");
        if (bLoadingState) _this.removeClass("loadings");
      }, 100);
    }
    BX.onCustomEvent("onHeaderProgressBarChange", [{}]);
    initCountdown();
    InitOwlSlider();
  }

  // form rating
  $(document).on("mouseenter", ".form .votes_block.with-text .item-rating", function () {
    var $this = $(this),
      index = $this.index(),
      ratingValue = $this.data("rating_value"),
      ratingMessage = $this.data("message");

    $(this).addClass("filed");
    $this.siblings().each(function () {
      if ($(this).index() <= index) $(this).addClass("filed");
      else $(this).removeClass("filed");
    });
    $this.closest(".votes_block").find(".rating_message").text(ratingMessage);
  });

  $(document).on("mouseleave", ".form .votes_block.with-text", function () {
    var $this = $(this),
      index = $this.data("rating"),
      ratingMessage = $this.closest(".votes_block").find(".rating_message").data("message");

    $this.find(".item-rating").each(function () {
      if ($(this).index() < index && index !== undefined) $(this).addClass("filed");
      else $(this).removeClass("filed");
    });
    $this.closest(".votes_block").find(".rating_message").text(ratingMessage);
  });

  $(document).on("click", ".form .votes_block.with-text .item-rating", function () {
    var $this = $(this),
      rating = $this.closest(".votes_block").data("rating"),
      index = $this.index() + 1,
      ratingMessage = $this.data("message");

    $this.closest(".votes_block").data("rating", index);
    if ($this.closest(".form-control").find("input[name=RATING]").length) {
      $this.closest(".form-control").find("input[name=RATING]").val(index);
    } else {
      $this.closest(".form-control").find("input[data-sid=RATING]").val(index);
    }
    $this.closest(".votes_block").find(".rating_message").data("message", ratingMessage);
  });

  //set cookie for basket link click
  $(document).on(
    "click",
    ".bx_ordercart_order_table_container .control > a, .basket-item-actions-remove, a[data-entity=basket-item-remove-delayed]",
    function (e) {
      $.removeCookie("click_basket", { path: "/" });
      $.cookie("click_basket", "Y", { path: "/" });
    }
  );

  $(document).on("click", ".bx_compare .tabs-head li", function () {
    var url = $(this).find(".sortbutton").data("href");
    BX.showWait(BX("bx_catalog_compare_block"));
    $.ajax({
      url: url,
      data: { ajax_action: "Y" },
      success: function (html) {
        history.pushState(null, null, url);
        $("#bx_catalog_compare_block").html(html);
        BX.closeWait();
      },
    });
  });
  var hoveredTrs;
  $(document).on(
    {
      mouseover: function (e) {
        var _ = $(this);
        var tbodyIndex = _.closest("tbody").index() + 1; //+1 for nth-child
        var trIndex = _.index() + 1; // +1 for nth-child
        hoveredTrs = $(e.delegateTarget)
          .find(".data_table_props")
          .children(":nth-child(" + tbodyIndex + ")")
          .children(":nth-child(" + trIndex + ")")
          .addClass("hovered");
      },
      mouseleave: function (e) {
        if (hoveredTrs) hoveredTrs.removeClass("hovered");
      },
    },
    ".bx_compare .data_table_props tbody>tr"
  );
  $(document).on("click", ".fancy_zoom", function (e) {
    e.preventDefault();
    var arPict = [],
      index = 0;
    $(this)
      .closest(".product-detail-gallery__container")
      .find(".product-detail-gallery__thmb-container .product-detail-gallery__item")
      .each(function () {
        var obImg = {};
        obImg = {
          src: $(this).data("big"),
          // opts: {
          //   caption: $(this).find("img").attr("alt"),
          // },
        };
        if ($(this).parent().hasClass("current")) {
          index = $(this).parent().index();
        }
        arPict.push(obImg);
      });
    if (!arPict.length) {
      arPict.push({
        src: $(this).closest(".product-detail-gallery__container").find(".zoom_picture").data("xoriginal"),
      });
    }
    if (typeof $.fn.fancybox !== "function") {
      return;
    }
    $.fancybox.open(
      arPict,
      {
        loop: false,
        buttons: [
          // "zoom",
          //"share",
          // "slideShow",
          //"fullScreen",
          //"download",
          // "thumbs",
          "close",
        ],
      },
      index
    );
  });

  /*tabs*/
  $(".tabs_section .tabs-head li").on("click", function () {
    if (!$(this).is(".current")) {
      $(".tabs_section .tabs-head li").removeClass("current");
      $(this).addClass("current");
      $(".tabs_section ul.tabs_content li").removeClass("current");
      if ($(this).attr("id") == "product_reviews_tab") {
        $(".shadow.common").hide();
        $("#reviews_content").show();
      } else {
        $(".shadow.common").show();
        $("#reviews_content").hide();
        $(".tabs_section ul.tabs_content > li:eq(" + $(this).index() + ")").addClass("current");
      }
    }
  });

  /*open first section slide*/
  setTimeout(function () {
    $(".jobs_wrapp .item:first .name tr").trigger("click");
  }, 300);

  $(".choise").on("click", function () {
    var _this = $(this);
    if (typeof _this.attr("data-block") != "undefined") {
      if ($(_this.attr("data-block")).closest(".tab-pane").length) {
        $('.ordered-block a[href="#' + $(_this.attr("data-block")).closest(".tab-pane").attr("id") + '"]').click();
        _this.data("block", ".ordered-block.tabs-block");
        $(_this.data("block")).data("offset", -100);
      } else {
        $(_this.data("block")).data("offset", -100);
      }
      scrollToBlock(_this.data("block"));
    }
  });

  $(document).on("click", ".buy_block .slide_offer", function () {
    scroll_block($(".js-offers-scroll"));
  });
  $(".share  > .share_wrapp .text").on("click", function () {
    var block = $(this).parent().find(".shares");
    if (!block.is(":visible") && !$(this).closest(".share.top").length) $("#content").css("z-index", 3);
    block.fadeToggle(100, function () {
      if (!block.is(":visible")) $("#content").css("z-index", 2);
    });
  });
  $("html, body").on("mousedown", function (e) {
    if (typeof e.target.className == "string" && e.target.className.indexOf("adm") < 0) {
      e.stopPropagation();
      $("div.shares").fadeOut(100, function () {
        $("#content").css("z-index", 2);
        $(".price_txt .share_wrapp").removeClass("opened");
      });
      $(".search_middle_block").removeClass("active_wide");
    }
  });
  $(".share_wrapp")
    .find("*")
    .on("mousedown", function (e) {
      e.stopPropagation();
    });

  $(".price_txt .share_wrapp .text").click(function () {
    $(this).parent().toggleClass("opened");
    $(this).parent().find(".shares").fadeToggle();
  });

  $(document).on("click", ".reviews-collapse-link", function () {
    $(".reviews-reply-form").slideToggle();
  });

  /* accordion action*/
  $(".panel-collapse").on("hidden.bs.collapse", function () {
    $(this).parent().toggleClass("opened");
  });
  $(".panel-collapse").on("show.bs.collapse", function () {
    $(this).parent().toggleClass("opened");
  });

  /* accordion */
  $(".accordion-head").on("click", function (e) {
    e.preventDefault();
    if (!$(this).next().hasClass("collapsing")) {
      $(this).toggleClass("accordion-open");
      $(this).toggleClass("accordion-close");
    }
  });

  /* progress bar */
  $("[data-appear-progress-animation]").each(function () {
    var $this = $(this);
    $this.appear(
      function () {
        var delay = $this.attr("data-appear-animation-delay") ? $this.attr("data-appear-animation-delay") : 1;
        if (delay > 1) $this.css("animation-delay", delay + "ms");
        $this.addClass($this.attr("data-appear-animation"));

        setTimeout(function () {
          $this.animate(
            {
              width: $this.attr("data-appear-progress-animation"),
            },
            1500,
            "easeOutQuad",
            function () {
              $this.find(".progress-bar-tooltip").animate(
                {
                  opacity: 1,
                },
                500,
                "easeOutQuad"
              );
            }
          );
        }, delay);
      },
      { accX: 0, accY: -50 }
    );
  });

  initCountdown();

  /* portfolio item */
  if ($(".item.animated-block").length) {
    $(".item.animated-block").appear(
      function () {
        var $this = $(this);

        $this.addClass($this.data("animation")).addClass("visible");
      },
      { accX: 0, accY: 150 }
    );
  }

  /* flexslider appear */
  if ($(".appear-block").length) {
    $(".appear-block").appear(
      function () {
        var $this = $(this);
        $this.removeClass("appear-block");

        CheckFlexSlider();
        InitFlexSlider();
        InitOwlSlider();
      },
      { accX: 0, accY: 150 }
    );
  }

  /* js-load-block appear */
  if ($(".js-load-block").length) {
    var objUrl = parseUrlQuery();
    var bClearCache = false;
    if ("clear_cache" in objUrl) {
      if (objUrl.clear_cache == "Y") {
        bClearCache = true;
      }
    }

    var items = [];
    var bIdle = true;
    var insertNextBlockContent = function () {
      if (bIdle) {
        if (items.length) {
          bIdle = false;
          var item = items.pop();

          item.content = $.trim(item.content);

          // remove /bitrix/js/main/core/core_window.js if it was loaded already
          if (item.content.indexOf("/bitrix/js/main/core/core_window.") !== -1 && BX.WindowManager) {
            item.content = item.content.replace(
              /<script src="\/bitrix\/js\/main\/core\/core_window\.[^>]*><\/script>/gm,
              ""
            );
          }

          // remove /bitrix/js/currency/core_currency.js if it was loaded already
          if (
            item.content.indexOf("/bitrix/js/currency/core_currency.") !== -1 &&
            typeof BX.Currency === "object" &&
            BX.Currency.defaultFormat
          ) {
            item.content = item.content.replace(
              /<script src="\/bitrix\/js\/currency\/core_currency\.[^>]*><\/script>/gm,
              ""
            );
          }

          // remove /bitrix/js/main/pageobject/pageobject.js if it was loaded already
          if (item.content.indexOf("/bitrix/js/main/pageobject/pageobject.") !== -1 && BX.PageObject) {
            item.content = item.content.replace(
              /<script src="\/bitrix\/js\/main\/pageobject\/pageobject\.[^>]*><\/script>/gm,
              ""
            );
          }

          // remove /bitrix/js/main/polyfill/promise/js/promise.js if it not need
          if (
            item.content.indexOf("/bitrix/js/main/polyfill/promise/js/promise.") !== -1 &&
            typeof window.Promise !== "undefined" &&
            window.Promise.toString().indexOf("[native code]") !== -1
          ) {
            item.content = item.content.replace(
              /<script src="\/bitrix\/js\/main\/polyfill\/promise\/js\/promise\.[^>]*><\/script>/gm,
              ""
            );
          }

          var ob = BX.processHTML(item.content);

          // stop ya metrika webvisor DOM indexer
          pauseYmObserver();

          item.block.removeAttr("data-file").removeClass("loader_circle");

          if (item.block.data("appendTo")) {
            item.block.find(item.block.data("appendTo"))[0].innerHTML = ob.HTML;
          } else {
            if (item.block.find('> div[id*="bx_incl_"]').length) {
              item.block.find('> div[id*="bx_incl_"]')[0].innerHTML = ob.HTML;
            } else {
              item.block[0].innerHTML = ob.HTML;
            }
          }

          BX.ajax.processScripts(ob.SCRIPT);

          var eventdata = { action: "jsLoadBlock" };
          BX.onCustomEvent("onCompleteAction", [eventdata, item.block]);

          BX.onCustomEvent("onHeaderProgressBarChange", [{}]);

          // resume ya metrika webvisor
          // 500ms
          setTimeout(resumeYmObserver, 500);

          bIdle = true;
          insertNextBlockContent();
        }
      }
    };

    $(".js-load-block").appear(
      function () {
        var $this = $(this);

        if ($this.data("file")) {
          var add_url = bClearCache ? "?clear_cache=Y" : "";
          if ($this.data("block")) {
            if ($this.data("file").indexOf("?") !== -1) {
              if (bClearCache) {
                add_url = add_url.slice(1);
              }
              add_url += "&BLOCK=" + $this.data("block");
            } else {
              add_url += (bClearCache ? "&" : "?") + "BLOCK=" + $this.data("block");
            }
          }

          // get content
          $.get($this.data("file") + add_url).done(function (html) {
            /*items.push({
              block: $this,
              content: html,
            });*/
			
			$this.html(html);
			
			

            if (items.length == 1) {
              setTimeout(insertNextBlockContent, 100);
            }
          });
        }
      },
      { accX: 0, accY: isMobile ? 300 : 150 }
    );
  }

  /*show ajax store amount product*/
  $(document).on("click", ".js-show-info-block", function (e) {
    if (window.matchMedia("(max-width: 500px)").matches) {
      return;
    }
    var $this = $(this);
    e.stopPropagation();
    $(".js-info-block").fadeOut();

    if ($this.hasClass("opened")) {
      $(".js-show-info-block").removeClass("opened");
    } else {
      $(".js-show-info-block").removeClass("opened");
      $this.addClass("opened");
    }

    if (!$this.siblings(".js-info-block").length) {
      var dataFields = $this.closest(".sa_block").data("fields");
      dataFields = dataFields == "null" || dataFields === undefined ? "" : dataFields;
      var dataUserFields = $this.closest(".sa_block").data("user-fields");
      dataUserFields = dataUserFields == "null" || dataUserFields === undefined ? "" : dataUserFields;
      var objUrl = parseUrlQuery(),
        add_url = "";
      if ("clear_cache" in objUrl) {
        if (objUrl.clear_cache == "Y") add_url += "?clear_cache=Y";
      }

      var obPostParams = {
        ajax: "Y",
        ELEMENT_ID: $this.data("id"),
        FIELDS: dataFields,
        USER_FIELDS: dataUserFields,
        STORES: $this.closest(".sa_block").data("stores") || "",
      };

      $this.addClass("loadings");
      $.post(arAsproOptions["SITE_DIR"] + "ajax/productStoreAmountCompact.php" + add_url, obPostParams).done(function (
        html
      ) {
        $this.removeClass("loadings");
        $(html).appendTo($this.closest(".sa_block"));

        $this
          .siblings(".js-info-block")
          .find(".more-btn a")
          .attr("href", $this.closest(".item_info").find("a").attr("href"));
        InitScrollBar();

        var eventdata = { action: "jsShowStores" };
        BX.onCustomEvent("onCompleteAction", [eventdata, $this]);
      });
    } else {
      if ($this.hasClass("opened")) {
        $this
          .siblings(".js-info-block")
          .find(".more-btn a")
          .attr("href", $this.closest(".item_info").find("a").attr("href"));
        $this.siblings(".js-info-block").fadeIn();
        InitScrollBar();
      } else {
        $this.siblings(".js-info-block").fadeOut();
      }
    }
  });
  $(document).on("click", ".js-info-block .svg-inline-close", function () {
    $(".js-show-info-block").removeClass("opened");
    $(this).closest(".js-info-block").fadeOut();
  });
  /**/

  /*adaptive menu start*/
  $(".menu.adaptive").on("click", function () {
    $(this).toggleClass("opened");
    if ($(this).hasClass("opened")) {
      $(".mobile_menu").toggleClass("opened").slideDown();
    } else {
      $(".mobile_menu").toggleClass("opened").slideUp();
    }
  });
  $(".mobile_menu .has-child >a").on("click", function (e) {
    var parentLi = $(this).parent();
    e.preventDefault();
    parentLi.toggleClass("opened");
    parentLi.find(".dropdown").slideToggle();
  });

  $(".mobile_menu .search-input-div input").on("keyup", function (e) {
    var inputValue = $(this).val();
    $(".center_block .stitle_form input").val(inputValue);
    if (e.keyCode == 13) {
      $(".center_block .stitle_form form").submit();
    }
  });

  $(".center_block .stitle_form input").on("keyup", function (e) {
    var inputValue = $(this).val();
    $(".mobile_menu .search-input-div input").val(inputValue);
    if (e.keyCode == 13) {
      $(".center_block .stitle_form form").submit();
    }
  });

  $(".mobile_menu .search-button-div button").on("click", function (e) {
    e.preventDefault();
    var inputValue = $(this).parents().find("input").val();
    $(".center_block .stitle_form input").val(inputValue);
    $(".center_block .stitle_form form").submit();
  });
  /*adaptive menu end*/

  $(document).on("click", ".mega-menu .dropdown-menu", function (e) {
    e.stopPropagation();
  });

  $(document).on("click", ".mega-menu .dropdown-toggle.more-items", function (e) {
    e.preventDefault();
  });

  $(document).on(
    "mouseenter",
    ".table-menu .dropdown,.table-menu .dropdown-submenu,.table-menu .dropdown-toggle",
    function () {
      // setTimeout(
      // 	function() {
      // 		CheckTopVisibleMenu();
      // 	}, 275);
    }
  );
  $(document).on("mouseenter", "#headerfixed .table-menu .dropdown-menu .dropdown-submenu", function () {
    setTimeout(function () {
      CheckTopVisibleMenu();
    }, 275);
  });

  $(".mega-menu .search-item .search-icon, .menu-row #title-search .fa-close").on("click", function (e) {
    e.preventDefault();
    $(".menu-row #title-search").toggleClass("hide");
  });

  $(".mega-menu ul.nav .search input").on("keyup", function (e) {
    var inputValue = $(this).val();
    $(".menu-row > .search input").val(inputValue);
    if (e.keyCode == 13) {
      $(".menu-row > .search form").submit();
    }
  });

  $(".menu-row > .search input").on("keyup", function (e) {
    var inputValue = $(this).val();
    $(".mega-menu ul.nav .search input").val(inputValue);
    if (e.keyCode == 13) {
      $(".menu-row > .search form").submit();
    }
  });

  $(".mega-menu ul.nav .search button").on("click", function (e) {
    e.preventDefault();
    var inputValue = $(this).parents(".search").find("input").val();
    $(".menu-and-search .search input").val(inputValue);
    $(".menu-row > .search form").submit();
  });

  $(".btn.btn-add").on("click", function () {
    $.ajax({
      type: "GET",
      url: arAsproOptions["SITE_DIR"] + "ajax/clearBasket.php",
      success: function (data) {},
    });
  });

  $(".sort_display a").on("click", function () {
    $(this).siblings().removeClass("current");
    $(this).addClass("current");
  });

  /*detail order show payments*/
  $(".sale-order-detail-payment-options-methods-info-change-link").on("click", function () {
    $(this).closest(".sale-order-detail-payment-options-methods-info").addClass("opened").siblings().addClass("opened");
  });

  /*expand/hide filter values*/
  $(document).on("click", ".expand_block", function () {
    togglePropBlock($(this));
  });

  /*touch event*/
  document.addEventListener(
    "touchend",
    function (event) {
      if (!$(event.target).closest(".menu-item").length && !$(event.target).hasClass("menu-item")) {
        $(".menu-row .dropdown-menu").css({ display: "none", opacity: 0 });
        $(".menu-item").removeClass("hover");
        $(".bx-breadcrumb-item.drop").removeClass("hover");
      }
      if (!$(event.target).closest(".menu.topest").length) {
        if (window.matchMedia("(max-width: 991px)").matches) {
          $(".menu.topest").css({ overflow: "hidden" });
        }
        $(".menu.topest > li").removeClass("hover");
      }
      if (!$(event.target).closest(".full.has-child").length) {
        $(".menu_top_block.catalog_block li").removeClass("hover");
      }
      if (!$(event.target).closest(".basket_block").length) {
        $(".basket_block .link").removeClass("hover");
        $(".basket_block .basket_popup_wrapp").slideUp();
      }
      if (!$(event.target).closest(".catalog_item").length) {
        var tabsContentUnhoverHover = $(".tab:visible").attr("data-unhover") * 1;
        $(".tab:visible").stop().animate({ height: tabsContentUnhoverHover }, 100);
        $(".tab:visible").find(".catalog_item").removeClass("hover");
        $(".tab:visible").find(".catalog_item .buttons_block").stop().fadeOut(233);
        if ($(".catalog_block").length) {
          $(".catalog_block").find(".catalog_item_wrapp").removeClass("hover");
          $(".catalog_block").find(".catalog_item").removeClass("hover");
        }
      }
      //togglePropBlock($(event.target));
    },
    false
  );

  touchMenu(".menu-row .menu-item");
  touchTopMenu(".menu.topest li");
  touchLeftMenu(".menu_top_block:not(.in-search) li.full");
  touchBreadcrumbs(".bx-breadcrumb-item.drop");

  $(document).on("keyup", ".coupon .input_coupon input", function () {
    if ($(this).val().length) {
      $(this).removeClass("error");
      $(this).closest(".input_coupon").find(".error").remove();
    } else {
      $(this).addClass("error");
      $("<label class='error'>" + BX.message("INPUT_COUPON") + "</label>").insertBefore($(this));
    }
  });
  showPhoneMask("input[autocomplete=tel]");
  BX.addCustomEvent(window, "onAjaxSuccessFilter", function (e) {
    setBasketStatusBtn();
    checkLinkedArticles();
    checkLinkedBlocks(".linked-banners-list");
  });

  $(document).on("click", ".block_container .items .item.initied", function () {
    var _this = $(this),
      itemID = _this.data("id"),
      animationTime = 200;

    _this.closest(".items").fadeOut(animationTime, function () {
      _this.closest(".block_container").find(".detail_items").fadeIn(animationTime);
      _this
        .closest(".block_container")
        .find(".detail_items .item[data-id=" + itemID + "]")
        .fadeIn(animationTime);

      var arCoordinates = _this.data("coordinates").split(",");

      if (typeof map !== "undefined") map.setCenter([arCoordinates[0], arCoordinates[1]], 15);
    });
  });

  $(document).on("click", ".block_container .top-close", function () {
    var _this = $(this).closest(".block_container").find(".detail_items .item:visible"),
      animationTime = 200;
    _this.fadeOut(animationTime);
    _this
      .closest(".block_container")
      .find(".detail_items")
      .fadeOut(animationTime, function () {
        _this.closest(".block_container").find(".items").fadeIn(animationTime);

        if (typeof map !== "undefined" && typeof clusterer !== "undefined") {
          map.setBounds(clusterer.getBounds(), {
            zoomMargin: 40,
            // checkZoomRange: true
          });
        }
      });
  });

  BX.addCustomEvent(window, "onAjaxSuccess", function (e) {
    if (e != "OK") {
      initSelects(document);
      InitOrderCustom();
      showPhoneMask("input[autocomplete=tel]");

      if (arAsproOptions["PAGES"]["CATALOG_PAGE"] && $(".catalog_detail").length && !$(".fast_view_frame").length) {
        const leftBlock = $(".main-catalog-wrapper > .left_block");
        const rightBlock = $(".right_block");

        if (!leftBlock.length && rightBlock.hasClass("wide_N")) {
          rightBlock.removeClass("wide_N").addClass("wide_Y");
        } else if (leftBlock.length && rightBlock.hasClass("wide_Y")) {
          rightBlock.removeClass("wide_Y").addClass("wide_N");
        }

        $(".bx_filter").remove();
        InitFlexSlider();
        InitOwlSlider();
      }

      if (arAsproOptions["PAGES"]["CATALOG_PAGE"]) {
        initCountdown();

        if (typeof window["stickySidebar"] !== "undefined") window["stickySidebar"].updateSticky();
      }

      if (arAsproOptions["PAGES"]["ORDER_PAGE"]) {
        orderActions(e);
      }

      if (e && typeof e === "object" && "action" in e && e.action === "ajaxContentLoadedTab") {
        lazyLoadPagenBlock();
      }

      InitStickySideBar();
    }
  });

  //event for default basket quantity change
  BX.addCustomEvent(window, "OnBasketChange", function (e) {
    if (arAsproOptions["PAGES"]["BASKET_PAGE"]) {
      var summ = 0,
        title = "";

      if (typeof BX.Sale !== "undefined") {
        if (typeof BX.Sale.BasketComponent !== "undefined") {
          summ = BX.Sale.BasketComponent.result.allSum;
          title = BX.message("JS_BASKET_COUNT_TITLE").replace("SUMM", summ);
        }
      } else {
        summ = $("#allSum_FORMATED")
          .html()
          .replace(/&nbsp;/g, " ");
        title = BX.message("JS_BASKET_COUNT_TITLE").replace("SUMM", summ);
      }

      if ($(".js-basket-block .wrap .prices").length) $(".js-basket-block .wrap .prices").html(summ);
      if ($("a.basket-link.basket").length) $("a.basket-link.basket").attr("title", title);
      if ($(".basket_fly .opener .basket_count").length) $(".basket_fly .opener .basket_count").attr("title", title);
    }
  });
});

if (!funcDefined("setBasketStatusBtn")) {
  setBasketStatusBtn = function (type) {
    var bSync = typeof type !== undefined;
    if (typeof arBasketAspro !== "undefined") {
      if ("BASKET" in arBasketAspro) {
        // basket items
        if (arBasketAspro.BASKET) {
          for (var i in arBasketAspro.BASKET) {
            $(".to-cart[data-item=" + i + "]").hide();
            $(".counter_block[data-item=" + i + "]")
              .closest(".counter_block_inner")
              .hide();
            $(".counter_block[data-item=" + i + "]").hide();
            $(".in-cart[data-item=" + i + "]").show();
            $(".in-cart[data-item=" + i + "]")
              .closest(".button_block")
              .addClass("wide");

            $(".wish_item.to[data-item=" + i + "]").show();
            $(".wish_item.in[data-item=" + i + "]").hide();

            if ($(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .basket_item_add").length) {
              $(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .basket_item_add").addClass("added");
              $(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .basket_item_add").attr(
                "title",
                $(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .basket_item_add").data("title2")
              );
            }
          }
        }
      }

      /*services*/
      if ("SERVICES" in arBasketAspro) {
        // services items
        $needFillServices = $(".buy_services_wrap[data-parent_product]").length;
        if (arBasketAspro.SERVICES && $needFillServices) {
          for (var i in arBasketAspro.SERVICES) {
            var serviceItem = $(
              ".buy_services_wrap[data-parent_product=" +
                arBasketAspro.SERVICES[i].link_id +
                "] .services-item[data-item_id=" +
                arBasketAspro.SERVICES[i].item_id +
                "]"
            );
            serviceItem.find('input[name="buy_switch_services"]').prop("checked", true);
            serviceItem.find('.counter_block input[name="quantity"]').val(arBasketAspro.SERVICES[i].quantity);
            serviceItem.addClass("services_on");
          }
        }
      }
      /**/

      if ("DELAY" in arBasketAspro) {
        // delay items
        if (arBasketAspro.DELAY) {
          for (var i in arBasketAspro.DELAY) {
            $(".wish_item.to[data-item=" + i + "]").hide();
            $(".wish_item.in[data-item=" + i + "]").show();
            if ($(".wish_item[data-item=" + i + "]").find(".value.added").length) {
              //$('.wish_item[data-item='+i+']').addClass("added");
              $(".wish_item[data-item=" + i + "]")
                .find(".value")
                .hide();
              $(".wish_item[data-item=" + i + "]")
                .find(".value.added")
                .css("display", "block");
            }

            $(".in-cart[data-item=" + i + "]").hide();
            $(".to-cart[data-item=" + i + "]").show();
            $(".to-cart[data-item=" + i + "]")
              .closest(".counter_wrapp")
              .find(".counter_block_inner")
              .show();
            $(".to-cart[data-item=" + i + "]")
              .closest(".counter_wrapp")
              .find(".counter_block")
              .show();
            $(".to-cart[data-item=" + i + "]")
              .closest(".button_block")
              .removeClass("wide");

            if ($(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .wish_item_add").length) {
              $(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .wish_item_add").addClass("added");
              $(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .wish_item_add").attr(
                "title",
                $(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .wish_item_add").data("title2")
              );
            }
          }
        }
      }

      if ("SUBSCRIBE" in arBasketAspro) {
        // subscribe items
        if (arBasketAspro.SUBSCRIBE) {
          for (var i in arBasketAspro.SUBSCRIBE) {
            $(".to-subscribe[data-item=" + i + "]").hide();
            $(".in-subscribe[data-item=" + i + "]").show();
          }
        }
      }

      if ("COMPARE" in arBasketAspro) {
        // compare items
        if (arBasketAspro.COMPARE) {
          for (var i in arBasketAspro.COMPARE) {
            $(".compare_item.to[data-item=" + i + "]").hide();
            $(".compare_item.in[data-item=" + i + "]").show();
            if ($(".compare_item[data-item=" + i + "]").find(".value.added").length) {
              //$('.compare_item[data-item='+i+']').addClass("added");
              $(".compare_item[data-item=" + i + "]")
                .find(".value")
                .hide();
              $(".compare_item[data-item=" + i + "]")
                .find(".value.added")
                .css("display", "block");
            }

            if ($(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .compare_item_add").length) {
              $(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .compare_item_add").addClass("added");
              $(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .compare_item_add").attr(
                "title",
                $(".banner_buttons.with_actions .wraps_buttons[data-id=" + i + "] .compare_item_add").data("title2")
              );
            }
          }
        }
      }
    }
  };
}

if (!funcDefined("togglePropBlock")) {
  togglePropBlock = function (className) {
    var all_props_block = className.closest(".bx_filter_parameters_box_container").find(".hidden_values");
    if (all_props_block.length && (className.hasClass("inner_text") || className.hasClass("expand_block"))) {
      if (all_props_block.is(":visible")) {
        className.text(BX.message("FILTER_EXPAND_VALUES"));
        all_props_block.hide();
      } else {
        className.text(BX.message("FILTER_HIDE_VALUES"));
        all_props_block.show();
      }
    }
  };
}

if (!funcDefined("showPhoneMask")) {
  showPhoneMask = function (className) {
    $(className).inputmask("mask", { mask: arAsproOptions["THEME"]["PHONE_MASK"], showMaskOnHover: false });
  };
}

if (!funcDefined("getActualBasket")) {
  getActualBasket = function (iblockID, type, sync) {
    var data = "";
    if (typeof iblockID !== "undefined" && iblockID) {
      data = { iblockID: iblockID };
    }
    $.ajax({
      type: "GET",
      url: arAsproOptions["SITE_DIR"] + "ajax/actualBasket.php",
      data: data,
      success: function (data) {
        if (!$(".js_ajax").length) $("body").append('<div class="js_ajax"></div>');
        $(".js_ajax").html(data);

        if (typeof sync !== "undefined") {
          setBasketStatusBtn(true);
        }

        if (typeof type !== undefined) {
          var eventdata = { action: "loadActualBasket" + type };
          BX.onCustomEvent("onCompleteAction", [eventdata]);
        }
      },
    });
  };
}

if (!funcDefined("reloadBasketCounters")) {
  reloadBasketCounters = function (count, sync) {
    var data = "";
    if (count) {
      $(".basket-link.basket .count, .wraps_icon_block.basket .count .items > span").text(count);
    } else {
      $.ajax({
        type: "GET",
        url: arAsproOptions["SITE_DIR"] + "ajax/actualBasket.php",
        data: data,
        success: function (data) {
          if (!$(".js_ajax").length) $("body").append('<div class="js_ajax"></div>');
          $(".js_ajax").html(data);
          var countServices =
            typeof arBasketAspro.SERVICES !== "undefined" ? Object.keys(arBasketAspro.SERVICES).length : 0;
          $(".basket-link.basket .count, .wraps_icon_block.basket .count .items > span").text(
            Object.keys(arBasketAspro.BASKET).length + countServices
          );
          $(".basket-link.delay .count, .wraps_icon_block.delay .count .items > span").text(
            Object.keys(arBasketAspro.DELAY).length
          );
          $(".basket-link.compare .count, .wraps_icon_block.compare .count .items > span").text(
            Object.keys(arBasketAspro.COMPARE).length
          );

          if (typeof sync !== "undefined") {
            setBasketStatusBtn(true);
          }
        },
      });
    }
  };
}

function touchMenu(selector) {
  if (isMobile) {
    if ($(selector).length) {
      $(selector).each(function () {
        var th = $(this);
        th.on("touchend", function (e) {
          var _th = $(e.target).closest(".menu-item");

          $(".menu.topest > li").removeClass("hover");
          $(".menu_top_block.catalog_block li").removeClass("hover");
          $(".bx-breadcrumb-item.drop").removeClass("hover");

          if (_th.find(".dropdown-menu").length && !_th.hasClass("hover")) {
            e.preventDefault();
            e.stopPropagation();
            _th.siblings().removeClass("hover");
            _th.addClass("hover");
            $(".menu-row .dropdown-menu").css({ display: "none", opacity: 0 });
            if (_th.hasClass("menu-item")) {
              _th.closest(".dropdown-menu").css({ display: "block", opacity: 1 });
            }
            if (_th.find("> .wrap > .dropdown-menu")) {
              _th.find("> .wrap > .dropdown-menu").css({ display: "block", opacity: 1 });
            } else if (_th.find("> .dropdown-menu")) {
              _th.find("> .dropdown-menu").css({ display: "block", opacity: 1 });
            }
            CheckTopVisibleMenu();
          } else {
            var href = $(e.target).attr("href") ? $(e.target).attr("href") : $(e.target).closest("a").attr("href");
            if (href && href !== "undefined") location.href = href;
          }
        });
      });
    }
  } else {
    $(selector).off();
  }
}

function touchTopMenu(selector) {
  if (isMobile) {
    if ($(selector).length) {
      $(selector).each(function () {
        var th = $(this);
        th.on("touchend", function (e) {
          var _th = $(e.target).closest("li");

          $(".menu-item").removeClass("hover");
          $(".menu_top_block.catalog_block li").removeClass("hover");
          $(".bx-breadcrumb-item.drop").removeClass("hover");

          if (_th.hasClass("more") && !_th.hasClass("hover")) {
            e.preventDefault();
            e.stopPropagation();
            _th.siblings().removeClass("hover");
            _th.addClass("hover");
            $(".menu.topest").css({ overflow: "visible" });
          } else {
            var href = $(e.target).attr("href") ? $(e.target).attr("href") : $(e.target).closest("a").attr("href");
            if (href && href !== "undefined") location.href = href;
          }
        });
      });
    }
  } else {
    $(selector).off();
  }
}

function touchLeftMenu(selector) {
  if (isMobile) {
    if ($(selector).length) {
      $(selector).each(function () {
        var th = $(this);
        th.on("touchend", function (e) {
          var _th = $(e.target).closest("li");

          $(".menu-item").removeClass("hover");
          $(".bx-breadcrumb-item.drop").removeClass("hover");
          $(".menu.topest > li").removeClass("hover");

          if (_th.hasClass("has-child") && !_th.hasClass("hover")) {
            e.preventDefault();
            e.stopPropagation();
            _th.siblings().removeClass("hover");
            _th.addClass("hover");
          } else {
            var href = $(e.target).attr("href") ? $(e.target).attr("href") : $(e.target).closest("a").attr("href");
            if (href && href !== "undefined") location.href = href;
          }
        });
      });
    }
  } else {
    $(selector).off();
  }
}

function touchBreadcrumbs(selector) {
  if (isMobile) {
    if ($(selector).length) {
      $(selector).each(function () {
        var th = $(this);
        th.on("touchend", function (e) {
          var _th = $(e.target).closest(".bx-breadcrumb-item");

          $(".menu-item").removeClass("hover");
          $(".menu.topest > li").removeClass("hover");
          $(".menu_top_block.catalog_block li").removeClass("hover");

          if (!_th.hasClass("hover")) {
            e.preventDefault();
            e.stopPropagation();
            _th.siblings().removeClass("hover");
            _th.addClass("hover");
          } else {
            _th.removeClass("hover");
            var href = $(e.target).attr("href") ? $(e.target).attr("href") : $(e.target).closest("a").attr("href");
            if (href && href !== "undefined") location.href = href;
          }
        });
      });
    }
  } else {
    $(selector).off();
  }
}

function touchItemBlock(selector) {
  /*if(isMobile){
    if($(selector).length)
    {
      $(selector).each(function(){
        var th=$(this),
          item=th.closest('.catalog_item');
        th.on('touchend', function(e) {
          if (!item.hasClass('hover')) {
            e.preventDefault();
            // e.stopPropagation();
            item.siblings().removeClass('hover');
            item.siblings().blur();
            item.closest('.catalog_block').find('.catalog_item').removeClass('hover');
            item.addClass('hover');
            item.addClass('touch');

            var tabsContentHover = th.closest('.tab').attr('data-hover') * 1,
              tabsContentUnhoverHover = th.closest('.tab').attr('data-unhover') * 1;

            th.closest('.tab').stop().animate({'height': tabsContentUnhoverHover}, 100);
            th.closest('.catalog_item').siblings().find('.buttons_block').stop().fadeOut(233)

            th.closest('.tab').fadeTo(100, 1);
            th.closest('.tab').stop().css({'height': tabsContentHover});
            th.closest('.catalog_item').find('.buttons_block').fadeIn(450, 'easeOutCirc');
          }
        })
      })
    }
  }else{
    $(selector).off();
  }*/
}
function touchBasket(selector) {
  if (arAsproOptions["THEME"]["SHOW_BASKET_ONADDTOCART"] !== "N") {
    if ($(window).outerWidth() > 600) {
      $(document)
        .find(selector)
        .on("touchend", function (e) {
          if ($(this).parent().find(".basket_popup_wrapp").length && !$(this).hasClass("hover")) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass("hover");
            $(this).parent().find(".basket_popup_wrapp").slideDown();
          }
        });
    } else {
      $(selector).off();
    }
  }
}

function showTotalSummItem(popup) {
  //show total summ
  if (
    arAsproOptions["THEME"]["SHOW_TOTAL_SUMM_TYPE"] == "ALWAYS" &&
    arAsproOptions["THEME"]["SHOW_TOTAL_SUMM"] == "Y"
  ) {
    var parent = "body ";
    if (typeof popup === "string" && popup == "Y") parent = ".popup ";
    $(parent + ".counter_wrapp .counter_block input.text").each(function () {
      var _th = $(this);
      if (_th.data("product")) {
        var obProduct = _th.data("product");
        if (typeof window[obProduct] == "object") window[obProduct].setPriceAction("Y");
        else setPriceItem(_th.closest(".main_item_wrapper"), _th.val());
      } else setPriceItem(_th.closest(".main_item_wrapper"), _th.val());
    });
  }
}

function initFull() {
  initSelects(document);
  initHoverBlock(document);
  touchItemBlock(".catalog_item a");
  InitOrderCustom();
  showTotalSummItem();
  basketActions();
  orderActions();

  checkMobileRegion();
}

checkMobileRegion = function () {
  if ($(".confirm_region").length) {
    if (!$(".top_mobile_region").length)
      $(
        '<div class="top_mobile_region"><div class="confirm_wrapper"><div class="confirm_region"></div></div></div>'
      ).insertBefore($("#mobileheader"));
    $(".top_mobile_region .confirm_region").html($(".confirm_region").html());

    /*$('.top_mobile_region .close_popup').click(function(){
      $(this).remove();
      $('.confirm_region').remove();
    })*/
  }
};
if (!funcDefined("orderActions")) {
  orderActions = function (e) {
    if (arAsproOptions["PAGES"]["ORDER_PAGE"]) {
      //phone
      if ($("#bx-soa-order input[autocomplete=tel]").length) {
        // get property phone
        for (var i = 0; i < BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties.length; ++i) {
          if (BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties[i].IS_PHONE == "Y") {
            var arPropertyPhone = BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties[i];
          }
        }

        // validate input type=tel
        if (
          typeof BX.Sale.OrderAjaxComponent !== "undefined" &&
          typeof BX.Sale.OrderAjaxComponent === "object" &&
          typeof arPropertyPhone == "object" &&
          arPropertyPhone
        ) {
          BX.Sale.OrderAjaxComponent.validatePhone = function (input, arProperty, fieldName) {
            if (!input || !arProperty) return [];

            var value = input.value,
              errors = [],
              name = BX.util.htmlspecialchars(arProperty.NAME),
              field = BX.message("SOA_FIELD") + ' "' + name + '"',
              re;

            if (arProperty.REQUIRED == "Y" && value.length == 0) {
              errors.push(field + " " + BX.message("SOA_REQUIRED"));
            }

            if (arProperty.IS_PHONE == "Y" && value.length > 0) {
              function regexpPhone(value, element, regexp) {
                var re = new RegExp(regexp);
                return re.test(value);
              }

              var validPhone = regexpPhone($(input).val(), $(input), arAsproOptions["THEME"]["VALIDATE_PHONE_MASK"]);

              if (!validPhone) {
                errors.push(field + " " + BX.message("JS_FORMAT_ORDER"));
              }
            }

            return errors;
          };

          BX.Sale.OrderAjaxComponent.getValidationDataPhone = function (arProperty, propContainer) {
            var data = {},
              inputs;
            switch (arProperty.TYPE) {
              case "STRING":
                data.action = "blur";
                data.func = BX.delegate(function (input, fieldName) {
                  return this.validatePhone(input, arProperty, fieldName);
                }, this);

                inputs = propContainer.querySelectorAll("input[type=tel]");
                if ($(inputs).length) {
                  data.inputs = inputs;
                  break;
                }
            }

            return data;
          };

          BX.Sale.OrderAjaxComponent.bindValidationPhone = function (id, propContainer) {
            if (!this.validation.properties || !this.validation.properties[id]) return;

            var arProperty = this.validation.properties[id],
              data = this.getValidationDataPhone(arProperty, propContainer),
              i,
              k;

            if (data && data.inputs && data.action) {
              for (i = 0; i < $(data.inputs).length; i++) {
                if (BX.type.isElementNode(data.inputs[i])) {
                  BX.bind(
                    data.inputs[i],
                    data.action,
                    BX.delegate(function () {
                      this.isValidProperty(data);
                    }, this)
                  );
                } else {
                  for (k = 0; k < $(data.inputs[i]).length; k++)
                    BX.bind(
                      data.inputs[i][k],
                      data.action,
                      BX.delegate(function () {
                        this.isValidProperty(data);
                      }, this)
                    );
                }
              }
            }
          };

          BX.Sale.OrderAjaxComponent.isValidPropertiesBlock = function (excludeLocation) {
            if (!this.options.propertyValidation) return [];

            var props = this.orderBlockNode.querySelectorAll(".bx-soa-customer-field[data-property-id-row]"),
              propsErrors = [],
              id,
              propContainer,
              arProperty,
              data,
              i;

            for (i = 0; i < props.length; i++) {
              id = props[i].getAttribute("data-property-id-row");

              if (!!excludeLocation && this.locations[id]) continue;

              propContainer = props[i].querySelector(".soa-property-container");
              if (propContainer) {
                arProperty = this.validation.properties[id];
                data = this.getValidationData(arProperty, propContainer);
                dataPhone = this.getValidationDataPhone(arProperty, propContainer);
                data = $.extend({}, data, dataPhone);

                propsErrors = propsErrors.concat(this.isValidProperty(data, true));
              }
            }

            return propsErrors;
          };

          // create input type=tel
          var input = $("input[autocomplete=tel]"),
            inputHTML = input[0].outerHTML,
            value = input.val(),
            newInput = input[0].outerHTML.replace('type="text"', 'type="tel" value="' + value + '"');

          if ($(input).length < 2) {
            input.hide();
            $(newInput).insertAfter(input);
          }
          showPhoneMask("input[autocomplete=tel][type=tel]");

          // change value input type=text when change input type=tel
          $("input[autocomplete=tel][type=tel]").on("blur", function () {
            var $this = $(this);

            var value = $this.val();
            $this.parent().find("input[autocomplete=tel][type=text]").val(value);
          });

          BX.Sale.OrderAjaxComponent.bindValidationPhone(arPropertyPhone.ID, $("input[autocomplete=tel]").parent()[0]);
        }
      }

      if ($(".bx-soa-cart-total").length) {
        if (!$(".change_basket").length)
          $(".bx-soa-cart-total").prepend(
            '<div class="change_basket">' +
              BX.message("BASKET_CHANGE_TITLE") +
              '<a href="' +
              arAsproOptions["SITE_DIR"] +
              'basket/" class="change_link">' +
              BX.message("BASKET_CHANGE_LINK") +
              "</a></div>"
          );

        if (typeof BX.Sale.OrderAjaxComponent == "object") {
          if (arAsproOptions["COUNTERS"]["USE_FULLORDER_GOALS"] !== "N") {
            if (typeof BX.Sale.OrderAjaxComponent.reachgoalbegin === "undefined") {
              BX.Sale.OrderAjaxComponent.reachgoalbegin = true;
              var eventdata = { goal: "goal_order_begin" };
              BX.onCustomEvent("onCounterGoals", [eventdata]);
            }
          }

          if (BX.Sale.OrderAjaxComponent.hasOwnProperty("params")) {
            $(".bx-soa-cart-total .change_link").attr("href", BX.Sale.OrderAjaxComponent.params.PATH_TO_BASKET);
            if (arAsproOptions["PRICES"]["MIN_PRICE"]) {
              if (arAsproOptions["PRICES"]["MIN_PRICE"] > Number(BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_PRICE)) {
                $('<div class="fademask_ext"></div>').appendTo($("body"));
                location.href = BX.Sale.OrderAjaxComponent.params.PATH_TO_BASKET;
              }
            }
          }

          // update oreder auth form
          if ($("#bx-soa-auth").length && !$("#bx-soa-auth .redisigned").length) {
            // update input USER_LOGIN
            if ($('input[name="USER_LOGIN"]').length) {
              var $label = $('input[name="USER_LOGIN"]')
                .closest(".bx-authform-formgroup-container")
                .find(".bx-authform-label-container");
              if (!$label.find(".bx-authform-starrequired").length) {
                $label.html($label.html() + '<span class="bx-authform-starrequired"> *</span>');
              }
            }

            // update input USER_PASSWORD
            if ($('input[name="USER_PASSWORD"]').length) {
              var $label = $('input[name="USER_PASSWORD"]')
                .closest(".bx-authform-formgroup-container")
                .find(".bx-authform-label-container");
              if (!$label.find(".bx-authform-starrequired").length) {
                $label.html($label.html() + '<span class="bx-authform-starrequired"> *</span>');
              }
            }

            if ($('input[name="USER_REMEMBER"]').length) {
              var $label = $('input[name="USER_REMEMBER"]')
                .attr("id", "ORDER_AUTH_USER_REMEMBER")
                .closest("label")
                .attr("for", "ORDER_AUTH_USER_REMEMBER");
              var html = $('input[name="USER_REMEMBER"]')
                .attr("id", "ORDER_AUTH_USER_REMEMBER")
                .wrap('<div id="trem_"></div>')
                .parent()
                .html();
              $("#trem_").remove();
              $label.html($label.text());
              $(html).insertBefore($label).closest(".bx-authform-formgroup-container").addClass("filter");
              var html = $("#bx-soa-auth .bx-authform>a")
                .addClass("pull-right")
                .addClass("forgot")
                .wrap('<div id="trem_"></div>')
                .parent()
                .html();
              $("#trem_").remove();
              $(html).insertAfter($label.closest(".checkbox"));
            }

            $("#bx-soa-auth .bx-soa-reg-block .btn")
              .removeClass("btn-default")
              .removeClass("btn-lg")
              .addClass("transparent")
              .addClass("btn-lg")
              .text(BX.message("ORDER_REGISTER_BUTTON"));

            $("#bx-soa-auth").append('<div class="redisigned hidden></div>');
          }

          // update oreder register form
          if ($(".bx-soa-section-content.reg").length && !$(".bx-soa-section-content.reg .redisigned").length) {
            var bRebindRegSubmit = false;

            if (arAsproOptions.THEME.LOGIN_EQUAL_EMAIL === "Y") {
              bRebindRegSubmit = true;

              // update input NEW_LOGIN
              if ($('input[name="NEW_LOGIN"]').length) {
                $('input[name="NEW_LOGIN"]').closest(".bx-authform-formgroup-container").hide();
              }
            }

            if (arAsproOptions.THEME.PERSONAL_ONEFIO === "Y") {
              bRebindRegSubmit = true;

              // update input NEW_NAME
              if ($('input[name="NEW_NAME"]').length) {
                $('input[name="NEW_NAME"]')
                  .closest(".bx-authform-formgroup-container")
                  .find(".bx-authform-label-container")
                  .html(BX.message("ORDER_FIO_LABEL") + '<span class="bx-authform-starrequired"> *</span>');
              }

              // update input NEW_LAST_NAME
              if ($('input[name="NEW_LAST_NAME"]').length) {
                $('input[name="NEW_LAST_NAME"]').closest(".bx-authform-formgroup-container").hide();
                $('input[name="NEW_LAST_NAME"]').val(" ");
              }
            }

            if (bRebindRegSubmit) {
              // bind new handler for submit button
              var $regSubmit = $("#do_register~input[type=submit]");
              if ($regSubmit.length) {
                BX.unbindAll($regSubmit[0]);
                $(document).on("click", "#do_register~input[type=submit]", function (e) {
                  e.preventDefault();
                  e.stopImmediatePropagation();

                  if (arAsproOptions.THEME.LOGIN_EQUAL_EMAIL === "Y") {
                    var email = BX.findChild(BX("bx-soa-auth"), { attribute: { name: "NEW_EMAIL" } }, true, false);
                    var login = BX.findChild(BX("bx-soa-auth"), { attribute: { name: "NEW_LOGIN" } }, true, false);

                    if (login && email) {
                      login.value = email.value;
                    }
                  }

                  BX("do_register").value = "Y";
                  BX.Sale.OrderAjaxComponent.sendRequest("showAuthForm");
                });
              }
            }

            // update captcha
            var $captcha = $(".bx-soa-section-content.reg").find(".bx-captcha");
            if ($captcha.length) {
              $captcha.addClass("captcha_image");
              $captcha.append('<div class="captcha_reload"></div>');
              $captcha
                .closest(".bx-authform-formgroup-container")
                .addClass("captcha-row")
                .find("input[name=captcha_word]")
                .closest(".bx-authform-input-container")
                .addClass("captcha_input");
            }

            //update show password
            $(".bx-authform-input-container:not(.eye-password-ignore) [type=password]").each(function (item) {
              $(this).closest(".bx-authform-input-container").addClass("eye-password");
            });

            // update input NEW_NAME && NEW_LAST_NAME
            if (
              $("input[name=NEW_NAME]").length &&
              $("input[name=NEW_LAST_NAME]").length &&
              arAsproOptions.THEME.PERSONAL_ONEFIO !== "Y"
            ) {
              if (!$("input[name=NEW_NAME]").closest(".bx-authform-formgroup-container.col-md-6").length) {
                $("input[name=NEW_NAME],input[name=NEW_LAST_NAME]")
                  .closest(".bx-authform-formgroup-container")
                  .addClass("col-md-6");
                var html = $("input[name=NEW_LAST_NAME]")
                  .closest(".bx-authform-formgroup-container")
                  .wrap('<div id="trem_"></div>')
                  .parent()
                  .html();
                $("#trem_").remove();
                $(html).insertAfter(
                  $("input[name=NEW_NAME]").closest(".bx-authform-formgroup-container").wrap('<div class="row"></div>')
                );
              }
            }

            // update input NEW_EMAIL && PHONE_NUMBER
            if ($("input[name=NEW_EMAIL]").length && $("input[name=PHONE_NUMBER]").length) {
              if (!$("input[name=PHONE_NUMBER]").closest(".bx-authform-formgroup-container.col-md-6").length) {
                $("input[name=NEW_EMAIL],input[name=PHONE_NUMBER]")
                  .closest(".bx-authform-formgroup-container")
                  .addClass("col-md-6");
                var html = $("input[name=PHONE_NUMBER]")
                  .closest(".bx-authform-formgroup-container")
                  .wrap('<div id="trem_"></div>')
                  .parent()
                  .html();
                $("#trem_").remove();
                $(html).insertAfter(
                  $("input[name=NEW_EMAIL]").closest(".bx-authform-formgroup-container").wrap('<div class="row"></div>')
                );
              }
            }

            // update input NEW_PASSWORD && NEW_PASSWORD_CONFIRM
            if ($("input[name=NEW_PASSWORD]").length && $("input[name=NEW_PASSWORD_CONFIRM]").length) {
              if (!$("input[name=NEW_PASSWORD]").closest(".bx-authform-formgroup-container.col-md-6").length) {
                $("input[name=NEW_PASSWORD],input[name=NEW_PASSWORD_CONFIRM]")
                  .closest(".bx-authform-formgroup-container")
                  .addClass("col-md-6");
                var html = $("input[name=NEW_PASSWORD_CONFIRM]")
                  .closest(".bx-authform-formgroup-container")
                  .wrap('<div id="trem_"></div>')
                  .parent()
                  .html();
                $("#trem_").remove();
                $(html).insertAfter(
                  $("input[name=NEW_PASSWORD]")
                    .closest(".bx-authform-formgroup-container")
                    .wrap('<div class="row"></div>')
                );
              }
            }

            // update input PHONE_NUMBER
            if ($("input[name=PHONE_NUMBER]").length) {
              var input = $("input[name=PHONE_NUMBER]"),
                inputHTML = input[0].outerHTML,
                value = input.val(),
                newInput = input[0].outerHTML.replace('type="text"', 'type="tel" value="' + value + '"');

              if ($(input).length < 2) {
                input.hide();
                $(newInput).insertAfter(input);

                showPhoneMask("input[name=PHONE_NUMBER][type=tel]");

                $("input[name=PHONE_NUMBER][type=tel]").on("blur", function () {
                  var $this = $(this);
                  var value = $this.val();
                  $this.parent().find("input[name=PHONE_NUMBER][type=text]").val(value);
                });

                var $label = $("input[name=PHONE_NUMBER][type=tel]")
                  .closest(".bx-authform-formgroup-container")
                  .find(".bx-authform-label-container");
                $label.html(
                  BX.message("ORDER_PHONE_LABEL") +
                    ($label.find(".bx-authform-starrequired").length
                      ? '<span class="bx-authform-starrequired"> *</span>'
                      : "")
                );
              }
            }

            $(".bx-soa-section-content.reg").append('<div class="redisigned hidden></div>');
          }

          var asproShowLicence = arAsproOptions["THEME"]["SHOW_LICENCE"] == "Y";
          var asproShowOffer = arAsproOptions["THEME"]["SHOW_OFFER"] == "Y";

          if ($(".bx-soa-cart-total-line-total").length && (asproShowLicence || asproShowOffer)) {
            if (typeof e === "undefined") {
              BX.Sale.OrderAjaxComponent.state_licence =
                arAsproOptions["THEME"]["LICENCE_CHECKED"] == "Y" ? "checked" : "";
              BX.Sale.OrderAjaxComponent.state_offer = arAsproOptions["THEME"]["OFFER_CHECKED"] == "Y" ? "checked" : "";
            }

            if (
              (!$(".licence_block.filter").length && asproShowLicence) ||
              (!$(".offer_block.filter").length && asproShowOffer)
            ) {
              $('<div class="form"><div class="license_order_wrap"></div></div>').insertBefore($("#bx-soa-orderSave"));

              if (!$(".licence_block.filter").length && asproShowLicence)
                $(
                  '<div class="licence_block filter label_block onoff"><label data-for="licenses_order" class="hidden error">' +
                    BX.message("JS_REQUIRED_LICENSES") +
                    '</label><input type="checkbox" name="licenses_order" required ' +
                    BX.Sale.OrderAjaxComponent.state_licence +
                    ' value="Y"><label data-for="licenses_order" class="license">' +
                    BX.message("LICENSES_TEXT") +
                    "</label></div>"
                ).appendTo($(".license_order_wrap"));

              if (!$(".offer_block.filter").length && asproShowOffer)
                $(
                  '<div class="offer_block filter label_block onoff"><label data-for="offer_order" class="hidden error">' +
                    BX.message("JS_REQUIRED_OFFER") +
                    '</label><input type="checkbox" name="offer_order" required ' +
                    BX.Sale.OrderAjaxComponent.state_offer +
                    ' value="Y"><label data-for="offer_order" class="offer_pub">' +
                    BX.message("OFFER_TEXT") +
                    "</label></div>"
                ).appendTo($(".license_order_wrap"));

              if (asproShowLicence) {
                $(document).on("click", ".bx-soa .licence_block label.license", function () {
                  var id = $(this).data("for");
                  $(".bx-soa .licence_block label.error").addClass("hidden");
                  if (!$("input[name=" + id + "]").prop("checked")) {
                    $("input[name=" + id + "]").prop("checked", "checked");
                    BX.Sale.OrderAjaxComponent.state_licence = "checked";
                  } else {
                    $("input[name=" + id + "]").prop("checked", "");
                    BX.Sale.OrderAjaxComponent.state_licence = "";
                  }
                });
              }

              if (asproShowOffer) {
                $(document).on("click", ".bx-soa .offer_block label.offer_pub", function () {
                  var id = $(this).data("for");
                  $(".bx-soa .offer_block label.error").addClass("hidden");
                  if (!$("input[name=" + id + "]").prop("checked")) {
                    $("input[name=" + id + "]").prop("checked", "checked");
                    BX.Sale.OrderAjaxComponent.state_offer = "checked";
                  } else {
                    $("input[name=" + id + "]").prop("checked", "");
                    BX.Sale.OrderAjaxComponent.state_offer = "";
                  }
                });
              }

              $(document).on("click", ".lic_condition a", function () {
                if (BX.hasClass(BX("bx-soa-order"), "orderform--v1")) {
                  if (BX.Sale.OrderAjaxComponent.isValidForm()) {
                    BX.Sale.OrderAjaxComponent.animateScrollTo($(".licence_block, .offer_block")[0], 800, 50);
                  }
                } else {
                  var iCountErrors = BX.Sale.OrderAjaxComponent.isValidPropertiesBlock().length;
                  if (!BX.Sale.OrderAjaxComponent.activeSectionId || !iCountErrors) {
                    BX.Sale.OrderAjaxComponent.animateScrollTo($(".licence_block, .offer_block")[0], 800, 50);
                  }
                }
              });
            }

            $("#bx-soa-orderSave, .bx-soa-cart-total-button-container").addClass("lic_condition");

            if (
              typeof BX.Sale.OrderAjaxComponent.oldClickOrderSaveAction === "undefined" &&
              typeof BX.Sale.OrderAjaxComponent.clickOrderSaveAction !== "undefined"
            ) {
              BX.Sale.OrderAjaxComponent.oldClickOrderSaveAction = BX.Sale.OrderAjaxComponent.clickOrderSaveAction;
              BX.Sale.OrderAjaxComponent.clickOrderSaveAction = function (event) {
                if (
                  ($('input[name="licenses_order"]').prop("checked") || !asproShowLicence) &&
                  ($('input[name="offer_order"]').prop("checked") || !asproShowOffer)
                ) {
                  $(".bx-soa .licence_block label.error").addClass("hidden");
                  $(".bx-soa .offer_block label.error").addClass("hidden");

                  if (BX.Sale.OrderAjaxComponent.isValidForm()) {
                    if (typeof BX.Sale.OrderAjaxComponent.allowOrderSave == "function")
                      BX.Sale.OrderAjaxComponent.allowOrderSave();
                    if (typeof BX.Sale.OrderAjaxComponent.doSaveAction == "function")
                      BX.Sale.OrderAjaxComponent.doSaveAction();
                    else BX.Sale.OrderAjaxComponent.oldClickOrderSaveAction(event);
                  }
                } else {
                  if (!$('input[name="licenses_order"]').prop("checked"))
                    $(".bx-soa .licence_block label.error").removeClass("hidden");

                  if (!$('input[name="offer_order"]').prop("checked"))
                    $(".bx-soa .offer_block label.error").removeClass("hidden");
                }
              };
              if (BX.Sale.OrderAjaxComponent.orderSaveBlockNode.querySelector(".checkbox")) {
                if (typeof browser == "object") {
                  if ("msie" in browser && browser.msie)
                    $(BX.Sale.OrderAjaxComponent.orderSaveBlockNode.querySelector(".checkbox")).remove();
                  else BX.Sale.OrderAjaxComponent.orderSaveBlockNode.querySelector(".checkbox").remove();
                }
              }
              BX.unbindAll(BX.Sale.OrderAjaxComponent.totalInfoBlockNode.querySelector("a.btn-order-save"));
              BX.unbindAll(BX.Sale.OrderAjaxComponent.mobileTotalBlockNode.querySelector("a.btn-order-save"));
              BX.unbindAll(BX.Sale.OrderAjaxComponent.orderSaveBlockNode.querySelector("a"));
              BX.bind(
                BX.Sale.OrderAjaxComponent.totalInfoBlockNode.querySelector("a.btn-order-save"),
                "click",
                BX.proxy(BX.Sale.OrderAjaxComponent.clickOrderSaveAction, BX.Sale.OrderAjaxComponent)
              );
              BX.bind(
                BX.Sale.OrderAjaxComponent.mobileTotalBlockNode.querySelector("a.btn-order-save"),
                "click",
                BX.proxy(BX.Sale.OrderAjaxComponent.clickOrderSaveAction, BX.Sale.OrderAjaxComponent)
              );
              BX.bind(
                BX.Sale.OrderAjaxComponent.orderSaveBlockNode.querySelector("a"),
                "click",
                BX.proxy(BX.Sale.OrderAjaxComponent.clickOrderSaveAction, BX.Sale.OrderAjaxComponent)
              );
            }
          }

          // fix hide total block
          $(window).scroll();

          if (checkCounters() && typeof BX.Sale.OrderAjaxComponent.oldSaveOrder === "undefined") {
            var saveFunc =
              typeof BX.Sale.OrderAjaxComponent.saveOrder !== "undefined" ? "saveOrder" : "saveOrderWithJson";
            if (typeof BX.Sale.OrderAjaxComponent[saveFunc] !== "undefined") {
              BX.Sale.OrderAjaxComponent.oldSaveOrder = BX.Sale.OrderAjaxComponent[saveFunc];
              BX.Sale.OrderAjaxComponent[saveFunc] = function (result) {
                var res = BX.parseJSON(result);
                if (res && res.order) {
                  if (!res.order.SHOW_AUTH) {
                    if (
                      res.order.REDIRECT_URL &&
                      res.order.REDIRECT_URL.length &&
                      (!res.order.ERROR || BX.util.object_keys(res.order.ERROR).length < 1)
                    ) {
                      if (
                        (arMatch = res.order.REDIRECT_URL.match(/ORDER_ID\=[^&=]*/g)) &&
                        arMatch.length &&
                        (_id = arMatch[0].replace(/ORDER_ID\=/g, "", arMatch[0]))
                      ) {
                        $.ajax({
                          url: arAsproOptions["SITE_DIR"] + "ajax/check_order.php",
                          dataType: "json",
                          type: "POST",
                          data: { ID: _id },
                          success: function (id) {
                            if (parseInt(id)) {
                              purchaseCounter(parseInt(id), BX.message("FULL_ORDER"), function (d) {
                                if (typeof localStorage !== "undefined" && typeof d === "object") {
                                  localStorage.setItem("gtm_e_" + _id, JSON.stringify(d));
                                }
                                BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                              });
                            } else {
                              BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                            }
                          },
                          error: function () {
                            BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                          },
                        });
                      } else {
                        BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                      }
                    } else {
                      BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                    }
                  } else {
                    BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                  }
                } else {
                  BX.Sale.OrderAjaxComponent.oldSaveOrder(result);
                }
              };
            }
          }

          if ($("#bx-soa-order-form .captcha-row").length) {
            if (
              window.asproRecaptcha &&
              window.asproRecaptcha.key &&
              window.asproRecaptcha.params.recaptchaSize == "invisible"
            ) {
              $("#bx-soa-order-form .captcha-row").addClass("invisible");
              if (asproRecaptcha.params.recaptchaLogoShow === "n") {
                $("#bx-soa-order-form .captcha-row").addClass("logo_captcha_n");
              }
            }
          }

          if ($("#bx-soa-order-form .captcha-row.invisible").length) {
            if (
              typeof BX.Sale.OrderAjaxComponent.oldSendRequest === "undefined" &&
              typeof BX.Sale.OrderAjaxComponent.sendRequest !== "undefined"
            ) {
              var tmpAction, tmpActionData;
              BX.Sale.OrderAjaxComponent.oldSendRequest = BX.Sale.OrderAjaxComponent.sendRequest;
              BX.Sale.OrderAjaxComponent.sendRequest = function (action, actionData) {
                var bSend = true;

                if ($("#bx-soa-order-form .captcha-row.invisible").length) {
                  if (window.renderRecaptchaById && window.asproRecaptcha && window.asproRecaptcha.key) {
                    if (window.asproRecaptcha.params.recaptchaSize == "invisible") {
                      var form = BX("bx-soa-order-form");
                      if ($(form).find(".g-recaptcha").length) {
                        if ($(form).find(".g-recaptcha-response").val()) {
                          bSend = true;
                        } else {
                          if (typeof grecaptcha != "undefined") {
                            grecaptcha.execute($(form).find(".g-recaptcha").data("widgetid"));
                            bSend = false;
                          } else {
                            bSend = false;
                          }
                        }
                      }
                    }
                  }
                }

                if (bSend) {
                  BX.Sale.OrderAjaxComponent.oldSendRequest(action, actionData);
                } else {
                  tmpAction = action;
                  tmpActionData = actionData;
                }
              };

              $(document).on("submit", "#bx-soa-order-form", function (e) {
                e.preventDefault();

                if (typeof tmpAction !== "undefined" || typeof tmpActionData !== "undefined") {
                  BX.Sale.OrderAjaxComponent.sendRequest(tmpAction, tmpActionData);
                  tmpAction = undefined;
                  tmpActionData = undefined;
                }
              });
            }
          }
        }

        $(".bx-ui-sls-quick-locations.quick-locations").on("click", function () {
          $(this).siblings().removeClass("active");
          $(this).addClass("active");
        });
      }
    }
  };
}

if (!funcDefined("basketActions")) {
  basketActions = function () {
    if (arAsproOptions["PAGES"]["BASKET_PAGE"]) {
      checkMinPrice();

      //remove4Cart
      if (typeof BX.Sale !== "undefined" && typeof BX.Sale === "object") {
        if (typeof BX.Sale.BasketComponent !== "undefined" && typeof BX.Sale.BasketComponent === "object") {
          $(document).on("click", ".basket-item-actions-remove", function () {
            var basketID = $(this).closest(".basket-items-list-item-container").data("id");
            if (BX.Sale.BasketComponent.items && BX.Sale.BasketComponent.items[basketID]) {
              delFromBasketCounter(BX.Sale.BasketComponent.items[basketID].PRODUCT_ID);
            }
          });
        }
      }

      if (location.hash) {
        var hash = location.hash.substring(1);
        if ($("#basket_toolbar_button_" + hash).length) $("#basket_toolbar_button_" + hash).trigger("click");

        if ($('.basket-items-list-header-filter a[data-filter="' + hash + '"]').length)
          $('.basket-items-list-header-filter a[data-filter="' + hash + '"]')[0].click();
      }
      var svg_cross =
        '<svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 8 8"><path id="Rounded_Rectangle_568_copy_13" data-name="Rounded Rectangle 568 copy 13" class="cls-1" d="M1615.4,589l2.32,2.315a0.987,0.987,0,0,1,0,1.4,1,1,0,0,1-1.41,0L1614,590.4l-2.31,2.315a1,1,0,0,1-1.41,0,0.987,0.987,0,0,1,0-1.4L1612.6,589l-2.32-2.314a0.989,0.989,0,0,1,0-1.4,1,1,0,0,1,1.41,0l2.31,2.315,2.31-2.315a1,1,0,0,1,1.41,0,0.989,0.989,0,0,1,0,1.4Z" transform="translate(-1610 -585)"/></svg>';

      $(".bx_sort_container").append(
        '<div class="top_control basket_action"><span style="opacity:0;" class="delete_all colored_theme_hover_text remove_all_basket">' +
          svg_cross +
          BX.message("BASKET_CLEAR_ALL_BUTTON") +
          "</span></div>"
      );
      if ($(".basket-items-list-header-filter").length) {
        $(".basket-items-list-header-filter").append(
          '<div class="top_control basket_action"><span style="opacity:1;" class="delete_all colored_theme_hover_text remove_all_basket">' +
            svg_cross +
            BX.message("BASKET_CLEAR_ALL_BUTTON") +
            "</span></div>"
        );

        var cur_index = $(".basket-items-list-header-filter > a.active").index();
        //fix delayed
        if (cur_index == 3) cur_index = 2;

        if ($(".basket-items-list-header-filter > a.active").data("filter") == "all") cur_index = "all";

        $(".basket-items-list-header-filter .top_control .delete_all").data("type", cur_index);

        $(".basket-items-list-header-filter > a").on("click", function () {
          var index = $(this).index();

          //fix delayed
          if (index == 3) index = 2;

          if ($(this).data("filter") == "all") index = "all";

          $(".basket-items-list-header-filter .top_control .delete_all").data("type", index);
        });
      } else {
        var cur_index = $(".bx_sort_container a.current").index();
        $(".bx_sort_container .top_control .delete_all").data("type", cur_index);
        if ($(".bx_ordercart > div:eq(" + cur_index + ") table tbody tr td.item").length)
          $(".bx_sort_container .top_control .delete_all").css("opacity", 1);

        $(".bx_ordercart .bx_ordercart_coupon #coupon").wrap('<div class="input"></div>');

        $(".bx_sort_container > a").on("click", function () {
          var index = $(this).index();
          $(".bx_sort_container .top_control .delete_all").data("type", index);

          if ($(".bx_ordercart > div:eq(" + index + ") table tbody tr td.item").length)
            $(".bx_sort_container .top_control .delete_all").css("opacity", 1);
          else $(".bx_sort_container .top_control .delete_all").css("opacity", 0);
        });
      }

      $(".basket_print").on("click", function () {
        // window.open(location.pathname+"?print=Y",'_blank');
        window.print();
      });

      $(".delete_all").on("click", function () {
        if (arAsproOptions["COUNTERS"]["USE_BASKET_GOALS"] !== "N") {
          var eventdata = { goal: "goal_basket_clear", params: { type: $(this).data("type") } };
          BX.onCustomEvent("onCounterGoals", [eventdata]);
        }
        $.post(
          arAsproOptions["SITE_DIR"] + "ajax/action_basket.php",
          "TYPE=" + $(this).data("type") + "&CLEAR_ALL=Y",
          $.proxy(function (data) {
            location.reload();
          })
        );
      });

      $(".bx_item_list_section .bx_catalog_item").sliceHeight({ row: ".bx_item_list_slide", item: ".bx_catalog_item" });

      BX.addCustomEvent("onAjaxSuccess", function (e) {
        checkMinPrice();

        var errorText = $.trim($("#warning_message").text());
        $("#basket_items_list .error_text").detach();
        if (errorText != "") {
          $("#warning_message").hide().text("");
          $("#basket_items_list").prepend('<div class="error_text">' + errorText + "</div>");
        }

        if (typeof e === "object" && e && "BASKET_DATA" in e) {
          if ($("#ajax_basket").length) {
            reloadTopBasket("add", $("#ajax_basket"), 200, 5000, "Y");
          }
          if ($("#basket_line .basket_fly").length) {
            basketFly("open", "N");
          }
        }
        if (checkCounters("google")) {
          BX.unbindAll(BX.Sale.BasketComponent.getEntity(BX.Sale.BasketComponent.getCacheNode(BX.Sale.BasketComponent.ids.basketRoot),'basket-checkout-button'));
        }
      });
      if (checkCounters("google")) {
        BX.unbindAll(BX.Sale.BasketComponent.getEntity(BX.Sale.BasketComponent.getCacheNode(BX.Sale.BasketComponent.ids.basketRoot),'basket-checkout-button'));
      }
      $(document).on(
        "click",
        ".bx_ordercart_order_pay_center .checkout, .basket-checkout-section-inner .basket-btn-checkout",
        function () {
          if (checkCounters("google")) {
            const gotoOrder = function(){
              BX.Sale.BasketComponent.checkOutAction()
            }
            checkoutCounter(1, "start order", gotoOrder);
          }
        }
      );
    }
  };
}

if (!funcDefined("checkMinPrice")) {
  checkMinPrice = function () {
    if (arAsproOptions["PAGES"]["BASKET_PAGE"]) {
      var summ_raw = 0,
        summ = 0;
      if ($("#allSum_FORMATED").length) {
        summ_raw = $("#allSum_FORMATED")
          .text()
          .replace(/[^0-9\.,]/g, "");
        summ = parseFloat(summ_raw);
        if ($("#basket_items").length) {
          var summ = 0;
          $("#basket_items tr").each(function () {
            if (typeof $(this).data("item-price") !== "undefined" && $(this).data("item-price"))
              summ +=
                $(this).data("item-price") *
                $(this)
                  .find("#QUANTITY_INPUT_" + $(this).attr("id"))
                  .val();
          });
        }
        if (!$(".catalog_back").length)
          $(".bx_ordercart_order_pay_center").prepend(
            '<a href="' +
              arAsproOptions["PAGES"]["CATALOG_PAGE_URL"] +
              '" class="catalog_back btn btn-default btn-lg white grey">' +
              BX.message("BASKET_CONTINUE_BUTTON") +
              "</a>"
          );
      }

      if (arAsproOptions["THEME"]["SHOW_ONECLICKBUY_ON_BASKET_PAGE"] == "Y")
        $(".basket-coupon-section").addClass("smallest");

      if (typeof BX.Sale !== "undefined") {
        if (typeof BX.Sale.BasketComponent !== "undefined" && typeof BX.Sale.BasketComponent.result !== "undefined")
          summ = BX.Sale.BasketComponent.result.allSum;
      }

      if (arAsproOptions["PRICES"]["MIN_PRICE"]) {
        if (arAsproOptions["PRICES"]["MIN_PRICE"] > summ) {
          var svgMinPrice =
            '<i class="svg  svg-inline-price colored_theme_svg" aria-hidden="true"><svg id="Group_278_copy" data-name="Group 278 copy" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"><path id="Ellipse_305_copy_2" data-name="Ellipse 305 copy 2" class="clswm-1" d="M1851,561a19,19,0,1,1,19-19A19,19,0,0,1,1851,561Zm0-36a17,17,0,1,0,17,17A17,17,0,0,0,1851,525Zm3.97,10.375-0.03.266c-0.01.062-.02,0.127-0.03,0.188l-0.94,7.515h0a2.988,2.988,0,0,1-5.94,0H1848l-0.91-7.525c-0.01-.041-0.01-0.086-0.02-0.128l-0.04-.316h0.01c-0.01-.125-0.04-0.246-0.04-0.375a4,4,0,0,1,8,0c0,0.129-.03.25-0.04,0.375h0.01ZM1851,533a2,2,0,0,0-2,2,1.723,1.723,0,0,0,.06.456L1850,543a1,1,0,0,0,2,0l0.94-7.544A1.723,1.723,0,0,0,1853,535,2,2,0,0,0,1851,533Zm0,14a3,3,0,1,1-3,3A3,3,0,0,1,1851,547Zm0,4a1,1,0,1,0-1-1A1,1,0,0,0,1851,551Z" transform="translate(-1832 -523)"></path>  <path class="clswm-2 op-cls" d="M1853,543l-1,1h-2l-1-1-1-8,1-2,1-1h2l1,1,1,2Zm-1,5,1,1v2l-1,1h-2l-1-1v-2l1-1h2Z" transform="translate(-1832 -523)"></path></svg></i>';
          if ($(".oneclickbuy.fast_order").length) $(".oneclickbuy.fast_order").remove();

          if ($(".basket-checkout-container").length) {
            if (!$(".icon_error_wrapper").length) {
              $(".basket-checkout-block.basket-checkout-block-btn").html(
                '<div class="icon_error_wrapper"><div class="icon_error_block">' +
                  svgMinPrice +
                  BX.message("MIN_ORDER_PRICE_TEXT").replace(
                    "#PRICE#",
                    jsPriceFormat(arAsproOptions["PRICES"]["MIN_PRICE"])
                  ) +
                  "</div></div>"
              );
            }
          } else {
            if (!$(".icon_error_wrapper").length && typeof jsPriceFormat !== "undefined") {
              $(".bx_ordercart_order_pay_center").prepend(
                '<div class="icon_error_wrapper"><div class="icon_error_block">' +
                  svgMinPrice +
                  BX.message("MIN_ORDER_PRICE_TEXT").replace(
                    "#PRICE#",
                    jsPriceFormat(arAsproOptions["PRICES"]["MIN_PRICE"])
                  ) +
                  "</div></div>"
              );
            }
            if ($(".bx_ordercart_order_pay .checkout").length) $(".bx_ordercart_order_pay .checkout").remove();
          }
        } else {
          if ($(".icon_error_wrapper").length) $(".icon_error_wrapper").remove();

          if ($(".basket-checkout-container").length) {
            if (
              !$(".oneclickbuy.fast_order").length &&
              arAsproOptions["THEME"]["SHOW_ONECLICKBUY_ON_BASKET_PAGE"] == "Y" &&
              !$(".basket-btn-checkout.disabled").length
            )
              $(".basket-checkout-section-inner").append(
                '<div class="fastorder"><span class="oneclickbuy btn btn-lg fast_order btn-transparent-border-color" onclick="oneClickBuyBasket()">' +
                  BX.message("BASKET_QUICK_ORDER_BUTTON") +
                  "</span></div>"
              );
          } else {
            if ($(".bx_ordercart_order_pay .checkout").length)
              $(".bx_ordercart .bx_ordercart_order_pay .checkout").css("opacity", "1");
            else
              $(".bx_ordercart_order_pay_center").append(
                '<a href="javascript:void(0)" onclick="checkOut();" class="checkout" style="opacity: 1;">' +
                  BX.message("BASKET_ORDER_BUTTON") +
                  "</a>"
              );
            if (
              !$(".oneclickbuy.fast_order").length &&
              arAsproOptions["THEME"]["SHOW_ONECLICKBUY_ON_BASKET_PAGE"] == "Y"
            )
              $(".bx_ordercart_order_pay_center").append(
                '<span class="oneclickbuy btn btn-lg fast_order btn-transparent-border-color" onclick="oneClickBuyBasket()">' +
                  BX.message("BASKET_QUICK_ORDER_BUTTON") +
                  "</span>"
              );
          }
        }
      } else {
        if ($(".basket-checkout-container").length) {
          if (
            !$(".oneclickbuy.fast_order").length &&
            arAsproOptions["THEME"]["SHOW_ONECLICKBUY_ON_BASKET_PAGE"] == "Y" &&
            !$(".basket-btn-checkout.disabled").length
          )
            $(".basket-checkout-section-inner").append(
              '<div class="fastorder"><span class="oneclickbuy btn btn-lg fast_order btn-transparent-border-color" onclick="oneClickBuyBasket()">' +
                BX.message("BASKET_QUICK_ORDER_BUTTON") +
                "</span></div>"
            );
        } else {
          $(".bx_ordercart .bx_ordercart_order_pay .checkout").css("opacity", "1");
          if (!$(".oneclickbuy.fast_order").length && arAsproOptions["THEME"]["SHOW_ONECLICKBUY_ON_BASKET_PAGE"] == "Y")
            $(".bx_ordercart_order_pay_center").append(
              '<span class="oneclickbuy btn btn-lg fast_order btn-transparent-border-color" onclick="oneClickBuyBasket()">' +
                BX.message("BASKET_QUICK_ORDER_BUTTON") +
                "</span>"
            );
        }
      }

      showBasketShareBtn();
      showBasketHeadingBtn();

      $("#basket-root .basket-checkout-container .basket-checkout-section .basket-checkout-block .basket-btn-checkout");
      $("#basket-root .basket-checkout-container").addClass("visible");
    }
  };
}

var isFrameDataReceived = false;
if (typeof window.frameCacheVars !== "undefined") {
  BX.addCustomEvent(window, "onFrameDataRequestFail", function (response) {
    console.log(response);
  });

  BX.addCustomEvent("onFrameDataReceivedBefore", function () {
    // stop ya metrika webvisor DOM indexer
    pauseYmObserver();
  });

  BX.addCustomEvent("onFrameDataReceived", function (json) {
    initFull();

    CheckTopMenuPadding();
    CheckTopMenuOncePadding();
    CheckTopMenuDotted();

    CheckSearchWidth();

    checkLinkedArticles();
    checkLinkedBlocks(".linked-banners-list");

    if ($(".logo-row.v2").length) {
      $(window).resize(); // need to check resize flexslider & menu
      setTimeout(function () {
        CheckTopMenuDotted();
      }, 100);
    }

    if (funcDefined("setNewHeader")) {
      if (typeof BX === "object" && (BX.message("TYPE_SKU") != "TYPE_1" || BX.message("HAS_SKU_PROPS") != "Y")) {
        setNewHeader();
      } else {
        var objNameWrapper = $(".bx_catalog_item_scu[id]");
        if (objNameWrapper.length) {
          var id = "ob" + objNameWrapper.attr("id").replace("_skudiv", "");
          if (id && window[id] !== undefined) {
            var obj = window[id].offers[window[id].offerNum];
            if (obj !== undefined) {
              setNewHeader(obj);
            }
          }
        }
      }
    }

    // resume ya metrika webvisor
    // (100ms sliceHeight) + (200ms mCustomScroll) + (100ms lazyload)
    setTimeout(resumeYmObserver, 400);

    isFrameDataReceived = true;
  });
} else {
  $(document).ready(initFull);
}

if (!funcDefined("setHeightBlockSlider")) {
  setHeightBlockSlider = function () {
    $(document).find(".specials.tab_slider_wrapp .tabs_content > li.cur").equalize({ children: ".item-title" });
    $(document).find(".specials.tab_slider_wrapp .tabs_content > li.cur").equalize({ children: ".item_info" });
    $(document).find(".specials.tab_slider_wrapp .tabs_content > li.cur").equalize({ children: ".catalog_item" });

    var sliderWidth = $(document).find(".specials.tab_slider_wrapp").outerWidth();

    var iCountTabs = $(document).find(".specials.tab_slider_wrapp .tabs_content > li.cur").length;

    if (iCountTabs <= 1) {
      $(document).find(".specials.tab_slider_wrapp .tabs_content > li.cur").css("height", "");

      var itemsButtonsHeight = 0;
      if ($(document).find(".specials.tab_slider_wrapp .tabs_content .tab.cur .tabs_slider li .footer_button").length) {
        $(document)
          .find(".specials.tab_slider_wrapp .tabs_content .tab.cur .tabs_slider li .footer_button")
          .css("height", "auto");
        itemsButtonsHeight = $(document)
          .find(".specials.tab_slider_wrapp .tabs_content .tab.cur .tabs_slider li .footer_button")
          .height();
        $(document)
          .find(".specials.tab_slider_wrapp .tabs_content .tab.cur .tabs_slider li .footer_button")
          .css("height", "");
      }
      var tabsContentUnhover = $(document).find(".specials.tab_slider_wrapp .tabs_content .tab.cur").height() * 1;
      var tabsContentHover = tabsContentUnhover + itemsButtonsHeight + 50;
      $(document).find(".specials.tab_slider_wrapp .tabs_content .tab.cur").attr("data-unhover", tabsContentUnhover);
      $(document).find(".specials.tab_slider_wrapp .tabs_content .tab.cur").attr("data-hover", tabsContentHover);
      $(document).find(".specials.tab_slider_wrapp .tabs_content").height(tabsContentUnhover);
      $(document).find(".specials.tab_slider_wrapp .tabs_content .tab.cur .flex-viewport").height(tabsContentUnhover);
    } else {
      $(document)
        .find(".specials.tab_slider_wrapp .tabs_content > li.cur")
        .each(function () {
          var _th = $(this);
          _th.css("height", "");

          var itemsButtonsHeight = 0;
          if (_th.find(".tabs_slider li .footer_button").length) {
            _th.find(".tabs_slider li .footer_button").css("height", "auto");
            itemsButtonsHeight = _th.find(".tabs_slider li .footer_button").height();
            _th.find(".tabs_slider li .footer_button").css("height", "");
          }

          var tabsContentUnhover = _th.height() * 1;
          var tabsContentHover = tabsContentUnhover + itemsButtonsHeight + 50;
          _th.attr("data-unhover", tabsContentUnhover);
          _th.attr("data-hover", tabsContentHover);
          _th.parent().height(tabsContentUnhover);
          _th.find(".flex-viewport").height(tabsContentUnhover);
        });
    }
  };
}

if (!funcDefined("checkTopFilter")) {
  checkTopFilter = function () {
    /*if(window.matchMedia('(max-width: 991px)').matches){
      //if($('.group_description_block.top').length){
        var top_pos=$('.adaptive_filter').position().top;
        $('.bx_filter.bx_filter_vertical').css({'top':top_pos+43});
      //}
    }*/
  };
}

if (!funcDefined("checkStickyFooter")) {
  checkStickyFooter = function () {
    try {
      ignoreResize.push(true);
      $("#content").css("min-height", "");
      var contentTop = $("#content").offset().top;
      var contentBottom = contentTop + $("#content").outerHeight();
      var footerTop = 0;
      if ($("footer").length) footerOffset = $("footer").offset().top;

      $("#content").css(
        "min-height",
        $(window).height() - contentTop - (footerTop - contentBottom) - $("footer").outerHeight() + "px"
      );
      ignoreResize.pop();
    } catch (e) {
      console.error(e);
    }
  };
}

if (!funcDefined("checkLinkedArticles")) {
  checkLinkedArticles = function () {
    try {
      if ($(".linked-blog-list.content .item-views").length) {
        var mobileRow = $(".linked-blog-list").data("mobile_row"),
          desktopRow = $(".linked-blog-list").data("desktop_row"),
          parentBlock = $(".ajax_load .js_append"),
          widthRow = parentBlock.getFloatWidth(),
          elementWidthRow = parentBlock.find("> .item:eq(0)").getFloatWidth(),
          elementsCount = parentBlock.find("> .item").length,
          slice = Math.floor(widthRow / elementWidthRow),
          rowInsertCount = Math.floor(elementsCount / slice),
          bCalculateRows = false;
        if (window.matchMedia("(max-width: 767px)").matches) {
          if (!mobileRow && desktopRow) {
            mobileRow = desktopRow;
          }
          if (mobileRow && mobileRow <= rowInsertCount) {
            rowInsertCount = slice * mobileRow;
            bCalculateRows = true;
          }
        } else {
          if (desktopRow && desktopRow <= rowInsertCount) {
            rowInsertCount = slice * desktopRow;
            bCalculateRows = true;
          }
        }
        if (!bCalculateRows) {
          rowInsertCount = elementsCount;
        }

        var linkedBlock = ".linked-blog-list";
        var blockInItems = parentBlock.find(linkedBlock);
        if (blockInItems.length) {
          $(blockInItems).insertAfter(parentBlock.find("> .item:eq(" + (rowInsertCount - 1) + ")"));
          setTimeout(function () {
            $(blockInItems).addClass("visible");
          }, 0);
        } else {
          var cloneBlock = $(linkedBlock).clone();
          var owlBlock = cloneBlock.find(".owl-carousel-wait");
          cloneBlock.insertAfter(parentBlock.find("> .item:eq(" + (rowInsertCount - 1) + ")"));
          owlBlock.removeClass("owl-carousel-wait").addClass("owl-carousel");
          setTimeout(function () {
            cloneBlock.addClass("visible");
            InitOwlSlider();
            owlBlock.removeClass("loader_circle");
          }, 1);
        }

        // $('.linked-blog-list').insertAfter(parentBlock.find('> .item:eq('+(rowInsertCount-1)+')'));
        // setTimeout(function(){
        // 	$('.linked-blog-list').addClass('visible');
        // }, 0);
      }
    } catch (e) {
      console.error(e);
    }
  };
}

if (!funcDefined("checkLinkedBlocks")) {
  checkLinkedBlocks = function (linkedBlock) {
    if (!linkedBlock) linkedBlock = ".linked-banners-list";

    try {
      if ($(linkedBlock + ".content" + " div").length) {
        var mobileRow = $(linkedBlock).data("mobile_row"),
          desktopRow = $(linkedBlock).data("desktop_row"),
          parentBlock = $(".ajax_load .js_append"),
          widthRow = parentBlock.getFloatWidth(),
          elementWidthRow = parentBlock.find("> .item:eq(0)").getFloatWidth(),
          elementsCount = parentBlock.find("> .item").length,
          slice = Math.floor(widthRow / elementWidthRow),
          rowInsertCount = Math.floor(elementsCount / slice),
          bCalculateRows = false;
        if (window.matchMedia("(max-width: 767px)").matches) {
          if (!mobileRow && desktopRow) {
            mobileRow = desktopRow;
          }
          if (mobileRow && mobileRow <= rowInsertCount) {
            rowInsertCount = slice * mobileRow;
            bCalculateRows = true;
          }
        } else {
          if (desktopRow && desktopRow <= rowInsertCount) {
            rowInsertCount = slice * desktopRow;
            bCalculateRows = true;
          }
        }
        if (!bCalculateRows) {
          rowInsertCount = elementsCount;
        }
        var blockInItems = parentBlock.find(linkedBlock);
        if (blockInItems.length) {
          $(blockInItems).insertAfter(parentBlock.find("> .item:eq(" + (rowInsertCount - 1) + ")"));
          setTimeout(function () {
            $(blockInItems).addClass("visible");
          }, 0);
        } else {
          var cloneBlock = $(linkedBlock).clone();
          var owlBlock = cloneBlock.find(".owl-carousel-wait");
          cloneBlock.insertAfter(parentBlock.find("> .item:eq(" + (rowInsertCount - 1) + ")"));
          owlBlock.removeClass("owl-carousel-wait").addClass("owl-carousel");
          setTimeout(function () {
            cloneBlock.addClass("visible");
            InitOwlSlider();
            owlBlock.removeClass("loader_circle");
          }, 1);
        }

        // $(linkedBlock).insertAfter(parentBlock.find('> .item:eq('+(rowInsertCount-1)+')'));
        // setTimeout(function(){
        // 	$(linkedBlock).addClass('visible');
        // }, 0);
      }
    } catch (e) {
      console.error(e);
    }
  };
}

/* EVENTS */
var timerResize = false,
  ignoreResize = [];
$(window).resize(function () {
  CheckPopupTop();
  checkLinkedArticles();
  checkLinkedBlocks(".linked-banners-list");

  // here immediate functions
  if (!ignoreResize.length) {
    if (timerResize) {
      clearTimeout(timerResize);
      timerResize = false;
    }
    timerResize = setTimeout(function () {
      // here delayed functions in event
      BX.onCustomEvent("onWindowResize", false);
    }, 50);
  }
});

var timerScroll = false,
  ignoreScroll = [],
  documentScrollTopLast = $(document).scrollTop(),
  startScroll = 0;
$(window).scroll(function () {
  CheckPopupTop();
  // here immediate functions
  documentScrollTopLast = $(document).scrollTop();
  SetFixedAskBlock();

  if (!ignoreScroll.length) {
    if (timerScroll) {
      clearTimeout(timerScroll);
      timerScroll = false;
    }
    timerScroll = setTimeout(function () {
      // here delayed functions in event
      BX.onCustomEvent("onWindowScroll", false);
    }, 50);
  }
});

BX.addCustomEvent("onWindowResize", function (eventdata) {
  try {
    ignoreResize.push(true);

    CheckTopMenuPadding();
    CheckTopMenuOncePadding();
    CheckSearchWidth();

    CheckTabActive();
    CheckTopMenuFullCatalogSubmenu();
    CheckHeaderFixedMenu();

    if (window.matchMedia("(min-width:768px)").matches) {
      closeYandexMap();
    }

    CheckTopMenuDotted();
    if ($("nav.mega-menu.sliced").length) $("nav.mega-menu.sliced").removeClass("initied");

    CheckTopVisibleMenu();

    checkScrollToTop();
    CheckObjectsSizes();

    CheckFlexSlider();
    initSly();

    // checkVerticalMobileFilter();
    if (typeof checkMobilePhone === "function") {
      checkMobilePhone();
    }
    checkTopFilter();
    if (typeof checkMobileFilter === "function") {
      checkMobileFilter();
    }

    if (arAsproOptions["THEME"] && arAsproOptions["THEME"]["COMPACT_FOOTER_MOBILE"] == "Y") {
      if (window.matchMedia("(max-width:767px)").matches) {
        $("footer").addClass("mobile");
        $(".bottom-menu .items>.wrap_compact_mobile").addClass("accordion-body collapse");
        $(".bottom-menu .items>.item.childs").attr("data-toggle", "collapse");
      } else {
        $("footer").removeClass("mobile");
        $(".bottom-menu .items>.wrap_compact_mobile").removeClass("accordion-body collapse");
        $(".bottom-menu .items>.item.childs").removeAttr("data-toggle");
      }
    }

    if (typeof window["stickySidebar"] !== "undefined") {
      if (window.matchMedia("(max-width: 991px)").matches) {
        window["stickySidebar"].destroy();
      } else {
        window["stickySidebar"].bindEvents();
      }
    }

    if ($(".flexslider.wsmooth").length) {
      $(".flexslider.wsmooth").each(function () {
        $(this).data("flexslider").smoothHeight();
      });
    }

    if (window.matchMedia("(min-width: 767px)").matches) $(".wrapper_middle_menu.wrap_menu").removeClass("mobile");

    if (window.matchMedia("(max-width: 767px)").matches) $(".wrapper_middle_menu.wrap_menu").addClass("mobile");

    if ($("#basket_form").length && $(window).outerWidth() <= 600) {
      $("#basket_form .tabs_content.basket > li.cur td").each(function () {
        $(this).css("width", "");
      });
    }

    $(".bx_filter_section .bx_filter_select_container").each(function () {
      var prop_id = $(this).closest(".bx_filter_parameters_box").attr("property_id");
      if ($("#smartFilterDropDown" + prop_id).length) {
        $("#smartFilterDropDown" + prop_id).css("max-width", $(this).width());
      }
    });

    if (window.matchMedia("(min-width: 768px)").matches) {
      // min-width: 768px
      $(".bx_filter .scrollbar").removeClass("mobile-scroll");
      $(".bx_filter .srollbar-custom").removeClass("mobile-scroll");
      $(".bx_filter .bx_filter_section form .bx_filter_parameters").removeClass("mobile-scroll");
      InitScrollBar();
      InitCustomScrollBar();
    } else {
      // max-width: 767px
      $(".bx_filter .scrollbar").addClass("mobile-scroll").removeClass("scroll-init");
      $(".bx_filter .srollbar-custom").addClass("mobile-scroll").removeClass("scroll-init");
      $(".bx_filter .bx_filter_section form .bx_filter_parameters")
        .addClass("mobile-scroll")
        .removeClass("scroll-init");
      if ($(".bx_filter .mobile-scroll.scrollbar").length)
        $(".bx_filter .mobile-scroll.scrollbar").mCustomScrollbar("destroy");
      if ($(".bx_filter .mobile-scroll.srollbar-custom").length)
        $(".bx_filter .mobile-scroll.srollbar-custom").mCustomScrollbar("destroy");
    }
  } catch (e) {
    console.log(e);
  } finally {
    ignoreResize.pop();
  }
});

BX.addCustomEvent("onWindowScroll", function (eventdata) {
  try {
    ignoreScroll.push(true);
  } catch (e) {
  } finally {
    ignoreScroll.pop();
  }
});

BX.addCustomEvent("onSlideInit", function (eventdata) {
  try {
    ignoreResize.push(true);
    if (eventdata) {
      var slider = eventdata.slider;
      if (slider) {
        if (slider.hasClass("small-gallery")) $(window).resize();
        // add classes .curent & .shown to slide
        slider.find(".item").removeClass("current");
        var curSlide = slider.find(".item.flex-active-slide"),
          curSlideId = curSlide.attr("id"),
          nav = slider.find(".flex-direction-nav");

        curSlide.addClass("current");

        slider.find(".visible").css("opacity", "1");
        slider.find(".height0").css("height", "auto");

        if (curSlide.hasClass("shown")) {
          slider.find(".item.clone[id=" + curSlideId + "_clone]").addClass("shown");
        }

        curSlide.addClass("shown");
      }
    }
  } catch (e) {
  } finally {
    ignoreResize.pop();
  }
});

BX.addCustomEvent("onCounterGoals", function (eventdata) {
  if (arAsproOptions["THEME"]["YA_GOALS"] == "Y" && arAsproOptions["THEME"]["YA_COUNTER_ID"]) {
    var idCounter = arAsproOptions["THEME"]["YA_COUNTER_ID"];
    idCounter = parseInt(idCounter);

    if (typeof eventdata != "object") eventdata = { goal: "undefined" };

    if (typeof eventdata.goal != "string") eventdata.goal = "undefined";

    if (idCounter) {
      try {
        waitCounter(idCounter, 50, function () {
          var obCounter = window["yaCounter" + idCounter];
          if (typeof obCounter == "object") {
            obCounter.reachGoal(eventdata.goal);
          }
        });
      } catch (e) {
        console.error(e);
      }
    } else {
      console.info("Bad counter id!", idCounter);
    }
  }
});

var onCaptchaVerifyinvisible = function (response) {
  $(".g-recaptcha:last").each(function () {
    var id = $(this).attr("data-widgetid");
    if (typeof id !== "undefined" && response) {
      if (!$(this).closest("form").find(".g-recaptcha-response").val())
        $(this).closest("form").find(".g-recaptcha-response").val(response);
      if ($("iframe[src*=recaptcha]").length) {
        $("iframe[src*=recaptcha]").each(function () {
          var block = $(this).parent().parent();
          if (!block.hasClass("grecaptcha-badge")) block.css("width", "100%");
        });
      }
      $(this).closest("form").submit();
    }
  });
};

var onCaptchaVerifynormal = function (response) {
  $(".g-recaptcha").each(function () {
    var id = $(this).attr("data-widgetid");
    if (typeof id !== "undefined") {
      if (grecaptcha.getResponse(id) != "") {
        $(this).closest("form").find(".recaptcha").valid();
      }
    }
  });
};

BX.addCustomEvent("onSubmitForm", function (eventdata) {
  try {
    if (!window.renderRecaptchaById || !window.asproRecaptcha || !window.asproRecaptcha.key) {
      eventdata.form.submit();
      $(eventdata.form).closest(".form").addClass("sending");
      return true;
    }

    if (window.asproRecaptcha.params.recaptchaSize == "invisible" && $(eventdata.form).find(".g-recaptcha").length) {
      if ($(eventdata.form).find(".g-recaptcha-response").val()) {
        eventdata.form.submit();
        $(eventdata.form).closest(".form").addClass("sending");
        return true;
      } else {
        if (typeof grecaptcha != "undefined") {
          grecaptcha.execute($(eventdata.form).find(".g-recaptcha").data("widgetid"));
        } else {
          return false;
        }
      }
    } else {
      eventdata.form.submit();
      $(eventdata.form).closest(".form").addClass("sending");
      return true;
    }
  } catch (e) {
    console.error(e);
    return true;
  }
});

$(document).on("click", ".catalog_reviews_extended span.dropdown-select__list-link", function () {
  var _this = $(this);
  var ajaxData = _this.data("review_sort_ajax");
  var container = _this.closest("[id^=comp_]");
  containerId = container.attr("id");
  if (containerId !== undefined && containerId) {
    ajaxData.containerId = containerId;
  }
  if (ajaxData !== undefined) {
    container.addClass("blur");
    $.ajax({
      type: "post",
      data: ajaxData,
      success: function (html) {
        $("#reviews_sort_continer").html(html);
      },
    });
  }
});

$(document).on("click", ".rating_vote:not(.disable)", function () {
  var _this = $(this);
  var action = _this.data("action");
  var parent = _this.closest(".rating-vote");
  var commentId = parent.data("comment_id");
  var userId = parent.data("user_id");
  var ajaxUrl = parent.data("ajax_url");
  $.ajax({
    url: ajaxUrl,
    dataType: "json",
    data: { commentId: commentId, action: action, userId: userId },
    success: function (data) {
      if (data.LIKE !== undefined) {
        _this.siblings(".rating-vote-result.like").text(data.LIKE);
      }
      if (data.DISLIKE !== undefined) {
        _this.siblings(".rating-vote-result.dislike").text(data.DISLIKE);
      }
      if (data.SET_ACTIVE_LIKE !== undefined) {
        parent.find(".rating_vote.plus").toggleClass("active");
      }
      if (data.SET_ACTIVE_DISLIKE !== undefined) {
        parent.find(".rating_vote.minus").toggleClass("active");
      }
    },
  });
});

function fileInputInit(message) {
  $("input[type=file]").uniform({ fileButtonHtml: BX.message("JS_FILE_BUTTON_NAME"), fileDefaultHtml: message });
  $(document).on("change", "input[type=file]", function () {
    if ($(this).val()) {
      $(this).closest(".uploader").addClass("files_add");
    } else {
      $(this).closest(".uploader").removeClass("files_add");
    }
  });
  $(".form .add_file").on("click", function () {
    var index = $(this).closest(".input").find("input[type=file]").length + 1;

    $(this)
      .closest(".form-group")
      .find(".input")
      .append('<input type="file" id="POPUP_FILE" name="FILE_n' + index + '"   class="inputfile" value="" />');
    $("input[type=file]").uniform({ fileButtonHtml: BX.message("JS_FILE_BUTTON_NAME"), fileDefaultHtml: message });
  });

  $(".form .add_file").on("click", function () {
    var index = $(this).closest(".input").find("input[type=file]").length + 1;

    $(this)
      .closest(".form-group")
      .find(".input")
      .append('<input type="file" id="POPUP_FILE" name="FILE_n' + index + '"   class="inputfile" value="" />');
    $("input[type=file]").uniform({ fileButtonHtml: BX.message("JS_FILE_BUTTON_NAME"), fileDefaultHtml: message });
  });
}

/*custom event for sku prices*/

/*BX.addCustomEvent('onAsproSkuSetPrice', function(eventdata){
  console.log(eventdata);
})*/

/*BX.addCustomEvent('onAsproSkuSetPriceMatrix', function(eventdata){
  console.log(eventdata);
})*/

function declOfNum(number, titles) {
  var cases = [2, 0, 1, 1, 1, 2];
  return number + " " + titles[number % 100 > 4 && number % 100 < 20 ? 2 : cases[Math.min(number % 10, 5)]];
}

function array_values_js(input) {
  var tmp_arr = new Array(),
    cnt = 0;
  for (key in input) {
    tmp_arr[cnt] = input[key];
    cnt++;
  }

  return tmp_arr;
}
