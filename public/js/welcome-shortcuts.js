(function () {
  if (!document.body.classList.contains("is-landing-page")) return;

  const loginLink = document.querySelector("[data-landing-shortcut='login']");
  const gmailLink = document.querySelector("[data-landing-shortcut='gmail']");
  const facebookLink = document.querySelector("[data-landing-shortcut='facebook']");
  const toggleAdjusterBtn = document.getElementById("toggleAdjusterBtn");
  const colorAdjusterPanel = document.getElementById("colorAdjusterPanel");
  const closeAdjusterBtn = document.getElementById("closeAdjusterBtn");

  function isTypingContext(target) {
    if (!target) return false;
    const tag = target.tagName;
    return (
      tag === "INPUT" ||
      tag === "TEXTAREA" ||
      tag === "SELECT" ||
      target.isContentEditable
    );
  }

  function activateScheme(scheme) {
    const btn = document.querySelector(`.color-btn[data-scheme="${scheme}"]`);
    if (btn) btn.click();
  }

  function toggleColorAdjuster() {
    if (!toggleAdjusterBtn || !colorAdjusterPanel) return;

    if (colorAdjusterPanel.classList.contains("open")) {
      if (closeAdjusterBtn) closeAdjusterBtn.click();
      else {
        colorAdjusterPanel.classList.remove("open");
        toggleAdjusterBtn.style.display = "block";
      }
    } else {
      toggleAdjusterBtn.click();
    }
  }

  document.addEventListener("keydown", function (e) {
    if (e.defaultPrevented || e.repeat) return;
    if (e.ctrlKey || e.metaKey || e.altKey) return;
    if (isTypingContext(e.target)) return;

    const key = e.key;

    if (key === "Enter") {
      if (loginLink) {
        e.preventDefault();
        loginLink.click();
      }
      return;
    }

    const letter = key.length === 1 ? key.toLowerCase() : "";

    if (letter === "g" && gmailLink) {
      e.preventDefault();
      gmailLink.click();
      return;
    }

    if (letter === "f" && facebookLink) {
      e.preventDefault();
      facebookLink.click();
      return;
    }

    if (letter === "a") {
      e.preventDefault();
      toggleColorAdjuster();
      return;
    }

    if (letter >= "1" && letter <= "8") {
      e.preventDefault();
      activateScheme(letter);
    }
  });
})();
