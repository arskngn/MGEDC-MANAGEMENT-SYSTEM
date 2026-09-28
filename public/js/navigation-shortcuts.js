(function () {
  const BACK_ARROW_FRAGMENT = "M10 19l-7-7";

  function normalizeText(value) {
    return (value || "").replace(/\s+/g, " ").trim();
  }

  function isExcludedBackLabel(text) {
    const t = text.toLowerCase();
    if (!t) return false;
    if (t === "clear" || t === "cancel" || t === "logout") return true;
    if (/sale\s+return|create\s+return|add\s+return|view\s+return|purchase\s+return|return\s+details|update\s+return/i.test(t)) {
      return true;
    }
    if (/\breturn\b/i.test(t) && !/\bback\b/i.test(t) && !/home/i.test(t)) {
      return true;
    }
    return false;
  }

  function hasBackArrow(link) {
    return !!link.querySelector(`path[d*="${BACK_ARROW_FRAGMENT}"]`);
  }

  function scoreBackLink(link) {
    const href = link.getAttribute("href");
    if (!href || href === "#" || href.startsWith("javascript:")) {
      return -1;
    }

    const text = normalizeText(link.textContent);
    if (isExcludedBackLabel(text)) {
      return -1;
    }

    if (link.hasAttribute("data-back-link")) {
      return 1000;
    }

    let score = -1;

    if (/back\s*to/i.test(text)) score = Math.max(score, 920);
    if (/^back$/i.test(text)) score = Math.max(score, 900);
    if (/return\s+home/i.test(text)) score = Math.max(score, 880);

    if (hasBackArrow(link)) {
      if (/^back$/i.test(text) || (/back/i.test(text) && text.length <= 30)) {
        score = Math.max(score, 860);
      } else if (text.length <= 4) {
        score = Math.max(score, 820);
      }
    }

    if (score < 0) {
      return -1;
    }

    const top = link.getBoundingClientRect().top;
    if (top > 420) {
      return -1;
    }

    return score + Math.max(0, 220 - top);
  }

  function shouldBackNavigate(target) {
    if (!target) {
      return true;
    }

    const tag = target.tagName;

    if (tag === "TEXTAREA") {
      const value = target.value || "";
      const start = target.selectionStart ?? 0;
      const end = target.selectionEnd ?? start;
      return value.length === 0 || (start === 0 && end === 0);
    }

    if (tag === "INPUT") {
      const type = (target.type || "").toLowerCase();
      if (
        ["checkbox", "radio", "button", "submit", "file", "range", "color"].includes(
          type
        )
      ) {
        return true;
      }
      const value = target.value || "";
      const start = target.selectionStart ?? 0;
      const end = target.selectionEnd ?? start;
      return value.length === 0 || (start === 0 && end === 0);
    }

    if (target.isContentEditable) {
      return !(target.textContent || "").length;
    }

    return true;
  }

  function isOverlayOpen() {
    const deleteModal = document.getElementById("global-delete-modal");
    if (deleteModal) {
      const style = window.getComputedStyle(deleteModal);
      if (style.display !== "none" && deleteModal.offsetParent !== null) {
        return true;
      }
    }

    const dialog = document.querySelector(
      '[role="dialog"][aria-modal="true"], [role="alertdialog"][aria-modal="true"]'
    );
    if (dialog && dialog.offsetParent !== null) {
      return true;
    }

    return false;
  }

  function findBackLink(root) {
    const scope = root || document;
    const explicit = scope.querySelector("[data-back-link]");
    if (explicit?.href) {
      return explicit;
    }

    const main = scope.querySelector("main");
    const links = main
      ? main.querySelectorAll("a[href]")
      : scope.querySelectorAll("a[href]");

    let best = null;
    let bestScore = -1;

    links.forEach((link) => {
      const score = scoreBackLink(link);
      if (score > bestScore) {
        bestScore = score;
        best = link;
      }
    });

    return best;
  }

  document.addEventListener("keydown", function (e) {
    if (e.defaultPrevented || e.repeat) return;
    if (e.key !== "Escape") return;
    if (e.ctrlKey || e.metaKey || e.altKey || e.shiftKey) return;
    if (isOverlayOpen()) return;

    const target = e.target || document.activeElement;
    if (!shouldBackNavigate(target)) return;

    const backLink = findBackLink(document);
    if (!backLink || !backLink.href) return;

    e.preventDefault();
    window.location.href = backLink.href;
  });

  window.NavigationShortcuts = {
    shouldBackNavigate,
    isOverlayOpen,
    findBackLink,
  };
})();
