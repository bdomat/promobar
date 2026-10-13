/**
 * PromoBar front script
 * @license MIT
 */
(function () {
  "use strict";

  function setCookie(name, value, days) {
    try {
      var d = new Date();
      d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
      document.cookie =
        name + "=" + value + ";expires=" + d.toUTCString() + ";path=/;SameSite=Lax" + (location.protocol === "https:" ? ";Secure" : "");
    } catch (e) {}
  }

  function pad2(n) {
    return (n < 10 ? "0" : "") + n;
  }

  function hydrateMessage(root) {
    var marquee = root.querySelector(".promobar__marquee[data-html]");
    if (!marquee) return;
    var htmlEscaped = marquee.getAttribute("data-html");
    if (!htmlEscaped) return;
    marquee.innerHTML = htmlEscaped;
    marquee.removeAttribute("data-html");
  }

  function initCountdown(root) {
    var el = root.querySelector(".promobar__countdown");
    if (!el) return;

    var endTimestamp = el.getAttribute("data-end");
    if (!endTimestamp) return;

    var end = new Date(parseInt(endTimestamp, 10));
    if (isNaN(end.getTime())) return;

    var dEl = el.querySelector('[data-cd="d"]');
    var hEl = el.querySelector('[data-cd="h"]');
    var mEl = el.querySelector('[data-cd="m"]');
    var sEl = el.querySelector('[data-cd="s"]');

    function tick() {
      var now = new Date();
      var diff = end - now;

      if (diff <= 0) {
        el.style.display = "none";
        clearInterval(timer);
        return;
      }

      var sec = Math.floor(diff / 1000);
      var days = Math.floor(sec / 86400);
      sec -= days * 86400;
      var hrs = Math.floor(sec / 3600);
      sec -= hrs * 3600;
      var mins = Math.floor(sec / 60);
      sec -= mins * 60;

      if (dEl) dEl.textContent = pad2(days);
      if (hEl) hEl.textContent = pad2(hrs);
      if (mEl) mEl.textContent = pad2(mins);
      if (sEl) sEl.textContent = pad2(sec);
    }

    var timer = setInterval(tick, 1000);
    tick();
  }

  document.addEventListener("DOMContentLoaded", function () {
    var bar = document.getElementById("promobar");
    if (!bar) return;

    var cookieName = bar.getAttribute("data-cookie") || "promobar_dismissed";
    var cookieDays = parseInt(bar.getAttribute("data-cookie-days") || "30", 10);
    if (isNaN(cookieDays) || cookieDays <= 0) cookieDays = 30;

    if (bar.querySelector(".promobar__close") && document.cookie.split(";").some(function (cookie) { return cookie.trim() === cookieName + "=1"; })) {
      bar.style.display = "none";
      return;
    }

    var close = bar.querySelector(".promobar__close");
    if (close) {
      close.addEventListener("click", function () {
        setCookie(cookieName, "1", cookieDays);
        bar.style.display = "none";
      });
    }

    hydrateMessage(bar);
    initCountdown(bar);
  });
})();
