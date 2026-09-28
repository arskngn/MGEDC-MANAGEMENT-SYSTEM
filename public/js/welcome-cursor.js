(function () {
  function initCustomCursor() {
    const cursor = document.getElementById("customCursor");
    if (!cursor || cursor.dataset.initialized === "true") return;

    cursor.dataset.initialized = "true";
    document.documentElement.classList.add("has-custom-cursor");
    document.body.appendChild(cursor);

    const interactiveSelectors = [
      ".landing-login-btn",
      ".landing-header__icon",
      ".color-btn",
      ".toggle-adjuster-btn",
      ".copy-btn",
      ".export-all-btn",
      ".color-adjuster-panel__close",
      "input[type='color']"
    ].join(",");

    function moveCursor(x, y) {
      cursor.style.left = "0";
      cursor.style.top = "0";
      cursor.style.transform = `translate3d(${x}px, ${y}px, 0) translate(-50%, -50%)`;
    }

    function enlargeCursor() {
      cursor.style.width = "50px";
      cursor.style.height = "50px";
      cursor.style.borderWidth = "3px";
    }

    function resetCursorSize() {
      cursor.style.width = "40px";
      cursor.style.height = "40px";
      cursor.style.borderWidth = "2px";
    }

    window.addEventListener(
      "mousemove",
      function (e) {
        cursor.classList.add("is-active");
        moveCursor(e.clientX, e.clientY);
      },
      { passive: true }
    );

    window.addEventListener(
      "mouseover",
      function (e) {
        if (e.target.closest(interactiveSelectors)) {
          enlargeCursor();
        } else {
          resetCursorSize();
        }
      },
      { passive: true }
    );

    document.addEventListener("mouseout", function (e) {
      if (!e.relatedTarget && !e.toElement) {
        cursor.classList.remove("is-active");
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initCustomCursor);
  } else {
    initCustomCursor();
  }
})();
