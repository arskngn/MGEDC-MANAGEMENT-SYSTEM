(function () {
  const presets = window.APPEARANCE_SCHEME_PRESETS || {};

  function normalizeHex(hex, fallback) {
    const value = String(hex || fallback || "#F15A22")
      .trim()
      .replace("#", "")
      .toUpperCase();
    return /^[0-9A-F]{6}$/.test(value) ? `#${value}` : fallback;
  }

  function getPresetColors(scheme) {
    const list = presets[scheme] || presets[1] || [];
    return list.slice(0, 6);
  }

  function readEditorState(editor) {
    const rawVal = editor.querySelector("[data-scheme-input]")?.value;
    const parsed = parseInt(rawVal, 10);
    const scheme = isNaN(parsed) ? 1 : parsed;
    const colors = [];
    for (let i = 1; i <= 6; i++) {
      const picker = editor.querySelector(
        `.appearance-color-picker[data-color-index="${i}"]`
      );
      colors.push(picker ? picker.value.toUpperCase() : "#F15A22");
    }
    return { scheme, colors };
  }

  function writeEditorState(editor, scheme, colors) {
    editor.querySelector("[data-scheme-input]").value = scheme;
    editor.querySelector("[data-colors-input]").value = JSON.stringify(colors);

    colors.forEach((hex, index) => {
      const i = index + 1;
      const picker = editor.querySelector(
        `.appearance-color-picker[data-color-index="${i}"]`
      );
      const hexInput = editor.querySelector(
        `.appearance-color-hex[data-color-hex="${i}"]`
      );
      if (picker) picker.value = hex;
      if (hexInput) hexInput.value = hex.toUpperCase();
    });

    editor.querySelectorAll(".appearance-scheme-btn").forEach((btn) => {
      const active = parseInt(btn.dataset.scheme, 10) === scheme;
      btn.classList.toggle("bg-[#4634ff]", active);
      btn.classList.toggle("text-white", active);
      btn.classList.toggle("border-[#4634ff]", active);
      btn.classList.toggle("bg-white", !active);
      btn.classList.toggle("text-gray-700", !active);
      btn.classList.toggle("border-gray-300", !active);
    });
  }

  function applySchemePreset(editor, scheme) {
    const colors = getPresetColors(scheme).map((hex, index) => {
      const current = editor.querySelector(
        `.appearance-color-picker[data-color-index="${index + 1}"]`
      );
      return normalizeHex(hex, current ? current.value : hex);
    });
    writeEditorState(editor, scheme, colors);
    syncPreview(editor);
  }

  function syncPreview(editor) {
    const previewApp = editor._previewApp;
    if (!previewApp || !window.applyPageAppearance) return;
    const state = readEditorState(editor);
    window.applyPageAppearance(previewApp, state);
  }

  function initEditor(editor) {
    const mount = editor.querySelector("[data-preview-mount]");
    if (!mount || !window.LiquidGradientApp) return;

    const previewApp = new window.LiquidGradientApp({ mountElement: mount });
    editor._previewApp = previewApp;

    const rawScheme = parseInt(editor.querySelector("[data-scheme-input]").value, 10);
    const initialScheme = isNaN(rawScheme) ? 1 : rawScheme;
    let initialColors;
    try {
      initialColors = JSON.parse(
        editor.querySelector("[data-colors-input]").value || "[]"
      );
    } catch (e) {
      initialColors = getPresetColors(initialScheme);
    }
    writeEditorState(editor, initialScheme, initialColors);
    window.applyPageAppearance(previewApp, readEditorState(editor));
    previewApp.render();
    (function tick() {
      previewApp.render();
      requestAnimationFrame(tick);
    })();

    editor.querySelectorAll(".appearance-scheme-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        applySchemePreset(editor, parseInt(btn.dataset.scheme, 10));
      });
    });

    editor.querySelectorAll(".appearance-color-picker").forEach((picker) => {
      picker.addEventListener("input", () => {
        const state = readEditorState(editor);
        const index = parseInt(picker.dataset.colorIndex, 10) - 1;
        state.colors[index] = picker.value.toUpperCase();
        writeEditorState(editor, state.scheme, state.colors);
        syncPreview(editor);
      });
    });

    editor.querySelectorAll(".appearance-color-hex").forEach((input) => {
      input.addEventListener("change", () => {
        let hex = input.value.trim();
        if (!hex.startsWith("#")) hex = `#${hex}`;
        const index = parseInt(input.dataset.colorHex, 10) - 1;
        const state = readEditorState(editor);
        state.colors[index] = normalizeHex(hex, state.colors[index]);
        writeEditorState(editor, state.scheme, state.colors);
        syncPreview(editor);
      });
    });

    const form = editor.closest("form");
    if (form) {
      form.addEventListener("submit", () => {
        const state = readEditorState(editor);
        writeEditorState(editor, state.scheme, state.colors);
      });
    }
  }

  document
    .querySelectorAll("[data-appearance-editor]")
    .forEach(initEditor);
})();
