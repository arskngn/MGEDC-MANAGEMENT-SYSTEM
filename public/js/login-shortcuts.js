(function () {
  if (!document.body.classList.contains("is-login-page")) return;

  const loginForm = document.getElementById("loginForm");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const homeLink = document.getElementById("loginHomeLink");
  const eyeIcon = document.getElementById("eyeIcon");
  const eyeOffIcon = document.getElementById("eyeOffIcon");
  const eyeToggle = document.getElementById("loginPasswordToggle");
  const capsWarning = document.getElementById("caps-lock-warning");

  if (!loginForm || !emailInput || !passwordInput) return;

  const homeUrl = loginForm.dataset.homeUrl || homeLink?.href || "/";
  const fields = [emailInput, passwordInput];

  function focusField(index) {
    const field = fields[index];
    if (!field) return;
    field.focus();
    const len = field.value.length;
    if (typeof field.setSelectionRange === "function") {
      field.setSelectionRange(len, len);
    }
  }

  function activeFieldIndex() {
    const active = document.activeElement;
    const index = fields.indexOf(active);
    return index >= 0 ? index : 0;
  }

  function togglePasswordVisibility() {
    const isHidden = passwordInput.getAttribute("type") === "password";
    passwordInput.setAttribute("type", isHidden ? "text" : "password");
    if (eyeIcon && eyeOffIcon) {
      eyeIcon.classList.toggle("hidden", !isHidden);
      eyeOffIcon.classList.toggle("hidden", isHidden);
    }
  }

  function goHome() {
    window.location.href = homeUrl;
  }

  function submitLogin() {
    if (typeof loginForm.requestSubmit === "function") {
      loginForm.requestSubmit();
    } else {
      loginForm.submit();
    }
  }

  function shouldEscapeGoHome(target) {
    if (target !== emailInput && target !== passwordInput) {
      return true;
    }
    if (window.NavigationShortcuts?.shouldBackNavigate) {
      return window.NavigationShortcuts.shouldBackNavigate(target);
    }
    const value = target.value || "";
    const start =
      typeof target.selectionStart === "number" ? target.selectionStart : 0;
    const end =
      typeof target.selectionEnd === "number" ? target.selectionEnd : start;
    return value.length === 0 || (start === 0 && end === 0);
  }

  let shiftDownForToggle = false;
  let otherKeyWhileShift = false;

  if (eyeToggle) {
    eyeToggle.addEventListener("click", togglePasswordVisibility);
  }

  if (passwordInput && capsWarning) {
    passwordInput.addEventListener("keyup", function (event) {
      capsWarning.classList.toggle(
        "hidden",
        !event.getModifierState("CapsLock")
      );
    });
  }

  passwordInput.addEventListener("keydown", function (e) {
    if (e.key === "Shift") {
      shiftDownForToggle = true;
      otherKeyWhileShift = false;
      return;
    }
    if (shiftDownForToggle && e.key.length === 1 && e.key !== "Shift") {
      otherKeyWhileShift = true;
    }
  });

  document.addEventListener("keyup", function (e) {
    if (e.key !== "Shift" || !shiftDownForToggle) return;
    shiftDownForToggle = false;
    if (
      !otherKeyWhileShift &&
      document.activeElement === passwordInput
    ) {
      e.preventDefault();
      togglePasswordVisibility();
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.defaultPrevented) return;

    const key = e.key;
    const target = e.target;
    const onLoginField =
      target === emailInput || target === passwordInput;

    if (key === "ArrowDown" && onLoginField) {
      e.preventDefault();
      focusField(Math.min(activeFieldIndex() + 1, fields.length - 1));
      return;
    }

    if (key === "ArrowUp" && onLoginField) {
      e.preventDefault();
      focusField(Math.max(activeFieldIndex() - 1, 0));
      return;
    }

    if (key === "Enter" && !e.shiftKey && !e.ctrlKey && !e.metaKey && !e.altKey) {
      if (target.tagName === "TEXTAREA") return;
      e.preventDefault();
      submitLogin();
      return;
    }

    if (key === "Escape" && !e.ctrlKey && !e.metaKey && !e.altKey) {
      if (shouldEscapeGoHome(target)) {
        e.preventDefault();
        goHome();
      }
    }
  });

  window.addEventListener("load", function () {
    focusField(0);
  });

  if (document.readyState === "complete") {
    focusField(0);
  }
})();
