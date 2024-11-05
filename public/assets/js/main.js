(function () {
  const t = document.createElement("link").relList;
  if (t && t.supports && t.supports("modulepreload")) return;
  for (const i of document.querySelectorAll('link[rel="modulepreload"]')) o(i);
  new MutationObserver(i => {
    for (const r of i)
      if (r.type === "childList")
        for (const a of r.addedNodes) a.tagName === "LINK" && a.rel === "modulepreload" && o(a)
  }).observe(document, {
    childList: !0,
    subtree: !0
  });

  function s(i) {
    const r = {};
    return i.integrity && (r.integrity = i.integrity), i.referrerPolicy && (r.referrerPolicy = i.referrerPolicy), i.crossOrigin === "use-credentials" ? r.credentials = "include" : i.crossOrigin === "anonymous" ? r.credentials = "omit" : r.credentials = "same-origin", r
  }

  function o(i) {
    if (i.ep) return;
    i.ep = !0;
    const r = s(i);
    fetch(i.href, r)
  }
})();
var ln = typeof globalThis < "u" ? globalThis : typeof window < "u" ? window : typeof global < "u" ? global : typeof self < "u" ? self : {},
  Jh = {
    exports: {}
  };
/*!
 * sweetalert2 v11.4.0
 * Released under the MIT License.
 */
(function (e, t) {
  (function (s, o) {
    e.exports = o()
  })(ln, function () {
    const s = "SweetAlert2:",
      o = u => {
        const f = [];
        for (let y = 0; y < u.length; y++) f.indexOf(u[y]) === -1 && f.push(u[y]);
        return f
      },
      i = u => u.charAt(0).toUpperCase() + u.slice(1),
      r = u => Array.prototype.slice.call(u),
      a = u => {
        console.warn("".concat(s, " ").concat(typeof u == "object" ? u.join(" ") : u))
      },
      l = u => {
        console.error("".concat(s, " ").concat(u))
      },
      d = [],
      p = u => {
        d.includes(u) || (d.push(u), a(u))
      },
      m = (u, f) => {
        p('"'.concat(u, '" is deprecated and will be removed in the next major release. Please use "').concat(f, '" instead.'))
      },
      g = u => typeof u == "function" ? u() : u,
      $ = u => u && typeof u.toPromise == "function",
      C = u => $(u) ? u.toPromise() : Promise.resolve(u),
      A = u => u && Promise.resolve(u) === u,
      O = {
        title: "",
        titleText: "",
        text: "",
        html: "",
        footer: "",
        icon: void 0,
        iconColor: void 0,
        iconHtml: void 0,
        template: void 0,
        toast: !1,
        showClass: {
          popup: "swal2-show",
          backdrop: "swal2-backdrop-show",
          icon: "swal2-icon-show"
        },
        hideClass: {
          popup: "swal2-hide",
          backdrop: "swal2-backdrop-hide",
          icon: "swal2-icon-hide"
        },
        customClass: {},
        target: "body",
        color: void 0,
        backdrop: !0,
        heightAuto: !0,
        allowOutsideClick: !0,
        allowEscapeKey: !0,
        allowEnterKey: !0,
        stopKeydownPropagation: !0,
        keydownListenerCapture: !1,
        showConfirmButton: !0,
        showDenyButton: !1,
        showCancelButton: !1,
        preConfirm: void 0,
        preDeny: void 0,
        confirmButtonText: "OK",
        confirmButtonAriaLabel: "",
        confirmButtonColor: void 0,
        denyButtonText: "No",
        denyButtonAriaLabel: "",
        denyButtonColor: void 0,
        cancelButtonText: "Cancel",
        cancelButtonAriaLabel: "",
        cancelButtonColor: void 0,
        buttonsStyling: !0,
        reverseButtons: !1,
        focusConfirm: !0,
        focusDeny: !1,
        focusCancel: !1,
        returnFocus: !0,
        showCloseButton: !1,
        closeButtonHtml: "&times;",
        closeButtonAriaLabel: "Close this dialog",
        loaderHtml: "",
        showLoaderOnConfirm: !1,
        showLoaderOnDeny: !1,
        imageUrl: void 0,
        imageWidth: void 0,
        imageHeight: void 0,
        imageAlt: "",
        timer: void 0,
        timerProgressBar: !1,
        width: void 0,
        padding: void 0,
        background: void 0,
        input: void 0,
        inputPlaceholder: "",
        inputLabel: "",
        inputValue: "",
        inputOptions: {},
        inputAutoTrim: !0,
        inputAttributes: {},
        inputValidator: void 0,
        returnInputValueOnDeny: !1,
        validationMessage: void 0,
        grow: !1,
        position: "center",
        progressSteps: [],
        currentProgressStep: void 0,
        progressStepsDistance: void 0,
        willOpen: void 0,
        didOpen: void 0,
        didRender: void 0,
        willClose: void 0,
        didClose: void 0,
        didDestroy: void 0,
        scrollbarPadding: !0
      },
      V = ["allowEscapeKey", "allowOutsideClick", "background", "buttonsStyling", "cancelButtonAriaLabel", "cancelButtonColor", "cancelButtonText", "closeButtonAriaLabel", "closeButtonHtml", "color", "confirmButtonAriaLabel", "confirmButtonColor", "confirmButtonText", "currentProgressStep", "customClass", "denyButtonAriaLabel", "denyButtonColor", "denyButtonText", "didClose", "didDestroy", "footer", "hideClass", "html", "icon", "iconColor", "iconHtml", "imageAlt", "imageHeight", "imageUrl", "imageWidth", "preConfirm", "preDeny", "progressSteps", "returnFocus", "reverseButtons", "showCancelButton", "showCloseButton", "showConfirmButton", "showDenyButton", "text", "title", "titleText", "willClose"],
      M = {},
      B = ["allowOutsideClick", "allowEnterKey", "backdrop", "focusConfirm", "focusDeny", "focusCancel", "returnFocus", "heightAuto", "keydownListenerCapture"],
      P = u => Object.prototype.hasOwnProperty.call(O, u),
      I = u => V.indexOf(u) !== -1,
      R = u => M[u],
      F = u => {
        P(u) || a('Unknown parameter "'.concat(u, '"'))
      },
      j = u => {
        B.includes(u) && a('The parameter "'.concat(u, '" is incompatible with toasts'))
      },
      q = u => {
        R(u) && m(u, R(u))
      },
      ne = u => {
        !u.backdrop && u.allowOutsideClick && a('"allowOutsideClick" parameter requires `backdrop` parameter to be set to `true`');
        for (const f in u) F(f), u.toast && j(f), q(f)
      },
      ee = "swal2-",
      ge = u => {
        const f = {};
        for (const y in u) f[u[y]] = ee + u[y];
        return f
      },
      T = ge(["container", "shown", "height-auto", "iosfix", "popup", "modal", "no-backdrop", "no-transition", "toast", "toast-shown", "show", "hide", "close", "title", "html-container", "actions", "confirm", "deny", "cancel", "default-outline", "footer", "icon", "icon-content", "image", "input", "file", "range", "select", "radio", "checkbox", "label", "textarea", "inputerror", "input-label", "validation-message", "progress-steps", "active-progress-step", "progress-step", "progress-step-line", "loader", "loading", "styled", "top", "top-start", "top-end", "top-left", "top-right", "center", "center-start", "center-end", "center-left", "center-right", "bottom", "bottom-start", "bottom-end", "bottom-left", "bottom-right", "grow-row", "grow-column", "grow-fullscreen", "rtl", "timer-progress-bar", "timer-progress-bar-container", "scrollbar-measure", "icon-success", "icon-warning", "icon-info", "icon-question", "icon-error"]),
      re = ge(["success", "warning", "info", "question", "error"]),
      Le = () => document.body.querySelector(".".concat(T.container)),
      We = u => {
        const f = Le();
        return f ? f.querySelector(u) : null
      },
      fe = u => We(".".concat(u)),
      ue = () => fe(T.popup),
      te = () => fe(T.icon),
      $t = () => fe(T.title),
      Lt = () => fe(T["html-container"]),
      xt = () => fe(T.image),
      ht = () => fe(T["progress-steps"]),
      U = () => fe(T["validation-message"]),
      pt = () => We(".".concat(T.actions, " .").concat(T.confirm)),
      lt = () => We(".".concat(T.actions, " .").concat(T.deny)),
      ot = () => fe(T["input-label"]),
      K = () => We(".".concat(T.loader)),
      ae = () => We(".".concat(T.actions, " .").concat(T.cancel)),
      H = () => fe(T.actions),
      ce = () => fe(T.footer),
      De = () => fe(T["timer-progress-bar"]),
      Ne = () => fe(T.close),
      k = `
  a[href],
  area[href],
  input:not([disabled]),
  select:not([disabled]),
  textarea:not([disabled]),
  button:not([disabled]),
  iframe,
  object,
  embed,
  [tabindex="0"],
  [contenteditable],
  audio[controls],
  video[controls],
  summary
`,
      x = () => {
        const u = r(ue().querySelectorAll('[tabindex]:not([tabindex="-1"]):not([tabindex="0"])')).sort((y, S) => {
            const X = parseInt(y.getAttribute("tabindex")),
              Ee = parseInt(S.getAttribute("tabindex"));
            return X > Ee ? 1 : X < Ee ? -1 : 0
          }),
          f = r(ue().querySelectorAll(k)).filter(y => y.getAttribute("tabindex") !== "-1");
        return o(u.concat(f)).filter(y => mt(y))
      },
      N = () => !G(document.body, T["toast-shown"]) && !G(document.body, T["no-backdrop"]),
      Y = () => ue() && G(ue(), T.toast),
      W = () => ue().hasAttribute("data-loading"),
      oe = {
        previousBodyPadding: null
      },
      se = (u, f) => {
        if (u.textContent = "", f) {
          const S = new DOMParser().parseFromString(f, "text/html");
          r(S.querySelector("head").childNodes).forEach(X => {
            u.appendChild(X)
          }), r(S.querySelector("body").childNodes).forEach(X => {
            u.appendChild(X)
          })
        }
      },
      G = (u, f) => {
        if (!f) return !1;
        const y = f.split(/\s+/);
        for (let S = 0; S < y.length; S++)
          if (!u.classList.contains(y[S])) return !1;
        return !0
      },
      le = (u, f) => {
        r(u.classList).forEach(y => {
          !Object.values(T).includes(y) && !Object.values(re).includes(y) && !Object.values(f.showClass).includes(y) && u.classList.remove(y)
        })
      },
      Q = (u, f, y) => {
        if (le(u, f), f.customClass && f.customClass[y]) {
          if (typeof f.customClass[y] != "string" && !f.customClass[y].forEach) return a("Invalid type of customClass.".concat(y, '! Expected string or iterable object, got "').concat(typeof f.customClass[y], '"'));
          de(u, f.customClass[y])
        }
      },
      pe = (u, f) => {
        if (!f) return null;
        switch (f) {
          case "select":
          case "textarea":
          case "file":
            return u.querySelector(".".concat(T.popup, " > .").concat(T[f]));
          case "checkbox":
            return u.querySelector(".".concat(T.popup, " > .").concat(T.checkbox, " input"));
          case "radio":
            return u.querySelector(".".concat(T.popup, " > .").concat(T.radio, " input:checked")) || u.querySelector(".".concat(T.popup, " > .").concat(T.radio, " input:first-child"));
          case "range":
            return u.querySelector(".".concat(T.popup, " > .").concat(T.range, " input"));
          default:
            return u.querySelector(".".concat(T.popup, " > .").concat(T.input))
        }
      },
      xe = u => {
        if (u.focus(), u.type !== "file") {
          const f = u.value;
          u.value = "", u.value = f
        }
      },
      Ce = (u, f, y) => {
        !u || !f || (typeof f == "string" && (f = f.split(/\s+/).filter(Boolean)), f.forEach(S => {
          Array.isArray(u) ? u.forEach(X => {
            y ? X.classList.add(S) : X.classList.remove(S)
          }) : y ? u.classList.add(S) : u.classList.remove(S)
        }))
      },
      de = (u, f) => {
        Ce(u, f, !0)
      },
      Pe = (u, f) => {
        Ce(u, f, !1)
      },
      Oe = (u, f) => {
        const y = r(u.childNodes);
        for (let S = 0; S < y.length; S++)
          if (G(y[S], f)) return y[S]
      },
      He = (u, f, y) => {
        y === "".concat(parseInt(y)) && (y = parseInt(y)), y || parseInt(y) === 0 ? u.style[f] = typeof y == "number" ? "".concat(y, "px") : y : u.style.removeProperty(f)
      },
      Me = function (u) {
        let f = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : "flex";
        u.style.display = f
      },
      Xe = u => {
        u.style.display = "none"
      },
      Ot = (u, f, y, S) => {
        const X = u.querySelector(f);
        X && (X.style[y] = S)
      },
      Xt = (u, f, y) => {
        f ? Me(u, y) : Xe(u)
      },
      mt = u => !!(u && (u.offsetWidth || u.offsetHeight || u.getClientRects().length)),
      Bs = () => !mt(pt()) && !mt(lt()) && !mt(ae()),
      _t = u => u.scrollHeight > u.clientHeight,
      Rt = u => {
        const f = window.getComputedStyle(u),
          y = parseFloat(f.getPropertyValue("animation-duration") || "0"),
          S = parseFloat(f.getPropertyValue("transition-duration") || "0");
        return y > 0 || S > 0
      },
      Cs = function (u) {
        let f = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : !1;
        const y = De();
        mt(y) && (f && (y.style.transition = "none", y.style.width = "100%"), setTimeout(() => {
          y.style.transition = "width ".concat(u / 1e3, "s linear"), y.style.width = "0%"
        }, 10))
      },
      Vo = () => {
        const u = De(),
          f = parseInt(window.getComputedStyle(u).width);
        u.style.removeProperty("transition"), u.style.width = "100%";
        const y = parseInt(window.getComputedStyle(u).width),
          S = f / y * 100;
        u.style.removeProperty("transition"), u.style.width = "".concat(S, "%")
      },
      Sn = () => typeof window > "u" || typeof document > "u",
      $l = 100,
      Se = {},
      ms = () => {
        Se.previousActiveElement && Se.previousActiveElement.focus ? (Se.previousActiveElement.focus(), Se.previousActiveElement = null) : document.body && document.body.focus()
      },
      Cl = u => new Promise(f => {
        if (!u) return f();
        const y = window.scrollX,
          S = window.scrollY;
        Se.restoreFocusTimeout = setTimeout(() => {
          ms(), f()
        }, $l), window.scrollTo(y, S)
      }),
      qt = `
 <div aria-labelledby="`.concat(T.title, '" aria-describedby="').concat(T["html-container"], '" class="').concat(T.popup, `" tabindex="-1">
   <button type="button" class="`).concat(T.close, `"></button>
   <ul class="`).concat(T["progress-steps"], `"></ul>
   <div class="`).concat(T.icon, `"></div>
   <img class="`).concat(T.image, `" />
   <h2 class="`).concat(T.title, '" id="').concat(T.title, `"></h2>
   <div class="`).concat(T["html-container"], '" id="').concat(T["html-container"], `"></div>
   <input class="`).concat(T.input, `" />
   <input type="file" class="`).concat(T.file, `" />
   <div class="`).concat(T.range, `">
     <input type="range" />
     <output></output>
   </div>
   <select class="`).concat(T.select, `"></select>
   <div class="`).concat(T.radio, `"></div>
   <label for="`).concat(T.checkbox, '" class="').concat(T.checkbox, `">
     <input type="checkbox" />
     <span class="`).concat(T.label, `"></span>
   </label>
   <textarea class="`).concat(T.textarea, `"></textarea>
   <div class="`).concat(T["validation-message"], '" id="').concat(T["validation-message"], `"></div>
   <div class="`).concat(T.actions, `">
     <div class="`).concat(T.loader, `"></div>
     <button type="button" class="`).concat(T.confirm, `"></button>
     <button type="button" class="`).concat(T.deny, `"></button>
     <button type="button" class="`).concat(T.cancel, `"></button>
   </div>
   <div class="`).concat(T.footer, `"></div>
   <div class="`).concat(T["timer-progress-bar-container"], `">
     <div class="`).concat(T["timer-progress-bar"], `"></div>
   </div>
 </div>
`).replace(/(^|\n)\s*/g, ""),
      Qn = () => {
        const u = Le();
        return u ? (u.remove(), Pe([document.documentElement, document.body], [T["no-backdrop"], T["toast-shown"], T["has-column"]]), !0) : !1
      },
      ks = () => {
        Se.currentInstance.resetValidationMessage()
      },
      Zi = () => {
        const u = ue(),
          f = Oe(u, T.input),
          y = Oe(u, T.file),
          S = u.querySelector(".".concat(T.range, " input")),
          X = u.querySelector(".".concat(T.range, " output")),
          Ee = Oe(u, T.select),
          bt = u.querySelector(".".concat(T.checkbox, " input")),
          Ft = Oe(u, T.textarea);
        f.oninput = ks, y.onchange = ks, Ee.onchange = ks, bt.onchange = ks, Ft.oninput = ks, S.oninput = () => {
          ks(), X.value = S.value
        }, S.onchange = () => {
          ks(), S.nextSibling.value = S.value
        }
      },
      ze = u => typeof u == "string" ? document.querySelector(u) : u,
      as = u => {
        const f = ue();
        f.setAttribute("role", u.toast ? "alert" : "dialog"), f.setAttribute("aria-live", u.toast ? "polite" : "assertive"), u.toast || f.setAttribute("aria-modal", "true")
      },
      As = u => {
        window.getComputedStyle(u).direction === "rtl" && de(Le(), T.rtl)
      },
      Ds = u => {
        const f = Qn();
        if (Sn()) {
          l("SweetAlert2 requires document to initialize");
          return
        }
        const y = document.createElement("div");
        y.className = T.container, f && de(y, T["no-transition"]), se(y, qt);
        const S = ze(u.target);
        S.appendChild(y), as(u), As(S), Zi()
      },
      ct = (u, f) => {
        u instanceof HTMLElement ? f.appendChild(u) : typeof u == "object" ? Ct(u, f) : u && se(f, u)
      },
      Ct = (u, f) => {
        u.jquery ? jo(f, u) : se(f, u.toString())
      },
      jo = (u, f) => {
        if (u.textContent = "", 0 in f)
          for (let y = 0; y in f; y++) u.appendChild(f[y].cloneNode(!0));
        else u.appendChild(f.cloneNode(!0))
      },
      Xs = (() => {
        if (Sn()) return !1;
        const u = document.createElement("div"),
          f = {
            WebkitAnimation: "webkitAnimationEnd",
            animation: "animationend"
          };
        for (const y in f)
          if (Object.prototype.hasOwnProperty.call(f, y) && typeof u.style[y] < "u") return f[y];
        return !1
      })(),
      En = () => {
        const u = document.createElement("div");
        u.className = T["scrollbar-measure"], document.body.appendChild(u);
        const f = u.getBoundingClientRect().width - u.clientWidth;
        return document.body.removeChild(u), f
      },
      Nt = (u, f) => {
        const y = H(),
          S = K();
        !f.showConfirmButton && !f.showDenyButton && !f.showCancelButton ? Xe(y) : Me(y), Q(y, f, "actions"), kl(y, S, f), se(S, f.loaderHtml), Q(S, f, "loader")
      };

    function kl(u, f, y) {
      const S = pt(),
        X = lt(),
        Ee = ae();
      eo(S, "confirm", y), eo(X, "deny", y), eo(Ee, "cancel", y), Xi(S, X, Ee, y), y.reverseButtons && (y.toast ? (u.insertBefore(Ee, S), u.insertBefore(X, S)) : (u.insertBefore(Ee, f), u.insertBefore(X, f), u.insertBefore(S, f)))
    }

    function Xi(u, f, y, S) {
      if (!S.buttonsStyling) return Pe([u, f, y], T.styled);
      de([u, f, y], T.styled), S.confirmButtonColor && (u.style.backgroundColor = S.confirmButtonColor, de(u, T["default-outline"])), S.denyButtonColor && (f.style.backgroundColor = S.denyButtonColor, de(f, T["default-outline"])), S.cancelButtonColor && (y.style.backgroundColor = S.cancelButtonColor, de(y, T["default-outline"]))
    }

    function eo(u, f, y) {
      Xt(u, y["show".concat(i(f), "Button")], "inline-block"), se(u, y["".concat(f, "ButtonText")]), u.setAttribute("aria-label", y["".concat(f, "ButtonAriaLabel")]), u.className = T[f], Q(u, y, "".concat(f, "Button")), de(u, y["".concat(f, "ButtonClass")])
    }

    function Ho(u, f) {
      typeof f == "string" ? u.style.background = f : f || de([document.documentElement, document.body], T["no-backdrop"])
    }

    function Al(u, f) {
      f in T ? de(u, T[f]) : (a('The "position" parameter is not valid, defaulting to "center"'), de(u, T.center))
    }

    function Qi(u, f) {
      if (f && typeof f == "string") {
        const y = "grow-".concat(f);
        y in T && de(u, T[y])
      }
    }
    const xl = (u, f) => {
      const y = Le();
      y && (Ho(y, f.backdrop), Al(y, f.position), Qi(y, f.grow), Q(y, f, "container"))
    };
    var Fe = {
      awaitingPromise: new WeakMap,
      promise: new WeakMap,
      innerParams: new WeakMap,
      domCache: new WeakMap
    };
    const Ms = ["input", "file", "range", "select", "radio", "checkbox", "textarea"],
      Sl = (u, f) => {
        const y = ue(),
          S = Fe.innerParams.get(u),
          X = !S || f.input !== S.input;
        Ms.forEach(Ee => {
          const bt = T[Ee],
            Ft = Oe(y, bt);
          Tl(Ee, f.inputAttributes), Ft.className = bt, X && Xe(Ft)
        }), f.input && (X && El(f), Ll(f))
      },
      El = u => {
        if (!dt[u.input]) return l('Unexpected type of input! Expected "text", "email", "password", "number", "tel", "select", "radio", "checkbox", "textarea", "file" or "url", got "'.concat(u.input, '"'));
        const f = er(u.input),
          y = dt[u.input](f, u);
        Me(y), setTimeout(() => {
          xe(y)
        })
      },
      Pl = u => {
        for (let f = 0; f < u.attributes.length; f++) {
          const y = u.attributes[f].name;
          ["type", "value", "style"].includes(y) || u.removeAttribute(y)
        }
      },
      Tl = (u, f) => {
        const y = pe(ue(), u);
        if (y) {
          Pl(y);
          for (const S in f) y.setAttribute(S, f[S])
        }
      },
      Ll = u => {
        const f = er(u.input);
        u.customClass && de(f, u.customClass.input)
      },
      Pn = (u, f) => {
        (!u.placeholder || f.inputPlaceholder) && (u.placeholder = f.inputPlaceholder)
      },
      Tn = (u, f, y) => {
        if (y.inputLabel) {
          u.id = T.input;
          const S = document.createElement("label"),
            X = T["input-label"];
          S.setAttribute("for", u.id), S.className = X, de(S, y.customClass.inputLabel), S.innerText = y.inputLabel, f.insertAdjacentElement("beforebegin", S)
        }
      },
      er = u => {
        const f = T[u] ? T[u] : T.input;
        return Oe(ue(), f)
      },
      dt = {};
    dt.text = dt.email = dt.password = dt.number = dt.tel = dt.url = (u, f) => (typeof f.inputValue == "string" || typeof f.inputValue == "number" ? u.value = f.inputValue : A(f.inputValue) || a('Unexpected type of inputValue! Expected "string", "number" or "Promise", got "'.concat(typeof f.inputValue, '"')), Tn(u, u, f), Pn(u, f), u.type = f.input, u), dt.file = (u, f) => (Tn(u, u, f), Pn(u, f), u), dt.range = (u, f) => {
      const y = u.querySelector("input"),
        S = u.querySelector("output");
      return y.value = f.inputValue, y.type = f.input, S.value = f.inputValue, Tn(y, u, f), u
    }, dt.select = (u, f) => {
      if (u.textContent = "", f.inputPlaceholder) {
        const y = document.createElement("option");
        se(y, f.inputPlaceholder), y.value = "", y.disabled = !0, y.selected = !0, u.appendChild(y)
      }
      return Tn(u, u, f), u
    }, dt.radio = u => (u.textContent = "", u), dt.checkbox = (u, f) => {
      const y = pe(ue(), "checkbox");
      y.value = "1", y.id = T.checkbox, y.checked = !!f.inputValue;
      const S = u.querySelector("span");
      return se(S, f.inputPlaceholder), u
    }, dt.textarea = (u, f) => {
      u.value = f.inputValue, Pn(u, f), Tn(u, u, f);
      const y = S => parseInt(window.getComputedStyle(S).marginLeft) + parseInt(window.getComputedStyle(S).marginRight);
      return setTimeout(() => {
        if ("MutationObserver" in window) {
          const S = parseInt(window.getComputedStyle(ue()).width),
            X = () => {
              const Ee = u.offsetWidth + y(u);
              Ee > S ? ue().style.width = "".concat(Ee, "px") : ue().style.width = null
            };
          new MutationObserver(X).observe(u, {
            attributes: !0,
            attributeFilter: ["style"]
          })
        }
      }), u
    };
    const Ol = (u, f) => {
        const y = Lt();
        Q(y, f, "htmlContainer"), f.html ? (ct(f.html, y), Me(y, "block")) : f.text ? (y.textContent = f.text, Me(y, "block")) : Xe(y), Sl(u, f)
      },
      Il = (u, f) => {
        const y = ce();
        Xt(y, f.footer), f.footer && ct(f.footer, y), Q(y, f, "footer")
      },
      Bl = (u, f) => {
        const y = Ne();
        se(y, f.closeButtonHtml), Q(y, f, "closeButton"), Xt(y, f.showCloseButton), y.setAttribute("aria-label", f.closeButtonAriaLabel)
      },
      qo = (u, f) => {
        const y = Fe.innerParams.get(u),
          S = te();
        if (y && f.icon === y.icon) {
          sr(S, f), tr(S, f);
          return
        }
        if (!f.icon && !f.iconHtml) return Xe(S);
        if (f.icon && Object.keys(re).indexOf(f.icon) === -1) return l('Unknown icon! Expected "success", "error", "warning", "info" or "question", got "'.concat(f.icon, '"')), Xe(S);
        Me(S), sr(S, f), tr(S, f), de(S, f.showClass.icon)
      },
      tr = (u, f) => {
        for (const y in re) f.icon !== y && Pe(u, re[y]);
        de(u, re[f.icon]), tt(u, f), Dl(), Q(u, f, "icon")
      },
      Dl = () => {
        const u = ue(),
          f = window.getComputedStyle(u).getPropertyValue("background-color"),
          y = u.querySelectorAll("[class^=swal2-success-circular-line], .swal2-success-fix");
        for (let S = 0; S < y.length; S++) y[S].style.backgroundColor = f
      },
      Ml = `
  <div class="swal2-success-circular-line-left"></div>
  <span class="swal2-success-line-tip"></span> <span class="swal2-success-line-long"></span>
  <div class="swal2-success-ring"></div> <div class="swal2-success-fix"></div>
  <div class="swal2-success-circular-line-right"></div>
`,
      Rl = `
  <span class="swal2-x-mark">
    <span class="swal2-x-mark-line-left"></span>
    <span class="swal2-x-mark-line-right"></span>
  </span>
`,
      sr = (u, f) => {
        u.textContent = "", f.iconHtml ? se(u, nr(f.iconHtml)) : f.icon === "success" ? se(u, Ml) : f.icon === "error" ? se(u, Rl) : se(u, nr({
          question: "?",
          warning: "!",
          info: "i"
        } [f.icon]))
      },
      tt = (u, f) => {
        if (f.iconColor) {
          u.style.color = f.iconColor, u.style.borderColor = f.iconColor;
          for (const y of [".swal2-success-line-tip", ".swal2-success-line-long", ".swal2-x-mark-line-left", ".swal2-x-mark-line-right"]) Ot(u, y, "backgroundColor", f.iconColor);
          Ot(u, ".swal2-success-ring", "borderColor", f.iconColor)
        }
      },
      nr = u => '<div class="'.concat(T["icon-content"], '">').concat(u, "</div>"),
      Nl = (u, f) => {
        const y = xt();
        if (!f.imageUrl) return Xe(y);
        Me(y, ""), y.setAttribute("src", f.imageUrl), y.setAttribute("alt", f.imageAlt), He(y, "width", f.imageWidth), He(y, "height", f.imageHeight), y.className = T.image, Q(y, f, "image")
      },
      Fl = u => {
        const f = document.createElement("li");
        return de(f, T["progress-step"]), se(f, u), f
      },
      Ul = u => {
        const f = document.createElement("li");
        return de(f, T["progress-step-line"]), u.progressStepsDistance && (f.style.width = u.progressStepsDistance), f
      },
      Vl = (u, f) => {
        const y = ht();
        if (!f.progressSteps || f.progressSteps.length === 0) return Xe(y);
        Me(y), y.textContent = "", f.currentProgressStep >= f.progressSteps.length && a("Invalid currentProgressStep parameter, it should be less than progressSteps.length (currentProgressStep like JS arrays starts from 0)"), f.progressSteps.forEach((S, X) => {
          const Ee = Fl(S);
          if (y.appendChild(Ee), X === f.currentProgressStep && de(Ee, T["active-progress-step"]), X !== f.progressSteps.length - 1) {
            const bt = Ul(f);
            y.appendChild(bt)
          }
        })
      },
      jl = (u, f) => {
        const y = $t();
        Xt(y, f.title || f.titleText, "block"), f.title && ct(f.title, y), f.titleText && (y.innerText = f.titleText), Q(y, f, "title")
      },
      Wo = (u, f) => {
        const y = Le(),
          S = ue();
        f.toast ? (He(y, "width", f.width), S.style.width = "100%", S.insertBefore(K(), te())) : He(S, "width", f.width), He(S, "padding", f.padding), f.color && (S.style.color = f.color), f.background && (S.style.background = f.background), Xe(U()), Ln(S, f)
      },
      Ln = (u, f) => {
        u.className = "".concat(T.popup, " ").concat(mt(u) ? f.showClass.popup : ""), f.toast ? (de([document.documentElement, document.body], T["toast-shown"]), de(u, T.toast)) : de(u, T.modal), Q(u, f, "popup"), typeof f.customClass == "string" && de(u, f.customClass), f.icon && de(u, T["icon-".concat(f.icon)])
      },
      zo = (u, f) => {
        Wo(u, f), xl(u, f), Vl(u, f), qo(u, f), Nl(u, f), jl(u, f), Bl(u, f), Ol(u, f), Nt(u, f), Il(u, f), typeof f.didRender == "function" && f.didRender(ue())
      },
      gs = Object.freeze({
        cancel: "cancel",
        backdrop: "backdrop",
        close: "close",
        esc: "esc",
        timer: "timer"
      }),
      at = () => {
        r(document.body.children).forEach(f => {
          f === Le() || f.contains(Le()) || (f.hasAttribute("aria-hidden") && f.setAttribute("data-previous-aria-hidden", f.getAttribute("aria-hidden")), f.setAttribute("aria-hidden", "true"))
        })
      },
      or = () => {
        r(document.body.children).forEach(f => {
          f.hasAttribute("data-previous-aria-hidden") ? (f.setAttribute("aria-hidden", f.getAttribute("data-previous-aria-hidden")), f.removeAttribute("data-previous-aria-hidden")) : f.removeAttribute("aria-hidden")
        })
      },
      ir = ["swal-title", "swal-html", "swal-footer"],
      Hl = u => {
        const f = typeof u.template == "string" ? document.querySelector(u.template) : u.template;
        if (!f) return {};
        const y = f.content;
        return lr(y), Object.assign(rr(y), ql(y), Wl(y), zl(y), ar(y), Kl(y, ir))
      },
      rr = u => {
        const f = {};
        return r(u.querySelectorAll("swal-param")).forEach(y => {
          Rs(y, ["name", "value"]);
          const S = y.getAttribute("name"),
            X = y.getAttribute("value");
          typeof O[S] == "boolean" && X === "false" && (f[S] = !1), typeof O[S] == "object" && (f[S] = JSON.parse(X))
        }), f
      },
      ql = u => {
        const f = {};
        return r(u.querySelectorAll("swal-button")).forEach(y => {
          Rs(y, ["type", "color", "aria-label"]);
          const S = y.getAttribute("type");
          f["".concat(S, "ButtonText")] = y.innerHTML, f["show".concat(i(S), "Button")] = !0, y.hasAttribute("color") && (f["".concat(S, "ButtonColor")] = y.getAttribute("color")), y.hasAttribute("aria-label") && (f["".concat(S, "ButtonAriaLabel")] = y.getAttribute("aria-label"))
        }), f
      },
      Wl = u => {
        const f = {},
          y = u.querySelector("swal-image");
        return y && (Rs(y, ["src", "width", "height", "alt"]), y.hasAttribute("src") && (f.imageUrl = y.getAttribute("src")), y.hasAttribute("width") && (f.imageWidth = y.getAttribute("width")), y.hasAttribute("height") && (f.imageHeight = y.getAttribute("height")), y.hasAttribute("alt") && (f.imageAlt = y.getAttribute("alt"))), f
      },
      zl = u => {
        const f = {},
          y = u.querySelector("swal-icon");
        return y && (Rs(y, ["type", "color"]), y.hasAttribute("type") && (f.icon = y.getAttribute("type")), y.hasAttribute("color") && (f.iconColor = y.getAttribute("color")), f.iconHtml = y.innerHTML), f
      },
      ar = u => {
        const f = {},
          y = u.querySelector("swal-input");
        y && (Rs(y, ["type", "label", "placeholder", "value"]), f.input = y.getAttribute("type") || "text", y.hasAttribute("label") && (f.inputLabel = y.getAttribute("label")), y.hasAttribute("placeholder") && (f.inputPlaceholder = y.getAttribute("placeholder")), y.hasAttribute("value") && (f.inputValue = y.getAttribute("value")));
        const S = u.querySelectorAll("swal-input-option");
        return S.length && (f.inputOptions = {}, r(S).forEach(X => {
          Rs(X, ["value"]);
          const Ee = X.getAttribute("value"),
            bt = X.innerHTML;
          f.inputOptions[Ee] = bt
        })), f
      },
      Kl = (u, f) => {
        const y = {};
        for (const S in f) {
          const X = f[S],
            Ee = u.querySelector(X);
          Ee && (Rs(Ee, []), y[X.replace(/^swal-/, "")] = Ee.innerHTML.trim())
        }
        return y
      },
      lr = u => {
        const f = ir.concat(["swal-param", "swal-button", "swal-image", "swal-icon", "swal-input", "swal-input-option"]);
        r(u.children).forEach(y => {
          const S = y.tagName.toLowerCase();
          f.indexOf(S) === -1 && a("Unrecognized element <".concat(S, ">"))
        })
      },
      Rs = (u, f) => {
        r(u.attributes).forEach(y => {
          f.indexOf(y.name) === -1 && a(['Unrecognized attribute "'.concat(y.name, '" on <').concat(u.tagName.toLowerCase(), ">."), "".concat(f.length ? "Allowed attributes are: ".concat(f.join(", ")) : "To set the value, use HTML within the element.")])
        })
      };
    var cr = {
      email: (u, f) => /^[a-zA-Z0-9.+_-]+@[a-zA-Z0-9.-]+\.[a-zA-Z0-9-]{2,24}$/.test(u) ? Promise.resolve() : Promise.resolve(f || "Invalid email address"),
      url: (u, f) => /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-z]{2,63}\b([-a-zA-Z0-9@:%_+.~#?&/=]*)$/.test(u) ? Promise.resolve() : Promise.resolve(f || "Invalid URL")
    };

    function Gl(u) {
      u.inputValidator || Object.keys(cr).forEach(f => {
        u.input === f && (u.inputValidator = cr[f])
      })
    }

    function Yl(u) {
      (!u.target || typeof u.target == "string" && !document.querySelector(u.target) || typeof u.target != "string" && !u.target.appendChild) && (a('Target parameter is not valid, defaulting to "body"'), u.target = "body")
    }

    function Jl(u) {
      Gl(u), u.showLoaderOnConfirm && !u.preConfirm && a(`showLoaderOnConfirm is set to true, but preConfirm is not defined.
showLoaderOnConfirm should be used together with preConfirm, see usage example:
https://sweetalert2.github.io/#ajax-request`), Yl(u), typeof u.title == "string" && (u.title = u.title.split(`
`).join("<br />")), Ds(u)
    }
    class Zl {
      constructor(f, y) {
        this.callback = f, this.remaining = y, this.running = !1, this.start()
      }
      start() {
        return this.running || (this.running = !0, this.started = new Date, this.id = setTimeout(this.callback, this.remaining)), this.remaining
      }
      stop() {
        return this.running && (this.running = !1, clearTimeout(this.id), this.remaining -= new Date().getTime() - this.started.getTime()), this.remaining
      }
      increase(f) {
        const y = this.running;
        return y && this.stop(), this.remaining += f, y && this.start(), this.remaining
      }
      getTimerLeft() {
        return this.running && (this.stop(), this.start()), this.remaining
      }
      isRunning() {
        return this.running
      }
    }
    const Xl = () => {
        oe.previousBodyPadding === null && document.body.scrollHeight > window.innerHeight && (oe.previousBodyPadding = parseInt(window.getComputedStyle(document.body).getPropertyValue("padding-right")), document.body.style.paddingRight = "".concat(oe.previousBodyPadding + En(), "px"))
      },
      dr = () => {
        oe.previousBodyPadding !== null && (document.body.style.paddingRight = "".concat(oe.previousBodyPadding, "px"), oe.previousBodyPadding = null)
      },
      Ql = () => {
        if ((/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream || navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1) && !G(document.body, T.iosfix)) {
          const f = document.body.scrollTop;
          document.body.style.top = "".concat(f * -1, "px"), de(document.body, T.iosfix), ec(), ur()
        }
      },
      ur = () => {
        const u = navigator.userAgent,
          f = !!u.match(/iPad/i) || !!u.match(/iPhone/i),
          y = !!u.match(/WebKit/i);
        f && y && !u.match(/CriOS/i) && ue().scrollHeight > window.innerHeight - 44 && (Le().style.paddingBottom = "".concat(44, "px"))
      },
      ec = () => {
        const u = Le();
        let f;
        u.ontouchstart = y => {
          f = tc(y)
        }, u.ontouchmove = y => {
          f && (y.preventDefault(), y.stopPropagation())
        }
      },
      tc = u => {
        const f = u.target,
          y = Le();
        return Qs(u) || fr(u) ? !1 : f === y || !_t(y) && f.tagName !== "INPUT" && f.tagName !== "TEXTAREA" && !(_t(Lt()) && Lt().contains(f))
      },
      Qs = u => u.touches && u.touches.length && u.touches[0].touchType === "stylus",
      fr = u => u.touches && u.touches.length > 1,
      sc = () => {
        if (G(document.body, T.iosfix)) {
          const u = parseInt(document.body.style.top, 10);
          Pe(document.body, T.iosfix), document.body.style.top = "", document.body.scrollTop = u * -1
        }
      },
      to = 10,
      hr = u => {
        const f = Le(),
          y = ue();
        typeof u.willOpen == "function" && u.willOpen(y);
        const X = window.getComputedStyle(document.body).overflowY;
        ic(f, y, u), setTimeout(() => {
          nc(f, y)
        }, to), N() && (oc(f, u.scrollbarPadding, X), at()), !Y() && !Se.previousActiveElement && (Se.previousActiveElement = document.activeElement), typeof u.didOpen == "function" && setTimeout(() => u.didOpen(y)), Pe(f, T["no-transition"])
      },
      pr = u => {
        const f = ue();
        if (u.target !== f) return;
        const y = Le();
        f.removeEventListener(Xs, pr), y.style.overflowY = "auto"
      },
      nc = (u, f) => {
        Xs && Rt(f) ? (u.style.overflowY = "hidden", f.addEventListener(Xs, pr)) : u.style.overflowY = "auto"
      },
      oc = (u, f, y) => {
        Ql(), f && y !== "hidden" && Xl(), setTimeout(() => {
          u.scrollTop = 0
        })
      },
      ic = (u, f, y) => {
        de(u, y.showClass.backdrop), f.style.setProperty("opacity", "0", "important"), Me(f, "grid"), setTimeout(() => {
          de(f, y.showClass.popup), f.style.removeProperty("opacity")
        }, to), de([document.documentElement, document.body], T.shown), y.heightAuto && y.backdrop && !y.toast && de([document.documentElement, document.body], T["height-auto"])
      },
      en = u => {
        let f = ue();
        f || new rn, f = ue();
        const y = K();
        Y() ? Xe(te()) : rc(f, u), Me(y), f.setAttribute("data-loading", !0), f.setAttribute("aria-busy", !0), f.focus()
      },
      rc = (u, f) => {
        const y = H(),
          S = K();
        !f && mt(pt()) && (f = pt()), Me(y), f && (Xe(f), S.setAttribute("data-button-to-replace", f.className)), S.parentNode.insertBefore(S, f), de([u, y], T.loading)
      },
      tn = (u, f) => {
        f.input === "select" || f.input === "radio" ? cc(u, f) : ["text", "email", "number", "tel", "textarea"].includes(f.input) && ($(f.inputValue) || A(f.inputValue)) && (en(pt()), dc(u, f))
      },
      ac = (u, f) => {
        const y = u.getInput();
        if (!y) return null;
        switch (f.input) {
          case "checkbox":
            return mr(y);
          case "radio":
            return gr(y);
          case "file":
            return lc(y);
          default:
            return f.inputAutoTrim ? y.value.trim() : y.value
        }
      },
      mr = u => u.checked ? 1 : 0,
      gr = u => u.checked ? u.value : null,
      lc = u => u.files.length ? u.getAttribute("multiple") !== null ? u.files : u.files[0] : null,
      cc = (u, f) => {
        const y = ue(),
          S = X => uc[f.input](y, Ko(X), f);
        $(f.inputOptions) || A(f.inputOptions) ? (en(pt()), C(f.inputOptions).then(X => {
          u.hideLoading(), S(X)
        })) : typeof f.inputOptions == "object" ? S(f.inputOptions) : l("Unexpected type of inputOptions! Expected object, Map or Promise, got ".concat(typeof f.inputOptions))
      },
      dc = (u, f) => {
        const y = u.getInput();
        Xe(y), C(f.inputValue).then(S => {
          y.value = f.input === "number" ? parseFloat(S) || 0 : "".concat(S), Me(y), y.focus(), u.hideLoading()
        }).catch(S => {
          l("Error in inputValue promise: ".concat(S)), y.value = "", Me(y), y.focus(), u.hideLoading()
        })
      },
      uc = {
        select: (u, f, y) => {
          const S = Oe(u, T.select),
            X = (Ee, bt, Ft) => {
              const St = document.createElement("option");
              St.value = Ft, se(St, bt), St.selected = _r(Ft, y.inputValue), Ee.appendChild(St)
            };
          f.forEach(Ee => {
            const bt = Ee[0],
              Ft = Ee[1];
            if (Array.isArray(Ft)) {
              const St = document.createElement("optgroup");
              St.label = bt, St.disabled = !1, S.appendChild(St), Ft.forEach(an => X(St, an[1], an[0]))
            } else X(S, Ft, bt)
          }), S.focus()
        },
        radio: (u, f, y) => {
          const S = Oe(u, T.radio);
          f.forEach(Ee => {
            const bt = Ee[0],
              Ft = Ee[1],
              St = document.createElement("input"),
              an = document.createElement("label");
            St.type = "radio", St.name = T.radio, St.value = bt, _r(bt, y.inputValue) && (St.checked = !0);
            const oi = document.createElement("span");
            se(oi, Ft), oi.className = T.label, an.appendChild(St), an.appendChild(oi), S.appendChild(an)
          });
          const X = S.querySelectorAll("input");
          X.length && X[0].focus()
        }
      },
      Ko = u => {
        const f = [];
        return typeof Map < "u" && u instanceof Map ? u.forEach((y, S) => {
          let X = y;
          typeof X == "object" && (X = Ko(X)), f.push([S, X])
        }) : Object.keys(u).forEach(y => {
          let S = u[y];
          typeof S == "object" && (S = Ko(S)), f.push([y, S])
        }), f
      },
      _r = (u, f) => f && f.toString() === u.toString(),
      fc = u => {
        const f = Fe.innerParams.get(u);
        u.disableButtons(), f.input ? br(u, "confirm") : so(u, !0)
      },
      sn = u => {
        const f = Fe.innerParams.get(u);
        u.disableButtons(), f.returnInputValueOnDeny ? br(u, "deny") : Go(u, !1)
      },
      hc = (u, f) => {
        u.disableButtons(), f(gs.cancel)
      },
      br = (u, f) => {
        const y = Fe.innerParams.get(u);
        if (!y.input) return l('The "input" parameter is needed to be set when using returnInputValueOn'.concat(i(f)));
        const S = ac(u, y);
        y.inputValidator ? vr(u, S, f) : u.getInput().checkValidity() ? f === "deny" ? Go(u, S) : so(u, S) : (u.enableButtons(), u.showValidationMessage(y.validationMessage))
      },
      vr = (u, f, y) => {
        const S = Fe.innerParams.get(u);
        u.disableInput(), Promise.resolve().then(() => C(S.inputValidator(f, S.validationMessage))).then(Ee => {
          u.enableButtons(), u.enableInput(), Ee ? u.showValidationMessage(Ee) : y === "deny" ? Go(u, f) : so(u, f)
        })
      },
      Go = (u, f) => {
        const y = Fe.innerParams.get(u || void 0);
        y.showLoaderOnDeny && en(lt()), y.preDeny ? (Fe.awaitingPromise.set(u || void 0, !0), Promise.resolve().then(() => C(y.preDeny(f, y.validationMessage))).then(X => {
          X === !1 ? u.hideLoading() : u.closePopup({
            isDenied: !0,
            value: typeof X > "u" ? f : X
          })
        }).catch(X => yr(u || void 0, X))) : u.closePopup({
          isDenied: !0,
          value: f
        })
      },
      _s = (u, f) => {
        u.closePopup({
          isConfirmed: !0,
          value: f
        })
      },
      yr = (u, f) => {
        u.rejectPromise(f)
      },
      so = (u, f) => {
        const y = Fe.innerParams.get(u || void 0);
        y.showLoaderOnConfirm && en(), y.preConfirm ? (u.resetValidationMessage(), Fe.awaitingPromise.set(u || void 0, !0), Promise.resolve().then(() => C(y.preConfirm(f, y.validationMessage))).then(X => {
          mt(U()) || X === !1 ? u.hideLoading() : _s(u, typeof X > "u" ? f : X)
        }).catch(X => yr(u || void 0, X))) : _s(u, f)
      },
      On = (u, f, y) => {
        Fe.innerParams.get(u).toast ? pc(u, f, y) : (wr(f), nn(f), gc(u, f, y))
      },
      pc = (u, f, y) => {
        f.popup.onclick = () => {
          const S = Fe.innerParams.get(u);
          S && (mc(S) || S.timer || S.input) || y(gs.close)
        }
      },
      mc = u => u.showConfirmButton || u.showDenyButton || u.showCancelButton || u.showCloseButton;
    let no = !1;
    const wr = u => {
        u.popup.onmousedown = () => {
          u.container.onmouseup = function (f) {
            u.container.onmouseup = void 0, f.target === u.container && (no = !0)
          }
        }
      },
      nn = u => {
        u.container.onmousedown = () => {
          u.popup.onmouseup = function (f) {
            u.popup.onmouseup = void 0, (f.target === u.popup || u.popup.contains(f.target)) && (no = !0)
          }
        }
      },
      gc = (u, f, y) => {
        f.container.onclick = S => {
          const X = Fe.innerParams.get(u);
          if (no) {
            no = !1;
            return
          }
          S.target === f.container && g(X.allowOutsideClick) && y(gs.backdrop)
        }
      },
      _c = () => mt(ue()),
      $r = () => pt() && pt().click(),
      bc = () => lt() && lt().click(),
      vc = () => ae() && ae().click(),
      yc = (u, f, y, S) => {
        f.keydownTarget && f.keydownHandlerAdded && (f.keydownTarget.removeEventListener("keydown", f.keydownHandler, {
          capture: f.keydownListenerCapture
        }), f.keydownHandlerAdded = !1), y.toast || (f.keydownHandler = X => $c(u, X, S), f.keydownTarget = y.keydownListenerCapture ? window : ue(), f.keydownListenerCapture = y.keydownListenerCapture, f.keydownTarget.addEventListener("keydown", f.keydownHandler, {
          capture: f.keydownListenerCapture
        }), f.keydownHandlerAdded = !0)
      },
      Yo = (u, f, y) => {
        const S = x();
        if (S.length) return f = f + y, f === S.length ? f = 0 : f === -1 && (f = S.length - 1), S[f].focus();
        ue().focus()
      },
      Cr = ["ArrowRight", "ArrowDown"],
      wc = ["ArrowLeft", "ArrowUp"],
      $c = (u, f, y) => {
        const S = Fe.innerParams.get(u);
        S && (S.stopKeydownPropagation && f.stopPropagation(), f.key === "Enter" ? kr(u, f, S) : f.key === "Tab" ? Ar(f, S) : [...Cr, ...wc].includes(f.key) ? oo(f.key) : f.key === "Escape" && Cc(f, S, y))
      },
      kr = (u, f, y) => {
        if (!(!g(y.allowEnterKey) || f.isComposing) && f.target && u.getInput() && f.target.outerHTML === u.getInput().outerHTML) {
          if (["textarea", "file"].includes(y.input)) return;
          $r(), f.preventDefault()
        }
      },
      Ar = (u, f) => {
        const y = u.target,
          S = x();
        let X = -1;
        for (let Ee = 0; Ee < S.length; Ee++)
          if (y === S[Ee]) {
            X = Ee;
            break
          } u.shiftKey ? Yo(f, X, -1) : Yo(f, X, 1), u.stopPropagation(), u.preventDefault()
      },
      oo = u => {
        const f = pt(),
          y = lt(),
          S = ae();
        if (![f, y, S].includes(document.activeElement)) return;
        const X = Cr.includes(u) ? "nextElementSibling" : "previousElementSibling",
          Ee = document.activeElement[X];
        Ee instanceof HTMLElement && Ee.focus()
      },
      Cc = (u, f, y) => {
        g(f.allowEscapeKey) && (u.preventDefault(), y(gs.esc))
      },
      kc = u => typeof u == "object" && u.jquery,
      xr = u => u instanceof Element || kc(u),
      Sr = u => {
        const f = {};
        return typeof u[0] == "object" && !xr(u[0]) ? Object.assign(f, u[0]) : ["title", "html", "icon"].forEach((y, S) => {
          const X = u[S];
          typeof X == "string" || xr(X) ? f[y] = X : X !== void 0 && l("Unexpected type of ".concat(y, '! Expected "string" or "Element", got ').concat(typeof X))
        }), f
      };

    function Ac() {
      const u = this;
      for (var f = arguments.length, y = new Array(f), S = 0; S < f; S++) y[S] = arguments[S];
      return new u(...y)
    }

    function Jo(u) {
      class f extends this {
        _main(S, X) {
          return super._main(S, Object.assign({}, u, X))
        }
      }
      return f
    }
    const Er = () => Se.timeout && Se.timeout.getTimerLeft(),
      Zo = () => {
        if (Se.timeout) return Vo(), Se.timeout.stop()
      },
      io = () => {
        if (Se.timeout) {
          const u = Se.timeout.start();
          return Cs(u), u
        }
      },
      ro = () => {
        const u = Se.timeout;
        return u && (u.running ? Zo() : io())
      },
      xc = u => {
        if (Se.timeout) {
          const f = Se.timeout.increase(u);
          return Cs(f, !0), f
        }
      },
      Pr = () => Se.timeout && Se.timeout.isRunning();
    let Xo = !1;
    const ao = {};

    function Tr() {
      let u = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : "data-swal-template";
      ao[u] = this, Xo || (document.body.addEventListener("click", Lr), Xo = !0)
    }
    const Lr = u => {
      for (let f = u.target; f && f !== document; f = f.parentNode)
        for (const y in ao) {
          const S = f.getAttribute(y);
          if (S) {
            ao[y].fire({
              template: S
            });
            return
          }
        }
    };
    var Or = Object.freeze({
      isValidParameter: P,
      isUpdatableParameter: I,
      isDeprecatedParameter: R,
      argsToParams: Sr,
      isVisible: _c,
      clickConfirm: $r,
      clickDeny: bc,
      clickCancel: vc,
      getContainer: Le,
      getPopup: ue,
      getTitle: $t,
      getHtmlContainer: Lt,
      getImage: xt,
      getIcon: te,
      getInputLabel: ot,
      getCloseButton: Ne,
      getActions: H,
      getConfirmButton: pt,
      getDenyButton: lt,
      getCancelButton: ae,
      getLoader: K,
      getFooter: ce,
      getTimerProgressBar: De,
      getFocusableElements: x,
      getValidationMessage: U,
      isLoading: W,
      fire: Ac,
      mixin: Jo,
      showLoading: en,
      enableLoading: en,
      getTimerLeft: Er,
      stopTimer: Zo,
      resumeTimer: io,
      toggleTimer: ro,
      increaseTimer: xc,
      isTimerRunning: Pr,
      bindClickHandler: Tr
    });

    function Qo() {
      const u = Fe.innerParams.get(this);
      if (!u) return;
      const f = Fe.domCache.get(this);
      Xe(f.loader), Y() ? u.icon && Me(te()) : Ir(f), Pe([f.popup, f.actions], T.loading), f.popup.removeAttribute("aria-busy"), f.popup.removeAttribute("data-loading"), f.confirmButton.disabled = !1, f.denyButton.disabled = !1, f.cancelButton.disabled = !1
    }
    const Ir = u => {
      const f = u.popup.getElementsByClassName(u.loader.getAttribute("data-button-to-replace"));
      f.length ? Me(f[0], "inline-block") : Bs() && Xe(u.actions)
    };

    function Br(u) {
      const f = Fe.innerParams.get(u || this),
        y = Fe.domCache.get(u || this);
      return y ? pe(y.popup, f.input) : null
    }
    var ls = {
      swalPromiseResolve: new WeakMap,
      swalPromiseReject: new WeakMap
    };

    function Dr(u, f, y, S) {
      Y() ? ei(u, S) : (Cl(y).then(() => ei(u, S)), Se.keydownTarget.removeEventListener("keydown", Se.keydownHandler, {
        capture: Se.keydownListenerCapture
      }), Se.keydownHandlerAdded = !1), /^((?!chrome|android).)*safari/i.test(navigator.userAgent) ? (f.setAttribute("style", "display:none !important"), f.removeAttribute("class"), f.innerHTML = "") : f.remove(), N() && (dr(), sc(), or()), Sc()
    }

    function Sc() {
      Pe([document.documentElement, document.body], [T.shown, T["height-auto"], T["no-backdrop"], T["toast-shown"]])
    }

    function lo(u) {
      u = Ec(u);
      const f = ls.swalPromiseResolve.get(this),
        y = Rr(this);
      this.isAwaitingPromise() ? u.isDismissed || (Fr(this), f(u)) : y && f(u)
    }

    function Mr() {
      return !!Fe.awaitingPromise.get(this)
    }
    const Rr = u => {
      const f = ue();
      if (!f) return !1;
      const y = Fe.innerParams.get(u);
      if (!y || G(f, y.hideClass.popup)) return !1;
      Pe(f, y.showClass.popup), de(f, y.hideClass.popup);
      const S = Le();
      return Pe(S, y.showClass.backdrop), de(S, y.hideClass.backdrop), Pc(u, f, y), !0
    };

    function Nr(u) {
      const f = ls.swalPromiseReject.get(this);
      Fr(this), f && f(u)
    }
    const Fr = u => {
        u.isAwaitingPromise() && (Fe.awaitingPromise.delete(u), Fe.innerParams.get(u) || u._destroy())
      },
      Ec = u => typeof u > "u" ? {
        isConfirmed: !1,
        isDenied: !1,
        isDismissed: !0
      } : Object.assign({
        isConfirmed: !1,
        isDenied: !1,
        isDismissed: !1
      }, u),
      Pc = (u, f, y) => {
        const S = Le(),
          X = Xs && Rt(f);
        typeof y.willClose == "function" && y.willClose(f), X ? Tc(u, f, S, y.returnFocus, y.didClose) : Dr(u, S, y.returnFocus, y.didClose)
      },
      Tc = (u, f, y, S, X) => {
        Se.swalCloseEventFinishedCallback = Dr.bind(null, u, y, S, X), f.addEventListener(Xs, function (Ee) {
          Ee.target === f && (Se.swalCloseEventFinishedCallback(), delete Se.swalCloseEventFinishedCallback)
        })
      },
      ei = (u, f) => {
        setTimeout(() => {
          typeof f == "function" && f.bind(u.params)(), u._destroy()
        })
      };

    function Ur(u, f, y) {
      const S = Fe.domCache.get(u);
      f.forEach(X => {
        S[X].disabled = y
      })
    }

    function ti(u, f) {
      if (!u) return !1;
      if (u.type === "radio") {
        const S = u.parentNode.parentNode.querySelectorAll("input");
        for (let X = 0; X < S.length; X++) S[X].disabled = f
      } else u.disabled = f
    }

    function Lc() {
      Ur(this, ["confirmButton", "denyButton", "cancelButton"], !1)
    }

    function Vr() {
      Ur(this, ["confirmButton", "denyButton", "cancelButton"], !0)
    }

    function si() {
      return ti(this.getInput(), !1)
    }

    function Oc() {
      return ti(this.getInput(), !0)
    }

    function Ic(u) {
      const f = Fe.domCache.get(this),
        y = Fe.innerParams.get(this);
      se(f.validationMessage, u), f.validationMessage.className = T["validation-message"], y.customClass && y.customClass.validationMessage && de(f.validationMessage, y.customClass.validationMessage), Me(f.validationMessage);
      const S = this.getInput();
      S && (S.setAttribute("aria-invalid", !0), S.setAttribute("aria-describedby", T["validation-message"]), xe(S), de(S, T.inputerror))
    }

    function Bc() {
      const u = Fe.domCache.get(this);
      u.validationMessage && Xe(u.validationMessage);
      const f = this.getInput();
      f && (f.removeAttribute("aria-invalid"), f.removeAttribute("aria-describedby"), Pe(f, T.inputerror))
    }

    function Dc() {
      return Fe.domCache.get(this).progressSteps
    }

    function co(u) {
      const f = ue(),
        y = Fe.innerParams.get(this);
      if (!f || G(f, y.hideClass.popup)) return a("You're trying to update the closed or closing popup, that won't work. Use the update() method in preConfirm parameter or show a new popup.");
      const S = Mc(u),
        X = Object.assign({}, y, S);
      zo(this, X), Fe.innerParams.set(this, X), Object.defineProperties(this, {
        params: {
          value: Object.assign({}, this.params, u),
          writable: !1,
          enumerable: !0
        }
      })
    }
    const Mc = u => {
      const f = {};
      return Object.keys(u).forEach(y => {
        I(y) ? f[y] = u[y] : a('Invalid parameter to update: "'.concat(y, `". Updatable params are listed here: https://github.com/sweetalert2/sweetalert2/blob/master/src/utils/params.js

If you think this parameter should be updatable, request it here: https://github.com/sweetalert2/sweetalert2/issues/new?template=02_feature_request.md`))
      }), f
    };

    function Rc() {
      const u = Fe.domCache.get(this),
        f = Fe.innerParams.get(this);
      if (!f) {
        jr(this);
        return
      }
      u.popup && Se.swalCloseEventFinishedCallback && (Se.swalCloseEventFinishedCallback(), delete Se.swalCloseEventFinishedCallback), Se.deferDisposalTimer && (clearTimeout(Se.deferDisposalTimer), delete Se.deferDisposalTimer), typeof f.didDestroy == "function" && f.didDestroy(), Nc(this)
    }
    const Nc = u => {
        jr(u), delete u.params, delete Se.keydownHandler, delete Se.keydownTarget, delete Se.currentInstance
      },
      jr = u => {
        u.isAwaitingPromise() ? (uo(Fe, u), Fe.awaitingPromise.set(u, !0)) : (uo(ls, u), uo(Fe, u))
      },
      uo = (u, f) => {
        for (const y in u) u[y].delete(f)
      };
    var Hr = Object.freeze({
      hideLoading: Qo,
      disableLoading: Qo,
      getInput: Br,
      close: lo,
      isAwaitingPromise: Mr,
      rejectPromise: Nr,
      closePopup: lo,
      closeModal: lo,
      closeToast: lo,
      enableButtons: Lc,
      disableButtons: Vr,
      enableInput: si,
      disableInput: Oc,
      showValidationMessage: Ic,
      resetValidationMessage: Bc,
      getProgressSteps: Dc,
      update: co,
      _destroy: Rc
    });
    let ni;
    class on {
      constructor() {
        if (typeof window > "u") return;
        ni = this;
        for (var f = arguments.length, y = new Array(f), S = 0; S < f; S++) y[S] = arguments[S];
        const X = Object.freeze(this.constructor.argsToParams(y));
        Object.defineProperties(this, {
          params: {
            value: X,
            writable: !1,
            enumerable: !0,
            configurable: !0
          }
        });
        const Ee = this._main(this.params);
        Fe.promise.set(this, Ee)
      }
      _main(f) {
        let y = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
        ne(Object.assign({}, y, f)), Se.currentInstance && (Se.currentInstance._destroy(), N() && or()), Se.currentInstance = this;
        const S = qr(f, y);
        Jl(S), Object.freeze(S), Se.timeout && (Se.timeout.stop(), delete Se.timeout), clearTimeout(Se.restoreFocusTimeout);
        const X = Wr(this);
        return zo(this, S), Fe.innerParams.set(this, S), Fc(this, X, S)
      }
      then(f) {
        return Fe.promise.get(this).then(f)
      } finally(f) {
        return Fe.promise.get(this).finally(f)
      }
    }
    const Fc = (u, f, y) => new Promise((S, X) => {
        const Ee = bt => {
          u.closePopup({
            isDismissed: !0,
            dismiss: bt
          })
        };
        ls.swalPromiseResolve.set(u, S), ls.swalPromiseReject.set(u, X), f.confirmButton.onclick = () => fc(u), f.denyButton.onclick = () => sn(u), f.cancelButton.onclick = () => hc(u, Ee), f.closeButton.onclick = () => Ee(gs.close), On(u, f, Ee), yc(u, Se, y, Ee), tn(u, y), hr(y), Uc(Se, y, Ee), Vc(f, y), setTimeout(() => {
          f.container.scrollTop = 0
        })
      }),
      qr = (u, f) => {
        const y = Hl(u),
          S = Object.assign({}, O, f, y, u);
        return S.showClass = Object.assign({}, O.showClass, S.showClass), S.hideClass = Object.assign({}, O.hideClass, S.hideClass), S
      },
      Wr = u => {
        const f = {
          popup: ue(),
          container: Le(),
          actions: H(),
          confirmButton: pt(),
          denyButton: lt(),
          cancelButton: ae(),
          loader: K(),
          closeButton: Ne(),
          validationMessage: U(),
          progressSteps: ht()
        };
        return Fe.domCache.set(u, f), f
      },
      Uc = (u, f, y) => {
        const S = De();
        Xe(S), f.timer && (u.timeout = new Zl(() => {
          y("timer"), delete u.timeout
        }, f.timer), f.timerProgressBar && (Me(S), Q(S, f, "timerProgressBar"), setTimeout(() => {
          u.timeout && u.timeout.running && Cs(f.timer)
        })))
      },
      Vc = (u, f) => {
        if (!f.toast) {
          if (!g(f.allowEnterKey)) return zr();
          jc(u, f) || Yo(f, -1, 1)
        }
      },
      jc = (u, f) => f.focusDeny && mt(u.denyButton) ? (u.denyButton.focus(), !0) : f.focusCancel && mt(u.cancelButton) ? (u.cancelButton.focus(), !0) : f.focusConfirm && mt(u.confirmButton) ? (u.confirmButton.focus(), !0) : !1,
      zr = () => {
        document.activeElement instanceof HTMLElement && typeof document.activeElement.blur == "function" && document.activeElement.blur()
      };
    Object.assign(on.prototype, Hr), Object.assign(on, Or), Object.keys(Hr).forEach(u => {
      on[u] = function () {
        if (ni) return ni[u](...arguments)
      }
    }), on.DismissReason = gs, on.version = "11.4.0";
    const rn = on;
    return rn.default = rn, rn
  }), typeof ln < "u" && ln.Sweetalert2 && (ln.swal = ln.sweetAlert = ln.Swal = ln.SweetAlert = ln.Sweetalert2)
})(Jh);
var Yr = Jh.exports;
class S0 {
  static install(t, s = {}) {
    var o;
    const i = Yr.mixin(s),
      r = function (...a) {
        return i.fire.call(i, ...a)
      };
    Object.assign(r, Yr), Object.keys(Yr).filter(a => typeof Yr[a] == "function").forEach(a => {
      r[a] = i[a].bind(i)
    }), (o = t.config) != null && o.globalProperties && !t.config.globalProperties.$swal ? (t.config.globalProperties.$swal = r, t.provide("$swal", r)) : Object.prototype.hasOwnProperty.call(t, "$swal") || (t.prototype.$swal = r, t.swal = r)
  }
}
/**
 * @vue/shared v3.4.21
 * (c) 2018-present Yuxi (Evan) You and Vue contributors
 * @license MIT
 **/
function Ua(e, t) {
  const s = new Set(e.split(","));
  return t ? o => s.has(o.toLowerCase()) : o => s.has(o)
}
const et = {},
  _o = [],
  Kt = () => {},
  E0 = () => !1,
  Ni = e => e.charCodeAt(0) === 111 && e.charCodeAt(1) === 110 && (e.charCodeAt(2) > 122 || e.charCodeAt(2) < 97),
  eu = e => e.startsWith("onUpdate:"),
  it = Object.assign,
  tu = (e, t) => {
    const s = e.indexOf(t);
    s > -1 && e.splice(s, 1)
  },
  P0 = Object.prototype.hasOwnProperty,
  Ze = (e, t) => P0.call(e, t),
  we = Array.isArray,
  bo = e => Mo(e) === "[object Map]",
  Gn = e => Mo(e) === "[object Set]",
  Qu = e => Mo(e) === "[object Date]",
  T0 = e => Mo(e) === "[object RegExp]",
  Be = e => typeof e == "function",
  rt = e => typeof e == "string",
  $n = e => typeof e == "symbol",
  st = e => e !== null && typeof e == "object",
  su = e => (st(e) || Be(e)) && Be(e.then) && Be(e.catch),
  Zh = Object.prototype.toString,
  Mo = e => Zh.call(e),
  L0 = e => Mo(e).slice(8, -1),
  Xh = e => Mo(e) === "[object Object]",
  nu = e => rt(e) && e !== "NaN" && e[0] !== "-" && "" + parseInt(e, 10) === e,
  vo = Ua(",key,ref,ref_for,ref_key,onVnodeBeforeMount,onVnodeMounted,onVnodeBeforeUpdate,onVnodeUpdated,onVnodeBeforeUnmount,onVnodeUnmounted"),
  Va = e => {
    const t = Object.create(null);
    return s => t[s] || (t[s] = e(s))
  },
  O0 = /-(\w)/g,
  jt = Va(e => e.replace(O0, (t, s) => s ? s.toUpperCase() : "")),
  I0 = /\B([A-Z])/g,
  ts = Va(e => e.replace(I0, "-$1").toLowerCase()),
  Fi = Va(e => e.charAt(0).toUpperCase() + e.slice(1)),
  fi = Va(e => e ? `on${Fi(e)}` : ""),
  vs = (e, t) => !Object.is(e, t),
  yo = (e, t) => {
    for (let s = 0; s < e.length; s++) e[s](t)
  },
  wa = (e, t, s) => {
    Object.defineProperty(e, t, {
      configurable: !0,
      enumerable: !1,
      value: s
    })
  },
  wi = e => {
    const t = parseFloat(e);
    return isNaN(t) ? e : t
  },
  $a = e => {
    const t = rt(e) ? Number(e) : NaN;
    return isNaN(t) ? e : t
  };
let ef;
const Qh = () => ef || (ef = typeof globalThis < "u" ? globalThis : typeof self < "u" ? self : typeof window < "u" ? window : typeof global < "u" ? global : {}),
  B0 = "Infinity,undefined,NaN,isFinite,isNaN,parseFloat,parseInt,decodeURI,decodeURIComponent,encodeURI,encodeURIComponent,Math,Number,Date,Array,Object,Boolean,String,RegExp,Map,Set,JSON,Intl,BigInt,console,Error",
  D0 = Ua(B0);

function Ro(e) {
  if (we(e)) {
    const t = {};
    for (let s = 0; s < e.length; s++) {
      const o = e[s],
        i = rt(o) ? F0(o) : Ro(o);
      if (i)
        for (const r in i) t[r] = i[r]
    }
    return t
  } else if (rt(e) || st(e)) return e
}
const M0 = /;(?![^(]*\))/g,
  R0 = /:([^]+)/,
  N0 = /\/\*[^]*?\*\//g;

function F0(e) {
  const t = {};
  return e.replace(N0, "").split(M0).forEach(s => {
    if (s) {
      const o = s.split(R0);
      o.length > 1 && (t[o[0].trim()] = o[1].trim())
    }
  }), t
}

function Ie(e) {
  let t = "";
  if (rt(e)) t = e;
  else if (we(e))
    for (let s = 0; s < e.length; s++) {
      const o = Ie(e[s]);
      o && (t += o + " ")
    } else if (st(e))
      for (const s in e) e[s] && (t += s + " ");
  return t.trim()
}

function ds(e) {
  if (!e) return null;
  let {
    class: t,
    style: s
  } = e;
  return t && !rt(t) && (e.class = Ie(t)), s && (e.style = Ro(s)), e
}
const U0 = "itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly",
  V0 = Ua(U0);

function ep(e) {
  return !!e || e === ""
}

function j0(e, t) {
  if (e.length !== t.length) return !1;
  let s = !0;
  for (let o = 0; s && o < e.length; o++) s = Cn(e[o], t[o]);
  return s
}

function Cn(e, t) {
  if (e === t) return !0;
  let s = Qu(e),
    o = Qu(t);
  if (s || o) return s && o ? e.getTime() === t.getTime() : !1;
  if (s = $n(e), o = $n(t), s || o) return e === t;
  if (s = we(e), o = we(t), s || o) return s && o ? j0(e, t) : !1;
  if (s = st(e), o = st(t), s || o) {
    if (!s || !o) return !1;
    const i = Object.keys(e).length,
      r = Object.keys(t).length;
    if (i !== r) return !1;
    for (const a in e) {
      const l = e.hasOwnProperty(a),
        d = t.hasOwnProperty(a);
      if (l && !d || !l && d || !Cn(e[a], t[a])) return !1
    }
  }
  return String(e) === String(t)
}

function ja(e, t) {
  return e.findIndex(s => Cn(s, t))
}
const L = e => rt(e) ? e : e == null ? "" : we(e) || st(e) && (e.toString === Zh || !Be(e.toString)) ? JSON.stringify(e, tp, 2) : String(e),
  tp = (e, t) => t && t.__v_isRef ? tp(e, t.value) : bo(t) ? {
    [`Map(${t.size})`]: [...t.entries()].reduce((s, [o, i], r) => (s[Hc(o, r) + " =>"] = i, s), {})
  } : Gn(t) ? {
    [`Set(${t.size})`]: [...t.values()].map(s => Hc(s))
  } : $n(t) ? Hc(t) : st(t) && !we(t) && !Xh(t) ? String(t) : t,
  Hc = (e, t = "") => {
    var s;
    return $n(e) ? `Symbol(${(s=e.description)!=null?s:t})` : e
  };
/**
 * @vue/reactivity v3.4.21
 * (c) 2018-present Yuxi (Evan) You and Vue contributors
 * @license MIT
 **/
let Qt;
class ou {
  constructor(t = !1) {
    this.detached = t, this._active = !0, this.effects = [], this.cleanups = [], this.parent = Qt, !t && Qt && (this.index = (Qt.scopes || (Qt.scopes = [])).push(this) - 1)
  }
  get active() {
    return this._active
  }
  run(t) {
    if (this._active) {
      const s = Qt;
      try {
        return Qt = this, t()
      } finally {
        Qt = s
      }
    }
  }
  on() {
    Qt = this
  }
  off() {
    Qt = this.parent
  }
  stop(t) {
    if (this._active) {
      let s, o;
      for (s = 0, o = this.effects.length; s < o; s++) this.effects[s].stop();
      for (s = 0, o = this.cleanups.length; s < o; s++) this.cleanups[s]();
      if (this.scopes)
        for (s = 0, o = this.scopes.length; s < o; s++) this.scopes[s].stop(!0);
      if (!this.detached && this.parent && !t) {
        const i = this.parent.scopes.pop();
        i && i !== this && (this.parent.scopes[this.index] = i, i.index = this.index)
      }
      this.parent = void 0, this._active = !1
    }
  }
}

function sp(e) {
  return new ou(e)
}

function np(e, t = Qt) {
  t && t.active && t.effects.push(e)
}

function op() {
  return Qt
}

function H0(e) {
  Qt && Qt.cleanups.push(e)
}
let Nn;
class Ao {
  constructor(t, s, o, i) {
    this.fn = t, this.trigger = s, this.scheduler = o, this.active = !0, this.deps = [], this._dirtyLevel = 4, this._trackId = 0, this._runnings = 0, this._shouldSchedule = !1, this._depsLength = 0, np(this, i)
  }
  get dirty() {
    if (this._dirtyLevel === 2 || this._dirtyLevel === 3) {
      this._dirtyLevel = 1, Yn();
      for (let t = 0; t < this._depsLength; t++) {
        const s = this.deps[t];
        if (s.computed && (q0(s.computed), this._dirtyLevel >= 4)) break
      }
      this._dirtyLevel === 1 && (this._dirtyLevel = 0), Jn()
    }
    return this._dirtyLevel >= 4
  }
  set dirty(t) {
    this._dirtyLevel = t ? 4 : 0
  }
  run() {
    if (this._dirtyLevel = 0, !this.active) return this.fn();
    let t = wn,
      s = Nn;
    try {
      return wn = !0, Nn = this, this._runnings++, tf(this), this.fn()
    } finally {
      sf(this), this._runnings--, Nn = s, wn = t
    }
  }
  stop() {
    var t;
    this.active && (tf(this), sf(this), (t = this.onStop) == null || t.call(this), this.active = !1)
  }
}

function q0(e) {
  return e.value
}

function tf(e) {
  e._trackId++, e._depsLength = 0
}

function sf(e) {
  if (e.deps.length > e._depsLength) {
    for (let t = e._depsLength; t < e.deps.length; t++) ip(e.deps[t], e);
    e.deps.length = e._depsLength
  }
}

function ip(e, t) {
  const s = e.get(t);
  s !== void 0 && t._trackId !== s && (e.delete(t), e.size === 0 && e.cleanup())
}

function W0(e, t) {
  e.effect instanceof Ao && (e = e.effect.fn);
  const s = new Ao(e, Kt, () => {
    s.dirty && s.run()
  });
  t && (it(s, t), t.scope && np(s, t.scope)), (!t || !t.lazy) && s.run();
  const o = s.run.bind(s);
  return o.effect = s, o
}

function z0(e) {
  e.effect.stop()
}
let wn = !0,
  pd = 0;
const rp = [];

function Yn() {
  rp.push(wn), wn = !1
}

function Jn() {
  const e = rp.pop();
  wn = e === void 0 ? !0 : e
}

function iu() {
  pd++
}

function ru() {
  for (pd--; !pd && md.length;) md.shift()()
}

function ap(e, t, s) {
  if (t.get(e) !== e._trackId) {
    t.set(e, e._trackId);
    const o = e.deps[e._depsLength];
    o !== t ? (o && ip(o, e), e.deps[e._depsLength++] = t) : e._depsLength++
  }
}
const md = [];

function lp(e, t, s) {
  iu();
  for (const o of e.keys()) {
    let i;
    o._dirtyLevel < t && (i ? ? (i = e.get(o) === o._trackId)) && (o._shouldSchedule || (o._shouldSchedule = o._dirtyLevel === 0), o._dirtyLevel = t), o._shouldSchedule && (i ? ? (i = e.get(o) === o._trackId)) && (o.trigger(), (!o._runnings || o.allowRecurse) && o._dirtyLevel !== 2 && (o._shouldSchedule = !1, o.scheduler && md.push(o.scheduler)))
  }
  ru()
}
const cp = (e, t) => {
    const s = new Map;
    return s.cleanup = e, s.computed = t, s
  },
  Ca = new WeakMap,
  Fn = Symbol(""),
  gd = Symbol("");

function Yt(e, t, s) {
  if (wn && Nn) {
    let o = Ca.get(e);
    o || Ca.set(e, o = new Map);
    let i = o.get(s);
    i || o.set(s, i = cp(() => o.delete(s))), ap(Nn, i)
  }
}

function Ws(e, t, s, o, i, r) {
  const a = Ca.get(e);
  if (!a) return;
  let l = [];
  if (t === "clear") l = [...a.values()];
  else if (s === "length" && we(e)) {
    const d = Number(o);
    a.forEach((p, m) => {
      (m === "length" || !$n(m) && m >= d) && l.push(p)
    })
  } else switch (s !== void 0 && l.push(a.get(s)), t) {
    case "add":
      we(e) ? nu(s) && l.push(a.get("length")) : (l.push(a.get(Fn)), bo(e) && l.push(a.get(gd)));
      break;
    case "delete":
      we(e) || (l.push(a.get(Fn)), bo(e) && l.push(a.get(gd)));
      break;
    case "set":
      bo(e) && l.push(a.get(Fn));
      break
  }
  iu();
  for (const d of l) d && lp(d, 4);
  ru()
}

function K0(e, t) {
  var s;
  return (s = Ca.get(e)) == null ? void 0 : s.get(t)
}
const G0 = Ua("__proto__,__v_isRef,__isVue"),
  dp = new Set(Object.getOwnPropertyNames(Symbol).filter(e => e !== "arguments" && e !== "caller").map(e => Symbol[e]).filter($n)),
  nf = Y0();

function Y0() {
  const e = {};
  return ["includes", "indexOf", "lastIndexOf"].forEach(t => {
    e[t] = function (...s) {
      const o = Ke(this);
      for (let r = 0, a = this.length; r < a; r++) Yt(o, "get", r + "");
      const i = o[t](...s);
      return i === -1 || i === !1 ? o[t](...s.map(Ke)) : i
    }
  }), ["push", "pop", "shift", "unshift", "splice"].forEach(t => {
    e[t] = function (...s) {
      Yn(), iu();
      const o = Ke(this)[t].apply(this, s);
      return ru(), Jn(), o
    }
  }), e
}

function J0(e) {
  const t = Ke(this);
  return Yt(t, "has", e), t.hasOwnProperty(e)
}
class up {
  constructor(t = !1, s = !1) {
    this._isReadonly = t, this._isShallow = s
  }
  get(t, s, o) {
    const i = this._isReadonly,
      r = this._isShallow;
    if (s === "__v_isReactive") return !i;
    if (s === "__v_isReadonly") return i;
    if (s === "__v_isShallow") return r;
    if (s === "__v_raw") return o === (i ? r ? _p : gp : r ? mp : pp).get(t) || Object.getPrototypeOf(t) === Object.getPrototypeOf(o) ? t : void 0;
    const a = we(t);
    if (!i) {
      if (a && Ze(nf, s)) return Reflect.get(nf, s, o);
      if (s === "hasOwnProperty") return J0
    }
    const l = Reflect.get(t, s, o);
    return ($n(s) ? dp.has(s) : G0(s)) || (i || Yt(t, "get", s), r) ? l : Tt(l) ? a && nu(s) ? l : l.value : st(l) ? i ? cu(l) : Ls(l) : l
  }
}
class fp extends up {
  constructor(t = !1) {
    super(!1, t)
  }
  set(t, s, o, i) {
    let r = t[s];
    if (!this._isShallow) {
      const d = Hn(r);
      if (!$i(o) && !Hn(o) && (r = Ke(r), o = Ke(o)), !we(t) && Tt(r) && !Tt(o)) return d ? !1 : (r.value = o, !0)
    }
    const a = we(t) && nu(s) ? Number(s) < t.length : Ze(t, s),
      l = Reflect.set(t, s, o, i);
    return t === Ke(i) && (a ? vs(o, r) && Ws(t, "set", s, o) : Ws(t, "add", s, o)), l
  }
  deleteProperty(t, s) {
    const o = Ze(t, s);
    t[s];
    const i = Reflect.deleteProperty(t, s);
    return i && o && Ws(t, "delete", s, void 0), i
  }
  has(t, s) {
    const o = Reflect.has(t, s);
    return (!$n(s) || !dp.has(s)) && Yt(t, "has", s), o
  }
  ownKeys(t) {
    return Yt(t, "iterate", we(t) ? "length" : Fn), Reflect.ownKeys(t)
  }
}
class hp extends up {
  constructor(t = !1) {
    super(!0, t)
  }
  set(t, s) {
    return !0
  }
  deleteProperty(t, s) {
    return !0
  }
}
const Z0 = new fp,
  X0 = new hp,
  Q0 = new fp(!0),
  eg = new hp(!0),
  au = e => e,
  Ha = e => Reflect.getPrototypeOf(e);

function Jr(e, t, s = !1, o = !1) {
  e = e.__v_raw;
  const i = Ke(e),
    r = Ke(t);
  s || (vs(t, r) && Yt(i, "get", t), Yt(i, "get", r));
  const {
    has: a
  } = Ha(i), l = o ? au : s ? uu : Ci;
  if (a.call(i, t)) return l(e.get(t));
  if (a.call(i, r)) return l(e.get(r));
  e !== i && e.get(t)
}

function Zr(e, t = !1) {
  const s = this.__v_raw,
    o = Ke(s),
    i = Ke(e);
  return t || (vs(e, i) && Yt(o, "has", e), Yt(o, "has", i)), e === i ? s.has(e) : s.has(e) || s.has(i)
}

function Xr(e, t = !1) {
  return e = e.__v_raw, !t && Yt(Ke(e), "iterate", Fn), Reflect.get(e, "size", e)
}

function of (e) {
  e = Ke(e);
  const t = Ke(this);
  return Ha(t).has.call(t, e) || (t.add(e), Ws(t, "add", e, e)), this
}

function rf(e, t) {
  t = Ke(t);
  const s = Ke(this),
    {
      has: o,
      get: i
    } = Ha(s);
  let r = o.call(s, e);
  r || (e = Ke(e), r = o.call(s, e));
  const a = i.call(s, e);
  return s.set(e, t), r ? vs(t, a) && Ws(s, "set", e, t) : Ws(s, "add", e, t), this
}

function af(e) {
  const t = Ke(this),
    {
      has: s,
      get: o
    } = Ha(t);
  let i = s.call(t, e);
  i || (e = Ke(e), i = s.call(t, e)), o && o.call(t, e);
  const r = t.delete(e);
  return i && Ws(t, "delete", e, void 0), r
}

function lf() {
  const e = Ke(this),
    t = e.size !== 0,
    s = e.clear();
  return t && Ws(e, "clear", void 0, void 0), s
}

function Qr(e, t) {
  return function (o, i) {
    const r = this,
      a = r.__v_raw,
      l = Ke(a),
      d = t ? au : e ? uu : Ci;
    return !e && Yt(l, "iterate", Fn), a.forEach((p, m) => o.call(i, d(p), d(m), r))
  }
}

function ea(e, t, s) {
  return function (...o) {
    const i = this.__v_raw,
      r = Ke(i),
      a = bo(r),
      l = e === "entries" || e === Symbol.iterator && a,
      d = e === "keys" && a,
      p = i[e](...o),
      m = s ? au : t ? uu : Ci;
    return !t && Yt(r, "iterate", d ? gd : Fn), {
      next() {
        const {
          value: g,
          done: $
        } = p.next();
        return $ ? {
          value: g,
          done: $
        } : {
          value: l ? [m(g[0]), m(g[1])] : m(g),
          done: $
        }
      },
      [Symbol.iterator]() {
        return this
      }
    }
  }
}

function cn(e) {
  return function (...t) {
    return e === "delete" ? !1 : e === "clear" ? void 0 : this
  }
}

function tg() {
  const e = {
      get(r) {
        return Jr(this, r)
      },
      get size() {
        return Xr(this)
      },
      has: Zr,
      add: of ,
      set: rf,
      delete: af,
      clear: lf,
      forEach: Qr(!1, !1)
    },
    t = {
      get(r) {
        return Jr(this, r, !1, !0)
      },
      get size() {
        return Xr(this)
      },
      has: Zr,
      add: of ,
      set: rf,
      delete: af,
      clear: lf,
      forEach: Qr(!1, !0)
    },
    s = {
      get(r) {
        return Jr(this, r, !0)
      },
      get size() {
        return Xr(this, !0)
      },
      has(r) {
        return Zr.call(this, r, !0)
      },
      add: cn("add"),
      set: cn("set"),
      delete: cn("delete"),
      clear: cn("clear"),
      forEach: Qr(!0, !1)
    },
    o = {
      get(r) {
        return Jr(this, r, !0, !0)
      },
      get size() {
        return Xr(this, !0)
      },
      has(r) {
        return Zr.call(this, r, !0)
      },
      add: cn("add"),
      set: cn("set"),
      delete: cn("delete"),
      clear: cn("clear"),
      forEach: Qr(!0, !0)
    };
  return ["keys", "values", "entries", Symbol.iterator].forEach(r => {
    e[r] = ea(r, !1, !1), s[r] = ea(r, !0, !1), t[r] = ea(r, !1, !0), o[r] = ea(r, !0, !0)
  }), [e, s, t, o]
}
const [sg, ng, og, ig] = tg();

function qa(e, t) {
  const s = t ? e ? ig : og : e ? ng : sg;
  return (o, i, r) => i === "__v_isReactive" ? !e : i === "__v_isReadonly" ? e : i === "__v_raw" ? o : Reflect.get(Ze(s, i) && i in o ? s : o, i, r)
}
const rg = {
    get: qa(!1, !1)
  },
  ag = {
    get: qa(!1, !0)
  },
  lg = {
    get: qa(!0, !1)
  },
  cg = {
    get: qa(!0, !0)
  },
  pp = new WeakMap,
  mp = new WeakMap,
  gp = new WeakMap,
  _p = new WeakMap;

function dg(e) {
  switch (e) {
    case "Object":
    case "Array":
      return 1;
    case "Map":
    case "Set":
    case "WeakMap":
    case "WeakSet":
      return 2;
    default:
      return 0
  }
}

function ug(e) {
  return e.__v_skip || !Object.isExtensible(e) ? 0 : dg(L0(e))
}

function Ls(e) {
  return Hn(e) ? e : Wa(e, !1, Z0, rg, pp)
}

function lu(e) {
  return Wa(e, !1, Q0, ag, mp)
}

function cu(e) {
  return Wa(e, !0, X0, lg, gp)
}

function fg(e) {
  return Wa(e, !0, eg, cg, _p)
}

function Wa(e, t, s, o, i) {
  if (!st(e) || e.__v_raw && !(t && e.__v_isReactive)) return e;
  const r = i.get(e);
  if (r) return r;
  const a = ug(e);
  if (a === 0) return e;
  const l = new Proxy(e, a === 2 ? o : s);
  return i.set(e, l), l
}

function Un(e) {
  return Hn(e) ? Un(e.__v_raw) : !!(e && e.__v_isReactive)
}

function Hn(e) {
  return !!(e && e.__v_isReadonly)
}

function $i(e) {
  return !!(e && e.__v_isShallow)
}

function du(e) {
  return Un(e) || Hn(e)
}

function Ke(e) {
  const t = e && e.__v_raw;
  return t ? Ke(t) : e
}

function za(e) {
  return Object.isExtensible(e) && wa(e, "__v_skip", !0), e
}
const Ci = e => st(e) ? Ls(e) : e,
  uu = e => st(e) ? cu(e) : e;
class bp {
  constructor(t, s, o, i) {
    this.getter = t, this._setter = s, this.dep = void 0, this.__v_isRef = !0, this.__v_isReadonly = !1, this.effect = new Ao(() => t(this._value), () => wo(this, this.effect._dirtyLevel === 2 ? 2 : 3)), this.effect.computed = this, this.effect.active = this._cacheable = !i, this.__v_isReadonly = o
  }
  get value() {
    const t = Ke(this);
    return (!t._cacheable || t.effect.dirty) && vs(t._value, t._value = t.effect.run()) && wo(t, 4), fu(t), t.effect._dirtyLevel >= 2 && wo(t, 2), t._value
  }
  set value(t) {
    this._setter(t)
  }
  get _dirty() {
    return this.effect.dirty
  }
  set _dirty(t) {
    this.effect.dirty = t
  }
}

function hg(e, t, s = !1) {
  let o, i;
  const r = Be(e);
  return r ? (o = e, i = Kt) : (o = e.get, i = e.set), new bp(o, i, r || !i, s)
}

function fu(e) {
  var t;
  wn && Nn && (e = Ke(e), ap(Nn, (t = e.dep) != null ? t : e.dep = cp(() => e.dep = void 0, e instanceof bp ? e : void 0)))
}

function wo(e, t = 4, s) {
  e = Ke(e);
  const o = e.dep;
  o && lp(o, t)
}

function Tt(e) {
  return !!(e && e.__v_isRef === !0)
}

function he(e) {
  return yp(e, !1)
}

function vp(e) {
  return yp(e, !0)
}

function yp(e, t) {
  return Tt(e) ? e : new pg(e, t)
}
class pg {
  constructor(t, s) {
    this.__v_isShallow = s, this.dep = void 0, this.__v_isRef = !0, this._rawValue = s ? t : Ke(t), this._value = s ? t : Ci(t)
  }
  get value() {
    return fu(this), this._value
  }
  set value(t) {
    const s = this.__v_isShallow || $i(t) || Hn(t);
    t = s ? t : Ke(t), vs(t, this._rawValue) && (this._rawValue = t, this._value = s ? t : Ci(t), wo(this, 4))
  }
}

function mg(e) {
  wo(e, 4)
}

function Pt(e) {
  return Tt(e) ? e.value : e
}

function gg(e) {
  return Be(e) ? e() : Pt(e)
}
const _g = {
  get: (e, t, s) => Pt(Reflect.get(e, t, s)),
  set: (e, t, s, o) => {
    const i = e[t];
    return Tt(i) && !Tt(s) ? (i.value = s, !0) : Reflect.set(e, t, s, o)
  }
};

function hu(e) {
  return Un(e) ? e : new Proxy(e, _g)
}
class bg {
  constructor(t) {
    this.dep = void 0, this.__v_isRef = !0;
    const {
      get: s,
      set: o
    } = t(() => fu(this), () => wo(this));
    this._get = s, this._set = o
  }
  get value() {
    return this._get()
  }
  set value(t) {
    this._set(t)
  }
}

function wp(e) {
  return new bg(e)
}

function vg(e) {
  const t = we(e) ? new Array(e.length) : {};
  for (const s in e) t[s] = $p(e, s);
  return t
}
class yg {
  constructor(t, s, o) {
    this._object = t, this._key = s, this._defaultValue = o, this.__v_isRef = !0
  }
  get value() {
    const t = this._object[this._key];
    return t === void 0 ? this._defaultValue : t
  }
  set value(t) {
    this._object[this._key] = t
  }
  get dep() {
    return K0(Ke(this._object), this._key)
  }
}
class wg {
  constructor(t) {
    this._getter = t, this.__v_isRef = !0, this.__v_isReadonly = !0
  }
  get value() {
    return this._getter()
  }
}

function $g(e, t, s) {
  return Tt(e) ? e : Be(e) ? new wg(e) : st(e) && arguments.length > 1 ? $p(e, t, s) : he(e)
}

function $p(e, t, s) {
  const o = e[t];
  return Tt(o) ? o : new yg(e, t, s)
}
const Cg = {
    GET: "get",
    HAS: "has",
    ITERATE: "iterate"
  },
  kg = {
    SET: "set",
    ADD: "add",
    DELETE: "delete",
    CLEAR: "clear"
  };
/**
 * @vue/runtime-core v3.4.21
 * (c) 2018-present Yuxi (Evan) You and Vue contributors
 * @license MIT
 **/
function Ag(e, t) {}
const xg = {
    SETUP_FUNCTION: 0,
    0: "SETUP_FUNCTION",
    RENDER_FUNCTION: 1,
    1: "RENDER_FUNCTION",
    WATCH_GETTER: 2,
    2: "WATCH_GETTER",
    WATCH_CALLBACK: 3,
    3: "WATCH_CALLBACK",
    WATCH_CLEANUP: 4,
    4: "WATCH_CLEANUP",
    NATIVE_EVENT_HANDLER: 5,
    5: "NATIVE_EVENT_HANDLER",
    COMPONENT_EVENT_HANDLER: 6,
    6: "COMPONENT_EVENT_HANDLER",
    VNODE_HOOK: 7,
    7: "VNODE_HOOK",
    DIRECTIVE_HOOK: 8,
    8: "DIRECTIVE_HOOK",
    TRANSITION_HOOK: 9,
    9: "TRANSITION_HOOK",
    APP_ERROR_HANDLER: 10,
    10: "APP_ERROR_HANDLER",
    APP_WARN_HANDLER: 11,
    11: "APP_WARN_HANDLER",
    FUNCTION_REF: 12,
    12: "FUNCTION_REF",
    ASYNC_COMPONENT_LOADER: 13,
    13: "ASYNC_COMPONENT_LOADER",
    SCHEDULER: 14,
    14: "SCHEDULER"
  },
  Sg = {
    sp: "serverPrefetch hook",
    bc: "beforeCreate hook",
    c: "created hook",
    bm: "beforeMount hook",
    m: "mounted hook",
    bu: "beforeUpdate hook",
    u: "updated",
    bum: "beforeUnmount hook",
    um: "unmounted hook",
    a: "activated hook",
    da: "deactivated hook",
    ec: "errorCaptured hook",
    rtc: "renderTracked hook",
    rtg: "renderTriggered hook",
    0: "setup function",
    1: "render function",
    2: "watcher getter",
    3: "watcher callback",
    4: "watcher cleanup function",
    5: "native event handler",
    6: "component event handler",
    7: "vnode hook",
    8: "directive hook",
    9: "transition hook",
    10: "app errorHandler",
    11: "app warnHandler",
    12: "ref function",
    13: "async component loader",
    14: "scheduler flush. This is likely a Vue internals bug. Please open an issue at https://github.com/vuejs/core ."
  };

function zs(e, t, s, o) {
  try {
    return o ? e(...o) : e()
  } catch (i) {
    Zn(i, t, s)
  }
}

function ns(e, t, s, o) {
  if (Be(e)) {
    const r = zs(e, t, s, o);
    return r && su(r) && r.catch(a => {
      Zn(a, t, s)
    }), r
  }
  const i = [];
  for (let r = 0; r < e.length; r++) i.push(ns(e[r], t, s, o));
  return i
}

function Zn(e, t, s, o = !0) {
  const i = t ? t.vnode : null;
  if (t) {
    let r = t.parent;
    const a = t.proxy,
      l = `https://vuejs.org/error-reference/#runtime-${s}`;
    for (; r;) {
      const p = r.ec;
      if (p) {
        for (let m = 0; m < p.length; m++)
          if (p[m](e, a, l) === !1) return
      }
      r = r.parent
    }
    const d = t.appContext.config.errorHandler;
    if (d) {
      zs(d, null, 10, [e, a, l]);
      return
    }
  }
  Eg(e, s, i, o)
}

function Eg(e, t, s, o = !0) {
  console.error(e)
}
let ki = !1,
  _d = !1;
const It = [];
let Es = 0;
const $o = [];
let mn = null,
  Mn = 0;
const Cp = Promise.resolve();
let pu = null;

function Ui(e) {
  const t = pu || Cp;
  return e ? t.then(this ? e.bind(this) : e) : t
}

function Pg(e) {
  let t = Es + 1,
    s = It.length;
  for (; t < s;) {
    const o = t + s >>> 1,
      i = It[o],
      r = Ai(i);
    r < e || r === e && i.pre ? t = o + 1 : s = o
  }
  return t
}

function Ka(e) {
  (!It.length || !It.includes(e, ki && e.allowRecurse ? Es + 1 : Es)) && (e.id == null ? It.push(e) : It.splice(Pg(e.id), 0, e), kp())
}

function kp() {
  !ki && !_d && (_d = !0, pu = Cp.then(Ap))
}

function Tg(e) {
  const t = It.indexOf(e);
  t > Es && It.splice(t, 1)
}

function ka(e) {
  we(e) ? $o.push(...e) : (!mn || !mn.includes(e, e.allowRecurse ? Mn + 1 : Mn)) && $o.push(e), kp()
}

function cf(e, t, s = ki ? Es + 1 : 0) {
  for (; s < It.length; s++) {
    const o = It[s];
    if (o && o.pre) {
      if (e && o.id !== e.uid) continue;
      It.splice(s, 1), s--, o()
    }
  }
}

function Aa(e) {
  if ($o.length) {
    const t = [...new Set($o)].sort((s, o) => Ai(s) - Ai(o));
    if ($o.length = 0, mn) {
      mn.push(...t);
      return
    }
    for (mn = t, Mn = 0; Mn < mn.length; Mn++) mn[Mn]();
    mn = null, Mn = 0
  }
}
const Ai = e => e.id == null ? 1 / 0 : e.id,
  Lg = (e, t) => {
    const s = Ai(e) - Ai(t);
    if (s === 0) {
      if (e.pre && !t.pre) return -1;
      if (t.pre && !e.pre) return 1
    }
    return s
  };

function Ap(e) {
  _d = !1, ki = !0, It.sort(Lg);
  try {
    for (Es = 0; Es < It.length; Es++) {
      const t = It[Es];
      t && t.active !== !1 && zs(t, null, 14)
    }
  } finally {
    Es = 0, It.length = 0, Aa(), ki = !1, pu = null, (It.length || $o.length) && Ap()
  }
}
let po, ta = [];

function xp(e, t) {
  var s, o;
  po = e, po ? (po.enabled = !0, ta.forEach(({
    event: i,
    args: r
  }) => po.emit(i, ...r)), ta = []) : typeof window < "u" && window.HTMLElement && !((o = (s = window.navigator) == null ? void 0 : s.userAgent) != null && o.includes("jsdom")) ? ((t.__VUE_DEVTOOLS_HOOK_REPLAY__ = t.__VUE_DEVTOOLS_HOOK_REPLAY__ || []).push(r => {
    xp(r, t)
  }), setTimeout(() => {
    po || (t.__VUE_DEVTOOLS_HOOK_REPLAY__ = null, ta = [])
  }, 3e3)) : ta = []
}

function Og(e, t, ...s) {
  if (e.isUnmounted) return;
  const o = e.vnode.props || et;
  let i = s;
  const r = t.startsWith("update:"),
    a = r && t.slice(7);
  if (a && a in o) {
    const m = `${a==="modelValue"?"model":a}Modifiers`,
      {
        number: g,
        trim: $
      } = o[m] || et;
    $ && (i = s.map(C => rt(C) ? C.trim() : C)), g && (i = s.map(wi))
  }
  let l, d = o[l = fi(t)] || o[l = fi(jt(t))];
  !d && r && (d = o[l = fi(ts(t))]), d && ns(d, e, 6, i);
  const p = o[l + "Once"];
  if (p) {
    if (!e.emitted) e.emitted = {};
    else if (e.emitted[l]) return;
    e.emitted[l] = !0, ns(p, e, 6, i)
  }
}

function Sp(e, t, s = !1) {
  const o = t.emitsCache,
    i = o.get(e);
  if (i !== void 0) return i;
  const r = e.emits;
  let a = {},
    l = !1;
  if (!Be(e)) {
    const d = p => {
      const m = Sp(p, t, !0);
      m && (l = !0, it(a, m))
    };
    !s && t.mixins.length && t.mixins.forEach(d), e.extends && d(e.extends), e.mixins && e.mixins.forEach(d)
  }
  return !r && !l ? (st(e) && o.set(e, null), null) : (we(r) ? r.forEach(d => a[d] = null) : it(a, r), st(e) && o.set(e, a), a)
}

function Ga(e, t) {
  return !e || !Ni(t) ? !1 : (t = t.slice(2).replace(/Once$/, ""), Ze(e, t[0].toLowerCase() + t.slice(1)) || Ze(e, ts(t)) || Ze(e, t))
}
let vt = null,
  Ya = null;

function xi(e) {
  const t = vt;
  return vt = e, Ya = e && e.type.__scopeId || null, t
}

function No(e) {
  Ya = e
}

function Fo() {
  Ya = null
}
const Ig = e => Ue;

function Ue(e, t = vt, s) {
  if (!t || e._n) return e;
  const o = (...i) => {
    o._d && Ad(-1);
    const r = xi(t);
    let a;
    try {
      a = e(...i)
    } finally {
      xi(r), o._d && Ad(1)
    }
    return a
  };
  return o._n = !0, o._c = !0, o._d = !0, o
}

function fa(e) {
  const {
    type: t,
    vnode: s,
    proxy: o,
    withProxy: i,
    props: r,
    propsOptions: [a],
    slots: l,
    attrs: d,
    emit: p,
    render: m,
    renderCache: g,
    data: $,
    setupState: C,
    ctx: A,
    inheritAttrs: O
  } = e;
  let V, M;
  const B = xi(e);
  try {
    if (s.shapeFlag & 4) {
      const I = i || o,
        R = I;
      V = es(m.call(R, I, g, r, C, $, A)), M = d
    } else {
      const I = t;
      V = es(I.length > 1 ? I(r, {
        attrs: d,
        slots: l,
        emit: p
      }) : I(r, null)), M = t.props ? d : Dg(d)
    }
  } catch (I) {
    gi.length = 0, Zn(I, e, 1), V = ie(Bt)
  }
  let P = V;
  if (M && O !== !1) {
    const I = Object.keys(M),
      {
        shapeFlag: R
      } = P;
    I.length && R & 7 && (a && I.some(eu) && (M = Mg(M, a)), P = Ts(P, M))
  }
  return s.dirs && (P = Ts(P), P.dirs = P.dirs ? P.dirs.concat(s.dirs) : s.dirs), s.transition && (P.transition = s.transition), V = P, xi(B), V
}

function Bg(e, t = !0) {
  let s;
  for (let o = 0; o < e.length; o++) {
    const i = e[o];
    if (kn(i)) {
      if (i.type !== Bt || i.children === "v-if") {
        if (s) return;
        s = i
      }
    } else return
  }
  return s
}
const Dg = e => {
    let t;
    for (const s in e)(s === "class" || s === "style" || Ni(s)) && ((t || (t = {}))[s] = e[s]);
    return t
  },
  Mg = (e, t) => {
    const s = {};
    for (const o in e)(!eu(o) || !(o.slice(9) in t)) && (s[o] = e[o]);
    return s
  };

function Rg(e, t, s) {
  const {
    props: o,
    children: i,
    component: r
  } = e, {
    props: a,
    children: l,
    patchFlag: d
  } = t, p = r.emitsOptions;
  if (t.dirs || t.transition) return !0;
  if (s && d >= 0) {
    if (d & 1024) return !0;
    if (d & 16) return o ? df(o, a, p) : !!a;
    if (d & 8) {
      const m = t.dynamicProps;
      for (let g = 0; g < m.length; g++) {
        const $ = m[g];
        if (a[$] !== o[$] && !Ga(p, $)) return !0
      }
    }
  } else return (i || l) && (!l || !l.$stable) ? !0 : o === a ? !1 : o ? a ? df(o, a, p) : !0 : !!a;
  return !1
}

function df(e, t, s) {
  const o = Object.keys(t);
  if (o.length !== Object.keys(e).length) return !0;
  for (let i = 0; i < o.length; i++) {
    const r = o[i];
    if (t[r] !== e[r] && !Ga(s, r)) return !0
  }
  return !1
}

function mu({
  vnode: e,
  parent: t
}, s) {
  for (; t;) {
    const o = t.subTree;
    if (o.suspense && o.suspense.activeBranch === e && (o.el = e.el), o === e)(e = t.vnode).el = s, t = t.parent;
    else break
  }
}
const gu = "components",
  Ng = "directives";

function ke(e, t) {
  return bu(gu, e, !0, t) || e
}
const Ep = Symbol.for("v-ndc");

function ha(e) {
  return rt(e) ? bu(gu, e, !1) || e : e || Ep
}

function _u(e) {
  return bu(Ng, e)
}

function bu(e, t, s = !0, o = !1) {
  const i = vt || wt;
  if (i) {
    const r = i.type;
    if (e === gu) {
      const l = Td(r, !1);
      if (l && (l === t || l === jt(t) || l === Fi(jt(t)))) return r
    }
    const a = uf(i[e] || r[e], t) || uf(i.appContext[e], t);
    return !a && o ? r : a
  }
}

function uf(e, t) {
  return e && (e[t] || e[jt(t)] || e[Fi(jt(t))])
}
const Pp = e => e.__isSuspense;
let bd = 0;
const Fg = {
    name: "Suspense",
    __isSuspense: !0,
    process(e, t, s, o, i, r, a, l, d, p) {
      if (e == null) Vg(t, s, o, i, r, a, l, d, p);
      else {
        if (r && r.deps > 0 && !e.suspense.isInFallback) {
          t.suspense = e.suspense, t.suspense.vnode = t, t.el = e.el;
          return
        }
        jg(e, t, s, o, i, a, l, d, p)
      }
    },
    hydrate: Hg,
    create: vu,
    normalize: qg
  },
  Ug = Fg;

function Si(e, t) {
  const s = e.props && e.props[t];
  Be(s) && s()
}

function Vg(e, t, s, o, i, r, a, l, d) {
  const {
    p,
    o: {
      createElement: m
    }
  } = d, g = m("div"), $ = e.suspense = vu(e, i, o, t, g, s, r, a, l, d);
  p(null, $.pendingBranch = e.ssContent, g, null, o, $, r, a), $.deps > 0 ? (Si(e, "onPending"), Si(e, "onFallback"), p(null, e.ssFallback, t, s, o, null, r, a), Co($, e.ssFallback)) : $.resolve(!1, !0)
}

function jg(e, t, s, o, i, r, a, l, {
  p: d,
  um: p,
  o: {
    createElement: m
  }
}) {
  const g = t.suspense = e.suspense;
  g.vnode = t, t.el = e.el;
  const $ = t.ssContent,
    C = t.ssFallback,
    {
      activeBranch: A,
      pendingBranch: O,
      isInFallback: V,
      isHydrating: M
    } = g;
  if (O) g.pendingBranch = $, bs($, O) ? (d(O, $, g.hiddenContainer, null, i, g, r, a, l), g.deps <= 0 ? g.resolve() : V && (M || (d(A, C, s, o, i, null, r, a, l), Co(g, C)))) : (g.pendingId = bd++, M ? (g.isHydrating = !1, g.activeBranch = O) : p(O, i, g), g.deps = 0, g.effects.length = 0, g.hiddenContainer = m("div"), V ? (d(null, $, g.hiddenContainer, null, i, g, r, a, l), g.deps <= 0 ? g.resolve() : (d(A, C, s, o, i, null, r, a, l), Co(g, C))) : A && bs($, A) ? (d(A, $, s, o, i, g, r, a, l), g.resolve(!0)) : (d(null, $, g.hiddenContainer, null, i, g, r, a, l), g.deps <= 0 && g.resolve()));
  else if (A && bs($, A)) d(A, $, s, o, i, g, r, a, l), Co(g, $);
  else if (Si(t, "onPending"), g.pendingBranch = $, $.shapeFlag & 512 ? g.pendingId = $.component.suspenseId : g.pendingId = bd++, d(null, $, g.hiddenContainer, null, i, g, r, a, l), g.deps <= 0) g.resolve();
  else {
    const {
      timeout: B,
      pendingId: P
    } = g;
    B > 0 ? setTimeout(() => {
      g.pendingId === P && g.fallback(C)
    }, B) : B === 0 && g.fallback(C)
  }
}

function vu(e, t, s, o, i, r, a, l, d, p, m = !1) {
  const {
    p: g,
    m: $,
    um: C,
    n: A,
    o: {
      parentNode: O,
      remove: V
    }
  } = p;
  let M;
  const B = Wg(e);
  B && t != null && t.pendingBranch && (M = t.pendingId, t.deps++);
  const P = e.props ? $a(e.props.timeout) : void 0,
    I = r,
    R = {
      vnode: e,
      parent: t,
      parentComponent: s,
      namespace: a,
      container: o,
      hiddenContainer: i,
      deps: 0,
      pendingId: bd++,
      timeout: typeof P == "number" ? P : -1,
      activeBranch: null,
      pendingBranch: null,
      isInFallback: !m,
      isHydrating: m,
      isUnmounted: !1,
      effects: [],
      resolve(F = !1, j = !1) {
        const {
          vnode: q,
          activeBranch: ne,
          pendingBranch: ee,
          pendingId: ge,
          effects: T,
          parentComponent: re,
          container: Le
        } = R;
        let We = !1;
        R.isHydrating ? R.isHydrating = !1 : F || (We = ne && ee.transition && ee.transition.mode === "out-in", We && (ne.transition.afterLeave = () => {
          ge === R.pendingId && ($(ee, Le, r === I ? A(ne) : r, 0), ka(T))
        }), ne && (O(ne.el) !== R.hiddenContainer && (r = A(ne)), C(ne, re, R, !0)), We || $(ee, Le, r, 0)), Co(R, ee), R.pendingBranch = null, R.isInFallback = !1;
        let fe = R.parent,
          ue = !1;
        for (; fe;) {
          if (fe.pendingBranch) {
            fe.effects.push(...T), ue = !0;
            break
          }
          fe = fe.parent
        }!ue && !We && ka(T), R.effects = [], B && t && t.pendingBranch && M === t.pendingId && (t.deps--, t.deps === 0 && !j && t.resolve()), Si(q, "onResolve")
      },
      fallback(F) {
        if (!R.pendingBranch) return;
        const {
          vnode: j,
          activeBranch: q,
          parentComponent: ne,
          container: ee,
          namespace: ge
        } = R;
        Si(j, "onFallback");
        const T = A(q),
          re = () => {
            R.isInFallback && (g(null, F, ee, T, ne, null, ge, l, d), Co(R, F))
          },
          Le = F.transition && F.transition.mode === "out-in";
        Le && (q.transition.afterLeave = re), R.isInFallback = !0, C(q, ne, null, !0), Le || re()
      },
      move(F, j, q) {
        R.activeBranch && $(R.activeBranch, F, j, q), R.container = F
      },
      next() {
        return R.activeBranch && A(R.activeBranch)
      },
      registerDep(F, j) {
        const q = !!R.pendingBranch;
        q && R.deps++;
        const ne = F.vnode.el;
        F.asyncDep.catch(ee => {
          Zn(ee, F, 0)
        }).then(ee => {
          if (F.isUnmounted || R.isUnmounted || R.pendingId !== F.suspenseId) return;
          F.asyncResolved = !0;
          const {
            vnode: ge
          } = F;
          Ed(F, ee, !1), ne && (ge.el = ne);
          const T = !ne && F.subTree.el;
          j(F, ge, O(ne || F.subTree.el), ne ? null : A(F.subTree), R, a, d), T && V(T), mu(F, ge.el), q && --R.deps === 0 && R.resolve()
        })
      },
      unmount(F, j) {
        R.isUnmounted = !0, R.activeBranch && C(R.activeBranch, s, F, j), R.pendingBranch && C(R.pendingBranch, s, F, j)
      }
    };
  return R
}

function Hg(e, t, s, o, i, r, a, l, d) {
  const p = t.suspense = vu(t, o, s, e.parentNode, document.createElement("div"), null, i, r, a, l, !0),
    m = d(e, p.pendingBranch = t.ssContent, s, p, r, a);
  return p.deps === 0 && p.resolve(!1, !0), m
}

function qg(e) {
  const {
    shapeFlag: t,
    children: s
  } = e, o = t & 32;
  e.ssContent = ff(o ? s.default : s), e.ssFallback = o ? ff(s.fallback) : ie(Bt)
}

function ff(e) {
  let t;
  if (Be(e)) {
    const s = zn && e._c;
    s && (e._d = !1, _()), e = e(), s && (e._d = !0, t = Gt, cm())
  }
  return we(e) && (e = Bg(e)), e = es(e), t && !e.dynamicChildren && (e.dynamicChildren = t.filter(s => s !== e)), e
}

function Tp(e, t) {
  t && t.pendingBranch ? we(e) ? t.effects.push(...e) : t.effects.push(e) : ka(e)
}

function Co(e, t) {
  e.activeBranch = t;
  const {
    vnode: s,
    parentComponent: o
  } = e;
  let i = t.el;
  for (; !i && t.component;) t = t.component.subTree, i = t.el;
  s.el = i, o && o.subTree === s && (o.vnode.el = i, mu(o, i))
}

function Wg(e) {
  var t;
  return ((t = e.props) == null ? void 0 : t.suspensible) != null && e.props.suspensible !== !1
}
const Lp = Symbol.for("v-scx"),
  Op = () => Ge(Lp);

function zg(e, t) {
  return Vi(e, null, t)
}

function Ip(e, t) {
  return Vi(e, null, {
    flush: "post"
  })
}

function Bp(e, t) {
  return Vi(e, null, {
    flush: "sync"
  })
}
const sa = {};

function Ks(e, t, s) {
  return Vi(e, t, s)
}

function Vi(e, t, {
  immediate: s,
  deep: o,
  flush: i,
  once: r,
  onTrack: a,
  onTrigger: l
} = et) {
  if (t && r) {
    const F = t;
    t = (...j) => {
      F(...j), R()
    }
  }
  const d = wt,
    p = F => o === !0 ? F : Rn(F, o === !1 ? 1 : void 0);
  let m, g = !1,
    $ = !1;
  if (Tt(e) ? (m = () => e.value, g = $i(e)) : Un(e) ? (m = () => p(e), g = !0) : we(e) ? ($ = !0, g = e.some(F => Un(F) || $i(F)), m = () => e.map(F => {
      if (Tt(F)) return F.value;
      if (Un(F)) return p(F);
      if (Be(F)) return zs(F, d, 2)
    })) : Be(e) ? t ? m = () => zs(e, d, 2) : m = () => (C && C(), ns(e, d, 3, [A])) : m = Kt, t && o) {
    const F = m;
    m = () => Rn(F())
  }
  let C, A = F => {
      C = P.onStop = () => {
        zs(F, d, 4), C = P.onStop = void 0
      }
    },
    O;
  if (Hi)
    if (A = Kt, t ? s && ns(t, d, 3, [m(), $ ? [] : void 0, A]) : m(), i === "sync") {
      const F = Op();
      O = F.__watcherHandles || (F.__watcherHandles = [])
    } else return Kt;
  let V = $ ? new Array(e.length).fill(sa) : sa;
  const M = () => {
    if (!(!P.active || !P.dirty))
      if (t) {
        const F = P.run();
        (o || g || ($ ? F.some((j, q) => vs(j, V[q])) : vs(F, V))) && (C && C(), ns(t, d, 3, [F, V === sa ? void 0 : $ && V[0] === sa ? [] : V, A]), V = F)
      } else P.run()
  };
  M.allowRecurse = !!t;
  let B;
  i === "sync" ? B = M : i === "post" ? B = () => Et(M, d && d.suspense) : (M.pre = !0, d && (M.id = d.uid), B = () => Ka(M));
  const P = new Ao(m, Kt, B),
    I = op(),
    R = () => {
      P.stop(), I && tu(I.effects, P)
    };
  return t ? s ? M() : V = P.run() : i === "post" ? Et(P.run.bind(P), d && d.suspense) : P.run(), O && O.push(R), R
}

function Kg(e, t, s) {
  const o = this.proxy,
    i = rt(e) ? e.includes(".") ? Dp(o, e) : () => o[e] : e.bind(o, o);
  let r;
  Be(t) ? r = t : (r = t.handler, s = t);
  const a = Kn(this),
    l = Vi(i, r.bind(o), s);
  return a(), l
}

function Dp(e, t) {
  const s = t.split(".");
  return () => {
    let o = e;
    for (let i = 0; i < s.length && o; i++) o = o[s[i]];
    return o
  }
}

function Rn(e, t, s = 0, o) {
  if (!st(e) || e.__v_skip) return e;
  if (t && t > 0) {
    if (s >= t) return e;
    s++
  }
  if (o = o || new Set, o.has(e)) return e;
  if (o.add(e), Tt(e)) Rn(e.value, t, s, o);
  else if (we(e))
    for (let i = 0; i < e.length; i++) Rn(e[i], t, s, o);
  else if (Gn(e) || bo(e)) e.forEach(i => {
    Rn(i, t, s, o)
  });
  else if (Xh(e))
    for (const i in e) Rn(e[i], t, s, o);
  return e
}

function be(e, t) {
  if (vt === null) return e;
  const s = sl(vt) || vt.proxy,
    o = e.dirs || (e.dirs = []);
  for (let i = 0; i < t.length; i++) {
    let [r, a, l, d = et] = t[i];
    r && (Be(r) && (r = {
      mounted: r,
      updated: r
    }), r.deep && Rn(a), o.push({
      dir: r,
      instance: s,
      value: a,
      oldValue: void 0,
      arg: l,
      modifiers: d
    }))
  }
  return e
}

function Ss(e, t, s, o) {
  const i = e.dirs,
    r = t && t.dirs;
  for (let a = 0; a < i.length; a++) {
    const l = i[a];
    r && (l.oldValue = r[a].value);
    let d = l.dir[o];
    d && (Yn(), ns(d, s, 8, [e.el, l, e, t]), Jn())
  }
}
const gn = Symbol("_leaveCb"),
  na = Symbol("_enterCb");

function yu() {
  const e = {
    isMounted: !1,
    isLeaving: !1,
    isUnmounting: !1,
    leavingVNodes: new Map
  };
  return At(() => {
    e.isMounted = !0
  }), Qa(() => {
    e.isUnmounting = !0
  }), e
}
const cs = [Function, Array],
  wu = {
    mode: String,
    appear: Boolean,
    persisted: Boolean,
    onBeforeEnter: cs,
    onEnter: cs,
    onAfterEnter: cs,
    onEnterCancelled: cs,
    onBeforeLeave: cs,
    onLeave: cs,
    onAfterLeave: cs,
    onLeaveCancelled: cs,
    onBeforeAppear: cs,
    onAppear: cs,
    onAfterAppear: cs,
    onAppearCancelled: cs
  },
  Gg = {
    name: "BaseTransition",
    props: wu,
    setup(e, {
      slots: t
    }) {
      const s = Zs(),
        o = yu();
      return () => {
        const i = t.default && Ja(t.default(), !0);
        if (!i || !i.length) return;
        let r = i[0];
        if (i.length > 1) {
          for (const $ of i)
            if ($.type !== Bt) {
              r = $;
              break
            }
        }
        const a = Ke(e),
          {
            mode: l
          } = a;
        if (o.isLeaving) return qc(r);
        const d = hf(r);
        if (!d) return qc(r);
        const p = xo(d, a, o, s);
        qn(d, p);
        const m = s.subTree,
          g = m && hf(m);
        if (g && g.type !== Bt && !bs(d, g)) {
          const $ = xo(g, a, o, s);
          if (qn(g, $), l === "out-in") return o.isLeaving = !0, $.afterLeave = () => {
            o.isLeaving = !1, s.update.active !== !1 && (s.effect.dirty = !0, s.update())
          }, qc(r);
          l === "in-out" && d.type !== Bt && ($.delayLeave = (C, A, O) => {
            const V = Rp(o, g);
            V[String(g.key)] = g, C[gn] = () => {
              A(), C[gn] = void 0, delete p.delayedLeave
            }, p.delayedLeave = O
          })
        }
        return r
      }
    }
  },
  Mp = Gg;

function Rp(e, t) {
  const {
    leavingVNodes: s
  } = e;
  let o = s.get(t.type);
  return o || (o = Object.create(null), s.set(t.type, o)), o
}

function xo(e, t, s, o) {
  const {
    appear: i,
    mode: r,
    persisted: a = !1,
    onBeforeEnter: l,
    onEnter: d,
    onAfterEnter: p,
    onEnterCancelled: m,
    onBeforeLeave: g,
    onLeave: $,
    onAfterLeave: C,
    onLeaveCancelled: A,
    onBeforeAppear: O,
    onAppear: V,
    onAfterAppear: M,
    onAppearCancelled: B
  } = t, P = String(e.key), I = Rp(s, e), R = (q, ne) => {
    q && ns(q, o, 9, ne)
  }, F = (q, ne) => {
    const ee = ne[1];
    R(q, ne), we(q) ? q.every(ge => ge.length <= 1) && ee() : q.length <= 1 && ee()
  }, j = {
    mode: r,
    persisted: a,
    beforeEnter(q) {
      let ne = l;
      if (!s.isMounted)
        if (i) ne = O || l;
        else return;
      q[gn] && q[gn](!0);
      const ee = I[P];
      ee && bs(e, ee) && ee.el[gn] && ee.el[gn](), R(ne, [q])
    },
    enter(q) {
      let ne = d,
        ee = p,
        ge = m;
      if (!s.isMounted)
        if (i) ne = V || d, ee = M || p, ge = B || m;
        else return;
      let T = !1;
      const re = q[na] = Le => {
        T || (T = !0, Le ? R(ge, [q]) : R(ee, [q]), j.delayedLeave && j.delayedLeave(), q[na] = void 0)
      };
      ne ? F(ne, [q, re]) : re()
    },
    leave(q, ne) {
      const ee = String(e.key);
      if (q[na] && q[na](!0), s.isUnmounting) return ne();
      R(g, [q]);
      let ge = !1;
      const T = q[gn] = re => {
        ge || (ge = !0, ne(), re ? R(A, [q]) : R(C, [q]), q[gn] = void 0, I[ee] === e && delete I[ee])
      };
      I[ee] = e, $ ? F($, [q, T]) : T()
    },
    clone(q) {
      return xo(q, t, s, o)
    }
  };
  return j
}

function qc(e) {
  if (ji(e)) return e = Ts(e), e.children = null, e
}

function hf(e) {
  return ji(e) ? e.children ? e.children[0] : void 0 : e
}

function qn(e, t) {
  e.shapeFlag & 6 && e.component ? qn(e.component.subTree, t) : e.shapeFlag & 128 ? (e.ssContent.transition = t.clone(e.ssContent), e.ssFallback.transition = t.clone(e.ssFallback)) : e.transition = t
}

function Ja(e, t = !1, s) {
  let o = [],
    i = 0;
  for (let r = 0; r < e.length; r++) {
    let a = e[r];
    const l = s == null ? a.key : String(s) + String(a.key != null ? a.key : r);
    a.type === Te ? (a.patchFlag & 128 && i++, o = o.concat(Ja(a.children, t, l))) : (t || a.type !== Bt) && o.push(l != null ? Ts(a, {
      key: l
    }) : a)
  }
  if (i > 1)
    for (let r = 0; r < o.length; r++) o[r].patchFlag = -2;
  return o
} /*! #__NO_SIDE_EFFECTS__ */
function Dt(e, t) {
  return Be(e) ? it({
    name: e.name
  }, t, {
    setup: e
  }) : e
}
const Vn = e => !!e.type.__asyncLoader; /*! #__NO_SIDE_EFFECTS__ */
function Yg(e) {
  Be(e) && (e = {
    loader: e
  });
  const {
    loader: t,
    loadingComponent: s,
    errorComponent: o,
    delay: i = 200,
    timeout: r,
    suspensible: a = !0,
    onError: l
  } = e;
  let d = null,
    p, m = 0;
  const g = () => (m++, d = null, $()),
    $ = () => {
      let C;
      return d || (C = d = t().catch(A => {
        if (A = A instanceof Error ? A : new Error(String(A)), l) return new Promise((O, V) => {
          l(A, () => O(g()), () => V(A), m + 1)
        });
        throw A
      }).then(A => C !== d && d ? d : (A && (A.__esModule || A[Symbol.toStringTag] === "Module") && (A = A.default), p = A, A)))
    };
  return Dt({
    name: "AsyncComponentWrapper",
    __asyncLoader: $,
    get __asyncResolved() {
      return p
    },
    setup() {
      const C = wt;
      if (p) return () => Wc(p, C);
      const A = B => {
        d = null, Zn(B, C, 13, !o)
      };
      if (a && C.suspense || Hi) return $().then(B => () => Wc(B, C)).catch(B => (A(B), () => o ? ie(o, {
        error: B
      }) : null));
      const O = he(!1),
        V = he(),
        M = he(!!i);
      return i && setTimeout(() => {
        M.value = !1
      }, i), r != null && setTimeout(() => {
        if (!O.value && !V.value) {
          const B = new Error(`Async component timed out after ${r}ms.`);
          A(B), V.value = B
        }
      }, r), $().then(() => {
        O.value = !0, C.parent && ji(C.parent.vnode) && (C.parent.effect.dirty = !0, Ka(C.parent.update))
      }).catch(B => {
        A(B), V.value = B
      }), () => {
        if (O.value && p) return Wc(p, C);
        if (V.value && o) return ie(o, {
          error: V.value
        });
        if (s && !M.value) return ie(s)
      }
    }
  })
}

function Wc(e, t) {
  const {
    ref: s,
    props: o,
    children: i,
    ce: r
  } = t.vnode, a = ie(e, o, i);
  return a.ref = s, a.ce = r, delete t.vnode.ce, a
}
const ji = e => e.type.__isKeepAlive,
  Jg = {
    name: "KeepAlive",
    __isKeepAlive: !0,
    props: {
      include: [String, RegExp, Array],
      exclude: [String, RegExp, Array],
      max: [String, Number]
    },
    setup(e, {
      slots: t
    }) {
      const s = Zs(),
        o = s.ctx;
      if (!o.renderer) return () => {
        const B = t.default && t.default();
        return B && B.length === 1 ? B[0] : B
      };
      const i = new Map,
        r = new Set;
      let a = null;
      const l = s.suspense,
        {
          renderer: {
            p: d,
            m: p,
            um: m,
            o: {
              createElement: g
            }
          }
        } = o,
        $ = g("div");
      o.activate = (B, P, I, R, F) => {
        const j = B.component;
        p(B, P, I, 0, l), d(j.vnode, B, P, I, j, l, R, B.slotScopeIds, F), Et(() => {
          j.isDeactivated = !1, j.a && yo(j.a);
          const q = B.props && B.props.onVnodeMounted;
          q && zt(q, j.parent, B)
        }, l)
      }, o.deactivate = B => {
        const P = B.component;
        p(B, $, null, 1, l), Et(() => {
          P.da && yo(P.da);
          const I = B.props && B.props.onVnodeUnmounted;
          I && zt(I, P.parent, B), P.isDeactivated = !0
        }, l)
      };

      function C(B) {
        zc(B), m(B, s, l, !0)
      }

      function A(B) {
        i.forEach((P, I) => {
          const R = Td(P.type);
          R && (!B || !B(R)) && O(I)
        })
      }

      function O(B) {
        const P = i.get(B);
        !a || !bs(P, a) ? C(P) : a && zc(a), i.delete(B), r.delete(B)
      }
      Ks(() => [e.include, e.exclude], ([B, P]) => {
        B && A(I => di(B, I)), P && A(I => !di(P, I))
      }, {
        flush: "post",
        deep: !0
      });
      let V = null;
      const M = () => {
        V != null && i.set(V, Kc(s.subTree))
      };
      return At(M), Xa(M), Qa(() => {
        i.forEach(B => {
          const {
            subTree: P,
            suspense: I
          } = s, R = Kc(P);
          if (B.type === R.type && B.key === R.key) {
            zc(R);
            const F = R.component.da;
            F && Et(F, I);
            return
          }
          C(B)
        })
      }), () => {
        if (V = null, !t.default) return null;
        const B = t.default(),
          P = B[0];
        if (B.length > 1) return a = null, B;
        if (!kn(P) || !(P.shapeFlag & 4) && !(P.shapeFlag & 128)) return a = null, P;
        let I = Kc(P);
        const R = I.type,
          F = Td(Vn(I) ? I.type.__asyncResolved || {} : R),
          {
            include: j,
            exclude: q,
            max: ne
          } = e;
        if (j && (!F || !di(j, F)) || q && F && di(q, F)) return a = I, P;
        const ee = I.key == null ? R : I.key,
          ge = i.get(ee);
        return I.el && (I = Ts(I), P.shapeFlag & 128 && (P.ssContent = I)), V = ee, ge ? (I.el = ge.el, I.component = ge.component, I.transition && qn(I, I.transition), I.shapeFlag |= 512, r.delete(ee), r.add(ee)) : (r.add(ee), ne && r.size > parseInt(ne, 10) && O(r.values().next().value)), I.shapeFlag |= 256, a = I, Pp(P.type) ? P : I
      }
    }
  },
  Zg = Jg;

function di(e, t) {
  return we(e) ? e.some(s => di(s, t)) : rt(e) ? e.split(",").includes(t) : T0(e) ? e.test(t) : !1
}

function Np(e, t) {
  Up(e, "a", t)
}

function Fp(e, t) {
  Up(e, "da", t)
}

function Up(e, t, s = wt) {
  const o = e.__wdc || (e.__wdc = () => {
    let i = s;
    for (; i;) {
      if (i.isDeactivated) return;
      i = i.parent
    }
    return e()
  });
  if (Za(t, o, s), s) {
    let i = s.parent;
    for (; i && i.parent;) ji(i.parent.vnode) && Xg(o, t, s, i), i = i.parent
  }
}

function Xg(e, t, s, o) {
  const i = Za(t, e, o, !0);
  el(() => {
    tu(o[t], i)
  }, s)
}

function zc(e) {
  e.shapeFlag &= -257, e.shapeFlag &= -513
}

function Kc(e) {
  return e.shapeFlag & 128 ? e.ssContent : e
}

function Za(e, t, s = wt, o = !1) {
  if (s) {
    const i = s[e] || (s[e] = []),
      r = t.__weh || (t.__weh = (...a) => {
        if (s.isUnmounted) return;
        Yn();
        const l = Kn(s),
          d = ns(t, s, e, a);
        return l(), Jn(), d
      });
    return o ? i.unshift(r) : i.push(r), r
  }
}
const Js = e => (t, s = wt) => (!Hi || e === "sp") && Za(e, (...o) => t(...o), s),
  Vp = Js("bm"),
  At = Js("m"),
  jp = Js("bu"),
  Xa = Js("u"),
  Qa = Js("bum"),
  el = Js("um"),
  Hp = Js("sp"),
  qp = Js("rtg"),
  Wp = Js("rtc");

function zp(e, t = wt) {
  Za("ec", e, t)
}

function Je(e, t, s, o) {
  let i;
  const r = s && s[o];
  if (we(e) || rt(e)) {
    i = new Array(e.length);
    for (let a = 0, l = e.length; a < l; a++) i[a] = t(e[a], a, void 0, r && r[a])
  } else if (typeof e == "number") {
    i = new Array(e);
    for (let a = 0; a < e; a++) i[a] = t(a + 1, a, void 0, r && r[a])
  } else if (st(e))
    if (e[Symbol.iterator]) i = Array.from(e, (a, l) => t(a, l, void 0, r && r[l]));
    else {
      const a = Object.keys(e);
      i = new Array(a.length);
      for (let l = 0, d = a.length; l < d; l++) {
        const p = a[l];
        i[l] = t(e[p], p, l, r && r[l])
      }
    }
  else i = [];
  return s && (s[o] = i), i
}

function Qg(e, t) {
  for (let s = 0; s < t.length; s++) {
    const o = t[s];
    if (we(o))
      for (let i = 0; i < o.length; i++) e[o[i].name] = o[i].fn;
    else o && (e[o.name] = o.key ? (...i) => {
      const r = o.fn(...i);
      return r && (r.key = o.key), r
    } : o.fn)
  }
  return e
}

function Wt(e, t, s = {}, o, i) {
  if (vt.isCE || vt.parent && Vn(vt.parent) && vt.parent.isCE) return t !== "default" && (s.name = t), ie("slot", s, o && o());
  let r = e[t];
  r && r._c && (r._d = !1), _();
  const a = r && Kp(r(s)),
    l = ve(Te, {
      key: s.key || a && a.key || `_${t}`
    }, a || (o ? o() : []), a && e._ === 1 ? 64 : -2);
  return !i && l.scopeId && (l.slotScopeIds = [l.scopeId + "-s"]), r && r._c && (r._d = !0), l
}

function Kp(e) {
  return e.some(t => kn(t) ? !(t.type === Bt || t.type === Te && !Kp(t.children)) : !0) ? e : null
}

function Gp(e, t) {
  const s = {};
  for (const o in e) s[t && /[A-Z]/.test(o) ? `on:${o}` : fi(o)] = e[o];
  return s
}
const vd = e => e ? hm(e) ? sl(e) || e.proxy : vd(e.parent) : null,
  hi = it(Object.create(null), {
    $: e => e,
    $el: e => e.vnode.el,
    $data: e => e.data,
    $props: e => e.props,
    $attrs: e => e.attrs,
    $slots: e => e.slots,
    $refs: e => e.refs,
    $parent: e => vd(e.parent),
    $root: e => vd(e.root),
    $emit: e => e.emit,
    $options: e => $u(e),
    $forceUpdate: e => e.f || (e.f = () => {
      e.effect.dirty = !0, Ka(e.update)
    }),
    $nextTick: e => e.n || (e.n = Ui.bind(e.proxy)),
    $watch: e => Kg.bind(e)
  }),
  Gc = (e, t) => e !== et && !e.__isScriptSetup && Ze(e, t),
  yd = {
    get({
      _: e
    }, t) {
      const {
        ctx: s,
        setupState: o,
        data: i,
        props: r,
        accessCache: a,
        type: l,
        appContext: d
      } = e;
      let p;
      if (t[0] !== "$") {
        const C = a[t];
        if (C !== void 0) switch (C) {
          case 1:
            return o[t];
          case 2:
            return i[t];
          case 4:
            return s[t];
          case 3:
            return r[t]
        } else {
          if (Gc(o, t)) return a[t] = 1, o[t];
          if (i !== et && Ze(i, t)) return a[t] = 2, i[t];
          if ((p = e.propsOptions[0]) && Ze(p, t)) return a[t] = 3, r[t];
          if (s !== et && Ze(s, t)) return a[t] = 4, s[t];
          wd && (a[t] = 0)
        }
      }
      const m = hi[t];
      let g, $;
      if (m) return t === "$attrs" && Yt(e, "get", t), m(e);
      if ((g = l.__cssModules) && (g = g[t])) return g;
      if (s !== et && Ze(s, t)) return a[t] = 4, s[t];
      if ($ = d.config.globalProperties, Ze($, t)) return $[t]
    },
    set({
      _: e
    }, t, s) {
      const {
        data: o,
        setupState: i,
        ctx: r
      } = e;
      return Gc(i, t) ? (i[t] = s, !0) : o !== et && Ze(o, t) ? (o[t] = s, !0) : Ze(e.props, t) || t[0] === "$" && t.slice(1) in e ? !1 : (r[t] = s, !0)
    },
    has({
      _: {
        data: e,
        setupState: t,
        accessCache: s,
        ctx: o,
        appContext: i,
        propsOptions: r
      }
    }, a) {
      let l;
      return !!s[a] || e !== et && Ze(e, a) || Gc(t, a) || (l = r[0]) && Ze(l, a) || Ze(o, a) || Ze(hi, a) || Ze(i.config.globalProperties, a)
    },
    defineProperty(e, t, s) {
      return s.get != null ? e._.accessCache[t] = 0 : Ze(s, "value") && this.set(e, t, s.value, null), Reflect.defineProperty(e, t, s)
    }
  },
  e2 = it({}, yd, {
    get(e, t) {
      if (t !== Symbol.unscopables) return yd.get(e, t, e)
    },
    has(e, t) {
      return t[0] !== "_" && !D0(t)
    }
  });

function t2() {
  return null
}

function s2() {
  return null
}

function n2(e) {}

function o2(e) {}

function i2() {
  return null
}

function r2() {}

function a2(e, t) {
  return null
}

function l2() {
  return Yp().slots
}

function c2() {
  return Yp().attrs
}

function Yp() {
  const e = Zs();
  return e.setupContext || (e.setupContext = gm(e))
}

function Ei(e) {
  return we(e) ? e.reduce((t, s) => (t[s] = null, t), {}) : e
}

function d2(e, t) {
  const s = Ei(e);
  for (const o in t) {
    if (o.startsWith("__skip")) continue;
    let i = s[o];
    i ? we(i) || Be(i) ? i = s[o] = {
      type: i,
      default: t[o]
    } : i.default = t[o] : i === null && (i = s[o] = {
      default: t[o]
    }), i && t[`__skip_${o}`] && (i.skipFactory = !0)
  }
  return s
}

function u2(e, t) {
  return !e || !t ? e || t : we(e) && we(t) ? e.concat(t) : it({}, Ei(e), Ei(t))
}

function f2(e, t) {
  const s = {};
  for (const o in e) t.includes(o) || Object.defineProperty(s, o, {
    enumerable: !0,
    get: () => e[o]
  });
  return s
}

function h2(e) {
  const t = Zs();
  let s = e();
  return Sd(), su(s) && (s = s.catch(o => {
    throw Kn(t), o
  })), [s, () => Kn(t)]
}
let wd = !0;

function p2(e) {
  const t = $u(e),
    s = e.proxy,
    o = e.ctx;
  wd = !1, t.beforeCreate && pf(t.beforeCreate, e, "bc");
  const {
    data: i,
    computed: r,
    methods: a,
    watch: l,
    provide: d,
    inject: p,
    created: m,
    beforeMount: g,
    mounted: $,
    beforeUpdate: C,
    updated: A,
    activated: O,
    deactivated: V,
    beforeDestroy: M,
    beforeUnmount: B,
    destroyed: P,
    unmounted: I,
    render: R,
    renderTracked: F,
    renderTriggered: j,
    errorCaptured: q,
    serverPrefetch: ne,
    expose: ee,
    inheritAttrs: ge,
    components: T,
    directives: re,
    filters: Le
  } = t;
  if (p && m2(p, o, null), a)
    for (const ue in a) {
      const te = a[ue];
      Be(te) && (o[ue] = te.bind(s))
    }
  if (i) {
    const ue = i.call(s, s);
    st(ue) && (e.data = Ls(ue))
  }
  if (wd = !0, r)
    for (const ue in r) {
      const te = r[ue],
        $t = Be(te) ? te.bind(s, s) : Be(te.get) ? te.get.bind(s, s) : Kt,
        Lt = !Be(te) && Be(te.set) ? te.set.bind(s) : Kt,
        xt = ss({
          get: $t,
          set: Lt
        });
      Object.defineProperty(o, ue, {
        enumerable: !0,
        configurable: !0,
        get: () => xt.value,
        set: ht => xt.value = ht
      })
    }
  if (l)
    for (const ue in l) Jp(l[ue], o, s, ue);
  if (d) {
    const ue = Be(d) ? d.call(s) : d;
    Reflect.ownKeys(ue).forEach(te => {
      pi(te, ue[te])
    })
  }
  m && pf(m, e, "c");

  function fe(ue, te) {
    we(te) ? te.forEach($t => ue($t.bind(s))) : te && ue(te.bind(s))
  }
  if (fe(Vp, g), fe(At, $), fe(jp, C), fe(Xa, A), fe(Np, O), fe(Fp, V), fe(zp, q), fe(Wp, F), fe(qp, j), fe(Qa, B), fe(el, I), fe(Hp, ne), we(ee))
    if (ee.length) {
      const ue = e.exposed || (e.exposed = {});
      ee.forEach(te => {
        Object.defineProperty(ue, te, {
          get: () => s[te],
          set: $t => s[te] = $t
        })
      })
    } else e.exposed || (e.exposed = {});
  R && e.render === Kt && (e.render = R), ge != null && (e.inheritAttrs = ge), T && (e.components = T), re && (e.directives = re)
}

function m2(e, t, s = Kt) {
  we(e) && (e = $d(e));
  for (const o in e) {
    const i = e[o];
    let r;
    st(i) ? "default" in i ? r = Ge(i.from || o, i.default, !0) : r = Ge(i.from || o) : r = Ge(i), Tt(r) ? Object.defineProperty(t, o, {
      enumerable: !0,
      configurable: !0,
      get: () => r.value,
      set: a => r.value = a
    }) : t[o] = r
  }
}

function pf(e, t, s) {
  ns(we(e) ? e.map(o => o.bind(t.proxy)) : e.bind(t.proxy), t, s)
}

function Jp(e, t, s, o) {
  const i = o.includes(".") ? Dp(s, o) : () => s[o];
  if (rt(e)) {
    const r = t[e];
    Be(r) && Ks(i, r)
  } else if (Be(e)) Ks(i, e.bind(s));
  else if (st(e))
    if (we(e)) e.forEach(r => Jp(r, t, s, o));
    else {
      const r = Be(e.handler) ? e.handler.bind(s) : t[e.handler];
      Be(r) && Ks(i, r, e)
    }
}

function $u(e) {
  const t = e.type,
    {
      mixins: s,
      extends: o
    } = t,
    {
      mixins: i,
      optionsCache: r,
      config: {
        optionMergeStrategies: a
      }
    } = e.appContext,
    l = r.get(t);
  let d;
  return l ? d = l : !i.length && !s && !o ? d = t : (d = {}, i.length && i.forEach(p => xa(d, p, a, !0)), xa(d, t, a)), st(t) && r.set(t, d), d
}

function xa(e, t, s, o = !1) {
  const {
    mixins: i,
    extends: r
  } = t;
  r && xa(e, r, s, !0), i && i.forEach(a => xa(e, a, s, !0));
  for (const a in t)
    if (!(o && a === "expose")) {
      const l = g2[a] || s && s[a];
      e[a] = l ? l(e[a], t[a]) : t[a]
    } return e
}
const g2 = {
  data: mf,
  props: gf,
  emits: gf,
  methods: ui,
  computed: ui,
  beforeCreate: Vt,
  created: Vt,
  beforeMount: Vt,
  mounted: Vt,
  beforeUpdate: Vt,
  updated: Vt,
  beforeDestroy: Vt,
  beforeUnmount: Vt,
  destroyed: Vt,
  unmounted: Vt,
  activated: Vt,
  deactivated: Vt,
  errorCaptured: Vt,
  serverPrefetch: Vt,
  components: ui,
  directives: ui,
  watch: b2,
  provide: mf,
  inject: _2
};

function mf(e, t) {
  return t ? e ? function () {
    return it(Be(e) ? e.call(this, this) : e, Be(t) ? t.call(this, this) : t)
  } : t : e
}

function _2(e, t) {
  return ui($d(e), $d(t))
}

function $d(e) {
  if (we(e)) {
    const t = {};
    for (let s = 0; s < e.length; s++) t[e[s]] = e[s];
    return t
  }
  return e
}

function Vt(e, t) {
  return e ? [...new Set([].concat(e, t))] : t
}

function ui(e, t) {
  return e ? it(Object.create(null), e, t) : t
}

function gf(e, t) {
  return e ? we(e) && we(t) ? [...new Set([...e, ...t])] : it(Object.create(null), Ei(e), Ei(t ? ? {})) : t
}

function b2(e, t) {
  if (!e) return t;
  if (!t) return e;
  const s = it(Object.create(null), e);
  for (const o in t) s[o] = Vt(e[o], t[o]);
  return s
}

function Zp() {
  return {
    app: null,
    config: {
      isNativeTag: E0,
      performance: !1,
      globalProperties: {},
      optionMergeStrategies: {},
      errorHandler: void 0,
      warnHandler: void 0,
      compilerOptions: {}
    },
    mixins: [],
    components: {},
    directives: {},
    provides: Object.create(null),
    optionsCache: new WeakMap,
    propsCache: new WeakMap,
    emitsCache: new WeakMap
  }
}
let v2 = 0;

function y2(e, t) {
  return function (o, i = null) {
    Be(o) || (o = it({}, o)), i != null && !st(i) && (i = null);
    const r = Zp(),
      a = new WeakSet;
    let l = !1;
    const d = r.app = {
      _uid: v2++,
      _component: o,
      _props: i,
      _container: null,
      _context: r,
      _instance: null,
      version: bm,
      get config() {
        return r.config
      },
      set config(p) {},
      use(p, ...m) {
        return a.has(p) || (p && Be(p.install) ? (a.add(p), p.install(d, ...m)) : Be(p) && (a.add(p), p(d, ...m))), d
      },
      mixin(p) {
        return r.mixins.includes(p) || r.mixins.push(p), d
      },
      component(p, m) {
        return m ? (r.components[p] = m, d) : r.components[p]
      },
      directive(p, m) {
        return m ? (r.directives[p] = m, d) : r.directives[p]
      },
      mount(p, m, g) {
        if (!l) {
          const $ = ie(o, i);
          return $.appContext = r, g === !0 ? g = "svg" : g === !1 && (g = void 0), m && t ? t($, p) : e($, p, g), l = !0, d._container = p, p.__vue_app__ = d, sl($.component) || $.component.proxy
        }
      },
      unmount() {
        l && (e(null, d._container), delete d._container.__vue_app__)
      },
      provide(p, m) {
        return r.provides[p] = m, d
      },
      runWithContext(p) {
        const m = ko;
        ko = d;
        try {
          return p()
        } finally {
          ko = m
        }
      }
    };
    return d
  }
}
let ko = null;

function pi(e, t) {
  if (wt) {
    let s = wt.provides;
    const o = wt.parent && wt.parent.provides;
    o === s && (s = wt.provides = Object.create(o)), s[e] = t
  }
}

function Ge(e, t, s = !1) {
  const o = wt || vt;
  if (o || ko) {
    const i = o ? o.parent == null ? o.vnode.appContext && o.vnode.appContext.provides : o.parent.provides : ko._context.provides;
    if (i && e in i) return i[e];
    if (arguments.length > 1) return s && Be(t) ? t.call(o && o.proxy) : t
  }
}

function w2() {
  return !!(wt || vt || ko)
}

function $2(e, t, s, o = !1) {
  const i = {},
    r = {};
  wa(r, tl, 1), e.propsDefaults = Object.create(null), Xp(e, t, i, r);
  for (const a in e.propsOptions[0]) a in i || (i[a] = void 0);
  s ? e.props = o ? i : lu(i) : e.type.props ? e.props = i : e.props = r, e.attrs = r
}

function C2(e, t, s, o) {
  const {
    props: i,
    attrs: r,
    vnode: {
      patchFlag: a
    }
  } = e, l = Ke(i), [d] = e.propsOptions;
  let p = !1;
  if ((o || a > 0) && !(a & 16)) {
    if (a & 8) {
      const m = e.vnode.dynamicProps;
      for (let g = 0; g < m.length; g++) {
        let $ = m[g];
        if (Ga(e.emitsOptions, $)) continue;
        const C = t[$];
        if (d)
          if (Ze(r, $)) C !== r[$] && (r[$] = C, p = !0);
          else {
            const A = jt($);
            i[A] = Cd(d, l, A, C, e, !1)
          }
        else C !== r[$] && (r[$] = C, p = !0)
      }
    }
  } else {
    Xp(e, t, i, r) && (p = !0);
    let m;
    for (const g in l)(!t || !Ze(t, g) && ((m = ts(g)) === g || !Ze(t, m))) && (d ? s && (s[g] !== void 0 || s[m] !== void 0) && (i[g] = Cd(d, l, g, void 0, e, !0)) : delete i[g]);
    if (r !== l)
      for (const g in r)(!t || !Ze(t, g)) && (delete r[g], p = !0)
  }
  p && Ws(e, "set", "$attrs")
}

function Xp(e, t, s, o) {
  const [i, r] = e.propsOptions;
  let a = !1,
    l;
  if (t)
    for (let d in t) {
      if (vo(d)) continue;
      const p = t[d];
      let m;
      i && Ze(i, m = jt(d)) ? !r || !r.includes(m) ? s[m] = p : (l || (l = {}))[m] = p : Ga(e.emitsOptions, d) || (!(d in o) || p !== o[d]) && (o[d] = p, a = !0)
    }
  if (r) {
    const d = Ke(s),
      p = l || et;
    for (let m = 0; m < r.length; m++) {
      const g = r[m];
      s[g] = Cd(i, d, g, p[g], e, !Ze(p, g))
    }
  }
  return a
}

function Cd(e, t, s, o, i, r) {
  const a = e[s];
  if (a != null) {
    const l = Ze(a, "default");
    if (l && o === void 0) {
      const d = a.default;
      if (a.type !== Function && !a.skipFactory && Be(d)) {
        const {
          propsDefaults: p
        } = i;
        if (s in p) o = p[s];
        else {
          const m = Kn(i);
          o = p[s] = d.call(null, t), m()
        }
      } else o = d
    }
    a[0] && (r && !l ? o = !1 : a[1] && (o === "" || o === ts(s)) && (o = !0))
  }
  return o
}

function Qp(e, t, s = !1) {
  const o = t.propsCache,
    i = o.get(e);
  if (i) return i;
  const r = e.props,
    a = {},
    l = [];
  let d = !1;
  if (!Be(e)) {
    const m = g => {
      d = !0;
      const [$, C] = Qp(g, t, !0);
      it(a, $), C && l.push(...C)
    };
    !s && t.mixins.length && t.mixins.forEach(m), e.extends && m(e.extends), e.mixins && e.mixins.forEach(m)
  }
  if (!r && !d) return st(e) && o.set(e, _o), _o;
  if (we(r))
    for (let m = 0; m < r.length; m++) {
      const g = jt(r[m]);
      _f(g) && (a[g] = et)
    } else if (r)
      for (const m in r) {
        const g = jt(m);
        if (_f(g)) {
          const $ = r[m],
            C = a[g] = we($) || Be($) ? {
              type: $
            } : it({}, $);
          if (C) {
            const A = yf(Boolean, C.type),
              O = yf(String, C.type);
            C[0] = A > -1, C[1] = O < 0 || A < O, (A > -1 || Ze(C, "default")) && l.push(g)
          }
        }
      }
  const p = [a, l];
  return st(e) && o.set(e, p), p
}

function _f(e) {
  return e[0] !== "$" && !vo(e)
}

function bf(e) {
  return e === null ? "null" : typeof e == "function" ? e.name || "" : typeof e == "object" && e.constructor && e.constructor.name || ""
}

function vf(e, t) {
  return bf(e) === bf(t)
}

function yf(e, t) {
  return we(t) ? t.findIndex(s => vf(s, e)) : Be(t) && vf(t, e) ? 0 : -1
}
const em = e => e[0] === "_" || e === "$stable",
  Cu = e => we(e) ? e.map(es) : [es(e)],
  k2 = (e, t, s) => {
    if (t._n) return t;
    const o = Ue((...i) => Cu(t(...i)), s);
    return o._c = !1, o
  },
  tm = (e, t, s) => {
    const o = e._ctx;
    for (const i in e) {
      if (em(i)) continue;
      const r = e[i];
      if (Be(r)) t[i] = k2(i, r, o);
      else if (r != null) {
        const a = Cu(r);
        t[i] = () => a
      }
    }
  },
  sm = (e, t) => {
    const s = Cu(t);
    e.slots.default = () => s
  },
  A2 = (e, t) => {
    if (e.vnode.shapeFlag & 32) {
      const s = t._;
      s ? (e.slots = Ke(t), wa(t, "_", s)) : tm(t, e.slots = {})
    } else e.slots = {}, t && sm(e, t);
    wa(e.slots, tl, 1)
  },
  x2 = (e, t, s) => {
    const {
      vnode: o,
      slots: i
    } = e;
    let r = !0,
      a = et;
    if (o.shapeFlag & 32) {
      const l = t._;
      l ? s && l === 1 ? r = !1 : (it(i, t), !s && l === 1 && delete i._) : (r = !t.$stable, tm(t, i)), a = t
    } else t && (sm(e, t), a = {
      default: 1
    });
    if (r)
      for (const l in i) !em(l) && a[l] == null && delete i[l]
  };

function Sa(e, t, s, o, i = !1) {
  if (we(e)) {
    e.forEach(($, C) => Sa($, t && (we(t) ? t[C] : t), s, o, i));
    return
  }
  if (Vn(o) && !i) return;
  const r = o.shapeFlag & 4 ? sl(o.component) || o.component.proxy : o.el,
    a = i ? null : r,
    {
      i: l,
      r: d
    } = e,
    p = t && t.r,
    m = l.refs === et ? l.refs = {} : l.refs,
    g = l.setupState;
  if (p != null && p !== d && (rt(p) ? (m[p] = null, Ze(g, p) && (g[p] = null)) : Tt(p) && (p.value = null)), Be(d)) zs(d, l, 12, [a, m]);
  else {
    const $ = rt(d),
      C = Tt(d);
    if ($ || C) {
      const A = () => {
        if (e.f) {
          const O = $ ? Ze(g, d) ? g[d] : m[d] : d.value;
          i ? we(O) && tu(O, r) : we(O) ? O.includes(r) || O.push(r) : $ ? (m[d] = [r], Ze(g, d) && (g[d] = m[d])) : (d.value = [r], e.k && (m[e.k] = d.value))
        } else $ ? (m[d] = a, Ze(g, d) && (g[d] = a)) : C && (d.value = a, e.k && (m[e.k] = a))
      };
      a ? (A.id = -1, Et(A, s)) : A()
    }
  }
}
let dn = !1;
const S2 = e => e.namespaceURI.includes("svg") && e.tagName !== "foreignObject",
  E2 = e => e.namespaceURI.includes("MathML"),
  oa = e => {
    if (S2(e)) return "svg";
    if (E2(e)) return "mathml"
  },
  ia = e => e.nodeType === 8;

function P2(e) {
  const {
    mt: t,
    p: s,
    o: {
      patchProp: o,
      createText: i,
      nextSibling: r,
      parentNode: a,
      remove: l,
      insert: d,
      createComment: p
    }
  } = e, m = (P, I) => {
    if (!I.hasChildNodes()) {
      s(null, P, I), Aa(), I._vnode = P;
      return
    }
    dn = !1, g(I.firstChild, P, null, null, null), Aa(), I._vnode = P, dn && console.error("Hydration completed but contains mismatches.")
  }, g = (P, I, R, F, j, q = !1) => {
    const ne = ia(P) && P.data === "[",
      ee = () => O(P, I, R, F, j, ne),
      {
        type: ge,
        ref: T,
        shapeFlag: re,
        patchFlag: Le
      } = I;
    let We = P.nodeType;
    I.el = P, Le === -2 && (q = !1, I.dynamicChildren = null);
    let fe = null;
    switch (ge) {
      case Wn:
        We !== 3 ? I.children === "" ? (d(I.el = i(""), a(P), P), fe = P) : fe = ee() : (P.data !== I.children && (dn = !0, P.data = I.children), fe = r(P));
        break;
      case Bt:
        B(P) ? (fe = r(P), M(I.el = P.content.firstChild, P, R)) : We !== 8 || ne ? fe = ee() : fe = r(P);
        break;
      case jn:
        if (ne && (P = r(P), We = P.nodeType), We === 1 || We === 3) {
          fe = P;
          const ue = !I.children.length;
          for (let te = 0; te < I.staticCount; te++) ue && (I.children += fe.nodeType === 1 ? fe.outerHTML : fe.data), te === I.staticCount - 1 && (I.anchor = fe), fe = r(fe);
          return ne ? r(fe) : fe
        } else ee();
        break;
      case Te:
        ne ? fe = A(P, I, R, F, j, q) : fe = ee();
        break;
      default:
        if (re & 1)(We !== 1 || I.type.toLowerCase() !== P.tagName.toLowerCase()) && !B(P) ? fe = ee() : fe = $(P, I, R, F, j, q);
        else if (re & 6) {
          I.slotScopeIds = j;
          const ue = a(P);
          if (ne ? fe = V(P) : ia(P) && P.data === "teleport start" ? fe = V(P, P.data, "teleport end") : fe = r(P), t(I, ue, null, R, F, oa(ue), q), Vn(I)) {
            let te;
            ne ? (te = ie(Te), te.anchor = fe ? fe.previousSibling : ue.lastChild) : te = P.nodeType === 3 ? J("") : ie("div"), te.el = P, I.component.subTree = te
          }
        } else re & 64 ? We !== 8 ? fe = ee() : fe = I.type.hydrate(P, I, R, F, j, q, e, C) : re & 128 && (fe = I.type.hydrate(P, I, R, F, oa(a(P)), j, q, e, g))
    }
    return T != null && Sa(T, null, F, I), fe
  }, $ = (P, I, R, F, j, q) => {
    q = q || !!I.dynamicChildren;
    const {
      type: ne,
      props: ee,
      patchFlag: ge,
      shapeFlag: T,
      dirs: re,
      transition: Le
    } = I, We = ne === "input" || ne === "option";
    if (We || ge !== -1) {
      re && Ss(I, null, R, "created");
      let fe = !1;
      if (B(P)) {
        fe = rm(F, Le) && R && R.vnode.props && R.vnode.props.appear;
        const te = P.content.firstChild;
        fe && Le.beforeEnter(te), M(te, P, R), I.el = P = te
      }
      if (T & 16 && !(ee && (ee.innerHTML || ee.textContent))) {
        let te = C(P.firstChild, I, P, R, F, j, q);
        for (; te;) {
          dn = !0;
          const $t = te;
          te = te.nextSibling, l($t)
        }
      } else T & 8 && P.textContent !== I.children && (dn = !0, P.textContent = I.children);
      if (ee)
        if (We || !q || ge & 48)
          for (const te in ee)(We && (te.endsWith("value") || te === "indeterminate") || Ni(te) && !vo(te) || te[0] === ".") && o(P, te, null, ee[te], void 0, void 0, R);
        else ee.onClick && o(P, "onClick", null, ee.onClick, void 0, void 0, R);
      let ue;
      (ue = ee && ee.onVnodeBeforeMount) && zt(ue, R, I), re && Ss(I, null, R, "beforeMount"), ((ue = ee && ee.onVnodeMounted) || re || fe) && Tp(() => {
        ue && zt(ue, R, I), fe && Le.enter(P), re && Ss(I, null, R, "mounted")
      }, F)
    }
    return P.nextSibling
  }, C = (P, I, R, F, j, q, ne) => {
    ne = ne || !!I.dynamicChildren;
    const ee = I.children,
      ge = ee.length;
    for (let T = 0; T < ge; T++) {
      const re = ne ? ee[T] : ee[T] = es(ee[T]);
      if (P) P = g(P, re, F, j, q, ne);
      else {
        if (re.type === Wn && !re.children) continue;
        dn = !0, s(null, re, R, null, F, j, oa(R), q)
      }
    }
    return P
  }, A = (P, I, R, F, j, q) => {
    const {
      slotScopeIds: ne
    } = I;
    ne && (j = j ? j.concat(ne) : ne);
    const ee = a(P),
      ge = C(r(P), I, ee, R, F, j, q);
    return ge && ia(ge) && ge.data === "]" ? r(I.anchor = ge) : (dn = !0, d(I.anchor = p("]"), ee, ge), ge)
  }, O = (P, I, R, F, j, q) => {
    if (dn = !0, I.el = null, q) {
      const ge = V(P);
      for (;;) {
        const T = r(P);
        if (T && T !== ge) l(T);
        else break
      }
    }
    const ne = r(P),
      ee = a(P);
    return l(P), s(null, I, ee, ne, R, F, oa(ee), j), ne
  }, V = (P, I = "[", R = "]") => {
    let F = 0;
    for (; P;)
      if (P = r(P), P && ia(P) && (P.data === I && F++, P.data === R)) {
        if (F === 0) return r(P);
        F--
      } return P
  }, M = (P, I, R) => {
    const F = I.parentNode;
    F && F.replaceChild(P, I);
    let j = R;
    for (; j;) j.vnode.el === I && (j.vnode.el = j.subTree.el = P), j = j.parent
  }, B = P => P.nodeType === 1 && P.tagName.toLowerCase() === "template";
  return [m, g]
}
const Et = Tp;

function nm(e) {
  return im(e)
}

function om(e) {
  return im(e, P2)
}

function im(e, t) {
  const s = Qh();
  s.__VUE__ = !0;
  const {
    insert: o,
    remove: i,
    patchProp: r,
    createElement: a,
    createText: l,
    createComment: d,
    setText: p,
    setElementText: m,
    parentNode: g,
    nextSibling: $,
    setScopeId: C = Kt,
    insertStaticContent: A
  } = e, O = (k, x, N, Y = null, W = null, oe = null, se = void 0, G = null, le = !!x.dynamicChildren) => {
    if (k === x) return;
    k && !bs(k, x) && (Y = K(k), ht(k, W, oe, !0), k = null), x.patchFlag === -2 && (le = !1, x.dynamicChildren = null);
    const {
      type: Q,
      ref: pe,
      shapeFlag: xe
    } = x;
    switch (Q) {
      case Wn:
        V(k, x, N, Y);
        break;
      case Bt:
        M(k, x, N, Y);
        break;
      case jn:
        k == null && B(x, N, Y, se);
        break;
      case Te:
        T(k, x, N, Y, W, oe, se, G, le);
        break;
      default:
        xe & 1 ? R(k, x, N, Y, W, oe, se, G, le) : xe & 6 ? re(k, x, N, Y, W, oe, se, G, le) : (xe & 64 || xe & 128) && Q.process(k, x, N, Y, W, oe, se, G, le, ce)
    }
    pe != null && W && Sa(pe, k && k.ref, oe, x || k, !x)
  }, V = (k, x, N, Y) => {
    if (k == null) o(x.el = l(x.children), N, Y);
    else {
      const W = x.el = k.el;
      x.children !== k.children && p(W, x.children)
    }
  }, M = (k, x, N, Y) => {
    k == null ? o(x.el = d(x.children || ""), N, Y) : x.el = k.el
  }, B = (k, x, N, Y) => {
    [k.el, k.anchor] = A(k.children, x, N, Y, k.el, k.anchor)
  }, P = ({
    el: k,
    anchor: x
  }, N, Y) => {
    let W;
    for (; k && k !== x;) W = $(k), o(k, N, Y), k = W;
    o(x, N, Y)
  }, I = ({
    el: k,
    anchor: x
  }) => {
    let N;
    for (; k && k !== x;) N = $(k), i(k), k = N;
    i(x)
  }, R = (k, x, N, Y, W, oe, se, G, le) => {
    x.type === "svg" ? se = "svg" : x.type === "math" && (se = "mathml"), k == null ? F(x, N, Y, W, oe, se, G, le) : ne(k, x, W, oe, se, G, le)
  }, F = (k, x, N, Y, W, oe, se, G) => {
    let le, Q;
    const {
      props: pe,
      shapeFlag: xe,
      transition: Ce,
      dirs: de
    } = k;
    if (le = k.el = a(k.type, oe, pe && pe.is, pe), xe & 8 ? m(le, k.children) : xe & 16 && q(k.children, le, null, Y, W, Yc(k, oe), se, G), de && Ss(k, null, Y, "created"), j(le, k, k.scopeId, se, Y), pe) {
      for (const Oe in pe) Oe !== "value" && !vo(Oe) && r(le, Oe, null, pe[Oe], oe, k.children, Y, W, ot);
      "value" in pe && r(le, "value", null, pe.value, oe), (Q = pe.onVnodeBeforeMount) && zt(Q, Y, k)
    }
    de && Ss(k, null, Y, "beforeMount");
    const Pe = rm(W, Ce);
    Pe && Ce.beforeEnter(le), o(le, x, N), ((Q = pe && pe.onVnodeMounted) || Pe || de) && Et(() => {
      Q && zt(Q, Y, k), Pe && Ce.enter(le), de && Ss(k, null, Y, "mounted")
    }, W)
  }, j = (k, x, N, Y, W) => {
    if (N && C(k, N), Y)
      for (let oe = 0; oe < Y.length; oe++) C(k, Y[oe]);
    if (W) {
      let oe = W.subTree;
      if (x === oe) {
        const se = W.vnode;
        j(k, se, se.scopeId, se.slotScopeIds, W.parent)
      }
    }
  }, q = (k, x, N, Y, W, oe, se, G, le = 0) => {
    for (let Q = le; Q < k.length; Q++) {
      const pe = k[Q] = G ? _n(k[Q]) : es(k[Q]);
      O(null, pe, x, N, Y, W, oe, se, G)
    }
  }, ne = (k, x, N, Y, W, oe, se) => {
    const G = x.el = k.el;
    let {
      patchFlag: le,
      dynamicChildren: Q,
      dirs: pe
    } = x;
    le |= k.patchFlag & 16;
    const xe = k.props || et,
      Ce = x.props || et;
    let de;
    if (N && Bn(N, !1), (de = Ce.onVnodeBeforeUpdate) && zt(de, N, x, k), pe && Ss(x, k, N, "beforeUpdate"), N && Bn(N, !0), Q ? ee(k.dynamicChildren, Q, G, N, Y, Yc(x, W), oe) : se || te(k, x, G, null, N, Y, Yc(x, W), oe, !1), le > 0) {
      if (le & 16) ge(G, x, xe, Ce, N, Y, W);
      else if (le & 2 && xe.class !== Ce.class && r(G, "class", null, Ce.class, W), le & 4 && r(G, "style", xe.style, Ce.style, W), le & 8) {
        const Pe = x.dynamicProps;
        for (let Oe = 0; Oe < Pe.length; Oe++) {
          const He = Pe[Oe],
            Me = xe[He],
            Xe = Ce[He];
          (Xe !== Me || He === "value") && r(G, He, Me, Xe, W, k.children, N, Y, ot)
        }
      }
      le & 1 && k.children !== x.children && m(G, x.children)
    } else !se && Q == null && ge(G, x, xe, Ce, N, Y, W);
    ((de = Ce.onVnodeUpdated) || pe) && Et(() => {
      de && zt(de, N, x, k), pe && Ss(x, k, N, "updated")
    }, Y)
  }, ee = (k, x, N, Y, W, oe, se) => {
    for (let G = 0; G < x.length; G++) {
      const le = k[G],
        Q = x[G],
        pe = le.el && (le.type === Te || !bs(le, Q) || le.shapeFlag & 70) ? g(le.el) : N;
      O(le, Q, pe, null, Y, W, oe, se, !0)
    }
  }, ge = (k, x, N, Y, W, oe, se) => {
    if (N !== Y) {
      if (N !== et)
        for (const G in N) !vo(G) && !(G in Y) && r(k, G, N[G], null, se, x.children, W, oe, ot);
      for (const G in Y) {
        if (vo(G)) continue;
        const le = Y[G],
          Q = N[G];
        le !== Q && G !== "value" && r(k, G, Q, le, se, x.children, W, oe, ot)
      }
      "value" in Y && r(k, "value", N.value, Y.value, se)
    }
  }, T = (k, x, N, Y, W, oe, se, G, le) => {
    const Q = x.el = k ? k.el : l(""),
      pe = x.anchor = k ? k.anchor : l("");
    let {
      patchFlag: xe,
      dynamicChildren: Ce,
      slotScopeIds: de
    } = x;
    de && (G = G ? G.concat(de) : de), k == null ? (o(Q, N, Y), o(pe, N, Y), q(x.children || [], N, pe, W, oe, se, G, le)) : xe > 0 && xe & 64 && Ce && k.dynamicChildren ? (ee(k.dynamicChildren, Ce, N, W, oe, se, G), (x.key != null || W && x === W.subTree) && ku(k, x, !0)) : te(k, x, N, pe, W, oe, se, G, le)
  }, re = (k, x, N, Y, W, oe, se, G, le) => {
    x.slotScopeIds = G, k == null ? x.shapeFlag & 512 ? W.ctx.activate(x, N, Y, se, le) : Le(x, N, Y, W, oe, se, le) : We(k, x, le)
  }, Le = (k, x, N, Y, W, oe, se) => {
    const G = k.component = fm(k, Y, W);
    if (ji(k) && (G.ctx.renderer = ce), pm(G), G.asyncDep) {
      if (W && W.registerDep(G, fe), !k.el) {
        const le = G.subTree = ie(Bt);
        M(null, le, x, N)
      }
    } else fe(G, k, x, N, W, oe, se)
  }, We = (k, x, N) => {
    const Y = x.component = k.component;
    if (Rg(k, x, N))
      if (Y.asyncDep && !Y.asyncResolved) {
        ue(Y, x, N);
        return
      } else Y.next = x, Tg(Y.update), Y.effect.dirty = !0, Y.update();
    else x.el = k.el, Y.vnode = x
  }, fe = (k, x, N, Y, W, oe, se) => {
    const G = () => {
        if (k.isMounted) {
          let {
            next: pe,
            bu: xe,
            u: Ce,
            parent: de,
            vnode: Pe
          } = k; {
            const Ot = am(k);
            if (Ot) {
              pe && (pe.el = Pe.el, ue(k, pe, se)), Ot.asyncDep.then(() => {
                k.isUnmounted || G()
              });
              return
            }
          }
          let Oe = pe,
            He;
          Bn(k, !1), pe ? (pe.el = Pe.el, ue(k, pe, se)) : pe = Pe, xe && yo(xe), (He = pe.props && pe.props.onVnodeBeforeUpdate) && zt(He, de, pe, Pe), Bn(k, !0);
          const Me = fa(k),
            Xe = k.subTree;
          k.subTree = Me, O(Xe, Me, g(Xe.el), K(Xe), k, W, oe), pe.el = Me.el, Oe === null && mu(k, Me.el), Ce && Et(Ce, W), (He = pe.props && pe.props.onVnodeUpdated) && Et(() => zt(He, de, pe, Pe), W)
        } else {
          let pe;
          const {
            el: xe,
            props: Ce
          } = x, {
            bm: de,
            m: Pe,
            parent: Oe
          } = k, He = Vn(x);
          if (Bn(k, !1), de && yo(de), !He && (pe = Ce && Ce.onVnodeBeforeMount) && zt(pe, Oe, x), Bn(k, !0), xe && Ne) {
            const Me = () => {
              k.subTree = fa(k), Ne(xe, k.subTree, k, W, null)
            };
            He ? x.type.__asyncLoader().then(() => !k.isUnmounted && Me()) : Me()
          } else {
            const Me = k.subTree = fa(k);
            O(null, Me, N, Y, k, W, oe), x.el = Me.el
          }
          if (Pe && Et(Pe, W), !He && (pe = Ce && Ce.onVnodeMounted)) {
            const Me = x;
            Et(() => zt(pe, Oe, Me), W)
          }(x.shapeFlag & 256 || Oe && Vn(Oe.vnode) && Oe.vnode.shapeFlag & 256) && k.a && Et(k.a, W), k.isMounted = !0, x = N = Y = null
        }
      },
      le = k.effect = new Ao(G, Kt, () => Ka(Q), k.scope),
      Q = k.update = () => {
        le.dirty && le.run()
      };
    Q.id = k.uid, Bn(k, !0), Q()
  }, ue = (k, x, N) => {
    x.component = k;
    const Y = k.vnode.props;
    k.vnode = x, k.next = null, C2(k, x.props, Y, N), x2(k, x.children, N), Yn(), cf(k), Jn()
  }, te = (k, x, N, Y, W, oe, se, G, le = !1) => {
    const Q = k && k.children,
      pe = k ? k.shapeFlag : 0,
      xe = x.children,
      {
        patchFlag: Ce,
        shapeFlag: de
      } = x;
    if (Ce > 0) {
      if (Ce & 128) {
        Lt(Q, xe, N, Y, W, oe, se, G, le);
        return
      } else if (Ce & 256) {
        $t(Q, xe, N, Y, W, oe, se, G, le);
        return
      }
    }
    de & 8 ? (pe & 16 && ot(Q, W, oe), xe !== Q && m(N, xe)) : pe & 16 ? de & 16 ? Lt(Q, xe, N, Y, W, oe, se, G, le) : ot(Q, W, oe, !0) : (pe & 8 && m(N, ""), de & 16 && q(xe, N, Y, W, oe, se, G, le))
  }, $t = (k, x, N, Y, W, oe, se, G, le) => {
    k = k || _o, x = x || _o;
    const Q = k.length,
      pe = x.length,
      xe = Math.min(Q, pe);
    let Ce;
    for (Ce = 0; Ce < xe; Ce++) {
      const de = x[Ce] = le ? _n(x[Ce]) : es(x[Ce]);
      O(k[Ce], de, N, null, W, oe, se, G, le)
    }
    Q > pe ? ot(k, W, oe, !0, !1, xe) : q(x, N, Y, W, oe, se, G, le, xe)
  }, Lt = (k, x, N, Y, W, oe, se, G, le) => {
    let Q = 0;
    const pe = x.length;
    let xe = k.length - 1,
      Ce = pe - 1;
    for (; Q <= xe && Q <= Ce;) {
      const de = k[Q],
        Pe = x[Q] = le ? _n(x[Q]) : es(x[Q]);
      if (bs(de, Pe)) O(de, Pe, N, null, W, oe, se, G, le);
      else break;
      Q++
    }
    for (; Q <= xe && Q <= Ce;) {
      const de = k[xe],
        Pe = x[Ce] = le ? _n(x[Ce]) : es(x[Ce]);
      if (bs(de, Pe)) O(de, Pe, N, null, W, oe, se, G, le);
      else break;
      xe--, Ce--
    }
    if (Q > xe) {
      if (Q <= Ce) {
        const de = Ce + 1,
          Pe = de < pe ? x[de].el : Y;
        for (; Q <= Ce;) O(null, x[Q] = le ? _n(x[Q]) : es(x[Q]), N, Pe, W, oe, se, G, le), Q++
      }
    } else if (Q > Ce)
      for (; Q <= xe;) ht(k[Q], W, oe, !0), Q++;
    else {
      const de = Q,
        Pe = Q,
        Oe = new Map;
      for (Q = Pe; Q <= Ce; Q++) {
        const _t = x[Q] = le ? _n(x[Q]) : es(x[Q]);
        _t.key != null && Oe.set(_t.key, Q)
      }
      let He, Me = 0;
      const Xe = Ce - Pe + 1;
      let Ot = !1,
        Xt = 0;
      const mt = new Array(Xe);
      for (Q = 0; Q < Xe; Q++) mt[Q] = 0;
      for (Q = de; Q <= xe; Q++) {
        const _t = k[Q];
        if (Me >= Xe) {
          ht(_t, W, oe, !0);
          continue
        }
        let Rt;
        if (_t.key != null) Rt = Oe.get(_t.key);
        else
          for (He = Pe; He <= Ce; He++)
            if (mt[He - Pe] === 0 && bs(_t, x[He])) {
              Rt = He;
              break
            } Rt === void 0 ? ht(_t, W, oe, !0) : (mt[Rt - Pe] = Q + 1, Rt >= Xt ? Xt = Rt : Ot = !0, O(_t, x[Rt], N, null, W, oe, se, G, le), Me++)
      }
      const Bs = Ot ? T2(mt) : _o;
      for (He = Bs.length - 1, Q = Xe - 1; Q >= 0; Q--) {
        const _t = Pe + Q,
          Rt = x[_t],
          Cs = _t + 1 < pe ? x[_t + 1].el : Y;
        mt[Q] === 0 ? O(null, Rt, N, Cs, W, oe, se, G, le) : Ot && (He < 0 || Q !== Bs[He] ? xt(Rt, N, Cs, 2) : He--)
      }
    }
  }, xt = (k, x, N, Y, W = null) => {
    const {
      el: oe,
      type: se,
      transition: G,
      children: le,
      shapeFlag: Q
    } = k;
    if (Q & 6) {
      xt(k.component.subTree, x, N, Y);
      return
    }
    if (Q & 128) {
      k.suspense.move(x, N, Y);
      return
    }
    if (Q & 64) {
      se.move(k, x, N, ce);
      return
    }
    if (se === Te) {
      o(oe, x, N);
      for (let xe = 0; xe < le.length; xe++) xt(le[xe], x, N, Y);
      o(k.anchor, x, N);
      return
    }
    if (se === jn) {
      P(k, x, N);
      return
    }
    if (Y !== 2 && Q & 1 && G)
      if (Y === 0) G.beforeEnter(oe), o(oe, x, N), Et(() => G.enter(oe), W);
      else {
        const {
          leave: xe,
          delayLeave: Ce,
          afterLeave: de
        } = G, Pe = () => o(oe, x, N), Oe = () => {
          xe(oe, () => {
            Pe(), de && de()
          })
        };
        Ce ? Ce(oe, Pe, Oe) : Oe()
      }
    else o(oe, x, N)
  }, ht = (k, x, N, Y = !1, W = !1) => {
    const {
      type: oe,
      props: se,
      ref: G,
      children: le,
      dynamicChildren: Q,
      shapeFlag: pe,
      patchFlag: xe,
      dirs: Ce
    } = k;
    if (G != null && Sa(G, null, N, k, !0), pe & 256) {
      x.ctx.deactivate(k);
      return
    }
    const de = pe & 1 && Ce,
      Pe = !Vn(k);
    let Oe;
    if (Pe && (Oe = se && se.onVnodeBeforeUnmount) && zt(Oe, x, k), pe & 6) lt(k.component, N, Y);
    else {
      if (pe & 128) {
        k.suspense.unmount(N, Y);
        return
      }
      de && Ss(k, null, x, "beforeUnmount"), pe & 64 ? k.type.remove(k, x, N, W, ce, Y) : Q && (oe !== Te || xe > 0 && xe & 64) ? ot(Q, x, N, !1, !0) : (oe === Te && xe & 384 || !W && pe & 16) && ot(le, x, N), Y && U(k)
    }(Pe && (Oe = se && se.onVnodeUnmounted) || de) && Et(() => {
      Oe && zt(Oe, x, k), de && Ss(k, null, x, "unmounted")
    }, N)
  }, U = k => {
    const {
      type: x,
      el: N,
      anchor: Y,
      transition: W
    } = k;
    if (x === Te) {
      pt(N, Y);
      return
    }
    if (x === jn) {
      I(k);
      return
    }
    const oe = () => {
      i(N), W && !W.persisted && W.afterLeave && W.afterLeave()
    };
    if (k.shapeFlag & 1 && W && !W.persisted) {
      const {
        leave: se,
        delayLeave: G
      } = W, le = () => se(N, oe);
      G ? G(k.el, oe, le) : le()
    } else oe()
  }, pt = (k, x) => {
    let N;
    for (; k !== x;) N = $(k), i(k), k = N;
    i(x)
  }, lt = (k, x, N) => {
    const {
      bum: Y,
      scope: W,
      update: oe,
      subTree: se,
      um: G
    } = k;
    Y && yo(Y), W.stop(), oe && (oe.active = !1, ht(se, k, x, N)), G && Et(G, x), Et(() => {
      k.isUnmounted = !0
    }, x), x && x.pendingBranch && !x.isUnmounted && k.asyncDep && !k.asyncResolved && k.suspenseId === x.pendingId && (x.deps--, x.deps === 0 && x.resolve())
  }, ot = (k, x, N, Y = !1, W = !1, oe = 0) => {
    for (let se = oe; se < k.length; se++) ht(k[se], x, N, Y, W)
  }, K = k => k.shapeFlag & 6 ? K(k.component.subTree) : k.shapeFlag & 128 ? k.suspense.next() : $(k.anchor || k.el);
  let ae = !1;
  const H = (k, x, N) => {
      k == null ? x._vnode && ht(x._vnode, null, null, !0) : O(x._vnode || null, k, x, null, null, null, N), ae || (ae = !0, cf(), Aa(), ae = !1), x._vnode = k
    },
    ce = {
      p: O,
      um: ht,
      m: xt,
      r: U,
      mt: Le,
      mc: q,
      pc: te,
      pbc: ee,
      n: K,
      o: e
    };
  let De, Ne;
  return t && ([De, Ne] = t(ce)), {
    render: H,
    hydrate: De,
    createApp: y2(H, De)
  }
}

function Yc({
  type: e,
  props: t
}, s) {
  return s === "svg" && e === "foreignObject" || s === "mathml" && e === "annotation-xml" && t && t.encoding && t.encoding.includes("html") ? void 0 : s
}

function Bn({
  effect: e,
  update: t
}, s) {
  e.allowRecurse = t.allowRecurse = s
}

function rm(e, t) {
  return (!e || e && !e.pendingBranch) && t && !t.persisted
}

function ku(e, t, s = !1) {
  const o = e.children,
    i = t.children;
  if (we(o) && we(i))
    for (let r = 0; r < o.length; r++) {
      const a = o[r];
      let l = i[r];
      l.shapeFlag & 1 && !l.dynamicChildren && ((l.patchFlag <= 0 || l.patchFlag === 32) && (l = i[r] = _n(i[r]), l.el = a.el), s || ku(a, l)), l.type === Wn && (l.el = a.el)
    }
}

function T2(e) {
  const t = e.slice(),
    s = [0];
  let o, i, r, a, l;
  const d = e.length;
  for (o = 0; o < d; o++) {
    const p = e[o];
    if (p !== 0) {
      if (i = s[s.length - 1], e[i] < p) {
        t[o] = i, s.push(o);
        continue
      }
      for (r = 0, a = s.length - 1; r < a;) l = r + a >> 1, e[s[l]] < p ? r = l + 1 : a = l;
      p < e[s[r]] && (r > 0 && (t[o] = s[r - 1]), s[r] = o)
    }
  }
  for (r = s.length, a = s[r - 1]; r-- > 0;) s[r] = a, a = t[a];
  return s
}

function am(e) {
  const t = e.subTree.component;
  if (t) return t.asyncDep && !t.asyncResolved ? t : am(t)
}
const L2 = e => e.__isTeleport,
  mi = e => e && (e.disabled || e.disabled === ""),
  wf = e => typeof SVGElement < "u" && e instanceof SVGElement,
  $f = e => typeof MathMLElement == "function" && e instanceof MathMLElement,
  kd = (e, t) => {
    const s = e && e.to;
    return rt(s) ? t ? t(s) : null : s
  },
  O2 = {
    name: "Teleport",
    __isTeleport: !0,
    process(e, t, s, o, i, r, a, l, d, p) {
      const {
        mc: m,
        pc: g,
        pbc: $,
        o: {
          insert: C,
          querySelector: A,
          createText: O,
          createComment: V
        }
      } = p, M = mi(t.props);
      let {
        shapeFlag: B,
        children: P,
        dynamicChildren: I
      } = t;
      if (e == null) {
        const R = t.el = O(""),
          F = t.anchor = O("");
        C(R, s, o), C(F, s, o);
        const j = t.target = kd(t.props, A),
          q = t.targetAnchor = O("");
        j && (C(q, j), a === "svg" || wf(j) ? a = "svg" : (a === "mathml" || $f(j)) && (a = "mathml"));
        const ne = (ee, ge) => {
          B & 16 && m(P, ee, ge, i, r, a, l, d)
        };
        M ? ne(s, F) : j && ne(j, q)
      } else {
        t.el = e.el;
        const R = t.anchor = e.anchor,
          F = t.target = e.target,
          j = t.targetAnchor = e.targetAnchor,
          q = mi(e.props),
          ne = q ? s : F,
          ee = q ? R : j;
        if (a === "svg" || wf(F) ? a = "svg" : (a === "mathml" || $f(F)) && (a = "mathml"), I ? ($(e.dynamicChildren, I, ne, i, r, a, l), ku(e, t, !0)) : d || g(e, t, ne, ee, i, r, a, l, !1), M) q ? t.props && e.props && t.props.to !== e.props.to && (t.props.to = e.props.to) : ra(t, s, R, p, 1);
        else if ((t.props && t.props.to) !== (e.props && e.props.to)) {
          const ge = t.target = kd(t.props, A);
          ge && ra(t, ge, null, p, 0)
        } else q && ra(t, F, j, p, 1)
      }
      lm(t)
    },
    remove(e, t, s, o, {
      um: i,
      o: {
        remove: r
      }
    }, a) {
      const {
        shapeFlag: l,
        children: d,
        anchor: p,
        targetAnchor: m,
        target: g,
        props: $
      } = e;
      if (g && r(m), a && r(p), l & 16) {
        const C = a || !mi($);
        for (let A = 0; A < d.length; A++) {
          const O = d[A];
          i(O, t, s, C, !!O.dynamicChildren)
        }
      }
    },
    move: ra,
    hydrate: I2
  };

function ra(e, t, s, {
  o: {
    insert: o
  },
  m: i
}, r = 2) {
  r === 0 && o(e.targetAnchor, t, s);
  const {
    el: a,
    anchor: l,
    shapeFlag: d,
    children: p,
    props: m
  } = e, g = r === 2;
  if (g && o(a, t, s), (!g || mi(m)) && d & 16)
    for (let $ = 0; $ < p.length; $++) i(p[$], t, s, 2);
  g && o(l, t, s)
}

function I2(e, t, s, o, i, r, {
  o: {
    nextSibling: a,
    parentNode: l,
    querySelector: d
  }
}, p) {
  const m = t.target = kd(t.props, d);
  if (m) {
    const g = m._lpa || m.firstChild;
    if (t.shapeFlag & 16)
      if (mi(t.props)) t.anchor = p(a(e), t, l(e), s, o, i, r), t.targetAnchor = g;
      else {
        t.anchor = a(e);
        let $ = g;
        for (; $;)
          if ($ = a($), $ && $.nodeType === 8 && $.data === "teleport anchor") {
            t.targetAnchor = $, m._lpa = t.targetAnchor && a(t.targetAnchor);
            break
          } p(g, t, m, s, o, i, r)
      } lm(t)
  }
  return t.anchor && a(t.anchor)
}
const B2 = O2;

function lm(e) {
  const t = e.ctx;
  if (t && t.ut) {
    let s = e.children[0].el;
    for (; s && s !== e.targetAnchor;) s.nodeType === 1 && s.setAttribute("data-v-owner", t.uid), s = s.nextSibling;
    t.ut()
  }
}
const Te = Symbol.for("v-fgt"),
  Wn = Symbol.for("v-txt"),
  Bt = Symbol.for("v-cmt"),
  jn = Symbol.for("v-stc"),
  gi = [];
let Gt = null;

function _(e = !1) {
  gi.push(Gt = e ? null : [])
}

function cm() {
  gi.pop(), Gt = gi[gi.length - 1] || null
}
let zn = 1;

function Ad(e) {
  zn += e
}

function dm(e) {
  return e.dynamicChildren = zn > 0 ? Gt || _o : null, cm(), zn > 0 && Gt && Gt.push(e), e
}

function w(e, t, s, o, i, r) {
  return dm(n(e, t, s, o, i, r, !0))
}

function ve(e, t, s, o, i) {
  return dm(ie(e, t, s, o, i, !0))
}

function kn(e) {
  return e ? e.__v_isVNode === !0 : !1
}

function bs(e, t) {
  return e.type === t.type && e.key === t.key
}

function D2(e) {}
const tl = "__vInternal",
  um = ({
    key: e
  }) => e ? ? null,
  pa = ({
    ref: e,
    ref_key: t,
    ref_for: s
  }) => (typeof e == "number" && (e = "" + e), e != null ? rt(e) || Tt(e) || Be(e) ? {
    i: vt,
    r: e,
    k: t,
    f: !!s
  } : e : null);

function n(e, t = null, s = null, o = 0, i = null, r = e === Te ? 0 : 1, a = !1, l = !1) {
  const d = {
    __v_isVNode: !0,
    __v_skip: !0,
    type: e,
    props: t,
    key: t && um(t),
    ref: t && pa(t),
    scopeId: Ya,
    slotScopeIds: null,
    children: s,
    component: null,
    suspense: null,
    ssContent: null,
    ssFallback: null,
    dirs: null,
    transition: null,
    el: null,
    anchor: null,
    target: null,
    targetAnchor: null,
    staticCount: 0,
    shapeFlag: r,
    patchFlag: o,
    dynamicProps: i,
    dynamicChildren: null,
    appContext: null,
    ctx: vt
  };
  return l ? (Au(d, s), r & 128 && e.normalize(d)) : s && (d.shapeFlag |= rt(s) ? 8 : 16), zn > 0 && !a && Gt && (d.patchFlag > 0 || r & 6) && d.patchFlag !== 32 && Gt.push(d), d
}
const ie = M2;

function M2(e, t = null, s = null, o = 0, i = null, r = !1) {
  if ((!e || e === Ep) && (e = Bt), kn(e)) {
    const l = Ts(e, t, !0);
    return s && Au(l, s), zn > 0 && !r && Gt && (l.shapeFlag & 6 ? Gt[Gt.indexOf(e)] = l : Gt.push(l)), l.patchFlag |= -2, l
  }
  if (H2(e) && (e = e.__vccOpts), t) {
    t = us(t);
    let {
      class: l,
      style: d
    } = t;
    l && !rt(l) && (t.class = Ie(l)), st(d) && (du(d) && !we(d) && (d = it({}, d)), t.style = Ro(d))
  }
  const a = rt(e) ? 1 : Pp(e) ? 128 : L2(e) ? 64 : st(e) ? 4 : Be(e) ? 2 : 0;
  return n(e, t, s, o, i, a, r, !0)
}

function us(e) {
  return e ? du(e) || tl in e ? it({}, e) : e : null
}

function Ts(e, t, s = !1) {
  const {
    props: o,
    ref: i,
    patchFlag: r,
    children: a
  } = e, l = t ? Pi(o || {}, t) : o;
  return {
    __v_isVNode: !0,
    __v_skip: !0,
    type: e.type,
    props: l,
    key: l && um(l),
    ref: t && t.ref ? s && i ? we(i) ? i.concat(pa(t)) : [i, pa(t)] : pa(t) : i,
    scopeId: e.scopeId,
    slotScopeIds: e.slotScopeIds,
    children: a,
    target: e.target,
    targetAnchor: e.targetAnchor,
    staticCount: e.staticCount,
    shapeFlag: e.shapeFlag,
    patchFlag: t && e.type !== Te ? r === -1 ? 16 : r | 16 : r,
    dynamicProps: e.dynamicProps,
    dynamicChildren: e.dynamicChildren,
    appContext: e.appContext,
    dirs: e.dirs,
    transition: e.transition,
    component: e.component,
    suspense: e.suspense,
    ssContent: e.ssContent && Ts(e.ssContent),
    ssFallback: e.ssFallback && Ts(e.ssFallback),
    el: e.el,
    anchor: e.anchor,
    ctx: e.ctx,
    ce: e.ce
  }
}

function J(e = " ", t = 0) {
  return ie(Wn, null, e, t)
}

function Ae(e, t) {
  const s = ie(jn, null, e);
  return s.staticCount = t, s
}

function D(e = "", t = !1) {
  return t ? (_(), ve(Bt, null, e)) : ie(Bt, null, e)
}

function es(e) {
  return e == null || typeof e == "boolean" ? ie(Bt) : we(e) ? ie(Te, null, e.slice()) : typeof e == "object" ? _n(e) : ie(Wn, null, String(e))
}

function _n(e) {
  return e.el === null && e.patchFlag !== -1 || e.memo ? e : Ts(e)
}

function Au(e, t) {
  let s = 0;
  const {
    shapeFlag: o
  } = e;
  if (t == null) t = null;
  else if (we(t)) s = 16;
  else if (typeof t == "object")
    if (o & 65) {
      const i = t.default;
      i && (i._c && (i._d = !1), Au(e, i()), i._c && (i._d = !0));
      return
    } else {
      s = 32;
      const i = t._;
      !i && !(tl in t) ? t._ctx = vt : i === 3 && vt && (vt.slots._ === 1 ? t._ = 1 : (t._ = 2, e.patchFlag |= 1024))
    }
  else Be(t) ? (t = {
    default: t,
    _ctx: vt
  }, s = 32) : (t = String(t), o & 64 ? (s = 16, t = [J(t)]) : s = 8);
  e.children = t, e.shapeFlag |= s
}

function Pi(...e) {
  const t = {};
  for (let s = 0; s < e.length; s++) {
    const o = e[s];
    for (const i in o)
      if (i === "class") t.class !== o.class && (t.class = Ie([t.class, o.class]));
      else if (i === "style") t.style = Ro([t.style, o.style]);
    else if (Ni(i)) {
      const r = t[i],
        a = o[i];
      a && r !== a && !(we(r) && r.includes(a)) && (t[i] = r ? [].concat(r, a) : a)
    } else i !== "" && (t[i] = o[i])
  }
  return t
}

function zt(e, t, s, o = null) {
  ns(e, t, 7, [s, o])
}
const R2 = Zp();
let N2 = 0;

function fm(e, t, s) {
  const o = e.type,
    i = (t ? t.appContext : e.appContext) || R2,
    r = {
      uid: N2++,
      vnode: e,
      type: o,
      parent: t,
      appContext: i,
      root: null,
      next: null,
      subTree: null,
      effect: null,
      update: null,
      scope: new ou(!0),
      render: null,
      proxy: null,
      exposed: null,
      exposeProxy: null,
      withProxy: null,
      provides: t ? t.provides : Object.create(i.provides),
      accessCache: null,
      renderCache: [],
      components: null,
      directives: null,
      propsOptions: Qp(o, i),
      emitsOptions: Sp(o, i),
      emit: null,
      emitted: null,
      propsDefaults: et,
      inheritAttrs: o.inheritAttrs,
      ctx: et,
      data: et,
      props: et,
      attrs: et,
      slots: et,
      refs: et,
      setupState: et,
      setupContext: null,
      attrsProxy: null,
      slotsProxy: null,
      suspense: s,
      suspenseId: s ? s.pendingId : 0,
      asyncDep: null,
      asyncResolved: !1,
      isMounted: !1,
      isUnmounted: !1,
      isDeactivated: !1,
      bc: null,
      c: null,
      bm: null,
      m: null,
      bu: null,
      u: null,
      um: null,
      bum: null,
      da: null,
      a: null,
      rtg: null,
      rtc: null,
      ec: null,
      sp: null
    };
  return r.ctx = {
    _: r
  }, r.root = t ? t.root : r, r.emit = Og.bind(null, r), e.ce && e.ce(r), r
}
let wt = null;
const Zs = () => wt || vt;
let Ea, xd; {
  const e = Qh(),
    t = (s, o) => {
      let i;
      return (i = e[s]) || (i = e[s] = []), i.push(o), r => {
        i.length > 1 ? i.forEach(a => a(r)) : i[0](r)
      }
    };
  Ea = t("__VUE_INSTANCE_SETTERS__", s => wt = s), xd = t("__VUE_SSR_SETTERS__", s => Hi = s)
}
const Kn = e => {
    const t = wt;
    return Ea(e), e.scope.on(), () => {
      e.scope.off(), Ea(t)
    }
  },
  Sd = () => {
    wt && wt.scope.off(), Ea(null)
  };

function hm(e) {
  return e.vnode.shapeFlag & 4
}
let Hi = !1;

function pm(e, t = !1) {
  t && xd(t);
  const {
    props: s,
    children: o
  } = e.vnode, i = hm(e);
  $2(e, s, i, t), A2(e, o);
  const r = i ? F2(e, t) : void 0;
  return t && xd(!1), r
}

function F2(e, t) {
  const s = e.type;
  e.accessCache = Object.create(null), e.proxy = za(new Proxy(e.ctx, yd));
  const {
    setup: o
  } = s;
  if (o) {
    const i = e.setupContext = o.length > 1 ? gm(e) : null,
      r = Kn(e);
    Yn();
    const a = zs(o, e, 0, [e.props, i]);
    if (Jn(), r(), su(a)) {
      if (a.then(Sd, Sd), t) return a.then(l => {
        Ed(e, l, t)
      }).catch(l => {
        Zn(l, e, 0)
      });
      e.asyncDep = a
    } else Ed(e, a, t)
  } else mm(e, t)
}

function Ed(e, t, s) {
  Be(t) ? e.type.__ssrInlineRender ? e.ssrRender = t : e.render = t : st(t) && (e.setupState = hu(t)), mm(e, s)
}
let Pa, Pd;

function U2(e) {
  Pa = e, Pd = t => {
    t.render._rc && (t.withProxy = new Proxy(t.ctx, e2))
  }
}
const V2 = () => !Pa;

function mm(e, t, s) {
  const o = e.type;
  if (!e.render) {
    if (!t && Pa && !o.render) {
      const i = o.template || $u(e).template;
      if (i) {
        const {
          isCustomElement: r,
          compilerOptions: a
        } = e.appContext.config, {
          delimiters: l,
          compilerOptions: d
        } = o, p = it(it({
          isCustomElement: r,
          delimiters: l
        }, a), d);
        o.render = Pa(i, p)
      }
    }
    e.render = o.render || Kt, Pd && Pd(e)
  } {
    const i = Kn(e);
    Yn();
    try {
      p2(e)
    } finally {
      Jn(), i()
    }
  }
}

function j2(e) {
  return e.attrsProxy || (e.attrsProxy = new Proxy(e.attrs, {
    get(t, s) {
      return Yt(e, "get", "$attrs"), t[s]
    }
  }))
}

function gm(e) {
  const t = s => {
    e.exposed = s || {}
  };
  return {
    get attrs() {
      return j2(e)
    },
    slots: e.slots,
    emit: e.emit,
    expose: t
  }
}

function sl(e) {
  if (e.exposed) return e.exposeProxy || (e.exposeProxy = new Proxy(hu(za(e.exposed)), {
    get(t, s) {
      if (s in t) return t[s];
      if (s in hi) return hi[s](e)
    },
    has(t, s) {
      return s in t || s in hi
    }
  }))
}

function Td(e, t = !0) {
  return Be(e) ? e.displayName || e.name : e.name || t && e.__name
}

function H2(e) {
  return Be(e) && "__vccOpts" in e
}
const ss = (e, t) => hg(e, t, Hi);

function q2(e, t, s = et) {
  const o = Zs(),
    i = jt(t),
    r = ts(t),
    a = wp((d, p) => {
      let m;
      return Bp(() => {
        const g = e[t];
        vs(m, g) && (m = g, p())
      }), {
        get() {
          return d(), s.get ? s.get(m) : m
        },
        set(g) {
          const $ = o.vnode.props;
          !($ && (t in $ || i in $ || r in $) && (`onUpdate:${t}` in $ || `onUpdate:${i}` in $ || `onUpdate:${r}` in $)) && vs(g, m) && (m = g, p()), o.emit(`update:${t}`, s.set ? s.set(g) : g)
        }
      }
    }),
    l = t === "modelValue" ? "modelModifiers" : `${t}Modifiers`;
  return a[Symbol.iterator] = () => {
    let d = 0;
    return {
      next() {
        return d < 2 ? {
          value: d++ ? e[l] || {} : a,
          done: !1
        } : {
          done: !0
        }
      }
    }
  }, a
}

function qi(e, t, s) {
  const o = arguments.length;
  return o === 2 ? st(t) && !we(t) ? kn(t) ? ie(e, null, [t]) : ie(e, t) : ie(e, null, t) : (o > 3 ? s = Array.prototype.slice.call(arguments, 2) : o === 3 && kn(s) && (s = [s]), ie(e, t, s))
}

function W2() {}

function z2(e, t, s, o) {
  const i = s[o];
  if (i && _m(i, e)) return i;
  const r = t();
  return r.memo = e.slice(), s[o] = r
}

function _m(e, t) {
  const s = e.memo;
  if (s.length != t.length) return !1;
  for (let o = 0; o < s.length; o++)
    if (vs(s[o], t[o])) return !1;
  return zn > 0 && Gt && Gt.push(e), !0
}
const bm = "3.4.21",
  K2 = Kt,
  G2 = Sg,
  Y2 = po,
  J2 = xp,
  Z2 = {
    createComponentInstance: fm,
    setupComponent: pm,
    renderComponentRoot: fa,
    setCurrentRenderingInstance: xi,
    isVNode: kn,
    normalizeVNode: es
  },
  X2 = Z2,
  Q2 = null,
  e_ = null,
  t_ = null;
/**
 * @vue/runtime-dom v3.4.21
 * (c) 2018-present Yuxi (Evan) You and Vue contributors
 * @license MIT
 **/
const s_ = "http://www.w3.org/2000/svg",
  n_ = "http://www.w3.org/1998/Math/MathML",
  bn = typeof document < "u" ? document : null,
  Cf = bn && bn.createElement("template"),
  o_ = {
    insert: (e, t, s) => {
      t.insertBefore(e, s || null)
    },
    remove: e => {
      const t = e.parentNode;
      t && t.removeChild(e)
    },
    createElement: (e, t, s, o) => {
      const i = t === "svg" ? bn.createElementNS(s_, e) : t === "mathml" ? bn.createElementNS(n_, e) : bn.createElement(e, s ? {
        is: s
      } : void 0);
      return e === "select" && o && o.multiple != null && i.setAttribute("multiple", o.multiple), i
    },
    createText: e => bn.createTextNode(e),
    createComment: e => bn.createComment(e),
    setText: (e, t) => {
      e.nodeValue = t
    },
    setElementText: (e, t) => {
      e.textContent = t
    },
    parentNode: e => e.parentNode,
    nextSibling: e => e.nextSibling,
    querySelector: e => bn.querySelector(e),
    setScopeId(e, t) {
      e.setAttribute(t, "")
    },
    insertStaticContent(e, t, s, o, i, r) {
      const a = s ? s.previousSibling : t.lastChild;
      if (i && (i === r || i.nextSibling))
        for (; t.insertBefore(i.cloneNode(!0), s), !(i === r || !(i = i.nextSibling)););
      else {
        Cf.innerHTML = o === "svg" ? `<svg>${e}</svg>` : o === "mathml" ? `<math>${e}</math>` : e;
        const l = Cf.content;
        if (o === "svg" || o === "mathml") {
          const d = l.firstChild;
          for (; d.firstChild;) l.appendChild(d.firstChild);
          l.removeChild(d)
        }
        t.insertBefore(l, s)
      }
      return [a ? a.nextSibling : t.firstChild, s ? s.previousSibling : t.lastChild]
    }
  },
  un = "transition",
  ri = "animation",
  So = Symbol("_vtc"),
  nl = (e, {
    slots: t
  }) => qi(Mp, ym(e), t);
nl.displayName = "Transition";
const vm = {
    name: String,
    type: String,
    css: {
      type: Boolean,
      default: !0
    },
    duration: [String, Number, Object],
    enterFromClass: String,
    enterActiveClass: String,
    enterToClass: String,
    appearFromClass: String,
    appearActiveClass: String,
    appearToClass: String,
    leaveFromClass: String,
    leaveActiveClass: String,
    leaveToClass: String
  },
  i_ = nl.props = it({}, wu, vm),
  Dn = (e, t = []) => {
    we(e) ? e.forEach(s => s(...t)) : e && e(...t)
  },
  kf = e => e ? we(e) ? e.some(t => t.length > 1) : e.length > 1 : !1;

function ym(e) {
  const t = {};
  for (const T in e) T in vm || (t[T] = e[T]);
  if (e.css === !1) return t;
  const {
    name: s = "v",
    type: o,
    duration: i,
    enterFromClass: r = `${s}-enter-from`,
    enterActiveClass: a = `${s}-enter-active`,
    enterToClass: l = `${s}-enter-to`,
    appearFromClass: d = r,
    appearActiveClass: p = a,
    appearToClass: m = l,
    leaveFromClass: g = `${s}-leave-from`,
    leaveActiveClass: $ = `${s}-leave-active`,
    leaveToClass: C = `${s}-leave-to`
  } = e, A = r_(i), O = A && A[0], V = A && A[1], {
    onBeforeEnter: M,
    onEnter: B,
    onEnterCancelled: P,
    onLeave: I,
    onLeaveCancelled: R,
    onBeforeAppear: F = M,
    onAppear: j = B,
    onAppearCancelled: q = P
  } = t, ne = (T, re, Le) => {
    pn(T, re ? m : l), pn(T, re ? p : a), Le && Le()
  }, ee = (T, re) => {
    T._isLeaving = !1, pn(T, g), pn(T, C), pn(T, $), re && re()
  }, ge = T => (re, Le) => {
    const We = T ? j : B,
      fe = () => ne(re, T, Le);
    Dn(We, [re, fe]), Af(() => {
      pn(re, T ? d : r), Fs(re, T ? m : l), kf(We) || xf(re, o, O, fe)
    })
  };
  return it(t, {
    onBeforeEnter(T) {
      Dn(M, [T]), Fs(T, r), Fs(T, a)
    },
    onBeforeAppear(T) {
      Dn(F, [T]), Fs(T, d), Fs(T, p)
    },
    onEnter: ge(!1),
    onAppear: ge(!0),
    onLeave(T, re) {
      T._isLeaving = !0;
      const Le = () => ee(T, re);
      Fs(T, g), $m(), Fs(T, $), Af(() => {
        T._isLeaving && (pn(T, g), Fs(T, C), kf(I) || xf(T, o, V, Le))
      }), Dn(I, [T, Le])
    },
    onEnterCancelled(T) {
      ne(T, !1), Dn(P, [T])
    },
    onAppearCancelled(T) {
      ne(T, !0), Dn(q, [T])
    },
    onLeaveCancelled(T) {
      ee(T), Dn(R, [T])
    }
  })
}

function r_(e) {
  if (e == null) return null;
  if (st(e)) return [Jc(e.enter), Jc(e.leave)]; {
    const t = Jc(e);
    return [t, t]
  }
}

function Jc(e) {
  return $a(e)
}

function Fs(e, t) {
  t.split(/\s+/).forEach(s => s && e.classList.add(s)), (e[So] || (e[So] = new Set)).add(t)
}

function pn(e, t) {
  t.split(/\s+/).forEach(o => o && e.classList.remove(o));
  const s = e[So];
  s && (s.delete(t), s.size || (e[So] = void 0))
}

function Af(e) {
  requestAnimationFrame(() => {
    requestAnimationFrame(e)
  })
}
let a_ = 0;

function xf(e, t, s, o) {
  const i = e._endId = ++a_,
    r = () => {
      i === e._endId && o()
    };
  if (s) return setTimeout(r, s);
  const {
    type: a,
    timeout: l,
    propCount: d
  } = wm(e, t);
  if (!a) return o();
  const p = a + "end";
  let m = 0;
  const g = () => {
      e.removeEventListener(p, $), r()
    },
    $ = C => {
      C.target === e && ++m >= d && g()
    };
  setTimeout(() => {
    m < d && g()
  }, l + 1), e.addEventListener(p, $)
}

function wm(e, t) {
  const s = window.getComputedStyle(e),
    o = A => (s[A] || "").split(", "),
    i = o(`${un}Delay`),
    r = o(`${un}Duration`),
    a = Sf(i, r),
    l = o(`${ri}Delay`),
    d = o(`${ri}Duration`),
    p = Sf(l, d);
  let m = null,
    g = 0,
    $ = 0;
  t === un ? a > 0 && (m = un, g = a, $ = r.length) : t === ri ? p > 0 && (m = ri, g = p, $ = d.length) : (g = Math.max(a, p), m = g > 0 ? a > p ? un : ri : null, $ = m ? m === un ? r.length : d.length : 0);
  const C = m === un && /\b(transform|all)(,|$)/.test(o(`${un}Property`).toString());
  return {
    type: m,
    timeout: g,
    propCount: $,
    hasTransform: C
  }
}

function Sf(e, t) {
  for (; e.length < t.length;) e = e.concat(e);
  return Math.max(...t.map((s, o) => Ef(s) + Ef(e[o])))
}

function Ef(e) {
  return e === "auto" ? 0 : Number(e.slice(0, -1).replace(",", ".")) * 1e3
}

function $m() {
  return document.body.offsetHeight
}

function l_(e, t, s) {
  const o = e[So];
  o && (t = (t ? [t, ...o] : [...o]).join(" ")), t == null ? e.removeAttribute("class") : s ? e.setAttribute("class", t) : e.className = t
}
const Ta = Symbol("_vod"),
  Cm = Symbol("_vsh"),
  Ti = {
    beforeMount(e, {
      value: t
    }, {
      transition: s
    }) {
      e[Ta] = e.style.display === "none" ? "" : e.style.display, s && t ? s.beforeEnter(e) : ai(e, t)
    },
    mounted(e, {
      value: t
    }, {
      transition: s
    }) {
      s && t && s.enter(e)
    },
    updated(e, {
      value: t,
      oldValue: s
    }, {
      transition: o
    }) {
      !t != !s && (o ? t ? (o.beforeEnter(e), ai(e, !0), o.enter(e)) : o.leave(e, () => {
        ai(e, !1)
      }) : ai(e, t))
    },
    beforeUnmount(e, {
      value: t
    }) {
      ai(e, t)
    }
  };

function ai(e, t) {
  e.style.display = t ? e[Ta] : "none", e[Cm] = !t
}

function c_() {
  Ti.getSSRProps = ({
    value: e
  }) => {
    if (!e) return {
      style: {
        display: "none"
      }
    }
  }
}
const km = Symbol("");

function d_(e) {
  const t = Zs();
  if (!t) return;
  const s = t.ut = (i = e(t.proxy)) => {
      Array.from(document.querySelectorAll(`[data-v-owner="${t.uid}"]`)).forEach(r => Od(r, i))
    },
    o = () => {
      const i = e(t.proxy);
      Ld(t.subTree, i), s(i)
    };
  Ip(o), At(() => {
    const i = new MutationObserver(o);
    i.observe(t.subTree.el.parentNode, {
      childList: !0
    }), el(() => i.disconnect())
  })
}

function Ld(e, t) {
  if (e.shapeFlag & 128) {
    const s = e.suspense;
    e = s.activeBranch, s.pendingBranch && !s.isHydrating && s.effects.push(() => {
      Ld(s.activeBranch, t)
    })
  }
  for (; e.component;) e = e.component.subTree;
  if (e.shapeFlag & 1 && e.el) Od(e.el, t);
  else if (e.type === Te) e.children.forEach(s => Ld(s, t));
  else if (e.type === jn) {
    let {
      el: s,
      anchor: o
    } = e;
    for (; s && (Od(s, t), s !== o);) s = s.nextSibling
  }
}

function Od(e, t) {
  if (e.nodeType === 1) {
    const s = e.style;
    let o = "";
    for (const i in t) s.setProperty(`--${i}`, t[i]), o += `--${i}: ${t[i]};`;
    s[km] = o
  }
}
const u_ = /(^|;)\s*display\s*:/;

function f_(e, t, s) {
  const o = e.style,
    i = rt(s);
  let r = !1;
  if (s && !i) {
    if (t)
      if (rt(t))
        for (const a of t.split(";")) {
          const l = a.slice(0, a.indexOf(":")).trim();
          s[l] == null && ma(o, l, "")
        } else
          for (const a in t) s[a] == null && ma(o, a, "");
    for (const a in s) a === "display" && (r = !0), ma(o, a, s[a])
  } else if (i) {
    if (t !== s) {
      const a = o[km];
      a && (s += ";" + a), o.cssText = s, r = u_.test(s)
    }
  } else t && e.removeAttribute("style");
  Ta in e && (e[Ta] = r ? o.display : "", e[Cm] && (o.display = "none"))
}
const Pf = /\s*!important$/;

function ma(e, t, s) {
  if (we(s)) s.forEach(o => ma(e, t, o));
  else if (s == null && (s = ""), t.startsWith("--")) e.setProperty(t, s);
  else {
    const o = h_(e, t);
    Pf.test(s) ? e.setProperty(ts(o), s.replace(Pf, ""), "important") : e[o] = s
  }
}
const Tf = ["Webkit", "Moz", "ms"],
  Zc = {};

function h_(e, t) {
  const s = Zc[t];
  if (s) return s;
  let o = jt(t);
  if (o !== "filter" && o in e) return Zc[t] = o;
  o = Fi(o);
  for (let i = 0; i < Tf.length; i++) {
    const r = Tf[i] + o;
    if (r in e) return Zc[t] = r
  }
  return t
}
const Lf = "http://www.w3.org/1999/xlink";

function p_(e, t, s, o, i) {
  if (o && t.startsWith("xlink:")) s == null ? e.removeAttributeNS(Lf, t.slice(6, t.length)) : e.setAttributeNS(Lf, t, s);
  else {
    const r = V0(t);
    s == null || r && !ep(s) ? e.removeAttribute(t) : e.setAttribute(t, r ? "" : s)
  }
}

function m_(e, t, s, o, i, r, a) {
  if (t === "innerHTML" || t === "textContent") {
    o && a(o, i, r), e[t] = s ? ? "";
    return
  }
  const l = e.tagName;
  if (t === "value" && l !== "PROGRESS" && !l.includes("-")) {
    const p = l === "OPTION" ? e.getAttribute("value") || "" : e.value,
      m = s ? ? "";
    (p !== m || !("_value" in e)) && (e.value = m), s == null && e.removeAttribute(t), e._value = s;
    return
  }
  let d = !1;
  if (s === "" || s == null) {
    const p = typeof e[t];
    p === "boolean" ? s = ep(s) : s == null && p === "string" ? (s = "", d = !0) : p === "number" && (s = 0, d = !0)
  }
  try {
    e[t] = s
  } catch {}
  d && e.removeAttribute(t)
}

function qs(e, t, s, o) {
  e.addEventListener(t, s, o)
}

function g_(e, t, s, o) {
  e.removeEventListener(t, s, o)
}
const Of = Symbol("_vei");

function __(e, t, s, o, i = null) {
  const r = e[Of] || (e[Of] = {}),
    a = r[t];
  if (o && a) a.value = o;
  else {
    const [l, d] = b_(t);
    if (o) {
      const p = r[t] = w_(o, i);
      qs(e, l, p, d)
    } else a && (g_(e, l, a, d), r[t] = void 0)
  }
}
const If = /(?:Once|Passive|Capture)$/;

function b_(e) {
  let t;
  if (If.test(e)) {
    t = {};
    let o;
    for (; o = e.match(If);) e = e.slice(0, e.length - o[0].length), t[o[0].toLowerCase()] = !0
  }
  return [e[2] === ":" ? e.slice(3) : ts(e.slice(2)), t]
}
let Xc = 0;
const v_ = Promise.resolve(),
  y_ = () => Xc || (v_.then(() => Xc = 0), Xc = Date.now());

function w_(e, t) {
  const s = o => {
    if (!o._vts) o._vts = Date.now();
    else if (o._vts <= s.attached) return;
    ns($_(o, s.value), t, 5, [o])
  };
  return s.value = e, s.attached = y_(), s
}

function $_(e, t) {
  if (we(t)) {
    const s = e.stopImmediatePropagation;
    return e.stopImmediatePropagation = () => {
      s.call(e), e._stopped = !0
    }, t.map(o => i => !i._stopped && o && o(i))
  } else return t
}
const Bf = e => e.charCodeAt(0) === 111 && e.charCodeAt(1) === 110 && e.charCodeAt(2) > 96 && e.charCodeAt(2) < 123,
  C_ = (e, t, s, o, i, r, a, l, d) => {
    const p = i === "svg";
    t === "class" ? l_(e, o, p) : t === "style" ? f_(e, s, o) : Ni(t) ? eu(t) || __(e, t, s, o, a) : (t[0] === "." ? (t = t.slice(1), !0) : t[0] === "^" ? (t = t.slice(1), !1) : k_(e, t, o, p)) ? m_(e, t, o, r, a, l, d) : (t === "true-value" ? e._trueValue = o : t === "false-value" && (e._falseValue = o), p_(e, t, o, p))
  };

function k_(e, t, s, o) {
  if (o) return !!(t === "innerHTML" || t === "textContent" || t in e && Bf(t) && Be(s));
  if (t === "spellcheck" || t === "draggable" || t === "translate" || t === "form" || t === "list" && e.tagName === "INPUT" || t === "type" && e.tagName === "TEXTAREA") return !1;
  if (t === "width" || t === "height") {
    const i = e.tagName;
    if (i === "IMG" || i === "VIDEO" || i === "CANVAS" || i === "SOURCE") return !1
  }
  return Bf(t) && rt(s) ? !1 : t in e
} /*! #__NO_SIDE_EFFECTS__ */
function Am(e, t) {
  const s = Dt(e);
  class o extends ol {
    constructor(r) {
      super(s, r, t)
    }
  }
  return o.def = s, o
} /*! #__NO_SIDE_EFFECTS__ */
const A_ = e => Am(e, Bm),
  x_ = typeof HTMLElement < "u" ? HTMLElement : class {};
class ol extends x_ {
  constructor(t, s = {}, o) {
    super(), this._def = t, this._props = s, this._instance = null, this._connected = !1, this._resolved = !1, this._numberProps = null, this._ob = null, this.shadowRoot && o ? o(this._createVNode(), this.shadowRoot) : (this.attachShadow({
      mode: "open"
    }), this._def.__asyncLoader || this._resolveProps(this._def))
  }
  connectedCallback() {
    this._connected = !0, this._instance || (this._resolved ? this._update() : this._resolveDef())
  }
  disconnectedCallback() {
    this._connected = !1, this._ob && (this._ob.disconnect(), this._ob = null), Ui(() => {
      this._connected || (Id(null, this.shadowRoot), this._instance = null)
    })
  }
  _resolveDef() {
    this._resolved = !0;
    for (let o = 0; o < this.attributes.length; o++) this._setAttr(this.attributes[o].name);
    this._ob = new MutationObserver(o => {
      for (const i of o) this._setAttr(i.attributeName)
    }), this._ob.observe(this, {
      attributes: !0
    });
    const t = (o, i = !1) => {
        const {
          props: r,
          styles: a
        } = o;
        let l;
        if (r && !we(r))
          for (const d in r) {
            const p = r[d];
            (p === Number || p && p.type === Number) && (d in this._props && (this._props[d] = $a(this._props[d])), (l || (l = Object.create(null)))[jt(d)] = !0)
          }
        this._numberProps = l, i && this._resolveProps(o), this._applyStyles(a), this._update()
      },
      s = this._def.__asyncLoader;
    s ? s().then(o => t(o, !0)) : t(this._def)
  }
  _resolveProps(t) {
    const {
      props: s
    } = t, o = we(s) ? s : Object.keys(s || {});
    for (const i of Object.keys(this)) i[0] !== "_" && o.includes(i) && this._setProp(i, this[i], !0, !1);
    for (const i of o.map(jt)) Object.defineProperty(this, i, {
      get() {
        return this._getProp(i)
      },
      set(r) {
        this._setProp(i, r)
      }
    })
  }
  _setAttr(t) {
    let s = this.getAttribute(t);
    const o = jt(t);
    this._numberProps && this._numberProps[o] && (s = $a(s)), this._setProp(o, s, !1)
  }
  _getProp(t) {
    return this._props[t]
  }
  _setProp(t, s, o = !0, i = !0) {
    s !== this._props[t] && (this._props[t] = s, i && this._instance && this._update(), o && (s === !0 ? this.setAttribute(ts(t), "") : typeof s == "string" || typeof s == "number" ? this.setAttribute(ts(t), s + "") : s || this.removeAttribute(ts(t))))
  }
  _update() {
    Id(this._createVNode(), this.shadowRoot)
  }
  _createVNode() {
    const t = ie(this._def, it({}, this._props));
    return this._instance || (t.ce = s => {
      this._instance = s, s.isCE = !0;
      const o = (r, a) => {
        this.dispatchEvent(new CustomEvent(r, {
          detail: a
        }))
      };
      s.emit = (r, ...a) => {
        o(r, a), ts(r) !== r && o(ts(r), a)
      };
      let i = this;
      for (; i = i && (i.parentNode || i.host);)
        if (i instanceof ol) {
          s.parent = i._instance, s.provides = i._instance.provides;
          break
        }
    }), t
  }
  _applyStyles(t) {
    t && t.forEach(s => {
      const o = document.createElement("style");
      o.textContent = s, this.shadowRoot.appendChild(o)
    })
  }
}

function S_(e = "$style") {
  {
    const t = Zs();
    if (!t) return et;
    const s = t.type.__cssModules;
    if (!s) return et;
    const o = s[e];
    return o || et
  }
}
const xm = new WeakMap,
  Sm = new WeakMap,
  La = Symbol("_moveCb"),
  Df = Symbol("_enterCb"),
  Em = {
    name: "TransitionGroup",
    props: it({}, i_, {
      tag: String,
      moveClass: String
    }),
    setup(e, {
      slots: t
    }) {
      const s = Zs(),
        o = yu();
      let i, r;
      return Xa(() => {
        if (!i.length) return;
        const a = e.moveClass || `${e.name||"v"}-move`;
        if (!I_(i[0].el, s.vnode.el, a)) return;
        i.forEach(T_), i.forEach(L_);
        const l = i.filter(O_);
        $m(), l.forEach(d => {
          const p = d.el,
            m = p.style;
          Fs(p, a), m.transform = m.webkitTransform = m.transitionDuration = "";
          const g = p[La] = $ => {
            $ && $.target !== p || (!$ || /transform$/.test($.propertyName)) && (p.removeEventListener("transitionend", g), p[La] = null, pn(p, a))
          };
          p.addEventListener("transitionend", g)
        })
      }), () => {
        const a = Ke(e),
          l = ym(a);
        let d = a.tag || Te;
        i = r, r = t.default ? Ja(t.default()) : [];
        for (let p = 0; p < r.length; p++) {
          const m = r[p];
          m.key != null && qn(m, xo(m, l, o, s))
        }
        if (i)
          for (let p = 0; p < i.length; p++) {
            const m = i[p];
            qn(m, xo(m, l, o, s)), xm.set(m, m.el.getBoundingClientRect())
          }
        return ie(d, null, r)
      }
    }
  },
  E_ = e => delete e.mode;
Em.props;
const P_ = Em;

function T_(e) {
  const t = e.el;
  t[La] && t[La](), t[Df] && t[Df]()
}

function L_(e) {
  Sm.set(e, e.el.getBoundingClientRect())
}

function O_(e) {
  const t = xm.get(e),
    s = Sm.get(e),
    o = t.left - s.left,
    i = t.top - s.top;
  if (o || i) {
    const r = e.el.style;
    return r.transform = r.webkitTransform = `translate(${o}px,${i}px)`, r.transitionDuration = "0s", e
  }
}

function I_(e, t, s) {
  const o = e.cloneNode(),
    i = e[So];
  i && i.forEach(l => {
    l.split(/\s+/).forEach(d => d && o.classList.remove(d))
  }), s.split(/\s+/).forEach(l => l && o.classList.add(l)), o.style.display = "none";
  const r = t.nodeType === 1 ? t : t.parentNode;
  r.appendChild(o);
  const {
    hasTransform: a
  } = wm(o);
  return r.removeChild(o), a
}
const An = e => {
  const t = e.props["onUpdate:modelValue"] || !1;
  return we(t) ? s => yo(t, s) : t
};

function B_(e) {
  e.target.composing = !0
}

function Mf(e) {
  const t = e.target;
  t.composing && (t.composing = !1, t.dispatchEvent(new Event("input")))
}
const fs = Symbol("_assign"),
  Re = {
    created(e, {
      modifiers: {
        lazy: t,
        trim: s,
        number: o
      }
    }, i) {
      e[fs] = An(i);
      const r = o || i.props && i.props.type === "number";
      qs(e, t ? "change" : "input", a => {
        if (a.target.composing) return;
        let l = e.value;
        s && (l = l.trim()), r && (l = wi(l)), e[fs](l)
      }), s && qs(e, "change", () => {
        e.value = e.value.trim()
      }), t || (qs(e, "compositionstart", B_), qs(e, "compositionend", Mf), qs(e, "change", Mf))
    },
    mounted(e, {
      value: t
    }) {
      e.value = t ? ? ""
    },
    beforeUpdate(e, {
      value: t,
      modifiers: {
        lazy: s,
        trim: o,
        number: i
      }
    }, r) {
      if (e[fs] = An(r), e.composing) return;
      const a = i || e.type === "number" ? wi(e.value) : e.value,
        l = t ? ? "";
      a !== l && (document.activeElement === e && e.type !== "range" && (s || o && e.value.trim() === l) || (e.value = l))
    }
  },
  il = {
    deep: !0,
    created(e, t, s) {
      e[fs] = An(s), qs(e, "change", () => {
        const o = e._modelValue,
          i = Eo(e),
          r = e.checked,
          a = e[fs];
        if (we(o)) {
          const l = ja(o, i),
            d = l !== -1;
          if (r && !d) a(o.concat(i));
          else if (!r && d) {
            const p = [...o];
            p.splice(l, 1), a(p)
          }
        } else if (Gn(o)) {
          const l = new Set(o);
          r ? l.add(i) : l.delete(i), a(l)
        } else a(Pm(e, r))
      })
    },
    mounted: Rf,
    beforeUpdate(e, t, s) {
      e[fs] = An(s), Rf(e, t, s)
    }
  };

function Rf(e, {
  value: t,
  oldValue: s
}, o) {
  e._modelValue = t, we(t) ? e.checked = ja(t, o.props.value) > -1 : Gn(t) ? e.checked = t.has(o.props.value) : t !== s && (e.checked = Cn(t, Pm(e, !0)))
}
const kt = {
    created(e, {
      value: t
    }, s) {
      e.checked = Cn(t, s.props.value), e[fs] = An(s), qs(e, "change", () => {
        e[fs](Eo(e))
      })
    },
    beforeUpdate(e, {
      value: t,
      oldValue: s
    }, o) {
      e[fs] = An(o), t !== s && (e.checked = Cn(t, o.props.value))
    }
  },
  Xn = {
    deep: !0,
    created(e, {
      value: t,
      modifiers: {
        number: s
      }
    }, o) {
      const i = Gn(t);
      qs(e, "change", () => {
        const r = Array.prototype.filter.call(e.options, a => a.selected).map(a => s ? wi(Eo(a)) : Eo(a));
        e[fs](e.multiple ? i ? new Set(r) : r : r[0]), e._assigning = !0, Ui(() => {
          e._assigning = !1
        })
      }), e[fs] = An(o)
    },
    mounted(e, {
      value: t,
      modifiers: {
        number: s
      }
    }) {
      Nf(e, t, s)
    },
    beforeUpdate(e, t, s) {
      e[fs] = An(s)
    },
    updated(e, {
      value: t,
      modifiers: {
        number: s
      }
    }) {
      e._assigning || Nf(e, t, s)
    }
  };

function Nf(e, t, s) {
  const o = e.multiple,
    i = we(t);
  if (!(o && !i && !Gn(t))) {
    for (let r = 0, a = e.options.length; r < a; r++) {
      const l = e.options[r],
        d = Eo(l);
      if (o)
        if (i) {
          const p = typeof d;
          p === "string" || p === "number" ? l.selected = t.includes(s ? wi(d) : d) : l.selected = ja(t, d) > -1
        } else l.selected = t.has(d);
      else if (Cn(Eo(l), t)) {
        e.selectedIndex !== r && (e.selectedIndex = r);
        return
      }
    }!o && e.selectedIndex !== -1 && (e.selectedIndex = -1)
  }
}

function Eo(e) {
  return "_value" in e ? e._value : e.value
}

function Pm(e, t) {
  const s = t ? "_trueValue" : "_falseValue";
  return s in e ? e[s] : t
}
const xu = {
  created(e, t, s) {
    aa(e, t, s, null, "created")
  },
  mounted(e, t, s) {
    aa(e, t, s, null, "mounted")
  },
  beforeUpdate(e, t, s, o) {
    aa(e, t, s, o, "beforeUpdate")
  },
  updated(e, t, s, o) {
    aa(e, t, s, o, "updated")
  }
};

function Tm(e, t) {
  switch (e) {
    case "SELECT":
      return Xn;
    case "TEXTAREA":
      return Re;
    default:
      switch (t) {
        case "checkbox":
          return il;
        case "radio":
          return kt;
        default:
          return Re
      }
  }
}

function aa(e, t, s, o, i) {
  const a = Tm(e.tagName, s.props && s.props.type)[i];
  a && a(e, t, s, o)
}

function D_() {
  Re.getSSRProps = ({
    value: e
  }) => ({
    value: e
  }), kt.getSSRProps = ({
    value: e
  }, t) => {
    if (t.props && Cn(t.props.value, e)) return {
      checked: !0
    }
  }, il.getSSRProps = ({
    value: e
  }, t) => {
    if (we(e)) {
      if (t.props && ja(e, t.props.value) > -1) return {
        checked: !0
      }
    } else if (Gn(e)) {
      if (t.props && e.has(t.props.value)) return {
        checked: !0
      }
    } else if (e) return {
      checked: !0
    }
  }, xu.getSSRProps = (e, t) => {
    if (typeof t.type != "string") return;
    const s = Tm(t.type.toUpperCase(), t.props && t.props.type);
    if (s.getSSRProps) return s.getSSRProps(e, t)
  }
}
const M_ = ["ctrl", "shift", "alt", "meta"],
  R_ = {
    stop: e => e.stopPropagation(),
    prevent: e => e.preventDefault(),
    self: e => e.target !== e.currentTarget,
    ctrl: e => !e.ctrlKey,
    shift: e => !e.shiftKey,
    alt: e => !e.altKey,
    meta: e => !e.metaKey,
    left: e => "button" in e && e.button !== 0,
    middle: e => "button" in e && e.button !== 1,
    right: e => "button" in e && e.button !== 2,
    exact: (e, t) => M_.some(s => e[`${s}Key`] && !t.includes(s))
  },
  Li = (e, t) => {
    const s = e._withMods || (e._withMods = {}),
      o = t.join(".");
    return s[o] || (s[o] = (i, ...r) => {
      for (let a = 0; a < t.length; a++) {
        const l = R_[t[a]];
        if (l && l(i, t)) return
      }
      return e(i, ...r)
    })
  },
  N_ = {
    esc: "escape",
    space: " ",
    up: "arrow-up",
    left: "arrow-left",
    right: "arrow-right",
    down: "arrow-down",
    delete: "backspace"
  },
  mo = (e, t) => {
    const s = e._withKeys || (e._withKeys = {}),
      o = t.join(".");
    return s[o] || (s[o] = i => {
      if (!("key" in i)) return;
      const r = ts(i.key);
      if (t.some(a => a === r || N_[a] === r)) return e(i)
    })
  },
  Lm = it({
    patchProp: C_
  }, o_);
let _i, Ff = !1;

function Om() {
  return _i || (_i = nm(Lm))
}

function Im() {
  return _i = Ff ? _i : om(Lm), Ff = !0, _i
}
const Id = (...e) => {
    Om().render(...e)
  },
  Bm = (...e) => {
    Im().hydrate(...e)
  },
  Dm = (...e) => {
    const t = Om().createApp(...e),
      {
        mount: s
      } = t;
    return t.mount = o => {
      const i = Rm(o);
      if (!i) return;
      const r = t._component;
      !Be(r) && !r.render && !r.template && (r.template = i.innerHTML), i.innerHTML = "";
      const a = s(i, !1, Mm(i));
      return i instanceof Element && (i.removeAttribute("v-cloak"), i.setAttribute("data-v-app", "")), a
    }, t
  },
  F_ = (...e) => {
    const t = Im().createApp(...e),
      {
        mount: s
      } = t;
    return t.mount = o => {
      const i = Rm(o);
      if (i) return s(i, !0, Mm(i))
    }, t
  };

function Mm(e) {
  if (e instanceof SVGElement) return "svg";
  if (typeof MathMLElement == "function" && e instanceof MathMLElement) return "mathml"
}

function Rm(e) {
  return rt(e) ? document.querySelector(e) : e
}
let Uf = !1;
const U_ = () => {
  Uf || (Uf = !0, D_(), c_())
};
/**
 * vue v3.4.21
 * (c) 2018-present Yuxi (Evan) You and Vue contributors
 * @license MIT
 **/
const V_ = () => {},
  j_ = Object.freeze(Object.defineProperty({
    __proto__: null,
    BaseTransition: Mp,
    BaseTransitionPropsValidators: wu,
    Comment: Bt,
    DeprecationTypes: t_,
    EffectScope: ou,
    ErrorCodes: xg,
    ErrorTypeStrings: G2,
    Fragment: Te,
    KeepAlive: Zg,
    ReactiveEffect: Ao,
    Static: jn,
    Suspense: Ug,
    Teleport: B2,
    Text: Wn,
    TrackOpTypes: Cg,
    Transition: nl,
    TransitionGroup: P_,
    TriggerOpTypes: kg,
    VueElement: ol,
    assertNumber: Ag,
    callWithAsyncErrorHandling: ns,
    callWithErrorHandling: zs,
    camelize: jt,
    capitalize: Fi,
    cloneVNode: Ts,
    compatUtils: e_,
    compile: V_,
    computed: ss,
    createApp: Dm,
    createBlock: ve,
    createCommentVNode: D,
    createElementBlock: w,
    createElementVNode: n,
    createHydrationRenderer: om,
    createPropsRestProxy: f2,
    createRenderer: nm,
    createSSRApp: F_,
    createSlots: Qg,
    createStaticVNode: Ae,
    createTextVNode: J,
    createVNode: ie,
    customRef: wp,
    defineAsyncComponent: Yg,
    defineComponent: Dt,
    defineCustomElement: Am,
    defineEmits: s2,
    defineExpose: n2,
    defineModel: r2,
    defineOptions: o2,
    defineProps: t2,
    defineSSRCustomElement: A_,
    defineSlots: i2,
    devtools: Y2,
    effect: W0,
    effectScope: sp,
    getCurrentInstance: Zs,
    getCurrentScope: op,
    getTransitionRawChildren: Ja,
    guardReactiveProps: us,
    h: qi,
    handleError: Zn,
    hasInjectionContext: w2,
    hydrate: Bm,
    initCustomFormatter: W2,
    initDirectivesForSSR: U_,
    inject: Ge,
    isMemoSame: _m,
    isProxy: du,
    isReactive: Un,
    isReadonly: Hn,
    isRef: Tt,
    isRuntimeOnly: V2,
    isShallow: $i,
    isVNode: kn,
    markRaw: za,
    mergeDefaults: d2,
    mergeModels: u2,
    mergeProps: Pi,
    nextTick: Ui,
    normalizeClass: Ie,
    normalizeProps: ds,
    normalizeStyle: Ro,
    onActivated: Np,
    onBeforeMount: Vp,
    onBeforeUnmount: Qa,
    onBeforeUpdate: jp,
    onDeactivated: Fp,
    onErrorCaptured: zp,
    onMounted: At,
    onRenderTracked: Wp,
    onRenderTriggered: qp,
    onScopeDispose: H0,
    onServerPrefetch: Hp,
    onUnmounted: el,
    onUpdated: Xa,
    openBlock: _,
    popScopeId: Fo,
    provide: pi,
    proxyRefs: hu,
    pushScopeId: No,
    queuePostFlushCb: ka,
    reactive: Ls,
    readonly: cu,
    ref: he,
    registerRuntimeCompiler: U2,
    render: Id,
    renderList: Je,
    renderSlot: Wt,
    resolveComponent: ke,
    resolveDirective: _u,
    resolveDynamicComponent: ha,
    resolveFilter: Q2,
    resolveTransitionHooks: xo,
    setBlockTracking: Ad,
    setDevtoolsHook: J2,
    setTransitionHooks: qn,
    shallowReactive: lu,
    shallowReadonly: fg,
    shallowRef: vp,
    ssrContextKey: Lp,
    ssrUtils: X2,
    stop: z0,
    toDisplayString: L,
    toHandlerKey: fi,
    toHandlers: Gp,
    toRaw: Ke,
    toRef: $g,
    toRefs: vg,
    toValue: gg,
    transformVNodeArgs: D2,
    triggerRef: mg,
    unref: Pt,
    useAttrs: c2,
    useCssModule: S_,
    useCssVars: d_,
    useModel: q2,
    useSSRContext: Op,
    useSlots: l2,
    useTransitionState: yu,
    vModelCheckbox: il,
    vModelDynamic: xu,
    vModelRadio: kt,
    vModelSelect: Xn,
    vModelText: Re,
    vShow: Ti,
    version: bm,
    warn: K2,
    watch: Ks,
    watchEffect: zg,
    watchPostEffect: Ip,
    watchSyncEffect: Bp,
    withAsyncContext: h2,
    withCtx: Ue,
    withDefaults: a2,
    withDirectives: be,
    withKeys: mo,
    withMemo: z2,
    withModifiers: Li,
    withScopeId: Ig
  }, Symbol.toStringTag, {
    value: "Module"
  })),
  H_ = [
    ["Afghanistan (‫افغانستان‬‎)", "af", "93"],
    ["Albania (Shqipëri)", "al", "355"],
    ["Algeria (‫الجزائر‬‎)", "dz", "213"],
    ["American Samoa", "as", "1", 5, ["684"]],
    ["Andorra", "ad", "376"],
    ["Angola", "ao", "244"],
    ["Anguilla", "ai", "1", 6, ["264"]],
    ["Antigua and Barbuda", "ag", "1", 7, ["268"]],
    ["Argentina", "ar", "54"],
    ["Armenia (Հայաստան)", "am", "374"],
    ["Aruba", "aw", "297"],
    ["Ascension Island", "ac", "247"],
    ["Australia", "au", "61", 0],
    ["Austria (Österreich)", "at", "43"],
    ["Azerbaijan (Azərbaycan)", "az", "994"],
    ["Bahamas", "bs", "1", 8, ["242"]],
    ["Bahrain (‫البحرين‬‎)", "bh", "973"],
    ["Bangladesh (বাংলাদেশ)", "bd", "880"],
    ["Barbados", "bb", "1", 9, ["246"]],
    ["Belarus (Беларусь)", "by", "375"],
    ["Belgium (België)", "be", "32"],
    ["Belize", "bz", "501"],
    ["Benin (Bénin)", "bj", "229"],
    ["Bermuda", "bm", "1", 10, ["441"]],
    ["Bhutan (འབྲུག)", "bt", "975"],
    ["Bolivia", "bo", "591"],
    ["Bosnia and Herzegovina (Босна и Херцеговина)", "ba", "387"],
    ["Botswana", "bw", "267"],
    ["Brazil (Brasil)", "br", "55"],
    ["British Indian Ocean Territory", "io", "246"],
    ["British Virgin Islands", "vg", "1", 11, ["284"]],
    ["Brunei", "bn", "673"],
    ["Bulgaria (България)", "bg", "359"],
    ["Burkina Faso", "bf", "226"],
    ["Burundi (Uburundi)", "bi", "257"],
    ["Cambodia (កម្ពុជា)", "kh", "855"],
    ["Cameroon (Cameroun)", "cm", "237"],
    ["Canada", "ca", "1", 1, ["204", "226", "236", "249", "250", "263", "289", "306", "343", "354", "365", "367", "368", "382", "387", "403", "416", "418", "428", "431", "437", "438", "450", "584", "468", "474", "506", "514", "519", "548", "579", "581", "584", "587", "604", "613", "639", "647", "672", "683", "705", "709", "742", "753", "778", "780", "782", "807", "819", "825", "867", "873", "902", "905"]],
    ["Cape Verde (Kabu Verdi)", "cv", "238"],
    ["Caribbean Netherlands", "bq", "599", 1, ["3", "4", "7"]],
    ["Cayman Islands", "ky", "1", 12, ["345"]],
    ["Central African Republic (République centrafricaine)", "cf", "236"],
    ["Chad (Tchad)", "td", "235"],
    ["Chile", "cl", "56"],
    ["China (中国)", "cn", "86"],
    ["Christmas Island", "cx", "61", 2, ["89164"]],
    ["Cocos (Keeling) Islands", "cc", "61", 1, ["89162"]],
    ["Colombia", "co", "57"],
    ["Comoros (‫جزر القمر‬‎)", "km", "269"],
    ["Congo (DRC) (Jamhuri ya Kidemokrasia ya Kongo)", "cd", "243"],
    ["Congo (Republic) (Congo-Brazzaville)", "cg", "242"],
    ["Cook Islands", "ck", "682"],
    ["Costa Rica", "cr", "506"],
    ["Côte d’Ivoire", "ci", "225"],
    ["Croatia (Hrvatska)", "hr", "385"],
    ["Cuba", "cu", "53"],
    ["Curaçao", "cw", "599", 0],
    ["Cyprus (Κύπρος)", "cy", "357"],
    ["Czech Republic (Česká republika)", "cz", "420"],
    ["Denmark (Danmark)", "dk", "45"],
    ["Djibouti", "dj", "253"],
    ["Dominica", "dm", "1", 13, ["767"]],
    ["Dominican Republic (República Dominicana)", "do", "1", 2, ["809", "829", "849"]],
    ["Ecuador", "ec", "593"],
    ["Egypt (‫مصر‬‎)", "eg", "20"],
    ["El Salvador", "sv", "503"],
    ["Equatorial Guinea (Guinea Ecuatorial)", "gq", "240"],
    ["Eritrea", "er", "291"],
    ["Estonia (Eesti)", "ee", "372"],
    ["Eswatini", "sz", "268"],
    ["Ethiopia", "et", "251"],
    ["Falkland Islands (Islas Malvinas)", "fk", "500"],
    ["Faroe Islands (Føroyar)", "fo", "298"],
    ["Fiji", "fj", "679"],
    ["Finland (Suomi)", "fi", "358", 0],
    ["France", "fr", "33"],
    ["French Guiana (Guyane française)", "gf", "594"],
    ["French Polynesia (Polynésie française)", "pf", "689"],
    ["Gabon", "ga", "241"],
    ["Gambia", "gm", "220"],
    ["Georgia (საქართველო)", "ge", "995"],
    ["Germany (Deutschland)", "de", "49"],
    ["Ghana (Gaana)", "gh", "233"],
    ["Gibraltar", "gi", "350"],
    ["Greece (Ελλάδα)", "gr", "30"],
    ["Greenland (Kalaallit Nunaat)", "gl", "299"],
    ["Grenada", "gd", "1", 14, ["473"]],
    ["Guadeloupe", "gp", "590", 0],
    ["Guam", "gu", "1", 15, ["671"]],
    ["Guatemala", "gt", "502"],
    ["Guernsey", "gg", "44", 1, ["1481", "7781", "7839", "7911"]],
    ["Guinea (Guinée)", "gn", "224"],
    ["Guinea-Bissau (Guiné Bissau)", "gw", "245"],
    ["Guyana", "gy", "592"],
    ["Haiti", "ht", "509"],
    ["Honduras", "hn", "504"],
    ["Hong Kong (香港)", "hk", "852"],
    ["Hungary (Magyarország)", "hu", "36"],
    ["Iceland (Ísland)", "is", "354"],
    ["India (भारत)", "in", "91"],
    ["Indonesia", "id", "62"],
    ["Iran (‫ایران‬‎)", "ir", "98"],
    ["Iraq (‫العراق‬‎)", "iq", "964"],
    ["Ireland", "ie", "353"],
    ["Isle of Man", "im", "44", 2, ["1624", "74576", "7524", "7924", "7624"]],
    ["Israel (‫ישראל‬‎)", "il", "972"],
    ["Italy (Italia)", "it", "39", 0],
    ["Jamaica", "jm", "1", 4, ["876", "658"]],
    ["Japan (日本)", "jp", "81"],
    ["Jersey", "je", "44", 3, ["1534", "7509", "7700", "7797", "7829", "7937"]],
    ["Jordan (‫الأردن‬‎)", "jo", "962"],
    ["Kazakhstan (Казахстан)", "kz", "7", 1, ["33", "7"]],
    ["Kenya", "ke", "254"],
    ["Kiribati", "ki", "686"],
    ["Kosovo", "xk", "383"],
    ["Kuwait (‫الكويت‬‎)", "kw", "965"],
    ["Kyrgyzstan (Кыргызстан)", "kg", "996"],
    ["Laos (ລາວ)", "la", "856"],
    ["Latvia (Latvija)", "lv", "371"],
    ["Lebanon (‫لبنان‬‎)", "lb", "961"],
    ["Lesotho", "ls", "266"],
    ["Liberia", "lr", "231"],
    ["Libya (‫ليبيا‬‎)", "ly", "218"],
    ["Liechtenstein", "li", "423"],
    ["Lithuania (Lietuva)", "lt", "370"],
    ["Luxembourg", "lu", "352"],
    ["Macau (澳門)", "mo", "853"],
    ["Madagascar (Madagasikara)", "mg", "261"],
    ["Malawi", "mw", "265"],
    ["Malaysia", "my", "60"],
    ["Maldives", "mv", "960"],
    ["Mali", "ml", "223"],
    ["Malta", "mt", "356"],
    ["Marshall Islands", "mh", "692"],
    ["Martinique", "mq", "596"],
    ["Mauritania (‫موريتانيا‬‎)", "mr", "222"],
    ["Mauritius (Moris)", "mu", "230"],
    ["Mayotte", "yt", "262", 1, ["269", "639"]],
    ["Mexico (México)", "mx", "52"],
    ["Micronesia", "fm", "691"],
    ["Moldova (Republica Moldova)", "md", "373"],
    ["Monaco", "mc", "377"],
    ["Mongolia (Монгол)", "mn", "976"],
    ["Montenegro (Crna Gora)", "me", "382"],
    ["Montserrat", "ms", "1", 16, ["664"]],
    ["Morocco (‫المغرب‬‎)", "ma", "212", 0],
    ["Mozambique (Moçambique)", "mz", "258"],
    ["Myanmar (Burma) (မြန်မာ)", "mm", "95"],
    ["Namibia (Namibië)", "na", "264"],
    ["Nauru", "nr", "674"],
    ["Nepal (नेपाल)", "np", "977"],
    ["Netherlands (Nederland)", "nl", "31"],
    ["New Caledonia (Nouvelle-Calédonie)", "nc", "687"],
    ["New Zealand", "nz", "64"],
    ["Nicaragua", "ni", "505"],
    ["Niger (Nijar)", "ne", "227"],
    ["Nigeria", "ng", "234"],
    ["Niue", "nu", "683"],
    ["Norfolk Island", "nf", "672"],
    ["North Korea (조선 민주주의 인민 공화국)", "kp", "850"],
    ["North Macedonia (Северна Македонија)", "mk", "389"],
    ["Northern Mariana Islands", "mp", "1", 17, ["670"]],
    ["Norway (Norge)", "no", "47", 0],
    ["Oman (‫عُمان‬‎)", "om", "968"],
    ["Pakistan (‫پاکستان‬‎)", "pk", "92"],
    ["Palau", "pw", "680"],
    ["Palestine (‫فلسطين‬‎)", "ps", "970"],
    ["Panama (Panamá)", "pa", "507"],
    ["Papua New Guinea", "pg", "675"],
    ["Paraguay", "py", "595"],
    ["Peru (Perú)", "pe", "51"],
    ["Philippines", "ph", "63"],
    ["Poland (Polska)", "pl", "48"],
    ["Portugal", "pt", "351"],
    ["Puerto Rico", "pr", "1", 3, ["787", "939"]],
    ["Qatar (‫قطر‬‎)", "qa", "974"],
    ["Réunion (La Réunion)", "re", "262", 0],
    ["Romania (România)", "ro", "40"],
    ["Russia (Россия)", "ru", "7", 0],
    ["Rwanda", "rw", "250"],
    ["Saint Barthélemy", "bl", "590", 1],
    ["Saint Helena", "sh", "290"],
    ["Saint Kitts and Nevis", "kn", "1", 18, ["869"]],
    ["Saint Lucia", "lc", "1", 19, ["758"]],
    ["Saint Martin (Saint-Martin (partie française))", "mf", "590", 2],
    ["Saint Pierre and Miquelon (Saint-Pierre-et-Miquelon)", "pm", "508"],
    ["Saint Vincent and the Grenadines", "vc", "1", 20, ["784"]],
    ["Samoa", "ws", "685"],
    ["San Marino", "sm", "378"],
    ["São Tomé and Príncipe (São Tomé e Príncipe)", "st", "239"],
    ["Saudi Arabia (‫المملكة العربية السعودية‬‎)", "sa", "966"],
    ["Senegal (Sénégal)", "sn", "221"],
    ["Serbia (Србија)", "rs", "381"],
    ["Seychelles", "sc", "248"],
    ["Sierra Leone", "sl", "232"],
    ["Singapore", "sg", "65"],
    ["Sint Maarten", "sx", "1", 21, ["721"]],
    ["Slovakia (Slovensko)", "sk", "421"],
    ["Slovenia (Slovenija)", "si", "386"],
    ["Solomon Islands", "sb", "677"],
    ["Somalia (Soomaaliya)", "so", "252"],
    ["South Africa", "za", "27"],
    ["South Korea (대한민국)", "kr", "82"],
    ["South Sudan (‫جنوب السودان‬‎)", "ss", "211"],
    ["Spain (España)", "es", "34"],
    ["Sri Lanka (ශ්‍රී ලංකාව)", "lk", "94"],
    ["Sudan (‫السودان‬‎)", "sd", "249"],
    ["Suriname", "sr", "597"],
    ["Svalbard and Jan Mayen", "sj", "47", 1, ["79"]],
    ["Sweden (Sverige)", "se", "46"],
    ["Switzerland (Schweiz)", "ch", "41"],
    ["Syria (‫سوريا‬‎)", "sy", "963"],
    ["Taiwan (台灣)", "tw", "886"],
    ["Tajikistan", "tj", "992"],
    ["Tanzania", "tz", "255"],
    ["Thailand (ไทย)", "th", "66"],
    ["Timor-Leste", "tl", "670"],
    ["Togo", "tg", "228"],
    ["Tokelau", "tk", "690"],
    ["Tonga", "to", "676"],
    ["Trinidad and Tobago", "tt", "1", 22, ["868"]],
    ["Tunisia (‫تونس‬‎)", "tn", "216"],
    ["Turkey (Türkiye)", "tr", "90"],
    ["Turkmenistan", "tm", "993"],
    ["Turks and Caicos Islands", "tc", "1", 23, ["649"]],
    ["Tuvalu", "tv", "688"],
    ["U.S. Virgin Islands", "vi", "1", 24, ["340"]],
    ["Uganda", "ug", "256"],
    ["Ukraine (Україна)", "ua", "380"],
    ["United Arab Emirates (‫الإمارات العربية المتحدة‬‎)", "ae", "971"],
    ["United Kingdom", "gb", "44", 0],
    ["United States", "us", "1", 0],
    ["Uruguay", "uy", "598"],
    ["Uzbekistan (Oʻzbekiston)", "uz", "998"],
    ["Vanuatu", "vu", "678"],
    ["Vatican City (Città del Vaticano)", "va", "39", 1, ["06698"]],
    ["Venezuela", "ve", "58"],
    ["Vietnam (Việt Nam)", "vn", "84"],
    ["Wallis and Futuna (Wallis-et-Futuna)", "wf", "681"],
    ["Western Sahara (‫الصحراء الغربية‬‎)", "eh", "212", 1, ["5288", "5289"]],
    ["Yemen (‫اليمن‬‎)", "ye", "967"],
    ["Zambia", "zm", "260"],
    ["Zimbabwe", "zw", "263"],
    ["Åland Islands", "ax", "358", 1, ["18"]]
  ],
  q_ = H_.map(([e, t, s, o = 0, i = null]) => ({
    name: e,
    iso2: t.toUpperCase(),
    dialCode: s,
    priority: o,
    areaCodes: i
  }));

function W_() {
  return fetch("https://ip2c.org/s").then(e => e.text()).then(e => {
    const t = (e || "").toString();
    if (!t || t[0] !== "1") throw new Error("unable to fetch the country");
    return t.substr(2, 2)
  })
}

function z_(e, t) {
  if (e.setSelectionRange) e.focus(), e.setSelectionRange(t, t);
  else if (e.createTextRange) {
    const s = e.createTextRange();
    s.collapse(!0), s.moveEnd("character", t), s.moveStart("character", t), s.select()
  }
}
const K_ = [{
    name: "allCountries",
    type: Array,
    default: q_,
    description: "All countries that are used in <code>libphonenumber-js</code>, can be overridden by this prop",
    inDemo: !1
  }, {
    name: "autoFormat",
    type: Boolean,
    default: !0,
    description: "Auto update the input to the formatted phone number when it's valid",
    inDemo: !0
  }, {
    name: "customValidate",
    type: [Boolean, RegExp],
    default: !1,
    description: "Custom validation RegExp for input",
    inDemo: !1
  }, {
    name: "defaultCountry",
    default: "",
    type: [String, Number],
    description: "Default country (by iso2 or dialCode), will override the country fetched from IP address of user",
    inDemo: !1
  }, {
    name: "disabled",
    default: !1,
    type: Boolean,
    description: "Disable <code>vue-tel-input</code>, including the input & flag dropdown",
    inDemo: !1
  }, {
    name: "autoDefaultCountry",
    default: !0,
    type: Boolean,
    description: "To fetch default country based on IP address of user",
    inDemo: !1
  }, {
    name: "dropdownOptions",
    type: Object,
    description: "Options for dropdown, see below",
    inDemo: !1
  }, {
    name: "dropdownOptions.disabled",
    default: !1,
    type: Boolean,
    description: "Disable dropdown",
    inDemo: !1
  }, {
    name: "dropdownOptions.showDialCodeInList",
    default: !0,
    type: Boolean,
    description: "Show dial code in the dropdown list",
    inDemo: !0
  }, {
    name: "dropdownOptions.showDialCodeInSelection",
    default: !1,
    type: Boolean,
    description: "Show dial code in the dropdown selection",
    inDemo: !0
  }, {
    name: "dropdownOptions.showFlags",
    default: !0,
    type: Boolean,
    description: "Show flags in the dropdown selection and list",
    inDemo: !0
  }, {
    name: "dropdownOptions.showSearchBox",
    default: !1,
    type: Boolean,
    description: "Show country search box",
    inDemo: !0
  }, {
    name: "dropdownOptions.tabindex",
    default: 0,
    type: Number,
    description: "Native dropdown <code>tabindex</code> attribute",
    inDemo: !1
  }, {
    name: "ignoredCountries",
    default: [],
    type: Array,
    description: "List of countries will NOT be shown on the dropdown",
    inDemo: !1
  }, {
    name: "inputOptions",
    type: Object,
    description: "Options for input, see below",
    inDemo: !1
  }, {
    name: "inputOptions.autocomplete",
    type: String,
    default: "on",
    description: "Native input <code>autocomplete</code> attribute",
    inDemo: !1
  }, {
    name: "inputOptions.autofocus",
    type: Boolean,
    default: !1,
    description: "Native input <code>autofocus</code> attribute",
    inDemo: !1
  }, {
    name: "inputOptions.aria-describedby",
    default: "",
    type: String,
    description: "Native input <code>aria-describedby</code> attribute",
    inDemo: !1
  }, {
    name: "inputOptions.id",
    default: "",
    type: String,
    description: "Native input <code>id</code> attribute",
    inDemo: !1
  }, {
    name: "inputOptions.maxlength",
    default: 25,
    type: Number,
    description: "Native input <code>maxlength</code> attribute",
    inDemo: !1
  }, {
    name: "inputOptions.name",
    default: "telephone",
    type: String,
    description: "Native input <code>name</code> attribute",
    inDemo: !1
  }, {
    name: "inputOptions.showDialCode",
    default: !1,
    type: Boolean,
    description: "Show dial code in input",
    inDemo: !1
  }, {
    name: "inputOptions.placeholder",
    default: "Enter a phone number",
    type: String,
    description: "Placeholder for the input",
    inDemo: !1
  }, {
    name: "inputOptions.readonly",
    default: !1,
    type: Boolean,
    description: "Native input <code>readonly</code> attribute",
    inDemo: !1
  }, {
    name: "inputOptions.required",
    default: !1,
    type: Boolean,
    description: "Native input <code>required</code> attribute",
    inDemo: !1
  }, {
    name: "inputOptions.tabindex",
    default: 0,
    type: Number,
    description: "Native input <code>tabindex</code> attribute",
    inDemo: !1
  }, {
    name: "inputOptions.type",
    default: "tel",
    type: String,
    description: "Native input <code>type</code> attribute",
    inDemo: !1
  }, {
    name: "inputOptions.styleClasses",
    default: "",
    type: [String, Array, Object],
    description: "Custom classes for the <code>input</code>",
    inDemo: !1
  }, {
    name: "invalidMsg",
    default: "",
    type: String,
    description: "",
    inDemo: !1
  }, {
    name: "mode",
    default: "auto",
    type: String,
    description: "Allowed values: <code>'auto'</code> (Default set by phone),  <code>'international'</code> (Format number with the dial code i.e. + 61), <code>'national'</code> (Format number without dial code i.e. 0321232)",
    inDemo: !0,
    options: ["auto", "national", "international"]
  }, {
    name: "onlyCountries",
    default: [],
    type: Array,
    description: "List of countries will be shown on the dropdown",
    inDemo: !1
  }, {
    name: "preferredCountries",
    default: [],
    type: Array,
    description: "Preferred countries list, will be on top of the dropdown",
    inDemo: !1
  }, {
    name: "styleClasses",
    default: "",
    type: [String, Array, Object],
    description: "Custom classes for the wrapper",
    inDemo: !1
  }, {
    name: "validCharactersOnly",
    default: !1,
    type: Boolean,
    description: "Only allow valid characters in a phone number (will also verify in <code>mounted</code>, so phone number with invalid characters will be shown as an empty string)",
    inDemo: !1
  }],
  Nm = [...K_].reduce((e, t) => {
    if (t.name.includes(".")) {
      const [s, o] = t.name.split(".");
      e[s] ? Object.assign(e[s], {
        [o]: t.default
      }) : Object.assign(e, {
        [s]: {
          [o]: t.default
        }
      })
    } else Object.assign(e, {
      [t.name]: t.default
    });
    return e
  }, {}),
  Bd = {
    options: {
      ...Nm
    }
  },
  G_ = {
    version: 4,
    country_calling_codes: {
      1: ["US", "AG", "AI", "AS", "BB", "BM", "BS", "CA", "DM", "DO", "GD", "GU", "JM", "KN", "KY", "LC", "MP", "MS", "PR", "SX", "TC", "TT", "VC", "VG", "VI"],
      7: ["RU", "KZ"],
      20: ["EG"],
      27: ["ZA"],
      30: ["GR"],
      31: ["NL"],
      32: ["BE"],
      33: ["FR"],
      34: ["ES"],
      36: ["HU"],
      39: ["IT", "VA"],
      40: ["RO"],
      41: ["CH"],
      43: ["AT"],
      44: ["GB", "GG", "IM", "JE"],
      45: ["DK"],
      46: ["SE"],
      47: ["NO", "SJ"],
      48: ["PL"],
      49: ["DE"],
      51: ["PE"],
      52: ["MX"],
      53: ["CU"],
      54: ["AR"],
      55: ["BR"],
      56: ["CL"],
      57: ["CO"],
      58: ["VE"],
      60: ["MY"],
      61: ["AU", "CC", "CX"],
      62: ["ID"],
      63: ["PH"],
      64: ["NZ"],
      65: ["SG"],
      66: ["TH"],
      81: ["JP"],
      82: ["KR"],
      84: ["VN"],
      86: ["CN"],
      90: ["TR"],
      91: ["IN"],
      92: ["PK"],
      93: ["AF"],
      94: ["LK"],
      95: ["MM"],
      98: ["IR"],
      211: ["SS"],
      212: ["MA", "EH"],
      213: ["DZ"],
      216: ["TN"],
      218: ["LY"],
      220: ["GM"],
      221: ["SN"],
      222: ["MR"],
      223: ["ML"],
      224: ["GN"],
      225: ["CI"],
      226: ["BF"],
      227: ["NE"],
      228: ["TG"],
      229: ["BJ"],
      230: ["MU"],
      231: ["LR"],
      232: ["SL"],
      233: ["GH"],
      234: ["NG"],
      235: ["TD"],
      236: ["CF"],
      237: ["CM"],
      238: ["CV"],
      239: ["ST"],
      240: ["GQ"],
      241: ["GA"],
      242: ["CG"],
      243: ["CD"],
      244: ["AO"],
      245: ["GW"],
      246: ["IO"],
      247: ["AC"],
      248: ["SC"],
      249: ["SD"],
      250: ["RW"],
      251: ["ET"],
      252: ["SO"],
      253: ["DJ"],
      254: ["KE"],
      255: ["TZ"],
      256: ["UG"],
      257: ["BI"],
      258: ["MZ"],
      260: ["ZM"],
      261: ["MG"],
      262: ["RE", "YT"],
      263: ["ZW"],
      264: ["NA"],
      265: ["MW"],
      266: ["LS"],
      267: ["BW"],
      268: ["SZ"],
      269: ["KM"],
      290: ["SH", "TA"],
      291: ["ER"],
      297: ["AW"],
      298: ["FO"],
      299: ["GL"],
      350: ["GI"],
      351: ["PT"],
      352: ["LU"],
      353: ["IE"],
      354: ["IS"],
      355: ["AL"],
      356: ["MT"],
      357: ["CY"],
      358: ["FI", "AX"],
      359: ["BG"],
      370: ["LT"],
      371: ["LV"],
      372: ["EE"],
      373: ["MD"],
      374: ["AM"],
      375: ["BY"],
      376: ["AD"],
      377: ["MC"],
      378: ["SM"],
      380: ["UA"],
      381: ["RS"],
      382: ["ME"],
      383: ["XK"],
      385: ["HR"],
      386: ["SI"],
      387: ["BA"],
      389: ["MK"],
      420: ["CZ"],
      421: ["SK"],
      423: ["LI"],
      500: ["FK"],
      501: ["BZ"],
      502: ["GT"],
      503: ["SV"],
      504: ["HN"],
      505: ["NI"],
      506: ["CR"],
      507: ["PA"],
      508: ["PM"],
      509: ["HT"],
      590: ["GP", "BL", "MF"],
      591: ["BO"],
      592: ["GY"],
      593: ["EC"],
      594: ["GF"],
      595: ["PY"],
      596: ["MQ"],
      597: ["SR"],
      598: ["UY"],
      599: ["CW", "BQ"],
      670: ["TL"],
      672: ["NF"],
      673: ["BN"],
      674: ["NR"],
      675: ["PG"],
      676: ["TO"],
      677: ["SB"],
      678: ["VU"],
      679: ["FJ"],
      680: ["PW"],
      681: ["WF"],
      682: ["CK"],
      683: ["NU"],
      685: ["WS"],
      686: ["KI"],
      687: ["NC"],
      688: ["TV"],
      689: ["PF"],
      690: ["TK"],
      691: ["FM"],
      692: ["MH"],
      850: ["KP"],
      852: ["HK"],
      853: ["MO"],
      855: ["KH"],
      856: ["LA"],
      880: ["BD"],
      886: ["TW"],
      960: ["MV"],
      961: ["LB"],
      962: ["JO"],
      963: ["SY"],
      964: ["IQ"],
      965: ["KW"],
      966: ["SA"],
      967: ["YE"],
      968: ["OM"],
      970: ["PS"],
      971: ["AE"],
      972: ["IL"],
      973: ["BH"],
      974: ["QA"],
      975: ["BT"],
      976: ["MN"],
      977: ["NP"],
      992: ["TJ"],
      993: ["TM"],
      994: ["AZ"],
      995: ["GE"],
      996: ["KG"],
      998: ["UZ"]
    },
    countries: {
      AC: ["247", "00", "(?:[01589]\\d|[46])\\d{4}", [5, 6]],
      AD: ["376", "00", "(?:1|6\\d)\\d{7}|[135-9]\\d{5}", [6, 8, 9],
        [
          ["(\\d{3})(\\d{3})", "$1 $2", ["[135-9]"]],
          ["(\\d{4})(\\d{4})", "$1 $2", ["1"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["6"]]
        ]
      ],
      AE: ["971", "00", "(?:[4-7]\\d|9[0-689])\\d{7}|800\\d{2,9}|[2-4679]\\d{7}", [5, 6, 7, 8, 9, 10, 11, 12],
        [
          ["(\\d{3})(\\d{2,9})", "$1 $2", ["60|8"]],
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["[236]|[479][2-8]"], "0$1"],
          ["(\\d{3})(\\d)(\\d{5})", "$1 $2 $3", ["[479]"]],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["5"], "0$1"]
        ], "0"
      ],
      AF: ["93", "00", "[2-7]\\d{8}", [9],
        [
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[2-7]"], "0$1"]
        ], "0"
      ],
      AG: ["1", "011", "(?:268|[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "([457]\\d{6})$|1", "268$1", 0, "268"],
      AI: ["1", "011", "(?:264|[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "([2457]\\d{6})$|1", "264$1", 0, "264"],
      AL: ["355", "00", "(?:700\\d\\d|900)\\d{3}|8\\d{5,7}|(?:[2-5]|6\\d)\\d{7}", [6, 7, 8, 9],
        [
          ["(\\d{3})(\\d{3,4})", "$1 $2", ["80|9"], "0$1"],
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["4[2-6]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["[2358][2-5]|4"], "0$1"],
          ["(\\d{3})(\\d{5})", "$1 $2", ["[23578]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["6"], "0$1"]
        ], "0"
      ],
      AM: ["374", "00", "(?:[1-489]\\d|55|60|77)\\d{6}", [8],
        [
          ["(\\d{3})(\\d{2})(\\d{3})", "$1 $2 $3", ["[89]0"], "0 $1"],
          ["(\\d{3})(\\d{5})", "$1 $2", ["2|3[12]"], "(0$1)"],
          ["(\\d{2})(\\d{6})", "$1 $2", ["1|47"], "(0$1)"],
          ["(\\d{2})(\\d{6})", "$1 $2", ["[3-9]"], "0$1"]
        ], "0"
      ],
      AO: ["244", "00", "[29]\\d{8}", [9],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[29]"]]
        ]
      ],
      AR: ["54", "00", "(?:11|[89]\\d\\d)\\d{8}|[2368]\\d{9}", [10, 11],
        [
          ["(\\d{4})(\\d{2})(\\d{4})", "$1 $2-$3", ["2(?:2[024-9]|3[0-59]|47|6[245]|9[02-8])|3(?:3[28]|4[03-9]|5[2-46-8]|7[1-578]|8[2-9])", "2(?:[23]02|6(?:[25]|4[6-8])|9(?:[02356]|4[02568]|72|8[23]))|3(?:3[28]|4(?:[04679]|3[5-8]|5[4-68]|8[2379])|5(?:[2467]|3[237]|8[2-5])|7[1-578]|8(?:[2469]|3[2578]|5[4-8]|7[36-8]|8[5-8]))|2(?:2[24-9]|3[1-59]|47)", "2(?:[23]02|6(?:[25]|4(?:64|[78]))|9(?:[02356]|4(?:[0268]|5[2-6])|72|8[23]))|3(?:3[28]|4(?:[04679]|3[78]|5(?:4[46]|8)|8[2379])|5(?:[2467]|3[237]|8[23])|7[1-578]|8(?:[2469]|3[278]|5[56][46]|86[3-6]))|2(?:2[24-9]|3[1-59]|47)|38(?:[58][78]|7[378])|3(?:4[35][56]|58[45]|8(?:[38]5|54|76))[4-6]", "2(?:[23]02|6(?:[25]|4(?:64|[78]))|9(?:[02356]|4(?:[0268]|5[2-6])|72|8[23]))|3(?:3[28]|4(?:[04679]|3(?:5(?:4[0-25689]|[56])|[78])|58|8[2379])|5(?:[2467]|3[237]|8(?:[23]|4(?:[45]|60)|5(?:4[0-39]|5|64)))|7[1-578]|8(?:[2469]|3[278]|54(?:4|5[13-7]|6[89])|86[3-6]))|2(?:2[24-9]|3[1-59]|47)|38(?:[58][78]|7[378])|3(?:454|85[56])[46]|3(?:4(?:36|5[56])|8(?:[38]5|76))[4-6]"], "0$1", 1],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2-$3", ["1"], "0$1", 1],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1-$2-$3", ["[68]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2-$3", ["[23]"], "0$1", 1],
          ["(\\d)(\\d{4})(\\d{2})(\\d{4})", "$2 15-$3-$4", ["9(?:2[2-469]|3[3-578])", "9(?:2(?:2[024-9]|3[0-59]|47|6[245]|9[02-8])|3(?:3[28]|4[03-9]|5[2-46-8]|7[1-578]|8[2-9]))", "9(?:2(?:[23]02|6(?:[25]|4[6-8])|9(?:[02356]|4[02568]|72|8[23]))|3(?:3[28]|4(?:[04679]|3[5-8]|5[4-68]|8[2379])|5(?:[2467]|3[237]|8[2-5])|7[1-578]|8(?:[2469]|3[2578]|5[4-8]|7[36-8]|8[5-8])))|92(?:2[24-9]|3[1-59]|47)", "9(?:2(?:[23]02|6(?:[25]|4(?:64|[78]))|9(?:[02356]|4(?:[0268]|5[2-6])|72|8[23]))|3(?:3[28]|4(?:[04679]|3[78]|5(?:4[46]|8)|8[2379])|5(?:[2467]|3[237]|8[23])|7[1-578]|8(?:[2469]|3[278]|5(?:[56][46]|[78])|7[378]|8(?:6[3-6]|[78]))))|92(?:2[24-9]|3[1-59]|47)|93(?:4[35][56]|58[45]|8(?:[38]5|54|76))[4-6]", "9(?:2(?:[23]02|6(?:[25]|4(?:64|[78]))|9(?:[02356]|4(?:[0268]|5[2-6])|72|8[23]))|3(?:3[28]|4(?:[04679]|3(?:5(?:4[0-25689]|[56])|[78])|5(?:4[46]|8)|8[2379])|5(?:[2467]|3[237]|8(?:[23]|4(?:[45]|60)|5(?:4[0-39]|5|64)))|7[1-578]|8(?:[2469]|3[278]|5(?:4(?:4|5[13-7]|6[89])|[56][46]|[78])|7[378]|8(?:6[3-6]|[78]))))|92(?:2[24-9]|3[1-59]|47)|93(?:4(?:36|5[56])|8(?:[38]5|76))[4-6]"], "0$1", 0, "$1 $2 $3-$4"],
          ["(\\d)(\\d{2})(\\d{4})(\\d{4})", "$2 15-$3-$4", ["91"], "0$1", 0, "$1 $2 $3-$4"],
          ["(\\d{3})(\\d{3})(\\d{5})", "$1-$2-$3", ["8"], "0$1"],
          ["(\\d)(\\d{3})(\\d{3})(\\d{4})", "$2 15-$3-$4", ["9"], "0$1", 0, "$1 $2 $3-$4"]
        ], "0", 0, "0?(?:(11|2(?:2(?:02?|[13]|2[13-79]|4[1-6]|5[2457]|6[124-8]|7[1-4]|8[13-6]|9[1267])|3(?:02?|1[467]|2[03-6]|3[13-8]|[49][2-6]|5[2-8]|[67])|4(?:7[3-578]|9)|6(?:[0136]|2[24-6]|4[6-8]?|5[15-8])|80|9(?:0[1-3]|[19]|2\\d|3[1-6]|4[02568]?|5[2-4]|6[2-46]|72?|8[23]?))|3(?:3(?:2[79]|6|8[2578])|4(?:0[0-24-9]|[12]|3[5-8]?|4[24-7]|5[4-68]?|6[02-9]|7[126]|8[2379]?|9[1-36-8])|5(?:1|2[1245]|3[237]?|4[1-46-9]|6[2-4]|7[1-6]|8[2-5]?)|6[24]|7(?:[069]|1[1568]|2[15]|3[145]|4[13]|5[14-8]|7[2-57]|8[126])|8(?:[01]|2[15-7]|3[2578]?|4[13-6]|5[4-8]?|6[1-357-9]|7[36-8]?|8[5-8]?|9[124])))15)?", "9$1"
      ],
      AS: ["1", "011", "(?:[58]\\d\\d|684|900)\\d{7}", [10], 0, "1", 0, "([267]\\d{6})$|1", "684$1", 0, "684"],
      AT: ["43", "00", "1\\d{3,12}|2\\d{6,12}|43(?:(?:0\\d|5[02-9])\\d{3,9}|2\\d{4,5}|[3467]\\d{4}|8\\d{4,6}|9\\d{4,7})|5\\d{4,12}|8\\d{7,12}|9\\d{8,12}|(?:[367]\\d|4[0-24-9])\\d{4,11}", [4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
        [
          ["(\\d)(\\d{3,12})", "$1 $2", ["1(?:11|[2-9])"], "0$1"],
          ["(\\d{3})(\\d{2})", "$1 $2", ["517"], "0$1"],
          ["(\\d{2})(\\d{3,5})", "$1 $2", ["5[079]"], "0$1"],
          ["(\\d{3})(\\d{3,10})", "$1 $2", ["(?:31|4)6|51|6(?:5[0-3579]|[6-9])|7(?:20|32|8)|[89]"], "0$1"],
          ["(\\d{4})(\\d{3,9})", "$1 $2", ["[2-467]|5[2-6]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["5"], "0$1"],
          ["(\\d{2})(\\d{4})(\\d{4,7})", "$1 $2 $3", ["5"], "0$1"]
        ], "0"
      ],
      AU: ["61", "001[14-689]|14(?:1[14]|34|4[17]|[56]6|7[47]|88)0011", "1(?:[0-79]\\d{7}(?:\\d(?:\\d{2})?)?|8[0-24-9]\\d{7})|[2-478]\\d{8}|1\\d{4,7}", [5, 6, 7, 8, 9, 10, 12],
        [
          ["(\\d{2})(\\d{3,4})", "$1 $2", ["16"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{2,4})", "$1 $2 $3", ["16"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["14|4"], "0$1"],
          ["(\\d)(\\d{4})(\\d{4})", "$1 $2 $3", ["[2378]"], "(0$1)"],
          ["(\\d{4})(\\d{3})(\\d{3})", "$1 $2 $3", ["1(?:30|[89])"]]
        ], "0", 0, "(183[12])|0", 0, 0, 0, [
          ["(?:(?:2(?:[0-26-9]\\d|3[0-8]|4[02-9]|5[0135-9])|3(?:[0-3589]\\d|4[0-578]|6[1-9]|7[0-35-9])|7(?:[013-57-9]\\d|2[0-8]))\\d{3}|8(?:51(?:0(?:0[03-9]|[12479]\\d|3[2-9]|5[0-8]|6[1-9]|8[0-7])|1(?:[0235689]\\d|1[0-69]|4[0-589]|7[0-47-9])|2(?:0[0-79]|[18][13579]|2[14-9]|3[0-46-9]|[4-6]\\d|7[89]|9[0-4]))|(?:6[0-8]|[78]\\d)\\d{3}|9(?:[02-9]\\d{3}|1(?:(?:[0-58]\\d|6[0135-9])\\d|7(?:0[0-24-9]|[1-9]\\d)|9(?:[0-46-9]\\d|5[0-79])))))\\d{3}", [9]],
          ["4(?:(?:79|94)[01]|83[0-389])\\d{5}|4(?:[0-3]\\d|4[047-9]|5[0-25-9]|6[0-26-9]|7[02-8]|8[0-24-9]|9[0-37-9])\\d{6}", [9]],
          ["180(?:0\\d{3}|2)\\d{3}", [7, 10]],
          ["190[0-26]\\d{6}", [10]], 0, 0, 0, ["163\\d{2,6}", [5, 6, 7, 8, 9]],
          ["14(?:5(?:1[0458]|[23][458])|71\\d)\\d{4}", [9]],
          ["13(?:00\\d{6}(?:\\d{2})?|45[0-4]\\d{3})|13\\d{4}", [6, 8, 10, 12]]
        ], "0011"
      ],
      AW: ["297", "00", "(?:[25-79]\\d\\d|800)\\d{4}", [7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[25-9]"]]
        ]
      ],
      AX: ["358", "00|99(?:[01469]|5(?:[14]1|3[23]|5[59]|77|88|9[09]))", "2\\d{4,9}|35\\d{4,5}|(?:60\\d\\d|800)\\d{4,6}|7\\d{5,11}|(?:[14]\\d|3[0-46-9]|50)\\d{4,8}", [5, 6, 7, 8, 9, 10, 11, 12], 0, "0", 0, 0, 0, 0, "18", 0, "00"],
      AZ: ["994", "00", "365\\d{6}|(?:[124579]\\d|60|88)\\d{7}", [9],
        [
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["90"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["1[28]|2|365|46", "1[28]|2|365[45]|46", "1[28]|2|365(?:4|5[02])|46"], "(0$1)"],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[13-9]"], "0$1"]
        ], "0"
      ],
      BA: ["387", "00", "6\\d{8}|(?:[35689]\\d|49|70)\\d{6}", [8, 9],
        [
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["6[1-3]|[7-9]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2-$3", ["[3-5]|6[56]"], "0$1"],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{3})", "$1 $2 $3 $4", ["6"], "0$1"]
        ], "0"
      ],
      BB: ["1", "011", "(?:246|[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "([2-9]\\d{6})$|1", "246$1", 0, "246"],
      BD: ["880", "00", "[1-469]\\d{9}|8[0-79]\\d{7,8}|[2-79]\\d{8}|[2-9]\\d{7}|[3-9]\\d{6}|[57-9]\\d{5}", [6, 7, 8, 9, 10],
        [
          ["(\\d{2})(\\d{4,6})", "$1-$2", ["31[5-8]|[459]1"], "0$1"],
          ["(\\d{3})(\\d{3,7})", "$1-$2", ["3(?:[67]|8[013-9])|4(?:6[168]|7|[89][18])|5(?:6[128]|9)|6(?:[15]|28|4[14])|7[2-589]|8(?:0[014-9]|[12])|9[358]|(?:3[2-5]|4[235]|5[2-578]|6[0389]|76|8[3-7]|9[24])1|(?:44|66)[01346-9]"], "0$1"],
          ["(\\d{4})(\\d{3,6})", "$1-$2", ["[13-9]|22"], "0$1"],
          ["(\\d)(\\d{7,8})", "$1-$2", ["2"], "0$1"]
        ], "0"
      ],
      BE: ["32", "00", "4\\d{8}|[1-9]\\d{7}", [8, 9],
        [
          ["(\\d{3})(\\d{2})(\\d{3})", "$1 $2 $3", ["(?:80|9)0"], "0$1"],
          ["(\\d)(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[239]|4[23]"], "0$1"],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[15-8]"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["4"], "0$1"]
        ], "0"
      ],
      BF: ["226", "00", "[025-7]\\d{7}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[025-7]"]]
        ]
      ],
      BG: ["359", "00", "00800\\d{7}|[2-7]\\d{6,7}|[89]\\d{6,8}|2\\d{5}", [6, 7, 8, 9, 12],
        [
          ["(\\d)(\\d)(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["2"], "0$1"],
          ["(\\d{3})(\\d{4})", "$1 $2", ["43[1-6]|70[1-9]"], "0$1"],
          ["(\\d)(\\d{3})(\\d{3,4})", "$1 $2 $3", ["2"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{2,3})", "$1 $2 $3", ["[356]|4[124-7]|7[1-9]|8[1-6]|9[1-7]"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{3})", "$1 $2 $3", ["(?:70|8)0"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{2})", "$1 $2 $3", ["43[1-7]|7"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[48]|9[08]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["9"], "0$1"]
        ], "0"
      ],
      BH: ["973", "00", "[136-9]\\d{7}", [8],
        [
          ["(\\d{4})(\\d{4})", "$1 $2", ["[13679]|8[02-4679]"]]
        ]
      ],
      BI: ["257", "00", "(?:[267]\\d|31)\\d{6}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[2367]"]]
        ]
      ],
      BJ: ["229", "00", "[24-689]\\d{7}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[24-689]"]]
        ]
      ],
      BL: ["590", "00", "590\\d{6}|(?:69|80|9\\d)\\d{7}", [9], 0, "0", 0, 0, 0, 0, 0, [
        ["590(?:2[7-9]|3[3-7]|5[12]|87)\\d{4}"],
        ["69(?:0\\d\\d|1(?:2[2-9]|3[0-5]))\\d{4}"],
        ["80[0-5]\\d{6}"], 0, 0, 0, 0, 0, ["9(?:(?:395|76[018])\\d|475[0-5])\\d{4}"]
      ]],
      BM: ["1", "011", "(?:441|[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "([2-9]\\d{6})$|1", "441$1", 0, "441"],
      BN: ["673", "00", "[2-578]\\d{6}", [7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[2-578]"]]
        ]
      ],
      BO: ["591", "00(?:1\\d)?", "(?:[2-467]\\d\\d|8001)\\d{5}", [8, 9],
        [
          ["(\\d)(\\d{7})", "$1 $2", ["[23]|4[46]"]],
          ["(\\d{8})", "$1", ["[67]"]],
          ["(\\d{3})(\\d{2})(\\d{4})", "$1 $2 $3", ["8"]]
        ], "0", 0, "0(1\\d)?"
      ],
      BQ: ["599", "00", "(?:[34]1|7\\d)\\d{5}", [7], 0, 0, 0, 0, 0, 0, "[347]"],
      BR: ["55", "00(?:1[245]|2[1-35]|31|4[13]|[56]5|99)", "(?:[1-46-9]\\d\\d|5(?:[0-46-9]\\d|5[0-46-9]))\\d{8}|[1-9]\\d{9}|[3589]\\d{8}|[34]\\d{7}", [8, 9, 10, 11],
        [
          ["(\\d{4})(\\d{4})", "$1-$2", ["300|4(?:0[02]|37)", "4(?:02|37)0|[34]00"]],
          ["(\\d{3})(\\d{2,3})(\\d{4})", "$1 $2 $3", ["(?:[358]|90)0"], "0$1"],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2-$3", ["(?:[14689][1-9]|2[12478]|3[1-578]|5[13-5]|7[13-579])[2-57]"], "($1)"],
          ["(\\d{2})(\\d{5})(\\d{4})", "$1 $2-$3", ["[16][1-9]|[2-57-9]"], "($1)"]
        ], "0", 0, "(?:0|90)(?:(1[245]|2[1-35]|31|4[13]|[56]5|99)(\\d{10,11}))?", "$2"
      ],
      BS: ["1", "011", "(?:242|[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "([3-8]\\d{6})$|1", "242$1", 0, "242"],
      BT: ["975", "00", "[17]\\d{7}|[2-8]\\d{6}", [7, 8],
        [
          ["(\\d)(\\d{3})(\\d{3})", "$1 $2 $3", ["[2-68]|7[246]"]],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["1[67]|7"]]
        ]
      ],
      BW: ["267", "00", "(?:0800|(?:[37]|800)\\d)\\d{6}|(?:[2-6]\\d|90)\\d{5}", [7, 8, 10],
        [
          ["(\\d{2})(\\d{5})", "$1 $2", ["90"]],
          ["(\\d{3})(\\d{4})", "$1 $2", ["[24-6]|3[15-9]"]],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["[37]"]],
          ["(\\d{4})(\\d{3})(\\d{3})", "$1 $2 $3", ["0"]],
          ["(\\d{3})(\\d{4})(\\d{3})", "$1 $2 $3", ["8"]]
        ]
      ],
      BY: ["375", "810", "(?:[12]\\d|33|44|902)\\d{7}|8(?:0[0-79]\\d{5,7}|[1-7]\\d{9})|8(?:1[0-489]|[5-79]\\d)\\d{7}|8[1-79]\\d{6,7}|8[0-79]\\d{5}|8\\d{5}", [6, 7, 8, 9, 10, 11],
        [
          ["(\\d{3})(\\d{3})", "$1 $2", ["800"], "8 $1"],
          ["(\\d{3})(\\d{2})(\\d{2,4})", "$1 $2 $3", ["800"], "8 $1"],
          ["(\\d{4})(\\d{2})(\\d{3})", "$1 $2-$3", ["1(?:5[169]|6[3-5]|7[179])|2(?:1[35]|2[34]|3[3-5])", "1(?:5[169]|6(?:3[1-3]|4|5[125])|7(?:1[3-9]|7[0-24-6]|9[2-7]))|2(?:1[35]|2[34]|3[3-5])"], "8 0$1"],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2-$3-$4", ["1(?:[56]|7[467])|2[1-3]"], "8 0$1"],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2-$3-$4", ["[1-4]"], "8 0$1"],
          ["(\\d{3})(\\d{3,4})(\\d{4})", "$1 $2 $3", ["[89]"], "8 $1"]
        ], "8", 0, "0|80?", 0, 0, 0, 0, "8~10"
      ],
      BZ: ["501", "00", "(?:0800\\d|[2-8])\\d{6}", [7, 11],
        [
          ["(\\d{3})(\\d{4})", "$1-$2", ["[2-8]"]],
          ["(\\d)(\\d{3})(\\d{4})(\\d{3})", "$1-$2-$3-$4", ["0"]]
        ]
      ],
      CA: ["1", "011", "(?:[2-8]\\d|90)\\d{8}|3\\d{6}", [7, 10], 0, "1", 0, 0, 0, 0, 0, [
        ["(?:2(?:04|[23]6|[48]9|50|63)|3(?:06|43|54|6[578]|82)|4(?:03|1[68]|[26]8|3[178]|50|74)|5(?:06|1[49]|48|79|8[147])|6(?:04|[18]3|39|47|72)|7(?:0[59]|42|53|78|8[02])|8(?:[06]7|19|25|73)|90[25])[2-9]\\d{6}", [10]],
        ["", [10]],
        ["8(?:00|33|44|55|66|77|88)[2-9]\\d{6}", [10]],
        ["900[2-9]\\d{6}", [10]],
        ["52(?:3(?:[2-46-9][02-9]\\d|5(?:[02-46-9]\\d|5[0-46-9]))|4(?:[2-478][02-9]\\d|5(?:[034]\\d|2[024-9]|5[0-46-9])|6(?:0[1-9]|[2-9]\\d)|9(?:[05-9]\\d|2[0-5]|49)))\\d{4}|52[34][2-9]1[02-9]\\d{4}|(?:5(?:00|2[125-9]|33|44|66|77|88)|622)[2-9]\\d{6}", [10]], 0, ["310\\d{4}", [7]], 0, ["600[2-9]\\d{6}", [10]]
      ]],
      CC: ["61", "001[14-689]|14(?:1[14]|34|4[17]|[56]6|7[47]|88)0011", "1(?:[0-79]\\d{8}(?:\\d{2})?|8[0-24-9]\\d{7})|[148]\\d{8}|1\\d{5,7}", [6, 7, 8, 9, 10, 12], 0, "0", 0, "([59]\\d{7})$|0", "8$1", 0, 0, [
        ["8(?:51(?:0(?:02|31|60|89)|1(?:18|76)|223)|91(?:0(?:1[0-2]|29)|1(?:[28]2|50|79)|2(?:10|64)|3(?:[06]8|22)|4[29]8|62\\d|70[23]|959))\\d{3}", [9]],
        ["4(?:(?:79|94)[01]|83[0-389])\\d{5}|4(?:[0-3]\\d|4[047-9]|5[0-25-9]|6[0-26-9]|7[02-8]|8[0-24-9]|9[0-37-9])\\d{6}", [9]],
        ["180(?:0\\d{3}|2)\\d{3}", [7, 10]],
        ["190[0-26]\\d{6}", [10]], 0, 0, 0, 0, ["14(?:5(?:1[0458]|[23][458])|71\\d)\\d{4}", [9]],
        ["13(?:00\\d{6}(?:\\d{2})?|45[0-4]\\d{3})|13\\d{4}", [6, 8, 10, 12]]
      ], "0011"],
      CD: ["243", "00", "[189]\\d{8}|[1-68]\\d{6}", [7, 9],
        [
          ["(\\d{2})(\\d{2})(\\d{3})", "$1 $2 $3", ["88"], "0$1"],
          ["(\\d{2})(\\d{5})", "$1 $2", ["[1-6]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["1"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[89]"], "0$1"]
        ], "0"
      ],
      CF: ["236", "00", "(?:[27]\\d{3}|8776)\\d{4}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[278]"]]
        ]
      ],
      CG: ["242", "00", "222\\d{6}|(?:0\\d|80)\\d{7}", [9],
        [
          ["(\\d)(\\d{4})(\\d{4})", "$1 $2 $3", ["8"]],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[02]"]]
        ]
      ],
      CH: ["41", "00", "8\\d{11}|[2-9]\\d{8}", [9],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["8[047]|90"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[2-79]|81"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4 $5", ["8"], "0$1"]
        ], "0"
      ],
      CI: ["225", "00", "[02]\\d{9}", [10],
        [
          ["(\\d{2})(\\d{2})(\\d)(\\d{5})", "$1 $2 $3 $4", ["2"]],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{4})", "$1 $2 $3 $4", ["0"]]
        ]
      ],
      CK: ["682", "00", "[2-578]\\d{4}", [5],
        [
          ["(\\d{2})(\\d{3})", "$1 $2", ["[2-578]"]]
        ]
      ],
      CL: ["56", "(?:0|1(?:1[0-69]|2[02-5]|5[13-58]|69|7[0167]|8[018]))0", "12300\\d{6}|6\\d{9,10}|[2-9]\\d{8}", [9, 10, 11],
        [
          ["(\\d{5})(\\d{4})", "$1 $2", ["219", "2196"], "($1)"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["44"]],
          ["(\\d)(\\d{4})(\\d{4})", "$1 $2 $3", ["2[1-36]"], "($1)"],
          ["(\\d)(\\d{4})(\\d{4})", "$1 $2 $3", ["9[2-9]"]],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["3[2-5]|[47]|5[1-3578]|6[13-57]|8(?:0[1-9]|[1-9])"], "($1)"],
          ["(\\d{3})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["60|8"]],
          ["(\\d{4})(\\d{3})(\\d{4})", "$1 $2 $3", ["1"]],
          ["(\\d{3})(\\d{3})(\\d{2})(\\d{3})", "$1 $2 $3 $4", ["60"]]
        ]
      ],
      CM: ["237", "00", "[26]\\d{8}|88\\d{6,7}", [8, 9],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["88"]],
          ["(\\d)(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4 $5", ["[26]|88"]]
        ]
      ],
      CN: ["86", "00|1(?:[12]\\d|79)\\d\\d00", "1[127]\\d{8,9}|2\\d{9}(?:\\d{2})?|[12]\\d{6,7}|86\\d{6}|(?:1[03-689]\\d|6)\\d{7,9}|(?:[3-579]\\d|8[0-57-9])\\d{6,9}", [7, 8, 9, 10, 11, 12],
        [
          ["(\\d{2})(\\d{5,6})", "$1 $2", ["(?:10|2[0-57-9])[19]", "(?:10|2[0-57-9])(?:10|9[56])", "10(?:10|9[56])|2[0-57-9](?:100|9[56])"], "0$1"],
          ["(\\d{3})(\\d{5,6})", "$1 $2", ["3(?:[157]|35|49|9[1-68])|4(?:[17]|2[179]|6[47-9]|8[23])|5(?:[1357]|2[37]|4[36]|6[1-46]|80)|6(?:3[1-5]|6[0238]|9[12])|7(?:01|[1579]|2[248]|3[014-9]|4[3-6]|6[023689])|8(?:1[236-8]|2[5-7]|[37]|8[36-8]|9[1-8])|9(?:0[1-3689]|1[1-79]|[379]|4[13]|5[1-5])|(?:4[35]|59|85)[1-9]", "(?:3(?:[157]\\d|35|49|9[1-68])|4(?:[17]\\d|2[179]|[35][1-9]|6[47-9]|8[23])|5(?:[1357]\\d|2[37]|4[36]|6[1-46]|80|9[1-9])|6(?:3[1-5]|6[0238]|9[12])|7(?:01|[1579]\\d|2[248]|3[014-9]|4[3-6]|6[023689])|8(?:1[236-8]|2[5-7]|[37]\\d|5[1-9]|8[36-8]|9[1-8])|9(?:0[1-3689]|1[1-79]|[379]\\d|4[13]|5[1-5]))[19]", "85[23](?:10|95)|(?:3(?:[157]\\d|35|49|9[1-68])|4(?:[17]\\d|2[179]|[35][1-9]|6[47-9]|8[23])|5(?:[1357]\\d|2[37]|4[36]|6[1-46]|80|9[1-9])|6(?:3[1-5]|6[0238]|9[12])|7(?:01|[1579]\\d|2[248]|3[014-9]|4[3-6]|6[023689])|8(?:1[236-8]|2[5-7]|[37]\\d|5[14-9]|8[36-8]|9[1-8])|9(?:0[1-3689]|1[1-79]|[379]\\d|4[13]|5[1-5]))(?:10|9[56])", "85[23](?:100|95)|(?:3(?:[157]\\d|35|49|9[1-68])|4(?:[17]\\d|2[179]|[35][1-9]|6[47-9]|8[23])|5(?:[1357]\\d|2[37]|4[36]|6[1-46]|80|9[1-9])|6(?:3[1-5]|6[0238]|9[12])|7(?:01|[1579]\\d|2[248]|3[014-9]|4[3-6]|6[023689])|8(?:1[236-8]|2[5-7]|[37]\\d|5[14-9]|8[36-8]|9[1-8])|9(?:0[1-3689]|1[1-79]|[379]\\d|4[13]|5[1-5]))(?:100|9[56])"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["(?:4|80)0"]],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2 $3", ["10|2(?:[02-57-9]|1[1-9])", "10|2(?:[02-57-9]|1[1-9])", "10[0-79]|2(?:[02-57-9]|1[1-79])|(?:10|21)8(?:0[1-9]|[1-9])"], "0$1", 1],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["3(?:[3-59]|7[02-68])|4(?:[26-8]|3[3-9]|5[2-9])|5(?:3[03-9]|[468]|7[028]|9[2-46-9])|6|7(?:[0-247]|3[04-9]|5[0-4689]|6[2368])|8(?:[1-358]|9[1-7])|9(?:[013479]|5[1-5])|(?:[34]1|55|79|87)[02-9]"], "0$1", 1],
          ["(\\d{3})(\\d{7,8})", "$1 $2", ["9"]],
          ["(\\d{4})(\\d{3})(\\d{4})", "$1 $2 $3", ["80"], "0$1", 1],
          ["(\\d{3})(\\d{4})(\\d{4})", "$1 $2 $3", ["[3-578]"], "0$1", 1],
          ["(\\d{3})(\\d{4})(\\d{4})", "$1 $2 $3", ["1[3-9]"]],
          ["(\\d{2})(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3 $4", ["[12]"], "0$1", 1]
        ], "0", 0, "(1(?:[12]\\d|79)\\d\\d)|0", 0, 0, 0, 0, "00"
      ],
      CO: ["57", "00(?:4(?:[14]4|56)|[579])", "(?:60\\d\\d|9101)\\d{6}|(?:1\\d|3)\\d{9}", [10, 11],
        [
          ["(\\d{3})(\\d{7})", "$1 $2", ["6"], "($1)"],
          ["(\\d{3})(\\d{7})", "$1 $2", ["3[0-357]|91"]],
          ["(\\d)(\\d{3})(\\d{7})", "$1-$2-$3", ["1"], "0$1", 0, "$1 $2 $3"]
        ], "0", 0, "0([3579]|4(?:[14]4|56))?"
      ],
      CR: ["506", "00", "(?:8\\d|90)\\d{8}|(?:[24-8]\\d{3}|3005)\\d{4}", [8, 10],
        [
          ["(\\d{4})(\\d{4})", "$1 $2", ["[2-7]|8[3-9]"]],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1-$2-$3", ["[89]"]]
        ], 0, 0, "(19(?:0[0-2468]|1[09]|20|66|77|99))"
      ],
      CU: ["53", "119", "[27]\\d{6,7}|[34]\\d{5,7}|63\\d{6}|(?:5|8\\d\\d)\\d{7}", [6, 7, 8, 10],
        [
          ["(\\d{2})(\\d{4,6})", "$1 $2", ["2[1-4]|[34]"], "(0$1)"],
          ["(\\d)(\\d{6,7})", "$1 $2", ["7"], "(0$1)"],
          ["(\\d)(\\d{7})", "$1 $2", ["[56]"], "0$1"],
          ["(\\d{3})(\\d{7})", "$1 $2", ["8"], "0$1"]
        ], "0"
      ],
      CV: ["238", "0", "(?:[2-59]\\d\\d|800)\\d{4}", [7],
        [
          ["(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3", ["[2-589]"]]
        ]
      ],
      CW: ["599", "00", "(?:[34]1|60|(?:7|9\\d)\\d)\\d{5}", [7, 8],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[3467]"]],
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["9[4-8]"]]
        ], 0, 0, 0, 0, 0, "[69]"
      ],
      CX: ["61", "001[14-689]|14(?:1[14]|34|4[17]|[56]6|7[47]|88)0011", "1(?:[0-79]\\d{8}(?:\\d{2})?|8[0-24-9]\\d{7})|[148]\\d{8}|1\\d{5,7}", [6, 7, 8, 9, 10, 12], 0, "0", 0, "([59]\\d{7})$|0", "8$1", 0, 0, [
        ["8(?:51(?:0(?:01|30|59|88)|1(?:17|46|75)|2(?:22|35))|91(?:00[6-9]|1(?:[28]1|49|78)|2(?:09|63)|3(?:12|26|75)|4(?:56|97)|64\\d|7(?:0[01]|1[0-2])|958))\\d{3}", [9]],
        ["4(?:(?:79|94)[01]|83[0-389])\\d{5}|4(?:[0-3]\\d|4[047-9]|5[0-25-9]|6[0-26-9]|7[02-8]|8[0-24-9]|9[0-37-9])\\d{6}", [9]],
        ["180(?:0\\d{3}|2)\\d{3}", [7, 10]],
        ["190[0-26]\\d{6}", [10]], 0, 0, 0, 0, ["14(?:5(?:1[0458]|[23][458])|71\\d)\\d{4}", [9]],
        ["13(?:00\\d{6}(?:\\d{2})?|45[0-4]\\d{3})|13\\d{4}", [6, 8, 10, 12]]
      ], "0011"],
      CY: ["357", "00", "(?:[279]\\d|[58]0)\\d{6}", [8],
        [
          ["(\\d{2})(\\d{6})", "$1 $2", ["[257-9]"]]
        ]
      ],
      CZ: ["420", "00", "(?:[2-578]\\d|60)\\d{7}|9\\d{8,11}", [9],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[2-8]|9[015-7]"]],
          ["(\\d{2})(\\d{3})(\\d{3})(\\d{2})", "$1 $2 $3 $4", ["96"]],
          ["(\\d{2})(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["9"]],
          ["(\\d{3})(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["9"]]
        ]
      ],
      DE: ["49", "00", "[2579]\\d{5,14}|49(?:[34]0|69|8\\d)\\d\\d?|49(?:37|49|60|7[089]|9\\d)\\d{1,3}|49(?:2[024-9]|3[2-689]|7[1-7])\\d{1,8}|(?:1|[368]\\d|4[0-8])\\d{3,13}|49(?:[015]\\d|2[13]|31|[46][1-8])\\d{1,9}", [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        [
          ["(\\d{2})(\\d{3,13})", "$1 $2", ["3[02]|40|[68]9"], "0$1"],
          ["(\\d{3})(\\d{3,12})", "$1 $2", ["2(?:0[1-389]|1[124]|2[18]|3[14])|3(?:[35-9][15]|4[015])|906|(?:2[4-9]|4[2-9]|[579][1-9]|[68][1-8])1", "2(?:0[1-389]|12[0-8])|3(?:[35-9][15]|4[015])|906|2(?:[13][14]|2[18])|(?:2[4-9]|4[2-9]|[579][1-9]|[68][1-8])1"], "0$1"],
          ["(\\d{4})(\\d{2,11})", "$1 $2", ["[24-6]|3(?:[3569][02-46-9]|4[2-4679]|7[2-467]|8[2-46-8])|70[2-8]|8(?:0[2-9]|[1-8])|90[7-9]|[79][1-9]", "[24-6]|3(?:3(?:0[1-467]|2[127-9]|3[124578]|7[1257-9]|8[1256]|9[145])|4(?:2[135]|4[13578]|9[1346])|5(?:0[14]|2[1-3589]|6[1-4]|7[13468]|8[13568])|6(?:2[1-489]|3[124-6]|6[13]|7[12579]|8[1-356]|9[135])|7(?:2[1-7]|4[145]|6[1-5]|7[1-4])|8(?:21|3[1468]|6|7[1467]|8[136])|9(?:0[12479]|2[1358]|4[134679]|6[1-9]|7[136]|8[147]|9[1468]))|70[2-8]|8(?:0[2-9]|[1-8])|90[7-9]|[79][1-9]|3[68]4[1347]|3(?:47|60)[1356]|3(?:3[46]|46|5[49])[1246]|3[4579]3[1357]"], "0$1"],
          ["(\\d{3})(\\d{4})", "$1 $2", ["138"], "0$1"],
          ["(\\d{5})(\\d{2,10})", "$1 $2", ["3"], "0$1"],
          ["(\\d{3})(\\d{5,11})", "$1 $2", ["181"], "0$1"],
          ["(\\d{3})(\\d)(\\d{4,10})", "$1 $2 $3", ["1(?:3|80)|9"], "0$1"],
          ["(\\d{3})(\\d{7,8})", "$1 $2", ["1[67]"], "0$1"],
          ["(\\d{3})(\\d{7,12})", "$1 $2", ["8"], "0$1"],
          ["(\\d{5})(\\d{6})", "$1 $2", ["185", "1850", "18500"], "0$1"],
          ["(\\d{3})(\\d{4})(\\d{4})", "$1 $2 $3", ["7"], "0$1"],
          ["(\\d{4})(\\d{7})", "$1 $2", ["18[68]"], "0$1"],
          ["(\\d{5})(\\d{6})", "$1 $2", ["15[0568]"], "0$1"],
          ["(\\d{4})(\\d{7})", "$1 $2", ["15[1279]"], "0$1"],
          ["(\\d{3})(\\d{8})", "$1 $2", ["18"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{7,8})", "$1 $2 $3", ["1(?:6[023]|7)"], "0$1"],
          ["(\\d{4})(\\d{2})(\\d{7})", "$1 $2 $3", ["15[279]"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{8})", "$1 $2 $3", ["15"], "0$1"]
        ], "0"
      ],
      DJ: ["253", "00", "(?:2\\d|77)\\d{6}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[27]"]]
        ]
      ],
      DK: ["45", "00", "[2-9]\\d{7}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[2-9]"]]
        ]
      ],
      DM: ["1", "011", "(?:[58]\\d\\d|767|900)\\d{7}", [10], 0, "1", 0, "([2-7]\\d{6})$|1", "767$1", 0, "767"],
      DO: ["1", "011", "(?:[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, 0, 0, 0, "8001|8[024]9"],
      DZ: ["213", "00", "(?:[1-4]|[5-79]\\d|80)\\d{7}", [8, 9],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[1-4]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["9"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[5-8]"], "0$1"]
        ], "0"
      ],
      EC: ["593", "00", "1\\d{9,10}|(?:[2-7]|9\\d)\\d{7}", [8, 9, 10, 11],
        [
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2-$3", ["[2-7]"], "(0$1)", 0, "$1-$2-$3"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["9"], "0$1"],
          ["(\\d{4})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["1"]]
        ], "0"
      ],
      EE: ["372", "00", "8\\d{9}|[4578]\\d{7}|(?:[3-8]\\d|90)\\d{5}", [7, 8, 10],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[369]|4[3-8]|5(?:[0-2]|5[0-478]|6[45])|7[1-9]|88", "[369]|4[3-8]|5(?:[02]|1(?:[0-8]|95)|5[0-478]|6(?:4[0-4]|5[1-589]))|7[1-9]|88"]],
          ["(\\d{4})(\\d{3,4})", "$1 $2", ["[45]|8(?:00|[1-49])", "[45]|8(?:00[1-9]|[1-49])"]],
          ["(\\d{2})(\\d{2})(\\d{4})", "$1 $2 $3", ["7"]],
          ["(\\d{4})(\\d{3})(\\d{3})", "$1 $2 $3", ["8"]]
        ]
      ],
      EG: ["20", "00", "[189]\\d{8,9}|[24-6]\\d{8}|[135]\\d{7}", [8, 9, 10],
        [
          ["(\\d)(\\d{7,8})", "$1 $2", ["[23]"], "0$1"],
          ["(\\d{2})(\\d{6,7})", "$1 $2", ["1[35]|[4-6]|8[2468]|9[235-7]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["[89]"], "0$1"],
          ["(\\d{2})(\\d{8})", "$1 $2", ["1"], "0$1"]
        ], "0"
      ],
      EH: ["212", "00", "[5-8]\\d{8}", [9], 0, "0", 0, 0, 0, 0, "528[89]"],
      ER: ["291", "00", "[178]\\d{6}", [7],
        [
          ["(\\d)(\\d{3})(\\d{3})", "$1 $2 $3", ["[178]"], "0$1"]
        ], "0"
      ],
      ES: ["34", "00", "[5-9]\\d{8}", [9],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[89]00"]],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[5-9]"]]
        ]
      ],
      ET: ["251", "00", "(?:11|[2-579]\\d)\\d{7}", [9],
        [
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[1-579]"], "0$1"]
        ], "0"
      ],
      FI: ["358", "00|99(?:[01469]|5(?:[14]1|3[23]|5[59]|77|88|9[09]))", "[1-35689]\\d{4}|7\\d{10,11}|(?:[124-7]\\d|3[0-46-9])\\d{8}|[1-9]\\d{5,8}", [5, 6, 7, 8, 9, 10, 11, 12],
        [
          ["(\\d)(\\d{4,9})", "$1 $2", ["[2568][1-8]|3(?:0[1-9]|[1-9])|9"], "0$1"],
          ["(\\d{3})(\\d{3,7})", "$1 $2", ["[12]00|[368]|70[07-9]"], "0$1"],
          ["(\\d{2})(\\d{4,8})", "$1 $2", ["[1245]|7[135]"], "0$1"],
          ["(\\d{2})(\\d{6,10})", "$1 $2", ["7"], "0$1"]
        ], "0", 0, 0, 0, 0, "1[03-79]|[2-9]", 0, "00"
      ],
      FJ: ["679", "0(?:0|52)", "45\\d{5}|(?:0800\\d|[235-9])\\d{6}", [7, 11],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[235-9]|45"]],
          ["(\\d{4})(\\d{3})(\\d{4})", "$1 $2 $3", ["0"]]
        ], 0, 0, 0, 0, 0, 0, 0, "00"
      ],
      FK: ["500", "00", "[2-7]\\d{4}", [5]],
      FM: ["691", "00", "(?:[39]\\d\\d|820)\\d{4}", [7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[389]"]]
        ]
      ],
      FO: ["298", "00", "[2-9]\\d{5}", [6],
        [
          ["(\\d{6})", "$1", ["[2-9]"]]
        ], 0, 0, "(10(?:01|[12]0|88))"
      ],
      FR: ["33", "00", "[1-9]\\d{8}", [9],
        [
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["8"], "0 $1"],
          ["(\\d)(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4 $5", ["[1-79]"], "0$1"]
        ], "0"
      ],
      GA: ["241", "00", "(?:[067]\\d|11)\\d{6}|[2-7]\\d{6}", [7, 8],
        [
          ["(\\d)(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[2-7]"], "0$1"],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["0"]],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["11|[67]"], "0$1"]
        ], 0, 0, "0(11\\d{6}|60\\d{6}|61\\d{6}|6[256]\\d{6}|7[467]\\d{6})", "$1"
      ],
      GB: ["44", "00", "[1-357-9]\\d{9}|[18]\\d{8}|8\\d{6}", [7, 9, 10],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["800", "8001", "80011", "800111", "8001111"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3", ["845", "8454", "84546", "845464"], "0$1"],
          ["(\\d{3})(\\d{6})", "$1 $2", ["800"], "0$1"],
          ["(\\d{5})(\\d{4,5})", "$1 $2", ["1(?:38|5[23]|69|76|94)", "1(?:(?:38|69)7|5(?:24|39)|768|946)", "1(?:3873|5(?:242|39[4-6])|(?:697|768)[347]|9467)"], "0$1"],
          ["(\\d{4})(\\d{5,6})", "$1 $2", ["1(?:[2-69][02-9]|[78])"], "0$1"],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2 $3", ["[25]|7(?:0|6[02-9])", "[25]|7(?:0|6(?:[03-9]|2[356]))"], "0$1"],
          ["(\\d{4})(\\d{6})", "$1 $2", ["7"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["[1389]"], "0$1"]
        ], "0", 0, 0, 0, 0, 0, [
          ["(?:1(?:1(?:3(?:[0-58]\\d\\d|73[0235])|4(?:[0-5]\\d\\d|69[7-9]|70[0-79])|(?:(?:5[0-26-9]|[78][0-49])\\d|6(?:[0-4]\\d|50))\\d)|(?:2(?:(?:0[024-9]|2[3-9]|3[3-79]|4[1-689]|[58][02-9]|6[0-47-9]|7[013-9]|9\\d)\\d|1(?:[0-7]\\d|8[0-2]))|(?:3(?:0\\d|1[0-8]|[25][02-9]|3[02-579]|[468][0-46-9]|7[1-35-79]|9[2-578])|4(?:0[03-9]|[137]\\d|[28][02-57-9]|4[02-69]|5[0-8]|[69][0-79])|5(?:0[1-35-9]|[16]\\d|2[024-9]|3[015689]|4[02-9]|5[03-9]|7[0-35-9]|8[0-468]|9[0-57-9])|6(?:0[034689]|1\\d|2[0-35689]|[38][013-9]|4[1-467]|5[0-69]|6[13-9]|7[0-8]|9[0-24578])|7(?:0[0246-9]|2\\d|3[0236-8]|4[03-9]|5[0-46-9]|6[013-9]|7[0-35-9]|8[024-9]|9[02-9])|8(?:0[35-9]|2[1-57-9]|3[02-578]|4[0-578]|5[124-9]|6[2-69]|7\\d|8[02-9]|9[02569])|9(?:0[02-589]|[18]\\d|2[02-689]|3[1-57-9]|4[2-9]|5[0-579]|6[2-47-9]|7[0-24578]|9[2-57]))\\d)\\d)|2(?:0[013478]|3[0189]|4[017]|8[0-46-9]|9[0-2])\\d{3})\\d{4}|1(?:2(?:0(?:46[1-4]|87[2-9])|545[1-79]|76(?:2\\d|3[1-8]|6[1-6])|9(?:7(?:2[0-4]|3[2-5])|8(?:2[2-8]|7[0-47-9]|8[3-5])))|3(?:6(?:38[2-5]|47[23])|8(?:47[04-9]|64[0157-9]))|4(?:044[1-7]|20(?:2[23]|8\\d)|6(?:0(?:30|5[2-57]|6[1-8]|7[2-8])|140)|8(?:052|87[1-3]))|5(?:2(?:4(?:3[2-79]|6\\d)|76\\d)|6(?:26[06-9]|686))|6(?:06(?:4\\d|7[4-79])|295[5-7]|35[34]\\d|47(?:24|61)|59(?:5[08]|6[67]|74)|9(?:55[0-4]|77[23]))|7(?:26(?:6[13-9]|7[0-7])|(?:442|688)\\d|50(?:2[0-3]|[3-68]2|76))|8(?:27[56]\\d|37(?:5[2-5]|8[239])|843[2-58])|9(?:0(?:0(?:6[1-8]|85)|52\\d)|3583|4(?:66[1-8]|9(?:2[01]|81))|63(?:23|3[1-4])|9561))\\d{3}", [9, 10]],
          ["7(?:457[0-57-9]|700[01]|911[028])\\d{5}|7(?:[1-3]\\d\\d|4(?:[0-46-9]\\d|5[0-689])|5(?:0[0-8]|[13-9]\\d|2[0-35-9])|7(?:0[1-9]|[1-7]\\d|8[02-9]|9[0-689])|8(?:[014-9]\\d|[23][0-8])|9(?:[024-9]\\d|1[02-9]|3[0-689]))\\d{6}", [10]],
          ["80[08]\\d{7}|800\\d{6}|8001111"],
          ["(?:8(?:4[2-5]|7[0-3])|9(?:[01]\\d|8[2-49]))\\d{7}|845464\\d", [7, 10]],
          ["70\\d{8}", [10]], 0, ["(?:3[0347]|55)\\d{8}", [10]],
          ["76(?:464|652)\\d{5}|76(?:0[0-28]|2[356]|34|4[01347]|5[49]|6[0-369]|77|8[14]|9[139])\\d{6}", [10]],
          ["56\\d{8}", [10]]
        ], 0, " x"
      ],
      GD: ["1", "011", "(?:473|[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "([2-9]\\d{6})$|1", "473$1", 0, "473"],
      GE: ["995", "00", "(?:[3-57]\\d\\d|800)\\d{6}", [9],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["70"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["32"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[57]"]],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[348]"], "0$1"]
        ], "0"
      ],
      GF: ["594", "00", "[56]94\\d{6}|(?:80|9\\d)\\d{7}", [9],
        [
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[56]|9[47]"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[89]"], "0$1"]
        ], "0"
      ],
      GG: ["44", "00", "(?:1481|[357-9]\\d{3})\\d{6}|8\\d{6}(?:\\d{2})?", [7, 9, 10], 0, "0", 0, "([25-9]\\d{5})$|0", "1481$1", 0, 0, [
        ["1481[25-9]\\d{5}", [10]],
        ["7(?:(?:781|839)\\d|911[17])\\d{5}", [10]],
        ["80[08]\\d{7}|800\\d{6}|8001111"],
        ["(?:8(?:4[2-5]|7[0-3])|9(?:[01]\\d|8[0-3]))\\d{7}|845464\\d", [7, 10]],
        ["70\\d{8}", [10]], 0, ["(?:3[0347]|55)\\d{8}", [10]],
        ["76(?:464|652)\\d{5}|76(?:0[0-28]|2[356]|34|4[01347]|5[49]|6[0-369]|77|8[14]|9[139])\\d{6}", [10]],
        ["56\\d{8}", [10]]
      ]],
      GH: ["233", "00", "(?:[235]\\d{3}|800)\\d{5}", [8, 9],
        [
          ["(\\d{3})(\\d{5})", "$1 $2", ["8"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[235]"], "0$1"]
        ], "0"
      ],
      GI: ["350", "00", "(?:[25]\\d|60)\\d{6}", [8],
        [
          ["(\\d{3})(\\d{5})", "$1 $2", ["2"]]
        ]
      ],
      GL: ["299", "00", "(?:19|[2-689]\\d|70)\\d{4}", [6],
        [
          ["(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3", ["19|[2-9]"]]
        ]
      ],
      GM: ["220", "00", "[2-9]\\d{6}", [7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[2-9]"]]
        ]
      ],
      GN: ["224", "00", "722\\d{6}|(?:3|6\\d)\\d{7}", [8, 9],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["3"]],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[67]"]]
        ]
      ],
      GP: ["590", "00", "590\\d{6}|(?:69|80|9\\d)\\d{7}", [9],
        [
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[569]"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["8"], "0$1"]
        ], "0", 0, 0, 0, 0, 0, [
          ["590(?:0[1-68]|[14][0-24-9]|2[0-68]|3[1-9]|5[3-579]|[68][0-689]|7[08]|9\\d)\\d{4}"],
          ["69(?:0\\d\\d|1(?:2[2-9]|3[0-5]))\\d{4}"],
          ["80[0-5]\\d{6}"], 0, 0, 0, 0, 0, ["9(?:(?:395|76[018])\\d|475[0-5])\\d{4}"]
        ]
      ],
      GQ: ["240", "00", "222\\d{6}|(?:3\\d|55|[89]0)\\d{7}", [9],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[235]"]],
          ["(\\d{3})(\\d{6})", "$1 $2", ["[89]"]]
        ]
      ],
      GR: ["30", "00", "5005000\\d{3}|8\\d{9,11}|(?:[269]\\d|70)\\d{8}", [10, 11, 12],
        [
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2 $3", ["21|7"]],
          ["(\\d{4})(\\d{6})", "$1 $2", ["2(?:2|3[2-57-9]|4[2-469]|5[2-59]|6[2-9]|7[2-69]|8[2-49])|5"]],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["[2689]"]],
          ["(\\d{3})(\\d{3,4})(\\d{5})", "$1 $2 $3", ["8"]]
        ]
      ],
      GT: ["502", "00", "80\\d{6}|(?:1\\d{3}|[2-7])\\d{7}", [8, 11],
        [
          ["(\\d{4})(\\d{4})", "$1 $2", ["[2-8]"]],
          ["(\\d{4})(\\d{3})(\\d{4})", "$1 $2 $3", ["1"]]
        ]
      ],
      GU: ["1", "011", "(?:[58]\\d\\d|671|900)\\d{7}", [10], 0, "1", 0, "([2-9]\\d{6})$|1", "671$1", 0, "671"],
      GW: ["245", "00", "[49]\\d{8}|4\\d{6}", [7, 9],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["40"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[49]"]]
        ]
      ],
      GY: ["592", "001", "(?:[2-8]\\d{3}|9008)\\d{3}", [7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[2-9]"]]
        ]
      ],
      HK: ["852", "00(?:30|5[09]|[126-9]?)", "8[0-46-9]\\d{6,7}|9\\d{4,7}|(?:[2-7]|9\\d{3})\\d{7}", [5, 6, 7, 8, 9, 11],
        [
          ["(\\d{3})(\\d{2,5})", "$1 $2", ["900", "9003"]],
          ["(\\d{4})(\\d{4})", "$1 $2", ["[2-7]|8[1-4]|9(?:0[1-9]|[1-8])"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["8"]],
          ["(\\d{3})(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["9"]]
        ], 0, 0, 0, 0, 0, 0, 0, "00"
      ],
      HN: ["504", "00", "8\\d{10}|[237-9]\\d{7}", [8, 11],
        [
          ["(\\d{4})(\\d{4})", "$1-$2", ["[237-9]"]]
        ]
      ],
      HR: ["385", "00", "(?:[24-69]\\d|3[0-79])\\d{7}|80\\d{5,7}|[1-79]\\d{7}|6\\d{5,6}", [6, 7, 8, 9],
        [
          ["(\\d{2})(\\d{2})(\\d{2,3})", "$1 $2 $3", ["6[01]"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{2,3})", "$1 $2 $3", ["8"], "0$1"],
          ["(\\d)(\\d{4})(\\d{3})", "$1 $2 $3", ["1"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[67]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["9"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[2-5]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["8"], "0$1"]
        ], "0"
      ],
      HT: ["509", "00", "(?:[2-489]\\d|55)\\d{6}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{4})", "$1 $2 $3", ["[2-589]"]]
        ]
      ],
      HU: ["36", "00", "[235-7]\\d{8}|[1-9]\\d{7}", [8, 9],
        [
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["1"], "(06 $1)"],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["[27][2-9]|3[2-7]|4[24-9]|5[2-79]|6|8[2-57-9]|9[2-69]"], "(06 $1)"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[2-9]"], "06 $1"]
        ], "06"
      ],
      ID: ["62", "00[89]", "(?:(?:00[1-9]|8\\d)\\d{4}|[1-36])\\d{6}|00\\d{10}|[1-9]\\d{8,10}|[2-9]\\d{7}", [7, 8, 9, 10, 11, 12, 13],
        [
          ["(\\d)(\\d{3})(\\d{3})", "$1 $2 $3", ["15"]],
          ["(\\d{2})(\\d{5,9})", "$1 $2", ["2[124]|[36]1"], "(0$1)"],
          ["(\\d{3})(\\d{5,7})", "$1 $2", ["800"], "0$1"],
          ["(\\d{3})(\\d{5,8})", "$1 $2", ["[2-79]"], "(0$1)"],
          ["(\\d{3})(\\d{3,4})(\\d{3})", "$1-$2-$3", ["8[1-35-9]"], "0$1"],
          ["(\\d{3})(\\d{6,8})", "$1 $2", ["1"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["804"], "0$1"],
          ["(\\d{3})(\\d)(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["80"], "0$1"],
          ["(\\d{3})(\\d{4})(\\d{4,5})", "$1-$2-$3", ["8"], "0$1"]
        ], "0"
      ],
      IE: ["353", "00", "(?:1\\d|[2569])\\d{6,8}|4\\d{6,9}|7\\d{8}|8\\d{8,9}", [7, 8, 9, 10],
        [
          ["(\\d{2})(\\d{5})", "$1 $2", ["2[24-9]|47|58|6[237-9]|9[35-9]"], "(0$1)"],
          ["(\\d{3})(\\d{5})", "$1 $2", ["[45]0"], "(0$1)"],
          ["(\\d)(\\d{3,4})(\\d{4})", "$1 $2 $3", ["1"], "(0$1)"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[2569]|4[1-69]|7[14]"], "(0$1)"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["70"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["81"], "(0$1)"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[78]"], "0$1"],
          ["(\\d{4})(\\d{3})(\\d{3})", "$1 $2 $3", ["1"]],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2 $3", ["4"], "(0$1)"],
          ["(\\d{2})(\\d)(\\d{3})(\\d{4})", "$1 $2 $3 $4", ["8"], "0$1"]
        ], "0"
      ],
      IL: ["972", "0(?:0|1[2-9])", "1\\d{6}(?:\\d{3,5})?|[57]\\d{8}|[1-489]\\d{7}", [7, 8, 9, 10, 11, 12],
        [
          ["(\\d{4})(\\d{3})", "$1-$2", ["125"]],
          ["(\\d{4})(\\d{2})(\\d{2})", "$1-$2-$3", ["121"]],
          ["(\\d)(\\d{3})(\\d{4})", "$1-$2-$3", ["[2-489]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1-$2-$3", ["[57]"], "0$1"],
          ["(\\d{4})(\\d{3})(\\d{3})", "$1-$2-$3", ["12"]],
          ["(\\d{4})(\\d{6})", "$1-$2", ["159"]],
          ["(\\d)(\\d{3})(\\d{3})(\\d{3})", "$1-$2-$3-$4", ["1[7-9]"]],
          ["(\\d{3})(\\d{1,2})(\\d{3})(\\d{4})", "$1-$2 $3-$4", ["15"]]
        ], "0"
      ],
      IM: ["44", "00", "1624\\d{6}|(?:[3578]\\d|90)\\d{8}", [10], 0, "0", 0, "([25-8]\\d{5})$|0", "1624$1", 0, "74576|(?:16|7[56])24"],
      IN: ["91", "00", "(?:000800|[2-9]\\d\\d)\\d{7}|1\\d{7,12}", [8, 9, 10, 11, 12, 13],
        [
          ["(\\d{8})", "$1", ["5(?:0|2[23]|3[03]|[67]1|88)", "5(?:0|2(?:21|3)|3(?:0|3[23])|616|717|888)", "5(?:0|2(?:21|3)|3(?:0|3[23])|616|717|8888)"], 0, 1],
          ["(\\d{4})(\\d{4,5})", "$1 $2", ["180", "1800"], 0, 1],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["140"], 0, 1],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2 $3", ["11|2[02]|33|4[04]|79[1-7]|80[2-46]", "11|2[02]|33|4[04]|79(?:[1-6]|7[19])|80(?:[2-4]|6[0-589])", "11|2[02]|33|4[04]|79(?:[124-6]|3(?:[02-9]|1[0-24-9])|7(?:1|9[1-6]))|80(?:[2-4]|6[0-589])"], "0$1", 1],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["1(?:2[0-249]|3[0-25]|4[145]|[68]|7[1257])|2(?:1[257]|3[013]|4[01]|5[0137]|6[0158]|78|8[1568])|3(?:26|4[1-3]|5[34]|6[01489]|7[02-46]|8[159])|4(?:1[36]|2[1-47]|5[12]|6[0-26-9]|7[0-24-9]|8[013-57]|9[014-7])|5(?:1[025]|22|[36][25]|4[28]|5[12]|[78]1)|6(?:12|[2-4]1|5[17]|6[13]|80)|7(?:12|3[134]|4[47]|61|88)|8(?:16|2[014]|3[126]|6[136]|7[078]|8[34]|91)|(?:43|59|75)[15]|(?:1[59]|29|67|72)[14]", "1(?:2[0-24]|3[0-25]|4[145]|[59][14]|6[1-9]|7[1257]|8[1-57-9])|2(?:1[257]|3[013]|4[01]|5[0137]|6[058]|78|8[1568]|9[14])|3(?:26|4[1-3]|5[34]|6[01489]|7[02-46]|8[159])|4(?:1[36]|2[1-47]|3[15]|5[12]|6[0-26-9]|7[0-24-9]|8[013-57]|9[014-7])|5(?:1[025]|22|[36][25]|4[28]|[578]1|9[15])|674|7(?:(?:2[14]|3[34]|5[15])[2-6]|61[346]|88[0-8])|8(?:70[2-6]|84[235-7]|91[3-7])|(?:1(?:29|60|8[06])|261|552|6(?:12|[2-47]1|5[17]|6[13]|80)|7(?:12|31|4[47])|8(?:16|2[014]|3[126]|6[136]|7[78]|83))[2-7]", "1(?:2[0-24]|3[0-25]|4[145]|[59][14]|6[1-9]|7[1257]|8[1-57-9])|2(?:1[257]|3[013]|4[01]|5[0137]|6[058]|78|8[1568]|9[14])|3(?:26|4[1-3]|5[34]|6[01489]|7[02-46]|8[159])|4(?:1[36]|2[1-47]|3[15]|5[12]|6[0-26-9]|7[0-24-9]|8[013-57]|9[014-7])|5(?:1[025]|22|[36][25]|4[28]|[578]1|9[15])|6(?:12(?:[2-6]|7[0-8])|74[2-7])|7(?:(?:2[14]|5[15])[2-6]|3171|61[346]|88(?:[2-7]|82))|8(?:70[2-6]|84(?:[2356]|7[19])|91(?:[3-6]|7[19]))|73[134][2-6]|(?:74[47]|8(?:16|2[014]|3[126]|6[136]|7[78]|83))(?:[2-6]|7[19])|(?:1(?:29|60|8[06])|261|552|6(?:[2-4]1|5[17]|6[13]|7(?:1|4[0189])|80)|7(?:12|88[01]))[2-7]"], "0$1", 1],
          ["(\\d{4})(\\d{3})(\\d{3})", "$1 $2 $3", ["1(?:[2-479]|5[0235-9])|[2-5]|6(?:1[1358]|2[2457-9]|3[2-5]|4[235-7]|5[2-689]|6[24578]|7[235689]|8[1-6])|7(?:1[013-9]|28|3[129]|4[1-35689]|5[29]|6[02-5]|70)|807", "1(?:[2-479]|5[0235-9])|[2-5]|6(?:1[1358]|2(?:[2457]|84|95)|3(?:[2-4]|55)|4[235-7]|5[2-689]|6[24578]|7[235689]|8[1-6])|7(?:1(?:[013-8]|9[6-9])|28[6-8]|3(?:17|2[0-49]|9[2-57])|4(?:1[2-4]|[29][0-7]|3[0-8]|[56]|8[0-24-7])|5(?:2[1-3]|9[0-6])|6(?:0[5689]|2[5-9]|3[02-8]|4|5[0-367])|70[13-7])|807[19]", "1(?:[2-479]|5(?:[0236-9]|5[013-9]))|[2-5]|6(?:2(?:84|95)|355|83)|73179|807(?:1|9[1-3])|(?:1552|6(?:1[1358]|2[2457]|3[2-4]|4[235-7]|5[2-689]|6[24578]|7[235689]|8[124-6])\\d|7(?:1(?:[013-8]\\d|9[6-9])|28[6-8]|3(?:2[0-49]|9[2-57])|4(?:1[2-4]|[29][0-7]|3[0-8]|[56]\\d|8[0-24-7])|5(?:2[1-3]|9[0-6])|6(?:0[5689]|2[5-9]|3[02-8]|4\\d|5[0-367])|70[13-7]))[2-7]"], "0$1", 1],
          ["(\\d{5})(\\d{5})", "$1 $2", ["[6-9]"], "0$1", 1],
          ["(\\d{4})(\\d{2,4})(\\d{4})", "$1 $2 $3", ["1(?:6|8[06])", "1(?:6|8[06]0)"], 0, 1],
          ["(\\d{4})(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["18"], 0, 1]
        ], "0"
      ],
      IO: ["246", "00", "3\\d{6}", [7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["3"]]
        ]
      ],
      IQ: ["964", "00", "(?:1|7\\d\\d)\\d{7}|[2-6]\\d{7,8}", [8, 9, 10],
        [
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["1"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[2-6]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["7"], "0$1"]
        ], "0"
      ],
      IR: ["98", "00", "[1-9]\\d{9}|(?:[1-8]\\d\\d|9)\\d{3,4}", [4, 5, 6, 7, 10],
        [
          ["(\\d{4,5})", "$1", ["96"], "0$1"],
          ["(\\d{2})(\\d{4,5})", "$1 $2", ["(?:1[137]|2[13-68]|3[1458]|4[145]|5[1468]|6[16]|7[1467]|8[13467])[12689]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["9"], "0$1"],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2 $3", ["[1-8]"], "0$1"]
        ], "0"
      ],
      IS: ["354", "00|1(?:0(?:01|[12]0)|100)", "(?:38\\d|[4-9])\\d{6}", [7, 9],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[4-9]"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["3"]]
        ], 0, 0, 0, 0, 0, 0, 0, "00"
      ],
      IT: ["39", "00", "0\\d{5,10}|1\\d{8,10}|3(?:[0-8]\\d{7,10}|9\\d{7,8})|(?:55|70)\\d{8}|8\\d{5}(?:\\d{2,4})?", [6, 7, 8, 9, 10, 11],
        [
          ["(\\d{2})(\\d{4,6})", "$1 $2", ["0[26]"]],
          ["(\\d{3})(\\d{3,6})", "$1 $2", ["0[13-57-9][0159]|8(?:03|4[17]|9[2-5])", "0[13-57-9][0159]|8(?:03|4[17]|9(?:2|3[04]|[45][0-4]))"]],
          ["(\\d{4})(\\d{2,6})", "$1 $2", ["0(?:[13-579][2-46-8]|8[236-8])"]],
          ["(\\d{4})(\\d{4})", "$1 $2", ["894"]],
          ["(\\d{2})(\\d{3,4})(\\d{4})", "$1 $2 $3", ["0[26]|5"]],
          ["(\\d{3})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["1(?:44|[679])|[378]"]],
          ["(\\d{3})(\\d{3,4})(\\d{4})", "$1 $2 $3", ["0[13-57-9][0159]|14"]],
          ["(\\d{2})(\\d{4})(\\d{5})", "$1 $2 $3", ["0[26]"]],
          ["(\\d{4})(\\d{3})(\\d{4})", "$1 $2 $3", ["0"]],
          ["(\\d{3})(\\d{4})(\\d{4,5})", "$1 $2 $3", ["3"]]
        ], 0, 0, 0, 0, 0, 0, [
          ["0669[0-79]\\d{1,6}|0(?:1(?:[0159]\\d|[27][1-5]|31|4[1-4]|6[1356]|8[2-57])|2\\d\\d|3(?:[0159]\\d|2[1-4]|3[12]|[48][1-6]|6[2-59]|7[1-7])|4(?:[0159]\\d|[23][1-9]|4[245]|6[1-5]|7[1-4]|81)|5(?:[0159]\\d|2[1-5]|3[2-6]|4[1-79]|6[4-6]|7[1-578]|8[3-8])|6(?:[0-57-9]\\d|6[0-8])|7(?:[0159]\\d|2[12]|3[1-7]|4[2-46]|6[13569]|7[13-6]|8[1-59])|8(?:[0159]\\d|2[3-578]|3[1-356]|[6-8][1-5])|9(?:[0159]\\d|[238][1-5]|4[12]|6[1-8]|7[1-6]))\\d{2,7}"],
          ["3[1-9]\\d{8}|3[2-9]\\d{7}", [9, 10]],
          ["80(?:0\\d{3}|3)\\d{3}", [6, 9]],
          ["(?:0878\\d{3}|89(?:2\\d|3[04]|4(?:[0-4]|[5-9]\\d\\d)|5[0-4]))\\d\\d|(?:1(?:44|6[346])|89(?:38|5[5-9]|9))\\d{6}", [6, 8, 9, 10]],
          ["1(?:78\\d|99)\\d{6}", [9, 10]], 0, 0, 0, ["55\\d{8}", [10]],
          ["84(?:[08]\\d{3}|[17])\\d{3}", [6, 9]]
        ]
      ],
      JE: ["44", "00", "1534\\d{6}|(?:[3578]\\d|90)\\d{8}", [10], 0, "0", 0, "([0-24-8]\\d{5})$|0", "1534$1", 0, 0, [
        ["1534[0-24-8]\\d{5}"],
        ["7(?:(?:(?:50|82)9|937)\\d|7(?:00[378]|97[7-9]))\\d{5}"],
        ["80(?:07(?:35|81)|8901)\\d{4}"],
        ["(?:8(?:4(?:4(?:4(?:05|42|69)|703)|5(?:041|800))|7(?:0002|1206))|90(?:066[59]|1810|71(?:07|55)))\\d{4}"],
        ["701511\\d{4}"], 0, ["(?:3(?:0(?:07(?:35|81)|8901)|3\\d{4}|4(?:4(?:4(?:05|42|69)|703)|5(?:041|800))|7(?:0002|1206))|55\\d{4})\\d{4}"],
        ["76(?:464|652)\\d{5}|76(?:0[0-28]|2[356]|34|4[01347]|5[49]|6[0-369]|77|8[14]|9[139])\\d{6}"],
        ["56\\d{8}"]
      ]],
      JM: ["1", "011", "(?:[58]\\d\\d|658|900)\\d{7}", [10], 0, "1", 0, 0, 0, 0, "658|876"],
      JO: ["962", "00", "(?:(?:[2689]|7\\d)\\d|32|53)\\d{6}", [8, 9],
        [
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["[2356]|87"], "(0$1)"],
          ["(\\d{3})(\\d{5,6})", "$1 $2", ["[89]"], "0$1"],
          ["(\\d{2})(\\d{7})", "$1 $2", ["70"], "0$1"],
          ["(\\d)(\\d{4})(\\d{4})", "$1 $2 $3", ["7"], "0$1"]
        ], "0"
      ],
      JP: ["81", "010", "00[1-9]\\d{6,14}|[257-9]\\d{9}|(?:00|[1-9]\\d\\d)\\d{6}", [8, 9, 10, 11, 12, 13, 14, 15, 16, 17],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1-$2-$3", ["(?:12|57|99)0"], "0$1"],
          ["(\\d{4})(\\d)(\\d{4})", "$1-$2-$3", ["1(?:26|3[79]|4[56]|5[4-68]|6[3-5])|499|5(?:76|97)|746|8(?:3[89]|47|51)|9(?:80|9[16])", "1(?:267|3(?:7[247]|9[278])|466|5(?:47|58|64)|6(?:3[245]|48|5[4-68]))|499[2468]|5(?:76|97)9|7468|8(?:3(?:8[7-9]|96)|477|51[2-9])|9(?:802|9(?:1[23]|69))|1(?:45|58)[67]", "1(?:267|3(?:7[247]|9[278])|466|5(?:47|58|64)|6(?:3[245]|48|5[4-68]))|499[2468]|5(?:769|979[2-69])|7468|8(?:3(?:8[7-9]|96[2457-9])|477|51[2-9])|9(?:802|9(?:1[23]|69))|1(?:45|58)[67]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1-$2-$3", ["60"], "0$1"],
          ["(\\d)(\\d{4})(\\d{4})", "$1-$2-$3", ["[36]|4(?:2[09]|7[01])", "[36]|4(?:2(?:0|9[02-69])|7(?:0[019]|1))"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1-$2-$3", ["1(?:1|5[45]|77|88|9[69])|2(?:2[1-37]|3[0-269]|4[59]|5|6[24]|7[1-358]|8[1369]|9[0-38])|4(?:[28][1-9]|3[0-57]|[45]|6[248]|7[2-579]|9[29])|5(?:2|3[0459]|4[0-369]|5[29]|8[02389]|9[0-389])|7(?:2[02-46-9]|34|[58]|6[0249]|7[57]|9[2-6])|8(?:2[124589]|3[26-9]|49|51|6|7[0-468]|8[68]|9[019])|9(?:[23][1-9]|4[15]|5[138]|6[1-3]|7[156]|8[189]|9[1-489])", "1(?:1|5(?:4[018]|5[017])|77|88|9[69])|2(?:2(?:[127]|3[014-9])|3[0-269]|4[59]|5(?:[1-3]|5[0-69]|9[19])|62|7(?:[1-35]|8[0189])|8(?:[16]|3[0134]|9[0-5])|9(?:[028]|17))|4(?:2(?:[13-79]|8[014-6])|3[0-57]|[45]|6[248]|7[2-47]|8[1-9]|9[29])|5(?:2|3(?:[045]|9[0-8])|4[0-369]|5[29]|8[02389]|9[0-3])|7(?:2[02-46-9]|34|[58]|6[0249]|7[57]|9(?:[23]|4[0-59]|5[01569]|6[0167]))|8(?:2(?:[1258]|4[0-39]|9[0-2469])|3(?:[29]|60)|49|51|6(?:[0-24]|36|5[0-3589]|7[23]|9[01459])|7[0-468]|8[68])|9(?:[23][1-9]|4[15]|5[138]|6[1-3]|7[156]|8[189]|9(?:[1289]|3[34]|4[0178]))|(?:264|837)[016-9]|2(?:57|93)[015-9]|(?:25[0468]|422|838)[01]|(?:47[59]|59[89]|8(?:6[68]|9))[019]", "1(?:1|5(?:4[018]|5[017])|77|88|9[69])|2(?:2[127]|3[0-269]|4[59]|5(?:[1-3]|5[0-69]|9(?:17|99))|6(?:2|4[016-9])|7(?:[1-35]|8[0189])|8(?:[16]|3[0134]|9[0-5])|9(?:[028]|17))|4(?:2(?:[13-79]|8[014-6])|3[0-57]|[45]|6[248]|7[2-47]|9[29])|5(?:2|3(?:[045]|9(?:[0-58]|6[4-9]|7[0-35689]))|4[0-369]|5[29]|8[02389]|9[0-3])|7(?:2[02-46-9]|34|[58]|6[0249]|7[57]|9(?:[23]|4[0-59]|5[01569]|6[0167]))|8(?:2(?:[1258]|4[0-39]|9[0169])|3(?:[29]|60|7(?:[017-9]|6[6-8]))|49|51|6(?:[0-24]|36[2-57-9]|5(?:[0-389]|5[23])|6(?:[01]|9[178])|7(?:2[2-468]|3[78])|9[0145])|7[0-468]|8[68])|9(?:4[15]|5[138]|7[156]|8[189]|9(?:[1289]|3(?:31|4[357])|4[0178]))|(?:8294|96)[1-3]|2(?:57|93)[015-9]|(?:223|8699)[014-9]|(?:25[0468]|422|838)[01]|(?:48|8292|9[23])[1-9]|(?:47[59]|59[89]|8(?:68|9))[019]"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{4})", "$1-$2-$3", ["[14]|[289][2-9]|5[3-9]|7[2-4679]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1-$2-$3", ["800"], "0$1"],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1-$2-$3", ["[257-9]"], "0$1"]
        ], "0", 0, "(000[259]\\d{6})$|(?:(?:003768)0?)|0", "$1"
      ],
      KE: ["254", "000", "(?:[17]\\d\\d|900)\\d{6}|(?:2|80)0\\d{6,7}|[4-6]\\d{6,8}", [7, 8, 9, 10],
        [
          ["(\\d{2})(\\d{5,7})", "$1 $2", ["[24-6]"], "0$1"],
          ["(\\d{3})(\\d{6})", "$1 $2", ["[17]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[89]"], "0$1"]
        ], "0"
      ],
      KG: ["996", "00", "8\\d{9}|[235-9]\\d{8}", [9, 10],
        [
          ["(\\d{4})(\\d{5})", "$1 $2", ["3(?:1[346]|[24-79])"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[235-79]|88"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d)(\\d{2,3})", "$1 $2 $3 $4", ["8"], "0$1"]
        ], "0"
      ],
      KH: ["855", "00[14-9]", "1\\d{9}|[1-9]\\d{7,8}", [8, 9, 10],
        [
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[1-9]"], "0$1"],
          ["(\\d{4})(\\d{3})(\\d{3})", "$1 $2 $3", ["1"]]
        ], "0"
      ],
      KI: ["686", "00", "(?:[37]\\d|6[0-79])\\d{6}|(?:[2-48]\\d|50)\\d{3}", [5, 8], 0, "0"],
      KM: ["269", "00", "[3478]\\d{6}", [7],
        [
          ["(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3", ["[3478]"]]
        ]
      ],
      KN: ["1", "011", "(?:[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "([2-7]\\d{6})$|1", "869$1", 0, "869"],
      KP: ["850", "00|99", "85\\d{6}|(?:19\\d|[2-7])\\d{7}", [8, 10],
        [
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["8"], "0$1"],
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["[2-7]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["1"], "0$1"]
        ], "0"
      ],
      KR: ["82", "00(?:[125689]|3(?:[46]5|91)|7(?:00|27|3|55|6[126]))", "00[1-9]\\d{8,11}|(?:[12]|5\\d{3})\\d{7}|[13-6]\\d{9}|(?:[1-6]\\d|80)\\d{7}|[3-6]\\d{4,5}|(?:00|7)0\\d{8}", [5, 6, 8, 9, 10, 11, 12, 13, 14],
        [
          ["(\\d{2})(\\d{3,4})", "$1-$2", ["(?:3[1-3]|[46][1-4]|5[1-5])1"], "0$1"],
          ["(\\d{4})(\\d{4})", "$1-$2", ["1"]],
          ["(\\d)(\\d{3,4})(\\d{4})", "$1-$2-$3", ["2"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1-$2-$3", ["60|8"], "0$1"],
          ["(\\d{2})(\\d{3,4})(\\d{4})", "$1-$2-$3", ["[1346]|5[1-5]"], "0$1"],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1-$2-$3", ["[57]"], "0$1"],
          ["(\\d{2})(\\d{5})(\\d{4})", "$1-$2-$3", ["5"], "0$1"]
        ], "0", 0, "0(8(?:[1-46-8]|5\\d\\d))?"
      ],
      KW: ["965", "00", "18\\d{5}|(?:[2569]\\d|41)\\d{6}", [7, 8],
        [
          ["(\\d{4})(\\d{3,4})", "$1 $2", ["[169]|2(?:[235]|4[1-35-9])|52"]],
          ["(\\d{3})(\\d{5})", "$1 $2", ["[245]"]]
        ]
      ],
      KY: ["1", "011", "(?:345|[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "([2-9]\\d{6})$|1", "345$1", 0, "345"],
      KZ: ["7", "810", "(?:33622|8\\d{8})\\d{5}|[78]\\d{9}", [10, 14], 0, "8", 0, 0, 0, 0, "33|7", 0, "8~10"],
      LA: ["856", "00", "[23]\\d{9}|3\\d{8}|(?:[235-8]\\d|41)\\d{6}", [8, 9, 10],
        [
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["2[13]|3[14]|[4-8]"], "0$1"],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{3})", "$1 $2 $3 $4", ["30[013-9]"], "0$1"],
          ["(\\d{2})(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["[23]"], "0$1"]
        ], "0"
      ],
      LB: ["961", "00", "[27-9]\\d{7}|[13-9]\\d{6}", [7, 8],
        [
          ["(\\d)(\\d{3})(\\d{3})", "$1 $2 $3", ["[13-69]|7(?:[2-57]|62|8[0-7]|9[04-9])|8[02-9]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["[27-9]"]]
        ], "0"
      ],
      LC: ["1", "011", "(?:[58]\\d\\d|758|900)\\d{7}", [10], 0, "1", 0, "([2-8]\\d{6})$|1", "758$1", 0, "758"],
      LI: ["423", "00", "[68]\\d{8}|(?:[2378]\\d|90)\\d{5}", [7, 9],
        [
          ["(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3", ["[2379]|8(?:0[09]|7)", "[2379]|8(?:0(?:02|9)|7)"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["8"]],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["69"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["6"]]
        ], "0", 0, "(1001)|0"
      ],
      LK: ["94", "00", "[1-9]\\d{8}", [9],
        [
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["7"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[1-689]"], "0$1"]
        ], "0"
      ],
      LR: ["231", "00", "(?:[25]\\d|33|77|88)\\d{7}|(?:2\\d|[4-6])\\d{6}", [7, 8, 9],
        [
          ["(\\d)(\\d{3})(\\d{3})", "$1 $2 $3", ["[4-6]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["2"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[23578]"], "0$1"]
        ], "0"
      ],
      LS: ["266", "00", "(?:[256]\\d\\d|800)\\d{5}", [8],
        [
          ["(\\d{4})(\\d{4})", "$1 $2", ["[2568]"]]
        ]
      ],
      LT: ["370", "00", "(?:[3469]\\d|52|[78]0)\\d{6}", [8],
        [
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["52[0-7]"], "(8-$1)", 1],
          ["(\\d{3})(\\d{2})(\\d{3})", "$1 $2 $3", ["[7-9]"], "8 $1", 1],
          ["(\\d{2})(\\d{6})", "$1 $2", ["37|4(?:[15]|6[1-8])"], "(8-$1)", 1],
          ["(\\d{3})(\\d{5})", "$1 $2", ["[3-6]"], "(8-$1)", 1]
        ], "8", 0, "[08]"
      ],
      LU: ["352", "00", "35[013-9]\\d{4,8}|6\\d{8}|35\\d{2,4}|(?:[2457-9]\\d|3[0-46-9])\\d{2,9}", [4, 5, 6, 7, 8, 9, 10, 11],
        [
          ["(\\d{2})(\\d{3})", "$1 $2", ["2(?:0[2-689]|[2-9])|[3-57]|8(?:0[2-9]|[13-9])|9(?:0[89]|[2-579])"]],
          ["(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3", ["2(?:0[2-689]|[2-9])|[3-57]|8(?:0[2-9]|[13-9])|9(?:0[89]|[2-579])"]],
          ["(\\d{2})(\\d{2})(\\d{3})", "$1 $2 $3", ["20[2-689]"]],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{1,2})", "$1 $2 $3 $4", ["2(?:[0367]|4[3-8])"]],
          ["(\\d{3})(\\d{2})(\\d{3})", "$1 $2 $3", ["80[01]|90[015]"]],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{3})", "$1 $2 $3 $4", ["20"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["6"]],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{1,2})", "$1 $2 $3 $4 $5", ["2(?:[0367]|4[3-8])"]],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{1,5})", "$1 $2 $3 $4", ["[3-57]|8[13-9]|9(?:0[89]|[2-579])|(?:2|80)[2-9]"]]
        ], 0, 0, "(15(?:0[06]|1[12]|[35]5|4[04]|6[26]|77|88|99)\\d)"
      ],
      LV: ["371", "00", "(?:[268]\\d|90)\\d{6}", [8],
        [
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["[269]|8[01]"]]
        ]
      ],
      LY: ["218", "00", "[2-9]\\d{8}", [9],
        [
          ["(\\d{2})(\\d{7})", "$1-$2", ["[2-9]"], "0$1"]
        ], "0"
      ],
      MA: ["212", "00", "[5-8]\\d{8}", [9],
        [
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["5[45]"], "0$1"],
          ["(\\d{4})(\\d{5})", "$1-$2", ["5(?:2[2-489]|3[5-9]|9)|8(?:0[89]|92)", "5(?:2(?:[2-49]|8[235-9])|3[5-9]|9)|8(?:0[89]|92)"], "0$1"],
          ["(\\d{2})(\\d{7})", "$1-$2", ["8"], "0$1"],
          ["(\\d{3})(\\d{6})", "$1-$2", ["[5-7]"], "0$1"]
        ], "0", 0, 0, 0, 0, 0, [
          ["5(?:2(?:[0-25-79]\\d|3[1-578]|4[02-46-8]|8[0235-7])|3(?:[0-47]\\d|5[02-9]|6[02-8]|8[014-9]|9[3-9])|(?:4[067]|5[03])\\d)\\d{5}"],
          ["(?:6(?:[0-79]\\d|8[0-247-9])|7(?:[0167]\\d|2[0-2]|5[01]|8[0-3]))\\d{6}"],
          ["80[0-7]\\d{6}"],
          ["89\\d{7}"], 0, 0, 0, 0, ["(?:592(?:4[0-2]|93)|80[89]\\d\\d)\\d{4}"]
        ]
      ],
      MC: ["377", "00", "(?:[3489]|6\\d)\\d{7}", [8, 9],
        [
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["4"], "0$1"],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[389]"]],
          ["(\\d)(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4 $5", ["6"], "0$1"]
        ], "0"
      ],
      MD: ["373", "00", "(?:[235-7]\\d|[89]0)\\d{6}", [8],
        [
          ["(\\d{3})(\\d{5})", "$1 $2", ["[89]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["22|3"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{3})", "$1 $2 $3", ["[25-7]"], "0$1"]
        ], "0"
      ],
      ME: ["382", "00", "(?:20|[3-79]\\d)\\d{6}|80\\d{6,7}", [8, 9],
        [
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[2-9]"], "0$1"]
        ], "0"
      ],
      MF: ["590", "00", "590\\d{6}|(?:69|80|9\\d)\\d{7}", [9], 0, "0", 0, 0, 0, 0, 0, [
        ["590(?:0[079]|[14]3|[27][79]|3[03-7]|5[0-268]|87)\\d{4}"],
        ["69(?:0\\d\\d|1(?:2[2-9]|3[0-5]))\\d{4}"],
        ["80[0-5]\\d{6}"], 0, 0, 0, 0, 0, ["9(?:(?:395|76[018])\\d|475[0-5])\\d{4}"]
      ]],
      MG: ["261", "00", "[23]\\d{8}", [9],
        [
          ["(\\d{2})(\\d{2})(\\d{3})(\\d{2})", "$1 $2 $3 $4", ["[23]"], "0$1"]
        ], "0", 0, "([24-9]\\d{6})$|0", "20$1"
      ],
      MH: ["692", "011", "329\\d{4}|(?:[256]\\d|45)\\d{5}", [7],
        [
          ["(\\d{3})(\\d{4})", "$1-$2", ["[2-6]"]]
        ], "1"
      ],
      MK: ["389", "00", "[2-578]\\d{7}", [8],
        [
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["2|34[47]|4(?:[37]7|5[47]|64)"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["[347]"], "0$1"],
          ["(\\d{3})(\\d)(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[58]"], "0$1"]
        ], "0"
      ],
      ML: ["223", "00", "[24-9]\\d{7}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[24-9]"]]
        ]
      ],
      MM: ["95", "00", "1\\d{5,7}|95\\d{6}|(?:[4-7]|9[0-46-9])\\d{6,8}|(?:2|8\\d)\\d{5,8}", [6, 7, 8, 9, 10],
        [
          ["(\\d)(\\d{2})(\\d{3})", "$1 $2 $3", ["16|2"], "0$1"],
          ["(\\d{2})(\\d{2})(\\d{3})", "$1 $2 $3", ["[45]|6(?:0[23]|[1-689]|7[235-7])|7(?:[0-4]|5[2-7])|8[1-6]"], "0$1"],
          ["(\\d)(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[12]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[4-7]|8[1-35]"], "0$1"],
          ["(\\d)(\\d{3})(\\d{4,6})", "$1 $2 $3", ["9(?:2[0-4]|[35-9]|4[137-9])"], "0$1"],
          ["(\\d)(\\d{4})(\\d{4})", "$1 $2 $3", ["2"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["8"], "0$1"],
          ["(\\d)(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["92"], "0$1"],
          ["(\\d)(\\d{5})(\\d{4})", "$1 $2 $3", ["9"], "0$1"]
        ], "0"
      ],
      MN: ["976", "001", "[12]\\d{7,9}|[5-9]\\d{7}", [8, 9, 10],
        [
          ["(\\d{2})(\\d{2})(\\d{4})", "$1 $2 $3", ["[12]1"], "0$1"],
          ["(\\d{4})(\\d{4})", "$1 $2", ["[5-9]"]],
          ["(\\d{3})(\\d{5,6})", "$1 $2", ["[12]2[1-3]"], "0$1"],
          ["(\\d{4})(\\d{5,6})", "$1 $2", ["[12](?:27|3[2-8]|4[2-68]|5[1-4689])", "[12](?:27|3[2-8]|4[2-68]|5[1-4689])[0-3]"], "0$1"],
          ["(\\d{5})(\\d{4,5})", "$1 $2", ["[12]"], "0$1"]
        ], "0"
      ],
      MO: ["853", "00", "0800\\d{3}|(?:28|[68]\\d)\\d{6}", [7, 8],
        [
          ["(\\d{4})(\\d{3})", "$1 $2", ["0"]],
          ["(\\d{4})(\\d{4})", "$1 $2", ["[268]"]]
        ]
      ],
      MP: ["1", "011", "[58]\\d{9}|(?:67|90)0\\d{7}", [10], 0, "1", 0, "([2-9]\\d{6})$|1", "670$1", 0, "670"],
      MQ: ["596", "00", "596\\d{6}|(?:69|80|9\\d)\\d{7}", [9],
        [
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[569]"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["8"], "0$1"]
        ], "0"
      ],
      MR: ["222", "00", "(?:[2-4]\\d\\d|800)\\d{5}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[2-48]"]]
        ]
      ],
      MS: ["1", "011", "(?:[58]\\d\\d|664|900)\\d{7}", [10], 0, "1", 0, "([34]\\d{6})$|1", "664$1", 0, "664"],
      MT: ["356", "00", "3550\\d{4}|(?:[2579]\\d\\d|800)\\d{5}", [8],
        [
          ["(\\d{4})(\\d{4})", "$1 $2", ["[2357-9]"]]
        ]
      ],
      MU: ["230", "0(?:0|[24-7]0|3[03])", "(?:[57]|8\\d\\d)\\d{7}|[2-468]\\d{6}", [7, 8, 10],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[2-46]|8[013]"]],
          ["(\\d{4})(\\d{4})", "$1 $2", ["[57]"]],
          ["(\\d{5})(\\d{5})", "$1 $2", ["8"]]
        ], 0, 0, 0, 0, 0, 0, 0, "020"
      ],
      MV: ["960", "0(?:0|19)", "(?:800|9[0-57-9]\\d)\\d{7}|[34679]\\d{6}", [7, 10],
        [
          ["(\\d{3})(\\d{4})", "$1-$2", ["[34679]"]],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["[89]"]]
        ], 0, 0, 0, 0, 0, 0, 0, "00"
      ],
      MW: ["265", "00", "(?:[1289]\\d|31|77)\\d{7}|1\\d{6}", [7, 9],
        [
          ["(\\d)(\\d{3})(\\d{3})", "$1 $2 $3", ["1[2-9]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["2"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[137-9]"], "0$1"]
        ], "0"
      ],
      MX: ["52", "0[09]", "1(?:(?:[27]2|44|87|99)[1-9]|65[0-689])\\d{7}|(?:1(?:[01]\\d|2[13-9]|[35][1-9]|4[0-35-9]|6[0-46-9]|7[013-9]|8[1-69]|9[1-8])|[2-9]\\d)\\d{8}", [10, 11],
        [
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2 $3", ["33|5[56]|81"], 0, 1],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["[2-9]"], 0, 1],
          ["(\\d)(\\d{2})(\\d{4})(\\d{4})", "$2 $3 $4", ["1(?:33|5[56]|81)"], 0, 1],
          ["(\\d)(\\d{3})(\\d{3})(\\d{4})", "$2 $3 $4", ["1"], 0, 1]
        ], "01", 0, "0(?:[12]|4[45])|1", 0, 0, 0, 0, "00"
      ],
      MY: ["60", "00", "1\\d{8,9}|(?:3\\d|[4-9])\\d{7}", [8, 9, 10],
        [
          ["(\\d)(\\d{3})(\\d{4})", "$1-$2 $3", ["[4-79]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1-$2 $3", ["1(?:[02469]|[378][1-9]|53)|8", "1(?:[02469]|[37][1-9]|53|8(?:[1-46-9]|5[7-9]))|8"], "0$1"],
          ["(\\d)(\\d{4})(\\d{4})", "$1-$2 $3", ["3"], "0$1"],
          ["(\\d)(\\d{3})(\\d{2})(\\d{4})", "$1-$2-$3-$4", ["1(?:[367]|80)"]],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1-$2 $3", ["15"], "0$1"],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1-$2 $3", ["1"], "0$1"]
        ], "0"
      ],
      MZ: ["258", "00", "(?:2|8\\d)\\d{7}", [8, 9],
        [
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["2|8[2-79]"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["8"]]
        ]
      ],
      NA: ["264", "00", "[68]\\d{7,8}", [8, 9],
        [
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["88"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["6"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["87"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["8"], "0$1"]
        ], "0"
      ],
      NC: ["687", "00", "(?:050|[2-57-9]\\d\\d)\\d{3}", [6],
        [
          ["(\\d{2})(\\d{2})(\\d{2})", "$1.$2.$3", ["[02-57-9]"]]
        ]
      ],
      NE: ["227", "00", "[027-9]\\d{7}", [8],
        [
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["08"]],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[089]|2[013]|7[047]"]]
        ]
      ],
      NF: ["672", "00", "[13]\\d{5}", [6],
        [
          ["(\\d{2})(\\d{4})", "$1 $2", ["1[0-3]"]],
          ["(\\d)(\\d{5})", "$1 $2", ["[13]"]]
        ], 0, 0, "([0-258]\\d{4})$", "3$1"
      ],
      NG: ["234", "009", "(?:[124-7]|9\\d{3})\\d{6}|[1-9]\\d{7}|[78]\\d{9,13}", [7, 8, 10, 11, 12, 13, 14],
        [
          ["(\\d{2})(\\d{2})(\\d{3})", "$1 $2 $3", ["78"], "0$1"],
          ["(\\d)(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[12]|9(?:0[3-9]|[1-9])"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{2,3})", "$1 $2 $3", ["[3-7]|8[2-9]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[7-9]"], "0$1"],
          ["(\\d{3})(\\d{4})(\\d{4,5})", "$1 $2 $3", ["[78]"], "0$1"],
          ["(\\d{3})(\\d{5})(\\d{5,6})", "$1 $2 $3", ["[78]"], "0$1"]
        ], "0"
      ],
      NI: ["505", "00", "(?:1800|[25-8]\\d{3})\\d{4}", [8],
        [
          ["(\\d{4})(\\d{4})", "$1 $2", ["[125-8]"]]
        ]
      ],
      NL: ["31", "00", "(?:[124-7]\\d\\d|3(?:[02-9]\\d|1[0-8]))\\d{6}|8\\d{6,9}|9\\d{6,10}|1\\d{4,5}", [5, 6, 7, 8, 9, 10, 11],
        [
          ["(\\d{3})(\\d{4,7})", "$1 $2", ["[89]0"], "0$1"],
          ["(\\d{2})(\\d{7})", "$1 $2", ["66"], "0$1"],
          ["(\\d)(\\d{8})", "$1 $2", ["6"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["1[16-8]|2[259]|3[124]|4[17-9]|5[124679]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[1-578]|91"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{5})", "$1 $2 $3", ["9"], "0$1"]
        ], "0"
      ],
      NO: ["47", "00", "(?:0|[2-9]\\d{3})\\d{4}", [5, 8],
        [
          ["(\\d{3})(\\d{2})(\\d{3})", "$1 $2 $3", ["8"]],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[2-79]"]]
        ], 0, 0, 0, 0, 0, "[02-689]|7[0-8]"
      ],
      NP: ["977", "00", "(?:1\\d|9)\\d{9}|[1-9]\\d{7}", [8, 10, 11],
        [
          ["(\\d)(\\d{7})", "$1-$2", ["1[2-6]"], "0$1"],
          ["(\\d{2})(\\d{6})", "$1-$2", ["1[01]|[2-8]|9(?:[1-59]|[67][2-6])"], "0$1"],
          ["(\\d{3})(\\d{7})", "$1-$2", ["9"]]
        ], "0"
      ],
      NR: ["674", "00", "(?:444|(?:55|8\\d)\\d|666)\\d{4}", [7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[4-68]"]]
        ]
      ],
      NU: ["683", "00", "(?:[4-7]|888\\d)\\d{3}", [4, 7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["8"]]
        ]
      ],
      NZ: ["64", "0(?:0|161)", "[1289]\\d{9}|50\\d{5}(?:\\d{2,3})?|[27-9]\\d{7,8}|(?:[34]\\d|6[0-35-9])\\d{6}|8\\d{4,6}", [5, 6, 7, 8, 9, 10],
        [
          ["(\\d{2})(\\d{3,8})", "$1 $2", ["8[1-79]"], "0$1"],
          ["(\\d{3})(\\d{2})(\\d{2,3})", "$1 $2 $3", ["50[036-8]|8|90", "50(?:[0367]|88)|8|90"], "0$1"],
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["24|[346]|7[2-57-9]|9[2-9]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["2(?:10|74)|[589]"], "0$1"],
          ["(\\d{2})(\\d{3,4})(\\d{4})", "$1 $2 $3", ["1|2[028]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,5})", "$1 $2 $3", ["2(?:[169]|7[0-35-9])|7"], "0$1"]
        ], "0", 0, 0, 0, 0, 0, 0, "00"
      ],
      OM: ["968", "00", "(?:1505|[279]\\d{3}|500)\\d{4}|800\\d{5,6}", [7, 8, 9],
        [
          ["(\\d{3})(\\d{4,6})", "$1 $2", ["[58]"]],
          ["(\\d{2})(\\d{6})", "$1 $2", ["2"]],
          ["(\\d{4})(\\d{4})", "$1 $2", ["[179]"]]
        ]
      ],
      PA: ["507", "00", "(?:00800|8\\d{3})\\d{6}|[68]\\d{7}|[1-57-9]\\d{6}", [7, 8, 10, 11],
        [
          ["(\\d{3})(\\d{4})", "$1-$2", ["[1-57-9]"]],
          ["(\\d{4})(\\d{4})", "$1-$2", ["[68]"]],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["8"]]
        ]
      ],
      PE: ["51", "00|19(?:1[124]|77|90)00", "(?:[14-8]|9\\d)\\d{7}", [8, 9],
        [
          ["(\\d{3})(\\d{5})", "$1 $2", ["80"], "(0$1)"],
          ["(\\d)(\\d{7})", "$1 $2", ["1"], "(0$1)"],
          ["(\\d{2})(\\d{6})", "$1 $2", ["[4-8]"], "(0$1)"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["9"]]
        ], "0", 0, 0, 0, 0, 0, 0, "00", " Anexo "
      ],
      PF: ["689", "00", "4\\d{5}(?:\\d{2})?|8\\d{7,8}", [6, 8, 9],
        [
          ["(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3", ["44"]],
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["4|8[7-9]"]],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["8"]]
        ]
      ],
      PG: ["675", "00|140[1-3]", "(?:180|[78]\\d{3})\\d{4}|(?:[2-589]\\d|64)\\d{5}", [7, 8],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["18|[2-69]|85"]],
          ["(\\d{4})(\\d{4})", "$1 $2", ["[78]"]]
        ], 0, 0, 0, 0, 0, 0, 0, "00"
      ],
      PH: ["63", "00", "(?:[2-7]|9\\d)\\d{8}|2\\d{5}|(?:1800|8)\\d{7,9}", [6, 8, 9, 10, 11, 12, 13],
        [
          ["(\\d)(\\d{5})", "$1 $2", ["2"], "(0$1)"],
          ["(\\d{4})(\\d{4,6})", "$1 $2", ["3(?:23|39|46)|4(?:2[3-6]|[35]9|4[26]|76)|544|88[245]|(?:52|64|86)2", "3(?:230|397|461)|4(?:2(?:35|[46]4|51)|396|4(?:22|63)|59[347]|76[15])|5(?:221|446)|642[23]|8(?:622|8(?:[24]2|5[13]))"], "(0$1)"],
          ["(\\d{5})(\\d{4})", "$1 $2", ["346|4(?:27|9[35])|883", "3469|4(?:279|9(?:30|56))|8834"], "(0$1)"],
          ["(\\d)(\\d{4})(\\d{4})", "$1 $2 $3", ["2"], "(0$1)"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[3-7]|8[2-8]"], "(0$1)"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["[89]"], "0$1"],
          ["(\\d{4})(\\d{3})(\\d{4})", "$1 $2 $3", ["1"]],
          ["(\\d{4})(\\d{1,2})(\\d{3})(\\d{4})", "$1 $2 $3 $4", ["1"]]
        ], "0"
      ],
      PK: ["92", "00", "122\\d{6}|[24-8]\\d{10,11}|9(?:[013-9]\\d{8,10}|2(?:[01]\\d\\d|2(?:[06-8]\\d|1[01]))\\d{7})|(?:[2-8]\\d{3}|92(?:[0-7]\\d|8[1-9]))\\d{6}|[24-9]\\d{8}|[89]\\d{7}", [8, 9, 10, 11, 12],
        [
          ["(\\d{3})(\\d{3})(\\d{2,7})", "$1 $2 $3", ["[89]0"], "0$1"],
          ["(\\d{4})(\\d{5})", "$1 $2", ["1"]],
          ["(\\d{3})(\\d{6,7})", "$1 $2", ["2(?:3[2358]|4[2-4]|9[2-8])|45[3479]|54[2-467]|60[468]|72[236]|8(?:2[2-689]|3[23578]|4[3478]|5[2356])|9(?:2[2-8]|3[27-9]|4[2-6]|6[3569]|9[25-8])", "9(?:2[3-8]|98)|(?:2(?:3[2358]|4[2-4]|9[2-8])|45[3479]|54[2-467]|60[468]|72[236]|8(?:2[2-689]|3[23578]|4[3478]|5[2356])|9(?:22|3[27-9]|4[2-6]|6[3569]|9[25-7]))[2-9]"], "(0$1)"],
          ["(\\d{2})(\\d{7,8})", "$1 $2", ["(?:2[125]|4[0-246-9]|5[1-35-7]|6[1-8]|7[14]|8[16]|91)[2-9]"], "(0$1)"],
          ["(\\d{5})(\\d{5})", "$1 $2", ["58"], "(0$1)"],
          ["(\\d{3})(\\d{7})", "$1 $2", ["3"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["2[125]|4[0-246-9]|5[1-35-7]|6[1-8]|7[14]|8[16]|91"], "(0$1)"],
          ["(\\d{3})(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["[24-9]"], "(0$1)"]
        ], "0"
      ],
      PL: ["48", "00", "(?:6|8\\d\\d)\\d{7}|[1-9]\\d{6}(?:\\d{2})?|[26]\\d{5}", [6, 7, 8, 9, 10],
        [
          ["(\\d{5})", "$1", ["19"]],
          ["(\\d{3})(\\d{3})", "$1 $2", ["11|20|64"]],
          ["(\\d{2})(\\d{2})(\\d{3})", "$1 $2 $3", ["(?:1[2-8]|2[2-69]|3[2-4]|4[1-468]|5[24-689]|6[1-3578]|7[14-7]|8[1-79]|9[145])1", "(?:1[2-8]|2[2-69]|3[2-4]|4[1-468]|5[24-689]|6[1-3578]|7[14-7]|8[1-79]|9[145])19"]],
          ["(\\d{3})(\\d{2})(\\d{2,3})", "$1 $2 $3", ["64"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["21|39|45|5[0137]|6[0469]|7[02389]|8(?:0[14]|8)"]],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["1[2-8]|[2-7]|8[1-79]|9[145]"]],
          ["(\\d{3})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["8"]]
        ]
      ],
      PM: ["508", "00", "[45]\\d{5}|(?:708|80\\d)\\d{6}", [6, 9],
        [
          ["(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3", ["[45]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["7"]],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["8"], "0$1"]
        ], "0"
      ],
      PR: ["1", "011", "(?:[589]\\d\\d|787)\\d{7}", [10], 0, "1", 0, 0, 0, 0, "787|939"],
      PS: ["970", "00", "[2489]2\\d{6}|(?:1\\d|5)\\d{8}", [8, 9, 10],
        [
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["[2489]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["5"], "0$1"],
          ["(\\d{4})(\\d{3})(\\d{3})", "$1 $2 $3", ["1"]]
        ], "0"
      ],
      PT: ["351", "00", "1693\\d{5}|(?:[26-9]\\d|30)\\d{7}", [9],
        [
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["2[12]"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["16|[236-9]"]]
        ]
      ],
      PW: ["680", "01[12]", "(?:[24-8]\\d\\d|345|900)\\d{4}", [7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[2-9]"]]
        ]
      ],
      PY: ["595", "00", "59\\d{4,6}|9\\d{5,10}|(?:[2-46-8]\\d|5[0-8])\\d{4,7}", [6, 7, 8, 9, 10, 11],
        [
          ["(\\d{3})(\\d{3,6})", "$1 $2", ["[2-9]0"], "0$1"],
          ["(\\d{2})(\\d{5})", "$1 $2", ["[26]1|3[289]|4[1246-8]|7[1-3]|8[1-36]"], "(0$1)"],
          ["(\\d{3})(\\d{4,5})", "$1 $2", ["2[279]|3[13-5]|4[359]|5|6(?:[34]|7[1-46-8])|7[46-8]|85"], "(0$1)"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["2[14-68]|3[26-9]|4[1246-8]|6(?:1|75)|7[1-35]|8[1-36]"], "(0$1)"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["87"]],
          ["(\\d{3})(\\d{6})", "$1 $2", ["9(?:[5-79]|8[1-6])"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[2-8]"], "0$1"],
          ["(\\d{4})(\\d{3})(\\d{4})", "$1 $2 $3", ["9"]]
        ], "0"
      ],
      QA: ["974", "00", "800\\d{4}|(?:2|800)\\d{6}|(?:0080|[3-7])\\d{7}", [7, 8, 9, 11],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["2[16]|8"]],
          ["(\\d{4})(\\d{4})", "$1 $2", ["[3-7]"]]
        ]
      ],
      RE: ["262", "00", "(?:26|[689]\\d)\\d{7}", [9],
        [
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[2689]"], "0$1"]
        ], "0", 0, 0, 0, 0, 0, [
          ["26(?:2\\d\\d|3(?:0\\d|1[0-6]))\\d{4}"],
          ["69(?:2\\d\\d|3(?:[06][0-6]|1[013]|2[0-2]|3[0-39]|4\\d|5[0-5]|7[0-37]|8[0-8]|9[0-479]))\\d{4}"],
          ["80\\d{7}"],
          ["89[1-37-9]\\d{6}"], 0, 0, 0, 0, ["9(?:399[0-3]|479[0-5]|76(?:2[27]|3[0-37]))\\d{4}"],
          ["8(?:1[019]|2[0156]|84|90)\\d{6}"]
        ]
      ],
      RO: ["40", "00", "(?:[2378]\\d|62|90)\\d{7}|[23]\\d{5}", [6, 9],
        [
          ["(\\d{3})(\\d{3})", "$1 $2", ["2[3-6]", "2[3-6]\\d9"], "0$1"],
          ["(\\d{2})(\\d{4})", "$1 $2", ["219|31"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[23]1"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[236-9]"], "0$1"]
        ], "0", 0, 0, 0, 0, 0, 0, 0, " int "
      ],
      RS: ["381", "00", "38[02-9]\\d{6,9}|6\\d{7,9}|90\\d{4,8}|38\\d{5,6}|(?:7\\d\\d|800)\\d{3,9}|(?:[12]\\d|3[0-79])\\d{5,10}", [6, 7, 8, 9, 10, 11, 12],
        [
          ["(\\d{3})(\\d{3,9})", "$1 $2", ["(?:2[389]|39)0|[7-9]"], "0$1"],
          ["(\\d{2})(\\d{5,10})", "$1 $2", ["[1-36]"], "0$1"]
        ], "0"
      ],
      RU: ["7", "810", "8\\d{13}|[347-9]\\d{9}", [10, 14],
        [
          ["(\\d{4})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["7(?:1[0-8]|2[1-9])", "7(?:1(?:[0-356]2|4[29]|7|8[27])|2(?:1[23]|[2-9]2))", "7(?:1(?:[0-356]2|4[29]|7|8[27])|2(?:13[03-69]|62[013-9]))|72[1-57-9]2"], "8 ($1)", 1],
          ["(\\d{5})(\\d)(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["7(?:1[0-68]|2[1-9])", "7(?:1(?:[06][3-6]|[18]|2[35]|[3-5][3-5])|2(?:[13][3-5]|[24-689]|7[457]))", "7(?:1(?:0(?:[356]|4[023])|[18]|2(?:3[013-9]|5)|3[45]|43[013-79]|5(?:3[1-8]|4[1-7]|5)|6(?:3[0-35-9]|[4-6]))|2(?:1(?:3[178]|[45])|[24-689]|3[35]|7[457]))|7(?:14|23)4[0-8]|71(?:33|45)[1-79]"], "8 ($1)", 1],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["7"], "8 ($1)", 1],
          ["(\\d{3})(\\d{3})(\\d{2})(\\d{2})", "$1 $2-$3-$4", ["[349]|8(?:[02-7]|1[1-8])"], "8 ($1)", 1],
          ["(\\d{4})(\\d{4})(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["8"], "8 ($1)"]
        ], "8", 0, 0, 0, 0, "3[04-689]|[489]", 0, "8~10"
      ],
      RW: ["250", "00", "(?:06|[27]\\d\\d|[89]00)\\d{6}", [8, 9],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["0"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["2"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[7-9]"], "0$1"]
        ], "0"
      ],
      SA: ["966", "00", "92\\d{7}|(?:[15]|8\\d)\\d{8}", [9, 10],
        [
          ["(\\d{4})(\\d{5})", "$1 $2", ["9"]],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["1"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["5"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["81"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["8"]]
        ], "0"
      ],
      SB: ["677", "0[01]", "(?:[1-6]|[7-9]\\d\\d)\\d{4}", [5, 7],
        [
          ["(\\d{2})(\\d{5})", "$1 $2", ["7|8[4-9]|9(?:[1-8]|9[0-8])"]]
        ]
      ],
      SC: ["248", "010|0[0-2]", "800\\d{4}|(?:[249]\\d|64)\\d{5}", [7],
        [
          ["(\\d)(\\d{3})(\\d{3})", "$1 $2 $3", ["[246]|9[57]"]]
        ], 0, 0, 0, 0, 0, 0, 0, "00"
      ],
      SD: ["249", "00", "[19]\\d{8}", [9],
        [
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[19]"], "0$1"]
        ], "0"
      ],
      SE: ["46", "00", "(?:[26]\\d\\d|9)\\d{9}|[1-9]\\d{8}|[1-689]\\d{7}|[1-4689]\\d{6}|2\\d{5}", [6, 7, 8, 9, 10],
        [
          ["(\\d{2})(\\d{2,3})(\\d{2})", "$1-$2 $3", ["20"], "0$1", 0, "$1 $2 $3"],
          ["(\\d{3})(\\d{4})", "$1-$2", ["9(?:00|39|44|9)"], "0$1", 0, "$1 $2"],
          ["(\\d{2})(\\d{3})(\\d{2})", "$1-$2 $3", ["[12][136]|3[356]|4[0246]|6[03]|90[1-9]"], "0$1", 0, "$1 $2 $3"],
          ["(\\d)(\\d{2,3})(\\d{2})(\\d{2})", "$1-$2 $3 $4", ["8"], "0$1", 0, "$1 $2 $3 $4"],
          ["(\\d{3})(\\d{2,3})(\\d{2})", "$1-$2 $3", ["1[2457]|2(?:[247-9]|5[0138])|3[0247-9]|4[1357-9]|5[0-35-9]|6(?:[125689]|4[02-57]|7[0-2])|9(?:[125-8]|3[02-5]|4[0-3])"], "0$1", 0, "$1 $2 $3"],
          ["(\\d{3})(\\d{2,3})(\\d{3})", "$1-$2 $3", ["9(?:00|39|44)"], "0$1", 0, "$1 $2 $3"],
          ["(\\d{2})(\\d{2,3})(\\d{2})(\\d{2})", "$1-$2 $3 $4", ["1[13689]|2[0136]|3[1356]|4[0246]|54|6[03]|90[1-9]"], "0$1", 0, "$1 $2 $3 $4"],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1-$2 $3 $4", ["10|7"], "0$1", 0, "$1 $2 $3 $4"],
          ["(\\d)(\\d{3})(\\d{3})(\\d{2})", "$1-$2 $3 $4", ["8"], "0$1", 0, "$1 $2 $3 $4"],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1-$2 $3 $4", ["[13-5]|2(?:[247-9]|5[0138])|6(?:[124-689]|7[0-2])|9(?:[125-8]|3[02-5]|4[0-3])"], "0$1", 0, "$1 $2 $3 $4"],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{3})", "$1-$2 $3 $4", ["9"], "0$1", 0, "$1 $2 $3 $4"],
          ["(\\d{3})(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1-$2 $3 $4 $5", ["[26]"], "0$1", 0, "$1 $2 $3 $4 $5"]
        ], "0"
      ],
      SG: ["65", "0[0-3]\\d", "(?:(?:1\\d|8)\\d\\d|7000)\\d{7}|[3689]\\d{7}", [8, 10, 11],
        [
          ["(\\d{4})(\\d{4})", "$1 $2", ["[369]|8(?:0[1-8]|[1-9])"]],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["8"]],
          ["(\\d{4})(\\d{4})(\\d{3})", "$1 $2 $3", ["7"]],
          ["(\\d{4})(\\d{3})(\\d{4})", "$1 $2 $3", ["1"]]
        ]
      ],
      SH: ["290", "00", "(?:[256]\\d|8)\\d{3}", [4, 5], 0, 0, 0, 0, 0, 0, "[256]"],
      SI: ["386", "00|10(?:22|66|88|99)", "[1-7]\\d{7}|8\\d{4,7}|90\\d{4,6}", [5, 6, 7, 8],
        [
          ["(\\d{2})(\\d{3,6})", "$1 $2", ["8[09]|9"], "0$1"],
          ["(\\d{3})(\\d{5})", "$1 $2", ["59|8"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["[37][01]|4[0139]|51|6"], "0$1"],
          ["(\\d)(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[1-57]"], "(0$1)"]
        ], "0", 0, 0, 0, 0, 0, 0, "00"
      ],
      SJ: ["47", "00", "0\\d{4}|(?:[489]\\d|79)\\d{6}", [5, 8], 0, 0, 0, 0, 0, 0, "79"],
      SK: ["421", "00", "[2-689]\\d{8}|[2-59]\\d{6}|[2-5]\\d{5}", [6, 7, 9],
        [
          ["(\\d)(\\d{2})(\\d{3,4})", "$1 $2 $3", ["21"], "0$1"],
          ["(\\d{2})(\\d{2})(\\d{2,3})", "$1 $2 $3", ["[3-5][1-8]1", "[3-5][1-8]1[67]"], "0$1"],
          ["(\\d)(\\d{3})(\\d{3})(\\d{2})", "$1/$2 $3 $4", ["2"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[689]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1/$2 $3 $4", ["[3-5]"], "0$1"]
        ], "0"
      ],
      SL: ["232", "00", "(?:[237-9]\\d|66)\\d{6}", [8],
        [
          ["(\\d{2})(\\d{6})", "$1 $2", ["[236-9]"], "(0$1)"]
        ], "0"
      ],
      SM: ["378", "00", "(?:0549|[5-7]\\d)\\d{6}", [8, 10],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[5-7]"]],
          ["(\\d{4})(\\d{6})", "$1 $2", ["0"]]
        ], 0, 0, "([89]\\d{5})$", "0549$1"
      ],
      SN: ["221", "00", "(?:[378]\\d|93)\\d{7}", [9],
        [
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["8"]],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[379]"]]
        ]
      ],
      SO: ["252", "00", "[346-9]\\d{8}|[12679]\\d{7}|[1-5]\\d{6}|[1348]\\d{5}", [6, 7, 8, 9],
        [
          ["(\\d{2})(\\d{4})", "$1 $2", ["8[125]"]],
          ["(\\d{6})", "$1", ["[134]"]],
          ["(\\d)(\\d{6})", "$1 $2", ["[15]|2[0-79]|3[0-46-8]|4[0-7]"]],
          ["(\\d)(\\d{7})", "$1 $2", ["(?:2|90)4|[67]"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[348]|64|79|90"]],
          ["(\\d{2})(\\d{5,7})", "$1 $2", ["1|28|6[0-35-9]|77|9[2-9]"]]
        ], "0"
      ],
      SR: ["597", "00", "(?:[2-5]|68|[78]\\d)\\d{5}", [6, 7],
        [
          ["(\\d{2})(\\d{2})(\\d{2})", "$1-$2-$3", ["56"]],
          ["(\\d{3})(\\d{3})", "$1-$2", ["[2-5]"]],
          ["(\\d{3})(\\d{4})", "$1-$2", ["[6-8]"]]
        ]
      ],
      SS: ["211", "00", "[19]\\d{8}", [9],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[19]"], "0$1"]
        ], "0"
      ],
      ST: ["239", "00", "(?:22|9\\d)\\d{5}", [7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[29]"]]
        ]
      ],
      SV: ["503", "00", "[267]\\d{7}|[89]00\\d{4}(?:\\d{4})?", [7, 8, 11],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[89]"]],
          ["(\\d{4})(\\d{4})", "$1 $2", ["[267]"]],
          ["(\\d{3})(\\d{4})(\\d{4})", "$1 $2 $3", ["[89]"]]
        ]
      ],
      SX: ["1", "011", "7215\\d{6}|(?:[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "(5\\d{6})$|1", "721$1", 0, "721"],
      SY: ["963", "00", "[1-39]\\d{8}|[1-5]\\d{7}", [8, 9],
        [
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[1-5]"], "0$1", 1],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["9"], "0$1", 1]
        ], "0"
      ],
      SZ: ["268", "00", "0800\\d{4}|(?:[237]\\d|900)\\d{6}", [8, 9],
        [
          ["(\\d{4})(\\d{4})", "$1 $2", ["[0237]"]],
          ["(\\d{5})(\\d{4})", "$1 $2", ["9"]]
        ]
      ],
      TA: ["290", "00", "8\\d{3}", [4], 0, 0, 0, 0, 0, 0, "8"],
      TC: ["1", "011", "(?:[58]\\d\\d|649|900)\\d{7}", [10], 0, "1", 0, "([2-479]\\d{6})$|1", "649$1", 0, "649"],
      TD: ["235", "00|16", "(?:22|[69]\\d|77)\\d{6}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[2679]"]]
        ], 0, 0, 0, 0, 0, 0, 0, "00"
      ],
      TG: ["228", "00", "[279]\\d{7}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[279]"]]
        ]
      ],
      TH: ["66", "00[1-9]", "(?:001800|[2-57]|[689]\\d)\\d{7}|1\\d{7,9}", [8, 9, 10, 13],
        [
          ["(\\d)(\\d{3})(\\d{4})", "$1 $2 $3", ["2"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[13-9]"], "0$1"],
          ["(\\d{4})(\\d{3})(\\d{3})", "$1 $2 $3", ["1"]]
        ], "0"
      ],
      TJ: ["992", "810", "[0-57-9]\\d{8}", [9],
        [
          ["(\\d{6})(\\d)(\\d{2})", "$1 $2 $3", ["331", "3317"]],
          ["(\\d{3})(\\d{2})(\\d{4})", "$1 $2 $3", ["44[02-479]|[34]7"]],
          ["(\\d{4})(\\d)(\\d{4})", "$1 $2 $3", ["3[1-5]"]],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[0-57-9]"]]
        ], 0, 0, 0, 0, 0, 0, 0, "8~10"
      ],
      TK: ["690", "00", "[2-47]\\d{3,6}", [4, 5, 6, 7]],
      TL: ["670", "00", "7\\d{7}|(?:[2-47]\\d|[89]0)\\d{5}", [7, 8],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[2-489]|70"]],
          ["(\\d{4})(\\d{4})", "$1 $2", ["7"]]
        ]
      ],
      TM: ["993", "810", "[1-6]\\d{7}", [8],
        [
          ["(\\d{2})(\\d{2})(\\d{2})(\\d{2})", "$1 $2-$3-$4", ["12"], "(8 $1)"],
          ["(\\d{3})(\\d)(\\d{2})(\\d{2})", "$1 $2-$3-$4", ["[1-5]"], "(8 $1)"],
          ["(\\d{2})(\\d{6})", "$1 $2", ["6"], "8 $1"]
        ], "8", 0, 0, 0, 0, 0, 0, "8~10"
      ],
      TN: ["216", "00", "[2-57-9]\\d{7}", [8],
        [
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["[2-57-9]"]]
        ]
      ],
      TO: ["676", "00", "(?:0800|(?:[5-8]\\d\\d|999)\\d)\\d{3}|[2-8]\\d{4}", [5, 7],
        [
          ["(\\d{2})(\\d{3})", "$1-$2", ["[2-4]|50|6[09]|7[0-24-69]|8[05]"]],
          ["(\\d{4})(\\d{3})", "$1 $2", ["0"]],
          ["(\\d{3})(\\d{4})", "$1 $2", ["[5-9]"]]
        ]
      ],
      TR: ["90", "00", "4\\d{6}|8\\d{11,12}|(?:[2-58]\\d\\d|900)\\d{7}", [7, 10, 12, 13],
        [
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["512|8[01589]|90"], "0$1", 1],
          ["(\\d{3})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["5(?:[0-59]|61)", "5(?:[0-59]|61[06])", "5(?:[0-59]|61[06]1)"], "0$1", 1],
          ["(\\d{3})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[24][1-8]|3[1-9]"], "(0$1)", 1],
          ["(\\d{3})(\\d{3})(\\d{6,7})", "$1 $2 $3", ["80"], "0$1", 1]
        ], "0"
      ],
      TT: ["1", "011", "(?:[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "([2-46-8]\\d{6})$|1", "868$1", 0, "868"],
      TV: ["688", "00", "(?:2|7\\d\\d|90)\\d{4}", [5, 6, 7],
        [
          ["(\\d{2})(\\d{3})", "$1 $2", ["2"]],
          ["(\\d{2})(\\d{4})", "$1 $2", ["90"]],
          ["(\\d{2})(\\d{5})", "$1 $2", ["7"]]
        ]
      ],
      TW: ["886", "0(?:0[25-79]|19)", "[2-689]\\d{8}|7\\d{9,10}|[2-8]\\d{7}|2\\d{6}", [7, 8, 9, 10, 11],
        [
          ["(\\d{2})(\\d)(\\d{4})", "$1 $2 $3", ["202"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[258]0"], "0$1"],
          ["(\\d)(\\d{3,4})(\\d{4})", "$1 $2 $3", ["[23568]|4(?:0[02-48]|[1-47-9])|7[1-9]", "[23568]|4(?:0[2-48]|[1-47-9])|(?:400|7)[1-9]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[49]"], "0$1"],
          ["(\\d{2})(\\d{4})(\\d{4,5})", "$1 $2 $3", ["7"], "0$1"]
        ], "0", 0, 0, 0, 0, 0, 0, 0, "#"
      ],
      TZ: ["255", "00[056]", "(?:[25-8]\\d|41|90)\\d{7}", [9],
        [
          ["(\\d{3})(\\d{2})(\\d{4})", "$1 $2 $3", ["[89]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[24]"], "0$1"],
          ["(\\d{2})(\\d{7})", "$1 $2", ["5"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[67]"], "0$1"]
        ], "0"
      ],
      UA: ["380", "00", "[89]\\d{9}|[3-9]\\d{8}", [9, 10],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["6[12][29]|(?:3[1-8]|4[136-8]|5[12457]|6[49])2|(?:56|65)[24]", "6[12][29]|(?:35|4[1378]|5[12457]|6[49])2|(?:56|65)[24]|(?:3[1-46-8]|46)2[013-9]"], "0$1"],
          ["(\\d{4})(\\d{5})", "$1 $2", ["3[1-8]|4(?:[1367]|[45][6-9]|8[4-6])|5(?:[1-5]|6[0135689]|7[4-6])|6(?:[12][3-7]|[459])", "3[1-8]|4(?:[1367]|[45][6-9]|8[4-6])|5(?:[1-5]|6(?:[015689]|3[02389])|7[4-6])|6(?:[12][3-7]|[459])"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[3-7]|89|9[1-9]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[89]"], "0$1"]
        ], "0", 0, 0, 0, 0, 0, 0, "0~0"
      ],
      UG: ["256", "00[057]", "800\\d{6}|(?:[29]0|[347]\\d)\\d{7}", [9],
        [
          ["(\\d{4})(\\d{5})", "$1 $2", ["202", "2024"], "0$1"],
          ["(\\d{3})(\\d{6})", "$1 $2", ["[27-9]|4(?:6[45]|[7-9])"], "0$1"],
          ["(\\d{2})(\\d{7})", "$1 $2", ["[34]"], "0$1"]
        ], "0"
      ],
      US: ["1", "011", "[2-9]\\d{9}|3\\d{6}", [10],
        [
          ["(\\d{3})(\\d{4})", "$1-$2", ["310"], 0, 1],
          ["(\\d{3})(\\d{3})(\\d{4})", "($1) $2-$3", ["[2-9]"], 0, 1, "$1-$2-$3"]
        ], "1", 0, 0, 0, 0, 0, [
          ["(?:5056(?:[0-35-9]\\d|4[468])|73020\\d)\\d{4}|(?:4722|505[2-57-9]|983[289])\\d{6}|(?:2(?:0[1-35-9]|1[02-9]|2[03-57-9]|3[149]|4[08]|5[1-46]|6[0279]|7[0269]|8[13])|3(?:0[1-57-9]|1[02-9]|2[013569]|3[0-24679]|4[167]|5[0-2]|6[0149]|8[056])|4(?:0[124-9]|1[02-579]|2[3-5]|3[0245]|4[023578]|58|6[349]|7[0589]|8[04])|5(?:0[1-47-9]|1[0235-8]|20|3[0149]|4[01]|5[179]|6[1-47]|7[0-5]|8[0256])|6(?:0[1-35-9]|1[024-9]|2[03689]|[34][016]|5[01679]|6[0-279]|78|8[0-29])|7(?:0[1-46-8]|1[2-9]|2[04-7]|3[1247]|4[037]|5[47]|6[02359]|7[0-59]|8[156])|8(?:0[1-68]|1[02-8]|2[068]|3[0-2589]|4[03578]|5[046-9]|6[02-5]|7[028])|9(?:0[1346-9]|1[02-9]|2[0589]|3[0146-8]|4[01357-9]|5[12469]|7[0-389]|8[04-69]))[2-9]\\d{6}"],
          [""],
          ["8(?:00|33|44|55|66|77|88)[2-9]\\d{6}"],
          ["900[2-9]\\d{6}"],
          ["52(?:3(?:[2-46-9][02-9]\\d|5(?:[02-46-9]\\d|5[0-46-9]))|4(?:[2-478][02-9]\\d|5(?:[034]\\d|2[024-9]|5[0-46-9])|6(?:0[1-9]|[2-9]\\d)|9(?:[05-9]\\d|2[0-5]|49)))\\d{4}|52[34][2-9]1[02-9]\\d{4}|5(?:00|2[125-9]|33|44|66|77|88)[2-9]\\d{6}"]
        ]
      ],
      UY: ["598", "0(?:0|1[3-9]\\d)", "0004\\d{2,9}|[1249]\\d{7}|(?:[49]\\d|80)\\d{5}", [6, 7, 8, 9, 10, 11, 12, 13],
        [
          ["(\\d{3})(\\d{3,4})", "$1 $2", ["0"]],
          ["(\\d{3})(\\d{4})", "$1 $2", ["[49]0|8"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["9"], "0$1"],
          ["(\\d{4})(\\d{4})", "$1 $2", ["[124]"]],
          ["(\\d{3})(\\d{3})(\\d{2,4})", "$1 $2 $3", ["0"]],
          ["(\\d{3})(\\d{3})(\\d{3})(\\d{2,4})", "$1 $2 $3 $4", ["0"]]
        ], "0", 0, 0, 0, 0, 0, 0, "00", " int. "
      ],
      UZ: ["998", "810", "(?:20|33|[5-79]\\d|88)\\d{7}", [9],
        [
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["[235-9]"], "8 $1"]
        ], "8", 0, 0, 0, 0, 0, 0, "8~10"
      ],
      VA: ["39", "00", "0\\d{5,10}|3[0-8]\\d{7,10}|55\\d{8}|8\\d{5}(?:\\d{2,4})?|(?:1\\d|39)\\d{7,8}", [6, 7, 8, 9, 10, 11], 0, 0, 0, 0, 0, 0, "06698"],
      VC: ["1", "011", "(?:[58]\\d\\d|784|900)\\d{7}", [10], 0, "1", 0, "([2-7]\\d{6})$|1", "784$1", 0, "784"],
      VE: ["58", "00", "[68]00\\d{7}|(?:[24]\\d|[59]0)\\d{8}", [10],
        [
          ["(\\d{3})(\\d{7})", "$1-$2", ["[24-689]"], "0$1"]
        ], "0"
      ],
      VG: ["1", "011", "(?:284|[58]\\d\\d|900)\\d{7}", [10], 0, "1", 0, "([2-578]\\d{6})$|1", "284$1", 0, "284"],
      VI: ["1", "011", "[58]\\d{9}|(?:34|90)0\\d{7}", [10], 0, "1", 0, "([2-9]\\d{6})$|1", "340$1", 0, "340"],
      VN: ["84", "00", "[12]\\d{9}|[135-9]\\d{8}|[16]\\d{7}|[16-8]\\d{6}", [7, 8, 9, 10],
        [
          ["(\\d{2})(\\d{5})", "$1 $2", ["80"], "0$1", 1],
          ["(\\d{4})(\\d{4,6})", "$1 $2", ["1"], 0, 1],
          ["(\\d{2})(\\d{3})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["6"], "0$1", 1],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[357-9]"], "0$1", 1],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2 $3", ["2[48]"], "0$1", 1],
          ["(\\d{3})(\\d{4})(\\d{3})", "$1 $2 $3", ["2"], "0$1", 1]
        ], "0"
      ],
      VU: ["678", "00", "[57-9]\\d{6}|(?:[238]\\d|48)\\d{3}", [5, 7],
        [
          ["(\\d{3})(\\d{4})", "$1 $2", ["[57-9]"]]
        ]
      ],
      WF: ["681", "00", "(?:40|72)\\d{4}|8\\d{5}(?:\\d{3})?", [6, 9],
        [
          ["(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3", ["[478]"]],
          ["(\\d{3})(\\d{2})(\\d{2})(\\d{2})", "$1 $2 $3 $4", ["8"]]
        ]
      ],
      WS: ["685", "0", "(?:[2-6]|8\\d{5})\\d{4}|[78]\\d{6}|[68]\\d{5}", [5, 6, 7, 10],
        [
          ["(\\d{5})", "$1", ["[2-5]|6[1-9]"]],
          ["(\\d{3})(\\d{3,7})", "$1 $2", ["[68]"]],
          ["(\\d{2})(\\d{5})", "$1 $2", ["7"]]
        ]
      ],
      XK: ["383", "00", "[23]\\d{7,8}|(?:4\\d\\d|[89]00)\\d{5}", [8, 9],
        [
          ["(\\d{3})(\\d{5})", "$1 $2", ["[89]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3})", "$1 $2 $3", ["[2-4]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[23]"], "0$1"]
        ], "0"
      ],
      YE: ["967", "00", "(?:1|7\\d)\\d{7}|[1-7]\\d{6}", [7, 8, 9],
        [
          ["(\\d)(\\d{3})(\\d{3,4})", "$1 $2 $3", ["[1-6]|7(?:[24-6]|8[0-7])"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["7"], "0$1"]
        ], "0"
      ],
      YT: ["262", "00", "(?:80|9\\d)\\d{7}|(?:26|63)9\\d{6}", [9], 0, "0", 0, 0, 0, 0, 0, [
        ["269(?:0[0-467]|5[0-4]|6\\d|[78]0)\\d{4}"],
        ["639(?:0[0-79]|1[019]|[267]\\d|3[09]|40|5[05-9]|9[04-79])\\d{4}"],
        ["80\\d{7}"], 0, 0, 0, 0, 0, ["9(?:(?:39|47)8[01]|769\\d)\\d{4}"]
      ]],
      ZA: ["27", "00", "[1-79]\\d{8}|8\\d{4,9}", [5, 6, 7, 8, 9, 10],
        [
          ["(\\d{2})(\\d{3,4})", "$1 $2", ["8[1-4]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{2,3})", "$1 $2 $3", ["8[1-4]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["860"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["[1-9]"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["8"], "0$1"]
        ], "0"
      ],
      ZM: ["260", "00", "800\\d{6}|(?:21|63|[79]\\d)\\d{7}", [9],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[28]"], "0$1"],
          ["(\\d{2})(\\d{7})", "$1 $2", ["[79]"], "0$1"]
        ], "0"
      ],
      ZW: ["263", "00", "2(?:[0-57-9]\\d{6,8}|6[0-24-9]\\d{6,7})|[38]\\d{9}|[35-8]\\d{8}|[3-6]\\d{7}|[1-689]\\d{6}|[1-3569]\\d{5}|[1356]\\d{4}", [5, 6, 7, 8, 9, 10],
        [
          ["(\\d{3})(\\d{3,5})", "$1 $2", ["2(?:0[45]|2[278]|[49]8)|3(?:[09]8|17)|6(?:[29]8|37|75)|[23][78]|(?:33|5[15]|6[68])[78]"], "0$1"],
          ["(\\d)(\\d{3})(\\d{2,4})", "$1 $2 $3", ["[49]"], "0$1"],
          ["(\\d{3})(\\d{4})", "$1 $2", ["80"], "0$1"],
          ["(\\d{2})(\\d{7})", "$1 $2", ["24|8[13-59]|(?:2[05-79]|39|5[45]|6[15-8])2", "2(?:02[014]|4|[56]20|[79]2)|392|5(?:42|525)|6(?:[16-8]21|52[013])|8[13-59]"], "(0$1)"],
          ["(\\d{2})(\\d{3})(\\d{4})", "$1 $2 $3", ["7"], "0$1"],
          ["(\\d{3})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["2(?:1[39]|2[0157]|[378]|[56][14])|3(?:12|29)", "2(?:1[39]|2[0157]|[378]|[56][14])|3(?:123|29)"], "0$1"],
          ["(\\d{4})(\\d{6})", "$1 $2", ["8"], "0$1"],
          ["(\\d{2})(\\d{3,5})", "$1 $2", ["1|2(?:0[0-36-9]|12|29|[56])|3(?:1[0-689]|[24-6])|5(?:[0236-9]|1[2-4])|6(?:[013-59]|7[0-46-9])|(?:33|55|6[68])[0-69]|(?:29|3[09]|62)[0-79]"], "0$1"],
          ["(\\d{2})(\\d{3})(\\d{3,4})", "$1 $2 $3", ["29[013-9]|39|54"], "0$1"],
          ["(\\d{4})(\\d{3,5})", "$1 $2", ["(?:25|54)8", "258|5483"], "0$1"]
        ], "0"
      ]
    },
    nonGeographic: {
      800: ["800", 0, "(?:00|[1-9]\\d)\\d{6}", [8],
        [
          ["(\\d{4})(\\d{4})", "$1 $2", ["\\d"]]
        ], 0, 0, 0, 0, 0, 0, [0, 0, ["(?:00|[1-9]\\d)\\d{6}"]]
      ],
      808: ["808", 0, "[1-9]\\d{7}", [8],
        [
          ["(\\d{4})(\\d{4})", "$1 $2", ["[1-9]"]]
        ], 0, 0, 0, 0, 0, 0, [0, 0, 0, 0, 0, 0, 0, 0, 0, ["[1-9]\\d{7}"]]
      ],
      870: ["870", 0, "7\\d{11}|[35-7]\\d{8}", [9, 12],
        [
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["[35-7]"]]
        ], 0, 0, 0, 0, 0, 0, [0, ["(?:[356]|774[45])\\d{8}|7[6-8]\\d{7}"]]
      ],
      878: ["878", 0, "10\\d{10}", [12],
        [
          ["(\\d{2})(\\d{5})(\\d{5})", "$1 $2 $3", ["1"]]
        ], 0, 0, 0, 0, 0, 0, [0, 0, 0, 0, 0, 0, 0, 0, ["10\\d{10}"]]
      ],
      881: ["881", 0, "6\\d{9}|[0-36-9]\\d{8}", [9, 10],
        [
          ["(\\d)(\\d{3})(\\d{5})", "$1 $2 $3", ["[0-37-9]"]],
          ["(\\d)(\\d{3})(\\d{5,6})", "$1 $2 $3", ["6"]]
        ], 0, 0, 0, 0, 0, 0, [0, ["6\\d{9}|[0-36-9]\\d{8}"]]
      ],
      882: ["882", 0, "[13]\\d{6}(?:\\d{2,5})?|[19]\\d{7}|(?:[25]\\d\\d|4)\\d{7}(?:\\d{2})?", [7, 8, 9, 10, 11, 12],
        [
          ["(\\d{2})(\\d{5})", "$1 $2", ["16|342"]],
          ["(\\d{2})(\\d{6})", "$1 $2", ["49"]],
          ["(\\d{2})(\\d{2})(\\d{4})", "$1 $2 $3", ["1[36]|9"]],
          ["(\\d{2})(\\d{4})(\\d{3})", "$1 $2 $3", ["3[23]"]],
          ["(\\d{2})(\\d{3,4})(\\d{4})", "$1 $2 $3", ["16"]],
          ["(\\d{2})(\\d{4})(\\d{4})", "$1 $2 $3", ["10|23|3(?:[15]|4[57])|4|51"]],
          ["(\\d{3})(\\d{4})(\\d{4})", "$1 $2 $3", ["34"]],
          ["(\\d{2})(\\d{4,5})(\\d{5})", "$1 $2 $3", ["[1-35]"]]
        ], 0, 0, 0, 0, 0, 0, [0, ["342\\d{4}|(?:337|49)\\d{6}|(?:3(?:2|47|7\\d{3})|50\\d{3})\\d{7}", [7, 8, 9, 10, 12]], 0, 0, 0, 0, 0, 0, ["1(?:3(?:0[0347]|[13][0139]|2[035]|4[013568]|6[0459]|7[06]|8[15-8]|9[0689])\\d{4}|6\\d{5,10})|(?:345\\d|9[89])\\d{6}|(?:10|2(?:3|85\\d)|3(?:[15]|[69]\\d\\d)|4[15-8]|51)\\d{8}"]]
      ],
      883: ["883", 0, "(?:[1-4]\\d|51)\\d{6,10}", [8, 9, 10, 11, 12],
        [
          ["(\\d{3})(\\d{3})(\\d{2,8})", "$1 $2 $3", ["[14]|2[24-689]|3[02-689]|51[24-9]"]],
          ["(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3", ["510"]],
          ["(\\d{3})(\\d{3})(\\d{4})", "$1 $2 $3", ["21"]],
          ["(\\d{4})(\\d{4})(\\d{4})", "$1 $2 $3", ["51[13]"]],
          ["(\\d{3})(\\d{3})(\\d{3})(\\d{3})", "$1 $2 $3 $4", ["[235]"]]
        ], 0, 0, 0, 0, 0, 0, [0, 0, 0, 0, 0, 0, 0, 0, ["(?:2(?:00\\d\\d|10)|(?:370[1-9]|51\\d0)\\d)\\d{7}|51(?:00\\d{5}|[24-9]0\\d{4,7})|(?:1[0-79]|2[24-689]|3[02-689]|4[0-4])0\\d{5,9}"]]
      ],
      888: ["888", 0, "\\d{11}", [11],
        [
          ["(\\d{3})(\\d{3})(\\d{5})", "$1 $2 $3"]
        ], 0, 0, 0, 0, 0, 0, [0, 0, 0, 0, 0, 0, ["\\d{11}"]]
      ],
      979: ["979", 0, "[1359]\\d{8}", [9],
        [
          ["(\\d)(\\d{4})(\\d{4})", "$1 $2 $3", ["[1359]"]]
        ], 0, 0, 0, 0, 0, 0, [0, 0, 0, ["[1359]\\d{8}"]]
      ]
    }
  };

function Y_(e, t) {
  var s = Array.prototype.slice.call(t);
  return s.push(G_), e.apply(this, s)
}

function Dd(e) {
  "@babel/helpers - typeof";
  return Dd = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function (t) {
    return typeof t
  } : function (t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t
  }, Dd(e)
}

function Vf(e, t) {
  for (var s = 0; s < t.length; s++) {
    var o = t[s];
    o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, o.key, o)
  }
}

function J_(e, t, s) {
  return t && Vf(e.prototype, t), s && Vf(e, s), Object.defineProperty(e, "prototype", {
    writable: !1
  }), e
}

function Z_(e, t) {
  if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
}

function X_(e, t) {
  if (typeof t != "function" && t !== null) throw new TypeError("Super expression must either be null or a function");
  e.prototype = Object.create(t && t.prototype, {
    constructor: {
      value: e,
      writable: !0,
      configurable: !0
    }
  }), Object.defineProperty(e, "prototype", {
    writable: !1
  }), t && Oi(e, t)
}

function Q_(e) {
  var t = Um();
  return function () {
    var s = Ii(e),
      o;
    if (t) {
      var i = Ii(this).constructor;
      o = Reflect.construct(s, arguments, i)
    } else o = s.apply(this, arguments);
    return eb(this, o)
  }
}

function eb(e, t) {
  if (t && (Dd(t) === "object" || typeof t == "function")) return t;
  if (t !== void 0) throw new TypeError("Derived constructors may only return object or undefined");
  return Fm(e)
}

function Fm(e) {
  if (e === void 0) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  return e
}

function Md(e) {
  var t = typeof Map == "function" ? new Map : void 0;
  return Md = function (s) {
    if (s === null || !tb(s)) return s;
    if (typeof s != "function") throw new TypeError("Super expression must either be null or a function");
    if (typeof t < "u") {
      if (t.has(s)) return t.get(s);
      t.set(s, o)
    }

    function o() {
      return ga(s, arguments, Ii(this).constructor)
    }
    return o.prototype = Object.create(s.prototype, {
      constructor: {
        value: o,
        enumerable: !1,
        writable: !0,
        configurable: !0
      }
    }), Oi(o, s)
  }, Md(e)
}

function ga(e, t, s) {
  return Um() ? ga = Reflect.construct : ga = function (o, i, r) {
    var a = [null];
    a.push.apply(a, i);
    var l = Function.bind.apply(o, a),
      d = new l;
    return r && Oi(d, r.prototype), d
  }, ga.apply(null, arguments)
}

function Um() {
  if (typeof Reflect > "u" || !Reflect.construct || Reflect.construct.sham) return !1;
  if (typeof Proxy == "function") return !0;
  try {
    return Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})), !0
  } catch {
    return !1
  }
}

function tb(e) {
  return Function.toString.call(e).indexOf("[native code]") !== -1
}

function Oi(e, t) {
  return Oi = Object.setPrototypeOf || function (s, o) {
    return s.__proto__ = o, s
  }, Oi(e, t)
}

function Ii(e) {
  return Ii = Object.setPrototypeOf ? Object.getPrototypeOf : function (t) {
    return t.__proto__ || Object.getPrototypeOf(t)
  }, Ii(e)
}
var js = function (e) {
    X_(s, e);
    var t = Q_(s);

    function s(o) {
      var i;
      return Z_(this, s), i = t.call(this, o), Object.setPrototypeOf(Fm(i), s.prototype), i.name = i.constructor.name, i
    }
    return J_(s)
  }(Md(Error)),
  Su = 2,
  sb = 17,
  nb = 3,
  ys = "0-9０-９٠-٩۰-۹",
  ob = "-‐-―−ー－",
  ib = "／/",
  rb = "．.",
  ab = "  ­​⁠　",
  lb = "()（）［］\\[\\]",
  cb = "~⁓∼～",
  Oa = "".concat(ob).concat(ib).concat(rb).concat(ab).concat(lb).concat(cb),
  Eu = "+＋";

function jf(e, t) {
  e = e.split("-"), t = t.split("-");
  for (var s = e[0].split("."), o = t[0].split("."), i = 0; i < 3; i++) {
    var r = Number(s[i]),
      a = Number(o[i]);
    if (r > a) return 1;
    if (a > r) return -1;
    if (!isNaN(r) && isNaN(a)) return 1;
    if (isNaN(r) && !isNaN(a)) return -1
  }
  return e[1] && t[1] ? e[1] > t[1] ? 1 : e[1] < t[1] ? -1 : 0 : !e[1] && t[1] ? 1 : e[1] && !t[1] ? -1 : 0
}
var db = {}.constructor;

function _a(e) {
  return e != null && e.constructor === db
}

function Rd(e) {
  "@babel/helpers - typeof";
  return Rd = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function (t) {
    return typeof t
  } : function (t) {
    return t && typeof Symbol == "function" && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t
  }, Rd(e)
}

function rl(e, t) {
  if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
}

function Hf(e, t) {
  for (var s = 0; s < t.length; s++) {
    var o = t[s];
    o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, o.key, o)
  }
}

function al(e, t, s) {
  return t && Hf(e.prototype, t), s && Hf(e, s), Object.defineProperty(e, "prototype", {
    writable: !1
  }), e
}
var ub = "1.2.0",
  fb = "1.7.35",
  qf = " ext. ",
  hb = /^\d+$/,
  Ht = function () {
    function e(t) {
      rl(this, e), _b(t), this.metadata = t, Vm.call(this, t)
    }
    return al(e, [{
      key: "getCountries",
      value: function () {
        return Object.keys(this.metadata.countries).filter(function (t) {
          return t !== "001"
        })
      }
    }, {
      key: "getCountryMetadata",
      value: function (t) {
        return this.metadata.countries[t]
      }
    }, {
      key: "nonGeographic",
      value: function () {
        if (!(this.v1 || this.v2 || this.v3)) return this.metadata.nonGeographic || this.metadata.nonGeographical
      }
    }, {
      key: "hasCountry",
      value: function (t) {
        return this.getCountryMetadata(t) !== void 0
      }
    }, {
      key: "hasCallingCode",
      value: function (t) {
        if (this.getCountryCodesForCallingCode(t)) return !0;
        if (this.nonGeographic()) {
          if (this.nonGeographic()[t]) return !0
        } else {
          var s = this.countryCallingCodes()[t];
          if (s && s.length === 1 && s[0] === "001") return !0
        }
      }
    }, {
      key: "isNonGeographicCallingCode",
      value: function (t) {
        return this.nonGeographic() ? !!this.nonGeographic()[t] : !this.getCountryCodesForCallingCode(t)
      }
    }, {
      key: "country",
      value: function (t) {
        return this.selectNumberingPlan(t)
      }
    }, {
      key: "selectNumberingPlan",
      value: function (t, s) {
        if (t && hb.test(t) && (s = t, t = null), t && t !== "001") {
          if (!this.hasCountry(t)) throw new Error("Unknown country: ".concat(t));
          this.numberingPlan = new Wf(this.getCountryMetadata(t), this)
        } else if (s) {
          if (!this.hasCallingCode(s)) throw new Error("Unknown calling code: ".concat(s));
          this.numberingPlan = new Wf(this.getNumberingPlanMetadata(s), this)
        } else this.numberingPlan = void 0;
        return this
      }
    }, {
      key: "getCountryCodesForCallingCode",
      value: function (t) {
        var s = this.countryCallingCodes()[t];
        if (s) return s.length === 1 && s[0].length === 3 ? void 0 : s
      }
    }, {
      key: "getCountryCodeForCallingCode",
      value: function (t) {
        var s = this.getCountryCodesForCallingCode(t);
        if (s) return s[0]
      }
    }, {
      key: "getNumberingPlanMetadata",
      value: function (t) {
        var s = this.getCountryCodeForCallingCode(t);
        if (s) return this.getCountryMetadata(s);
        if (this.nonGeographic()) {
          var o = this.nonGeographic()[t];
          if (o) return o
        } else {
          var i = this.countryCallingCodes()[t];
          if (i && i.length === 1 && i[0] === "001") return this.metadata.countries["001"]
        }
      }
    }, {
      key: "countryCallingCode",
      value: function () {
        return this.numberingPlan.callingCode()
      }
    }, {
      key: "IDDPrefix",
      value: function () {
        return this.numberingPlan.IDDPrefix()
      }
    }, {
      key: "defaultIDDPrefix",
      value: function () {
        return this.numberingPlan.defaultIDDPrefix()
      }
    }, {
      key: "nationalNumberPattern",
      value: function () {
        return this.numberingPlan.nationalNumberPattern()
      }
    }, {
      key: "possibleLengths",
      value: function () {
        return this.numberingPlan.possibleLengths()
      }
    }, {
      key: "formats",
      value: function () {
        return this.numberingPlan.formats()
      }
    }, {
      key: "nationalPrefixForParsing",
      value: function () {
        return this.numberingPlan.nationalPrefixForParsing()
      }
    }, {
      key: "nationalPrefixTransformRule",
      value: function () {
        return this.numberingPlan.nationalPrefixTransformRule()
      }
    }, {
      key: "leadingDigits",
      value: function () {
        return this.numberingPlan.leadingDigits()
      }
    }, {
      key: "hasTypes",
      value: function () {
        return this.numberingPlan.hasTypes()
      }
    }, {
      key: "type",
      value: function (t) {
        return this.numberingPlan.type(t)
      }
    }, {
      key: "ext",
      value: function () {
        return this.numberingPlan.ext()
      }
    }, {
      key: "countryCallingCodes",
      value: function () {
        return this.v1 ? this.metadata.country_phone_code_to_countries : this.metadata.country_calling_codes
      }
    }, {
      key: "chooseCountryByCountryCallingCode",
      value: function (t) {
        return this.selectNumberingPlan(t)
      }
    }, {
      key: "hasSelectedNumberingPlan",
      value: function () {
        return this.numberingPlan !== void 0
      }
    }]), e
  }(),
  Wf = function () {
    function e(t, s) {
      rl(this, e), this.globalMetadataObject = s, this.metadata = t, Vm.call(this, s.metadata)
    }
    return al(e, [{
      key: "callingCode",
      value: function () {
        return this.metadata[0]
      }
    }, {
      key: "getDefaultCountryMetadataForRegion",
      value: function () {
        return this.globalMetadataObject.getNumberingPlanMetadata(this.callingCode())
      }
    }, {
      key: "IDDPrefix",
      value: function () {
        if (!(this.v1 || this.v2)) return this.metadata[1]
      }
    }, {
      key: "defaultIDDPrefix",
      value: function () {
        if (!(this.v1 || this.v2)) return this.metadata[12]
      }
    }, {
      key: "nationalNumberPattern",
      value: function () {
        return this.v1 || this.v2 ? this.metadata[1] : this.metadata[2]
      }
    }, {
      key: "possibleLengths",
      value: function () {
        if (!this.v1) return this.metadata[this.v2 ? 2 : 3]
      }
    }, {
      key: "_getFormats",
      value: function (t) {
        return t[this.v1 ? 2 : this.v2 ? 3 : 4]
      }
    }, {
      key: "formats",
      value: function () {
        var t = this,
          s = this._getFormats(this.metadata) || this._getFormats(this.getDefaultCountryMetadataForRegion()) || [];
        return s.map(function (o) {
          return new pb(o, t)
        })
      }
    }, {
      key: "nationalPrefix",
      value: function () {
        return this.metadata[this.v1 ? 3 : this.v2 ? 4 : 5]
      }
    }, {
      key: "_getNationalPrefixFormattingRule",
      value: function (t) {
        return t[this.v1 ? 4 : this.v2 ? 5 : 6]
      }
    }, {
      key: "nationalPrefixFormattingRule",
      value: function () {
        return this._getNationalPrefixFormattingRule(this.metadata) || this._getNationalPrefixFormattingRule(this.getDefaultCountryMetadataForRegion())
      }
    }, {
      key: "_nationalPrefixForParsing",
      value: function () {
        return this.metadata[this.v1 ? 5 : this.v2 ? 6 : 7]
      }
    }, {
      key: "nationalPrefixForParsing",
      value: function () {
        return this._nationalPrefixForParsing() || this.nationalPrefix()
      }
    }, {
      key: "nationalPrefixTransformRule",
      value: function () {
        return this.metadata[this.v1 ? 6 : this.v2 ? 7 : 8]
      }
    }, {
      key: "_getNationalPrefixIsOptionalWhenFormatting",
      value: function () {
        return !!this.metadata[this.v1 ? 7 : this.v2 ? 8 : 9]
      }
    }, {
      key: "nationalPrefixIsOptionalWhenFormattingInNationalFormat",
      value: function () {
        return this._getNationalPrefixIsOptionalWhenFormatting(this.metadata) || this._getNationalPrefixIsOptionalWhenFormatting(this.getDefaultCountryMetadataForRegion())
      }
    }, {
      key: "leadingDigits",
      value: function () {
        return this.metadata[this.v1 ? 8 : this.v2 ? 9 : 10]
      }
    }, {
      key: "types",
      value: function () {
        return this.metadata[this.v1 ? 9 : this.v2 ? 10 : 11]
      }
    }, {
      key: "hasTypes",
      value: function () {
        return this.types() && this.types().length === 0 ? !1 : !!this.types()
      }
    }, {
      key: "type",
      value: function (t) {
        if (this.hasTypes() && zf(this.types(), t)) return new gb(zf(this.types(), t), this)
      }
    }, {
      key: "ext",
      value: function () {
        return this.v1 || this.v2 ? qf : this.metadata[13] || qf
      }
    }]), e
  }(),
  pb = function () {
    function e(t, s) {
      rl(this, e), this._format = t, this.metadata = s
    }
    return al(e, [{
      key: "pattern",
      value: function () {
        return this._format[0]
      }
    }, {
      key: "format",
      value: function () {
        return this._format[1]
      }
    }, {
      key: "leadingDigitsPatterns",
      value: function () {
        return this._format[2] || []
      }
    }, {
      key: "nationalPrefixFormattingRule",
      value: function () {
        return this._format[3] || this.metadata.nationalPrefixFormattingRule()
      }
    }, {
      key: "nationalPrefixIsOptionalWhenFormattingInNationalFormat",
      value: function () {
        return !!this._format[4] || this.metadata.nationalPrefixIsOptionalWhenFormattingInNationalFormat()
      }
    }, {
      key: "nationalPrefixIsMandatoryWhenFormattingInNationalFormat",
      value: function () {
        return this.usesNationalPrefix() && !this.nationalPrefixIsOptionalWhenFormattingInNationalFormat()
      }
    }, {
      key: "usesNationalPrefix",
      value: function () {
        return !!(this.nationalPrefixFormattingRule() && !mb.test(this.nationalPrefixFormattingRule()))
      }
    }, {
      key: "internationalFormat",
      value: function () {
        return this._format[5] || this.format()
      }
    }]), e
  }(),
  mb = /^\(?\$1\)?$/,
  gb = function () {
    function e(t, s) {
      rl(this, e), this.type = t, this.metadata = s
    }
    return al(e, [{
      key: "pattern",
      value: function () {
        return this.metadata.v1 ? this.type : this.type[0]
      }
    }, {
      key: "possibleLengths",
      value: function () {
        if (!this.metadata.v1) return this.type[1] || this.metadata.possibleLengths()
      }
    }]), e
  }();

function zf(e, t) {
  switch (t) {
    case "FIXED_LINE":
      return e[0];
    case "MOBILE":
      return e[1];
    case "TOLL_FREE":
      return e[2];
    case "PREMIUM_RATE":
      return e[3];
    case "PERSONAL_NUMBER":
      return e[4];
    case "VOICEMAIL":
      return e[5];
    case "UAN":
      return e[6];
    case "PAGER":
      return e[7];
    case "VOIP":
      return e[8];
    case "SHARED_COST":
      return e[9]
  }
}

function _b(e) {
  if (!e) throw new Error("[libphonenumber-js] `metadata` argument not passed. Check your arguments.");
  if (!_a(e) || !_a(e.countries)) throw new Error("[libphonenumber-js] `metadata` argument was passed but it's not a valid metadata. Must be an object having `.countries` child object property. Got ".concat(_a(e) ? "an object of shape: { " + Object.keys(e).join(", ") + " }" : "a " + bb(e) + ": " + e, "."))
}
var bb = function (e) {
  return Rd(e)
};

function Pu(e, t) {
  if (t = new Ht(t), t.hasCountry(e)) return t.country(e).countryCallingCode();
  throw new Error("Unknown country: ".concat(e))
}

function vb(e, t) {
  return t.countries.hasOwnProperty(e)
}

function Vm(e) {
  var t = e.version;
  typeof t == "number" ? (this.v1 = t === 1, this.v2 = t === 2, this.v3 = t === 3, this.v4 = t === 4) : t ? jf(t, ub) === -1 ? this.v2 = !0 : jf(t, fb) === -1 ? this.v3 = !0 : this.v4 = !0 : this.v1 = !0
}
var yb = ";ext=",
  fo = function (e) {
    return "([".concat(ys, "]{1,").concat(e, "})")
  };

function jm(e) {
  var t = "20",
    s = "15",
    o = "9",
    i = "6",
    r = "[  \\t,]*",
    a = "[:\\.．]?[  \\t,-]*",
    l = "#?",
    d = "(?:e?xt(?:ensi(?:ó?|ó))?n?|ｅ?ｘｔｎ?|доб|anexo)",
    p = "(?:[xｘ#＃~～]|int|ｉｎｔ)",
    m = "[- ]+",
    g = "[  \\t]*",
    $ = "(?:,{2}|;)",
    C = yb + fo(t),
    A = r + d + a + fo(t) + l,
    O = r + p + a + fo(o) + l,
    V = m + fo(i) + "#",
    M = g + $ + a + fo(s) + l,
    B = g + "(?:,)+" + a + fo(o) + l;
  return C + "|" + A + "|" + O + "|" + V + "|" + M + "|" + B
}
var wb = "[" + ys + "]{" + Su + "}",
  $b = "[" + Eu + "]{0,1}(?:[" + Oa + "]*[" + ys + "]){3,}[" + Oa + ys + "]*",
  Cb = new RegExp("^[" + Eu + "]{0,1}(?:[" + Oa + "]*[" + ys + "]){1,2}$", "i"),
  kb = $b + "(?:" + jm() + ")?",
  Ab = new RegExp("^" + wb + "$|^" + kb + "$", "i");

function xb(e) {
  return e.length >= Su && Ab.test(e)
}

function Sb(e) {
  return Cb.test(e)
}
var Kf = new RegExp("(?:" + jm() + ")$", "i");

function Eb(e) {
  var t = e.search(Kf);
  if (t < 0) return {};
  for (var s = e.slice(0, t), o = e.match(Kf), i = 1; i < o.length;) {
    if (o[i]) return {
      number: s,
      ext: o[i]
    };
    i++
  }
}
var Pb = {
  0: "0",
  1: "1",
  2: "2",
  3: "3",
  4: "4",
  5: "5",
  6: "6",
  7: "7",
  8: "8",
  9: "9",
  "０": "0",
  "１": "1",
  "２": "2",
  "３": "3",
  "４": "4",
  "５": "5",
  "６": "6",
  "７": "7",
  "８": "8",
  "９": "9",
  "٠": "0",
  "١": "1",
  "٢": "2",
  "٣": "3",
  "٤": "4",
  "٥": "5",
  "٦": "6",
  "٧": "7",
  "٨": "8",
  "٩": "9",
  "۰": "0",
  "۱": "1",
  "۲": "2",
  "۳": "3",
  "۴": "4",
  "۵": "5",
  "۶": "6",
  "۷": "7",
  "۸": "8",
  "۹": "9"
};

function Tb(e) {
  return Pb[e]
}

function Lb(e, t) {
  var s = typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (s) return (s = s.call(e)).next.bind(s);
  if (Array.isArray(e) || (s = Ob(e)) || t && e && typeof e.length == "number") {
    s && (e = s);
    var o = 0;
    return function () {
      return o >= e.length ? {
        done: !0
      } : {
        done: !1,
        value: e[o++]
      }
    }
  }
  throw new TypeError(`Invalid attempt to iterate non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)
}

function Ob(e, t) {
  if (e) {
    if (typeof e == "string") return Gf(e, t);
    var s = Object.prototype.toString.call(e).slice(8, -1);
    if (s === "Object" && e.constructor && (s = e.constructor.name), s === "Map" || s === "Set") return Array.from(e);
    if (s === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(s)) return Gf(e, t)
  }
}

function Gf(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var s = 0, o = new Array(t); s < t; s++) o[s] = e[s];
  return o
}

function Yf(e) {
  for (var t = "", s = Lb(e.split("")), o; !(o = s()).done;) {
    var i = o.value;
    t += Ib(i, t) || ""
  }
  return t
}

function Ib(e, t) {
  return e === "+" ? t ? void 0 : "+" : Tb(e)
}

function Bb(e, t) {
  var s = typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (s) return (s = s.call(e)).next.bind(s);
  if (Array.isArray(e) || (s = Db(e)) || t && e && typeof e.length == "number") {
    s && (e = s);
    var o = 0;
    return function () {
      return o >= e.length ? {
        done: !0
      } : {
        done: !1,
        value: e[o++]
      }
    }
  }
  throw new TypeError(`Invalid attempt to iterate non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)
}

function Db(e, t) {
  if (e) {
    if (typeof e == "string") return Jf(e, t);
    var s = Object.prototype.toString.call(e).slice(8, -1);
    if (s === "Object" && e.constructor && (s = e.constructor.name), s === "Map" || s === "Set") return Array.from(e);
    if (s === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(s)) return Jf(e, t)
  }
}

function Jf(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var s = 0, o = new Array(t); s < t; s++) o[s] = e[s];
  return o
}

function Mb(e, t) {
  for (var s = e.slice(), o = Bb(t), i; !(i = o()).done;) {
    var r = i.value;
    e.indexOf(r) < 0 && s.push(r)
  }
  return s.sort(function (a, l) {
    return a - l
  })
}

function Tu(e, t) {
  return Hm(e, void 0, t)
}

function Hm(e, t, s) {
  var o = s.type(t),
    i = o && o.possibleLengths() || s.possibleLengths();
  if (!i) return "IS_POSSIBLE";
  if (t === "FIXED_LINE_OR_MOBILE") {
    if (!s.type("FIXED_LINE")) return Hm(e, "MOBILE", s);
    var r = s.type("MOBILE");
    r && (i = Mb(i, r.possibleLengths()))
  } else if (t && !o) return "INVALID_LENGTH";
  var a = e.length,
    l = i[0];
  return l === a ? "IS_POSSIBLE" : l > a ? "TOO_SHORT" : i[i.length - 1] < a ? "TOO_LONG" : i.indexOf(a, 1) >= 0 ? "IS_POSSIBLE" : "INVALID_LENGTH"
}

function Rb(e, t, s) {
  if (t === void 0 && (t = {}), s = new Ht(s), t.v2) {
    if (!e.countryCallingCode) throw new Error("Invalid phone number object passed");
    s.selectNumberingPlan(e.countryCallingCode)
  } else {
    if (!e.phone) return !1;
    if (e.country) {
      if (!s.hasCountry(e.country)) throw new Error("Unknown country: ".concat(e.country));
      s.country(e.country)
    } else {
      if (!e.countryCallingCode) throw new Error("Invalid phone number object passed");
      s.selectNumberingPlan(e.countryCallingCode)
    }
  }
  if (s.possibleLengths()) return qm(e.phone || e.nationalNumber, s);
  if (e.countryCallingCode && s.isNonGeographicCallingCode(e.countryCallingCode)) return !0;
  throw new Error('Missing "possibleLengths" in metadata. Perhaps the metadata has been generated before v1.0.18.')
}

function qm(e, t) {
  switch (Tu(e, t)) {
    case "IS_POSSIBLE":
      return !0;
    default:
      return !1
  }
}

function Ys(e, t) {
  return e = e || "", new RegExp("^(?:" + t + ")$").test(e)
}

function Nb(e, t) {
  var s = typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (s) return (s = s.call(e)).next.bind(s);
  if (Array.isArray(e) || (s = Fb(e)) || t && e && typeof e.length == "number") {
    s && (e = s);
    var o = 0;
    return function () {
      return o >= e.length ? {
        done: !0
      } : {
        done: !1,
        value: e[o++]
      }
    }
  }
  throw new TypeError(`Invalid attempt to iterate non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)
}

function Fb(e, t) {
  if (e) {
    if (typeof e == "string") return Zf(e, t);
    var s = Object.prototype.toString.call(e).slice(8, -1);
    if (s === "Object" && e.constructor && (s = e.constructor.name), s === "Map" || s === "Set") return Array.from(e);
    if (s === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(s)) return Zf(e, t)
  }
}

function Zf(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var s = 0, o = new Array(t); s < t; s++) o[s] = e[s];
  return o
}
var Ub = ["MOBILE", "PREMIUM_RATE", "TOLL_FREE", "SHARED_COST", "VOIP", "PERSONAL_NUMBER", "PAGER", "UAN", "VOICEMAIL"];

function Lu(e, t, s) {
  if (t = t || {}, !(!e.country && !e.countryCallingCode)) {
    s = new Ht(s), s.selectNumberingPlan(e.country, e.countryCallingCode);
    var o = t.v2 ? e.nationalNumber : e.phone;
    if (Ys(o, s.nationalNumberPattern())) {
      if (Qc(o, "FIXED_LINE", s)) return s.type("MOBILE") && s.type("MOBILE").pattern() === "" || !s.type("MOBILE") || Qc(o, "MOBILE", s) ? "FIXED_LINE_OR_MOBILE" : "FIXED_LINE";
      for (var i = Nb(Ub), r; !(r = i()).done;) {
        var a = r.value;
        if (Qc(o, a, s)) return a
      }
    }
  }
}

function Qc(e, t, s) {
  return t = s.type(t), !t || !t.pattern() || t.possibleLengths() && t.possibleLengths().indexOf(e.length) < 0 ? !1 : Ys(e, t.pattern())
}

function Vb(e, t, s) {
  if (t = t || {}, s = new Ht(s), s.selectNumberingPlan(e.country, e.countryCallingCode), s.hasTypes()) return Lu(e, t, s.metadata) !== void 0;
  var o = t.v2 ? e.nationalNumber : e.phone;
  return Ys(o, s.nationalNumberPattern())
}

function jb(e, t, s) {
  var o = new Ht(s),
    i = o.getCountryCodesForCallingCode(e);
  return i ? i.filter(function (r) {
    return Hb(t, r, s)
  }) : []
}

function Hb(e, t, s) {
  var o = new Ht(s);
  return o.selectNumberingPlan(t), o.numberingPlan.possibleLengths().indexOf(e.length) >= 0
}

function qb(e) {
  return e.replace(new RegExp("[".concat(Oa, "]+"), "g"), " ").trim()
}
var Wb = /(\$\d)/;

function zb(e, t, s) {
  var o = s.useInternationalFormat,
    i = s.withNationalPrefix;
  s.carrierCode, s.metadata;
  var r = e.replace(new RegExp(t.pattern()), o ? t.internationalFormat() : i && t.nationalPrefixFormattingRule() ? t.format().replace(Wb, t.nationalPrefixFormattingRule()) : t.format());
  return o ? qb(r) : r
}
var Kb = /^[\d]+(?:[~\u2053\u223C\uFF5E][\d]+)?$/;

function Gb(e, t, s) {
  var o = new Ht(s);
  if (o.selectNumberingPlan(e, t), o.defaultIDDPrefix()) return o.defaultIDDPrefix();
  if (Kb.test(o.IDDPrefix())) return o.IDDPrefix()
}

function Yb(e) {
  var t = e.number,
    s = e.ext;
  if (!t) return "";
  if (t[0] !== "+") throw new Error('"formatRFC3966()" expects "number" to be in E.164 format.');
  return "tel:".concat(t).concat(s ? ";ext=" + s : "")
}

function Jb(e, t) {
  var s = typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (s) return (s = s.call(e)).next.bind(s);
  if (Array.isArray(e) || (s = Zb(e)) || t && e && typeof e.length == "number") {
    s && (e = s);
    var o = 0;
    return function () {
      return o >= e.length ? {
        done: !0
      } : {
        done: !1,
        value: e[o++]
      }
    }
  }
  throw new TypeError(`Invalid attempt to iterate non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)
}

function Zb(e, t) {
  if (e) {
    if (typeof e == "string") return Xf(e, t);
    var s = Object.prototype.toString.call(e).slice(8, -1);
    if (s === "Object" && e.constructor && (s = e.constructor.name), s === "Map" || s === "Set") return Array.from(e);
    if (s === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(s)) return Xf(e, t)
  }
}

function Xf(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var s = 0, o = new Array(t); s < t; s++) o[s] = e[s];
  return o
}

function Qf(e, t) {
  var s = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var o = Object.getOwnPropertySymbols(e);
    t && (o = o.filter(function (i) {
      return Object.getOwnPropertyDescriptor(e, i).enumerable
    })), s.push.apply(s, o)
  }
  return s
}

function eh(e) {
  for (var t = 1; t < arguments.length; t++) {
    var s = arguments[t] != null ? arguments[t] : {};
    t % 2 ? Qf(Object(s), !0).forEach(function (o) {
      Xb(e, o, s[o])
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(s)) : Qf(Object(s)).forEach(function (o) {
      Object.defineProperty(e, o, Object.getOwnPropertyDescriptor(s, o))
    })
  }
  return e
}

function Xb(e, t, s) {
  return t in e ? Object.defineProperty(e, t, {
    value: s,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : e[t] = s, e
}
var th = {
  formatExtension: function (e, t, s) {
    return "".concat(e).concat(s.ext()).concat(t)
  }
};

function Qb(e, t, s, o) {
  if (s ? s = eh(eh({}, th), s) : s = th, o = new Ht(o), e.country && e.country !== "001") {
    if (!o.hasCountry(e.country)) throw new Error("Unknown country: ".concat(e.country));
    o.country(e.country)
  } else if (e.countryCallingCode) o.selectNumberingPlan(e.countryCallingCode);
  else return e.phone || "";
  var i = o.countryCallingCode(),
    r = s.v2 ? e.nationalNumber : e.phone,
    a;
  switch (t) {
    case "NATIONAL":
      return r ? (a = Ia(r, e.carrierCode, "NATIONAL", o, s), ed(a, e.ext, o, s.formatExtension)) : "";
    case "INTERNATIONAL":
      return r ? (a = Ia(r, null, "INTERNATIONAL", o, s), a = "+".concat(i, " ").concat(a), ed(a, e.ext, o, s.formatExtension)) : "+".concat(i);
    case "E.164":
      return "+".concat(i).concat(r);
    case "RFC3966":
      return Yb({
        number: "+".concat(i).concat(r),
        ext: e.ext
      });
    case "IDD":
      if (!s.fromCountry) return;
      var l = tv(r, e.carrierCode, i, s.fromCountry, o);
      return ed(l, e.ext, o, s.formatExtension);
    default:
      throw new Error('Unknown "format" argument passed to "formatNumber()": "'.concat(t, '"'))
  }
}

function Ia(e, t, s, o, i) {
  var r = ev(o.formats(), e);
  return r ? zb(e, r, {
    useInternationalFormat: s === "INTERNATIONAL",
    withNationalPrefix: !(r.nationalPrefixIsOptionalWhenFormattingInNationalFormat() && i && i.nationalPrefix === !1),
    carrierCode: t,
    metadata: o
  }) : e
}

function ev(e, t) {
  for (var s = Jb(e), o; !(o = s()).done;) {
    var i = o.value;
    if (i.leadingDigitsPatterns().length > 0) {
      var r = i.leadingDigitsPatterns()[i.leadingDigitsPatterns().length - 1];
      if (t.search(r) !== 0) continue
    }
    if (Ys(t, i.pattern())) return i
  }
}

function ed(e, t, s, o) {
  return t ? o(e, t, s) : e
}

function tv(e, t, s, o, i) {
  var r = Pu(o, i.metadata);
  if (r === s) {
    var a = Ia(e, t, "NATIONAL", i);
    return s === "1" ? s + " " + a : a
  }
  var l = Gb(o, void 0, i.metadata);
  if (l) return "".concat(l, " ").concat(s, " ").concat(Ia(e, null, "INTERNATIONAL", i))
}

function sh(e, t) {
  var s = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var o = Object.getOwnPropertySymbols(e);
    t && (o = o.filter(function (i) {
      return Object.getOwnPropertyDescriptor(e, i).enumerable
    })), s.push.apply(s, o)
  }
  return s
}

function nh(e) {
  for (var t = 1; t < arguments.length; t++) {
    var s = arguments[t] != null ? arguments[t] : {};
    t % 2 ? sh(Object(s), !0).forEach(function (o) {
      sv(e, o, s[o])
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(s)) : sh(Object(s)).forEach(function (o) {
      Object.defineProperty(e, o, Object.getOwnPropertyDescriptor(s, o))
    })
  }
  return e
}

function sv(e, t, s) {
  return t in e ? Object.defineProperty(e, t, {
    value: s,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : e[t] = s, e
}

function nv(e, t) {
  if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
}

function oh(e, t) {
  for (var s = 0; s < t.length; s++) {
    var o = t[s];
    o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, o.key, o)
  }
}

function ov(e, t, s) {
  return t && oh(e.prototype, t), s && oh(e, s), Object.defineProperty(e, "prototype", {
    writable: !1
  }), e
}
var iv = function () {
    function e(t, s, o) {
      if (nv(this, e), !t) throw new TypeError("`country` or `countryCallingCode` not passed");
      if (!s) throw new TypeError("`nationalNumber` not passed");
      if (!o) throw new TypeError("`metadata` not passed");
      var i = av(t, o),
        r = i.country,
        a = i.countryCallingCode;
      this.country = r, this.countryCallingCode = a, this.nationalNumber = s, this.number = "+" + this.countryCallingCode + this.nationalNumber, this.getMetadata = function () {
        return o
      }
    }
    return ov(e, [{
      key: "setExt",
      value: function (t) {
        this.ext = t
      }
    }, {
      key: "getPossibleCountries",
      value: function () {
        return this.country ? [this.country] : jb(this.countryCallingCode, this.nationalNumber, this.getMetadata())
      }
    }, {
      key: "isPossible",
      value: function () {
        return Rb(this, {
          v2: !0
        }, this.getMetadata())
      }
    }, {
      key: "isValid",
      value: function () {
        return Vb(this, {
          v2: !0
        }, this.getMetadata())
      }
    }, {
      key: "isNonGeographic",
      value: function () {
        var t = new Ht(this.getMetadata());
        return t.isNonGeographicCallingCode(this.countryCallingCode)
      }
    }, {
      key: "isEqual",
      value: function (t) {
        return this.number === t.number && this.ext === t.ext
      }
    }, {
      key: "getType",
      value: function () {
        return Lu(this, {
          v2: !0
        }, this.getMetadata())
      }
    }, {
      key: "format",
      value: function (t, s) {
        return Qb(this, t, s ? nh(nh({}, s), {}, {
          v2: !0
        }) : {
          v2: !0
        }, this.getMetadata())
      }
    }, {
      key: "formatNational",
      value: function (t) {
        return this.format("NATIONAL", t)
      }
    }, {
      key: "formatInternational",
      value: function (t) {
        return this.format("INTERNATIONAL", t)
      }
    }, {
      key: "getURI",
      value: function (t) {
        return this.format("RFC3966", t)
      }
    }]), e
  }(),
  rv = function (e) {
    return /^[A-Z]{2}$/.test(e)
  };

function av(e, t) {
  var s, o, i = new Ht(t);
  return rv(e) ? (s = e, i.selectNumberingPlan(s), o = i.countryCallingCode()) : o = e, {
    country: s,
    countryCallingCode: o
  }
}
var lv = new RegExp("([" + ys + "])");

function cv(e, t, s, o) {
  if (t) {
    var i = new Ht(o);
    i.selectNumberingPlan(t, s);
    var r = new RegExp(i.IDDPrefix());
    if (e.search(r) === 0) {
      e = e.slice(e.match(r)[0].length);
      var a = e.match(lv);
      if (!(a && a[1] != null && a[1].length > 0 && a[1] === "0")) return e
    }
  }
}

function dv(e, t) {
  if (e && t.numberingPlan.nationalPrefixForParsing()) {
    var s = new RegExp("^(?:" + t.numberingPlan.nationalPrefixForParsing() + ")"),
      o = s.exec(e);
    if (o) {
      var i, r, a = o.length - 1,
        l = a > 0 && o[a];
      if (t.nationalPrefixTransformRule() && l) i = e.replace(s, t.nationalPrefixTransformRule()), a > 1 && (r = o[1]);
      else {
        var d = o[0];
        i = e.slice(d.length), l && (r = o[1])
      }
      var p;
      if (l) {
        var m = e.indexOf(o[1]),
          g = e.slice(0, m);
        g === t.numberingPlan.nationalPrefix() && (p = t.numberingPlan.nationalPrefix())
      } else p = o[0];
      return {
        nationalNumber: i,
        nationalPrefix: p,
        carrierCode: r
      }
    }
  }
  return {
    nationalNumber: e
  }
}

function Nd(e, t) {
  var s = dv(e, t),
    o = s.carrierCode,
    i = s.nationalNumber;
  if (i !== e) {
    if (!uv(e, i, t)) return {
      nationalNumber: e
    };
    if (t.possibleLengths() && !fv(i, t)) return {
      nationalNumber: e
    }
  }
  return {
    nationalNumber: i,
    carrierCode: o
  }
}

function uv(e, t, s) {
  return !(Ys(e, s.nationalNumberPattern()) && !Ys(t, s.nationalNumberPattern()))
}

function fv(e, t) {
  switch (Tu(e, t)) {
    case "TOO_SHORT":
    case "INVALID_LENGTH":
      return !1;
    default:
      return !0
  }
}

function hv(e, t, s, o) {
  var i = t ? Pu(t, o) : s;
  if (e.indexOf(i) === 0) {
    o = new Ht(o), o.selectNumberingPlan(t, s);
    var r = e.slice(i.length),
      a = Nd(r, o),
      l = a.nationalNumber,
      d = Nd(e, o),
      p = d.nationalNumber;
    if (!Ys(p, o.nationalNumberPattern()) && Ys(l, o.nationalNumberPattern()) || Tu(p, o) === "TOO_LONG") return {
      countryCallingCode: i,
      number: r
    }
  }
  return {
    number: e
  }
}

function pv(e, t, s, o) {
  if (!e) return {};
  var i;
  if (e[0] !== "+") {
    var r = cv(e, t, s, o);
    if (r && r !== e) i = !0, e = "+" + r;
    else {
      if (t || s) {
        var a = hv(e, t, s, o),
          l = a.countryCallingCode,
          d = a.number;
        if (l) return {
          countryCallingCodeSource: "FROM_NUMBER_WITHOUT_PLUS_SIGN",
          countryCallingCode: l,
          number: d
        }
      }
      return {
        number: e
      }
    }
  }
  if (e[1] === "0") return {};
  o = new Ht(o);
  for (var p = 2; p - 1 <= nb && p <= e.length;) {
    var m = e.slice(1, p);
    if (o.hasCallingCode(m)) return o.selectNumberingPlan(m), {
      countryCallingCodeSource: i ? "FROM_NUMBER_WITH_IDD" : "FROM_NUMBER_WITH_PLUS_SIGN",
      countryCallingCode: m,
      number: e.slice(p)
    };
    p++
  }
  return {}
}

function mv(e, t) {
  var s = typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (s) return (s = s.call(e)).next.bind(s);
  if (Array.isArray(e) || (s = gv(e)) || t && e && typeof e.length == "number") {
    s && (e = s);
    var o = 0;
    return function () {
      return o >= e.length ? {
        done: !0
      } : {
        done: !1,
        value: e[o++]
      }
    }
  }
  throw new TypeError(`Invalid attempt to iterate non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)
}

function gv(e, t) {
  if (e) {
    if (typeof e == "string") return ih(e, t);
    var s = Object.prototype.toString.call(e).slice(8, -1);
    if (s === "Object" && e.constructor && (s = e.constructor.name), s === "Map" || s === "Set") return Array.from(e);
    if (s === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(s)) return ih(e, t)
  }
}

function ih(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var s = 0, o = new Array(t); s < t; s++) o[s] = e[s];
  return o
}

function _v(e, t) {
  var s = t.countries,
    o = t.defaultCountry,
    i = t.metadata;
  i = new Ht(i);
  for (var r = [], a = mv(s), l; !(l = a()).done;) {
    var d = l.value;
    if (i.country(d), i.leadingDigits()) {
      if (e && e.search(i.leadingDigits()) === 0) return d
    } else if (Lu({
        phone: e,
        country: d
      }, void 0, i.metadata))
      if (o) {
        if (d === o) return d;
        r.push(d)
      } else return d
  }
  if (r.length > 0) return r[0]
}

function bv(e, t) {
  var s = t.nationalNumber,
    o = t.defaultCountry,
    i = t.metadata,
    r = i.getCountryCodesForCallingCode(e);
  if (r) return r.length === 1 ? r[0] : _v(s, {
    countries: r,
    defaultCountry: o,
    metadata: i.metadata
  })
}
var Wm = "+",
  vv = "[\\-\\.\\(\\)]?",
  rh = "([" + ys + "]|" + vv + ")",
  yv = "^\\" + Wm + rh + "*[" + ys + "]" + rh + "*$",
  wv = new RegExp(yv, "g"),
  Fd = ys,
  $v = "[" + Fd + "]+((\\-)*[" + Fd + "])*",
  Cv = "a-zA-Z",
  kv = "[" + Cv + "]+((\\-)*[" + Fd + "])*",
  Av = "^(" + $v + "\\.)*" + kv + "\\.?$",
  xv = new RegExp(Av, "g"),
  ah = "tel:",
  Ud = ";phone-context=",
  Sv = ";isub=";

function Ev(e) {
  var t = e.indexOf(Ud);
  if (t < 0) return null;
  var s = t + Ud.length;
  if (s >= e.length) return "";
  var o = e.indexOf(";", s);
  return o >= 0 ? e.substring(s, o) : e.substring(s)
}

function Pv(e) {
  return e === null ? !0 : e.length === 0 ? !1 : wv.test(e) || xv.test(e)
}

function Tv(e, t) {
  var s = t.extractFormattedPhoneNumber,
    o = Ev(e);
  if (!Pv(o)) throw new js("NOT_A_NUMBER");
  var i;
  if (o === null) i = s(e) || "";
  else {
    i = "", o.charAt(0) === Wm && (i += o);
    var r = e.indexOf(ah),
      a;
    r >= 0 ? a = r + ah.length : a = 0;
    var l = e.indexOf(Ud);
    i += e.substring(a, l)
  }
  var d = i.indexOf(Sv);
  if (d > 0 && (i = i.substring(0, d)), i !== "") return i
}
var Lv = 250,
  Ov = new RegExp("[" + Eu + ys + "]"),
  Iv = new RegExp("[^" + ys + "#]+$");

function Bv(e, t, s) {
  if (t = t || {}, s = new Ht(s), t.defaultCountry && !s.hasCountry(t.defaultCountry)) throw t.v2 ? new js("INVALID_COUNTRY") : new Error("Unknown country: ".concat(t.defaultCountry));
  var o = Mv(e, t.v2, t.extract),
    i = o.number,
    r = o.ext,
    a = o.error;
  if (!i) {
    if (t.v2) throw a === "TOO_SHORT" ? new js("TOO_SHORT") : new js("NOT_A_NUMBER");
    return {}
  }
  var l = Nv(i, t.defaultCountry, t.defaultCallingCode, s),
    d = l.country,
    p = l.nationalNumber,
    m = l.countryCallingCode,
    g = l.countryCallingCodeSource,
    $ = l.carrierCode;
  if (!s.hasSelectedNumberingPlan()) {
    if (t.v2) throw new js("INVALID_COUNTRY");
    return {}
  }
  if (!p || p.length < Su) {
    if (t.v2) throw new js("TOO_SHORT");
    return {}
  }
  if (p.length > sb) {
    if (t.v2) throw new js("TOO_LONG");
    return {}
  }
  if (t.v2) {
    var C = new iv(m, p, s.metadata);
    return d && (C.country = d), $ && (C.carrierCode = $), r && (C.ext = r), C.__countryCallingCodeSource = g, C
  }
  var A = (t.extended ? s.hasSelectedNumberingPlan() : d) ? Ys(p, s.nationalNumberPattern()) : !1;
  return t.extended ? {
    country: d,
    countryCallingCode: m,
    carrierCode: $,
    valid: A,
    possible: A ? !0 : !!(t.extended === !0 && s.possibleLengths() && qm(p, s)),
    phone: p,
    ext: r
  } : A ? Rv(d, p, r) : {}
}

function Dv(e, t, s) {
  if (e) {
    if (e.length > Lv) {
      if (s) throw new js("TOO_LONG");
      return
    }
    if (t === !1) return e;
    var o = e.search(Ov);
    if (!(o < 0)) return e.slice(o).replace(Iv, "")
  }
}

function Mv(e, t, s) {
  var o = Tv(e, {
    extractFormattedPhoneNumber: function (r) {
      return Dv(r, s, t)
    }
  });
  if (!o) return {};
  if (!xb(o)) return Sb(o) ? {
    error: "TOO_SHORT"
  } : {};
  var i = Eb(o);
  return i.ext ? i : {
    number: o
  }
}

function Rv(e, t, s) {
  var o = {
    country: e,
    phone: t
  };
  return s && (o.ext = s), o
}

function Nv(e, t, s, o) {
  var i = pv(Yf(e), t, s, o.metadata),
    r = i.countryCallingCodeSource,
    a = i.countryCallingCode,
    l = i.number,
    d;
  if (a) o.selectNumberingPlan(a);
  else if (l && (t || s)) o.selectNumberingPlan(t, s), t && (d = t), a = s || Pu(t, o.metadata);
  else return {};
  if (!l) return {
    countryCallingCodeSource: r,
    countryCallingCode: a
  };
  var p = Nd(Yf(l), o),
    m = p.nationalNumber,
    g = p.carrierCode,
    $ = bv(a, {
      nationalNumber: m,
      defaultCountry: t,
      metadata: o
    });
  return $ && (d = $, $ === "001" || o.country(d)), {
    country: d,
    countryCallingCode: a,
    countryCallingCodeSource: r,
    nationalNumber: m,
    carrierCode: g
  }
}

function lh(e, t) {
  var s = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var o = Object.getOwnPropertySymbols(e);
    t && (o = o.filter(function (i) {
      return Object.getOwnPropertyDescriptor(e, i).enumerable
    })), s.push.apply(s, o)
  }
  return s
}

function ch(e) {
  for (var t = 1; t < arguments.length; t++) {
    var s = arguments[t] != null ? arguments[t] : {};
    t % 2 ? lh(Object(s), !0).forEach(function (o) {
      Fv(e, o, s[o])
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(s)) : lh(Object(s)).forEach(function (o) {
      Object.defineProperty(e, o, Object.getOwnPropertyDescriptor(s, o))
    })
  }
  return e
}

function Fv(e, t, s) {
  return t in e ? Object.defineProperty(e, t, {
    value: s,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : e[t] = s, e
}

function Uv(e, t, s) {
  return Bv(e, ch(ch({}, t), {}, {
    v2: !0
  }), s)
}

function dh(e, t) {
  var s = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var o = Object.getOwnPropertySymbols(e);
    t && (o = o.filter(function (i) {
      return Object.getOwnPropertyDescriptor(e, i).enumerable
    })), s.push.apply(s, o)
  }
  return s
}

function Vv(e) {
  for (var t = 1; t < arguments.length; t++) {
    var s = arguments[t] != null ? arguments[t] : {};
    t % 2 ? dh(Object(s), !0).forEach(function (o) {
      jv(e, o, s[o])
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(s)) : dh(Object(s)).forEach(function (o) {
      Object.defineProperty(e, o, Object.getOwnPropertyDescriptor(s, o))
    })
  }
  return e
}

function jv(e, t, s) {
  return t in e ? Object.defineProperty(e, t, {
    value: s,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : e[t] = s, e
}

function Hv(e, t) {
  return Kv(e) || zv(e, t) || Wv(e, t) || qv()
}

function qv() {
  throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)
}

function Wv(e, t) {
  if (e) {
    if (typeof e == "string") return uh(e, t);
    var s = Object.prototype.toString.call(e).slice(8, -1);
    if (s === "Object" && e.constructor && (s = e.constructor.name), s === "Map" || s === "Set") return Array.from(e);
    if (s === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(s)) return uh(e, t)
  }
}

function uh(e, t) {
  (t == null || t > e.length) && (t = e.length);
  for (var s = 0, o = new Array(t); s < t; s++) o[s] = e[s];
  return o
}

function zv(e, t) {
  var s = e == null ? null : typeof Symbol < "u" && e[Symbol.iterator] || e["@@iterator"];
  if (s != null) {
    var o = [],
      i = !0,
      r = !1,
      a, l;
    try {
      for (s = s.call(e); !(i = (a = s.next()).done) && (o.push(a.value), !(t && o.length === t)); i = !0);
    } catch (d) {
      r = !0, l = d
    } finally {
      try {
        !i && s.return != null && s.return()
      } finally {
        if (r) throw l
      }
    }
    return o
  }
}

function Kv(e) {
  if (Array.isArray(e)) return e
}

function Gv(e) {
  var t = Array.prototype.slice.call(e),
    s = Hv(t, 4),
    o = s[0],
    i = s[1],
    r = s[2],
    a = s[3],
    l, d, p;
  if (typeof o == "string") l = o;
  else throw new TypeError("A text for parsing must be a string.");
  if (!i || typeof i == "string") a ? (d = r, p = a) : (d = void 0, p = r), i && (d = Vv({
    defaultCountry: i
  }, d));
  else if (_a(i)) r ? (d = i, p = r) : p = i;
  else throw new Error("Invalid second argument: ".concat(i));
  return {
    text: l,
    options: d,
    metadata: p
  }
}

function fh(e, t) {
  var s = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var o = Object.getOwnPropertySymbols(e);
    t && (o = o.filter(function (i) {
      return Object.getOwnPropertyDescriptor(e, i).enumerable
    })), s.push.apply(s, o)
  }
  return s
}

function hh(e) {
  for (var t = 1; t < arguments.length; t++) {
    var s = arguments[t] != null ? arguments[t] : {};
    t % 2 ? fh(Object(s), !0).forEach(function (o) {
      Yv(e, o, s[o])
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(s)) : fh(Object(s)).forEach(function (o) {
      Object.defineProperty(e, o, Object.getOwnPropertyDescriptor(s, o))
    })
  }
  return e
}

function Yv(e, t, s) {
  return t in e ? Object.defineProperty(e, t, {
    value: s,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : e[t] = s, e
}

function Jv(e, t, s) {
  t && t.defaultCountry && !vb(t.defaultCountry, s) && (t = hh(hh({}, t), {}, {
    defaultCountry: void 0
  }));
  try {
    return Uv(e, t, s)
  } catch (o) {
    if (!(o instanceof js)) throw o
  }
}

function Zv() {
  var e = Gv(arguments),
    t = e.text,
    s = e.options,
    o = e.metadata;
  return Jv(t, s, o)
}

function td() {
  return Y_(Zv, arguments)
}
const Xv = {
    beforeMount(e, t, s) {
      if (typeof t.value != "function") {
        const o = s.context.name;
        let i = `[Vue-click-outside:] provided expression ${t.expression} is not a function, but has to be`;
        o && (i += `Found in component ${o}`), console.warn(i)
      }
      e.clickOutsideEvent = function (o) {
        const i = o.composedPath ? o.composedPath() : o.path;
        e === o.target || e.contains(o.target) || i.includes(e) || t.value(o, e)
      }, document.body.addEventListener("click", e.clickOutsideEvent)
    },
    unmounted(e) {
      document.body.removeEventListener("click", e.clickOutsideEvent)
    }
  },
  Qv = (e, t) => {
    const s = e.__vccOpts || e;
    for (const [o, i] of t) s[o] = i;
    return s
  };

function Ut(e) {
  const t = Bd.options[e];
  return typeof t > "u" ? Bd.options[e] : t
}
const ey = {
    name: "VueTelInput",
    directives: {
      clickOutside: Xv
    },
    props: {
      modelValue: {
        type: String,
        default: ""
      },
      allCountries: {
        type: Array,
        default: () => Ut("allCountries")
      },
      autoFormat: {
        type: Boolean,
        default: () => Ut("autoFormat")
      },
      customValidate: {
        type: [Boolean, RegExp],
        default: () => Ut("customValidate")
      },
      defaultCountry: {
        type: [String, Number],
        default: () => Ut("defaultCountry")
      },
      disabled: {
        type: Boolean,
        default: () => Ut("disabled")
      },
      autoDefaultCountry: {
        type: Boolean,
        default: () => Ut("autoDefaultCountry")
      },
      dropdownOptions: {
        type: Object,
        default: () => Ut("dropdownOptions")
      },
      ignoredCountries: {
        type: Array,
        default: () => Ut("ignoredCountries")
      },
      inputOptions: {
        type: Object,
        default: () => Ut("inputOptions")
      },
      invalidMsg: {
        type: String,
        default: () => Ut("invalidMsg")
      },
      mode: {
        type: String,
        default: () => Ut("mode")
      },
      onlyCountries: {
        type: Array,
        default: () => Ut("onlyCountries")
      },
      preferredCountries: {
        type: Array,
        default: () => Ut("preferredCountries")
      },
      validCharactersOnly: {
        type: Boolean,
        default: () => Ut("validCharactersOnly")
      },
      styleClasses: {
        type: [String, Array, Object],
        default: () => Ut("styleClasses")
      }
    },
    data() {
      return {
        phone: "",
        activeCountryCode: "",
        open: !1,
        finishMounted: !1,
        selectedIndex: null,
        typeToFindInput: "",
        typeToFindTimer: null,
        dropdownOpenDirection: "below",
        parsedPlaceholder: this.inputOptions.placeholder,
        searchQuery: ""
      }
    },
    computed: {
      activeCountry() {
        return this.findCountry(this.activeCountryCode)
      },
      parsedMode() {
        return this.mode === "auto" ? !this.phone || this.phone[0] !== "+" ? "national" : "international" : ["international", "national"].includes(this.mode) ? this.mode : (console.error('Invalid value of prop "mode"'), "international")
      },
      filteredCountries() {
        return this.onlyCountries.length ? this.allCountries.filter(({
          iso2: e
        }) => this.onlyCountries.some(t => t.toUpperCase() === e)) : this.ignoredCountries.length ? this.allCountries.filter(({
          iso2: e
        }) => !this.ignoredCountries.includes(e.toUpperCase()) && !this.ignoredCountries.includes(e.toLowerCase())) : this.allCountries
      },
      sortedCountries() {
        const e = [...this.getCountries(this.preferredCountries).map(s => ({
          ...s,
          preferred: !0
        })), ...this.filteredCountries];
        if (!this.dropdownOptions.showSearchBox) return e;
        const t = this.searchQuery.replace(/[~`!@#$%^&*()+={}\[\];:\'\"<>.,\/\\\?-_]/g, "");
        return e.filter(s => new RegExp(t, "i").test(s.name) || new RegExp(t, "i").test(s.iso2) || new RegExp(t, "i").test(s.dialCode))
      },
      phoneObject() {
        var e, t, s;
        let o;
        ((e = this.phone) == null ? void 0 : e[0]) === "+" ? o = td(this.phone) || {}: o = td(this.phone, this.activeCountryCode) || {};
        const {
          metadata: i,
          ...r
        } = o;
        let a = (t = o.isValid) == null ? void 0 : t.call(o),
          l = this.phone;
        return a && (l = (s = o.format) == null ? void 0 : s.call(o, this.parsedMode.toUpperCase())), o.country && (this.ignoredCountries.length || this.onlyCountries.length) && (this.findCountry(o.country) || (a = !1, Object.assign(o, {
          country: null
        }))), Object.assign(r, {
          countryCode: o.country,
          valid: a,
          country: this.activeCountry,
          formatted: l
        }), r
      }
    },
    watch: {
      activeCountry(e, t) {
        if (!e && t != null && t.iso2) {
          this.activeCountryCode = t.iso2;
          return
        }
        e != null && e.iso2 && this.$emit("country-changed", e)
      },
      "phoneObject.countryCode": function (e) {
        this.activeCountryCode = e || ""
      },
      "phoneObject.valid": function () {
        this.$emit("validate", this.phoneObject)
      },
      "phoneObject.formatted": function (e) {
        !this.autoFormat || this.customValidate || (this.emitInput(e), this.$nextTick(() => {
          e && !this.modelValue && (this.phone = e)
        }))
      },
      "inputOptions.placeholder": function () {
        this.resetPlaceholder()
      },
      modelValue(e, t) {
        this.testCharacters() ? this.phone = e : this.$nextTick(() => {
          this.phone = t, this.onInput()
        })
      },
      open(e) {
        e ? (this.setDropdownPosition(), this.$emit("open")) : this.$emit("close")
      }
    },
    mounted() {
      this.modelValue && (this.phone = this.modelValue.trim()), this.cleanInvalidCharacters(), this.initializeCountry().then(() => {
        var e;
        !this.phone && (e = this.inputOptions) != null && e.showDialCode && this.activeCountryCode && (this.phone = `+${this.activeCountryCode}`), this.$emit("validate", this.phoneObject)
      }).catch(console.error).then(() => {
        this.finishMounted = !0
      })
    },
    methods: {
      resetPlaceholder() {
        this.parsedPlaceholder = this.inputOptions.placeholder
      },
      initializeCountry() {
        return new Promise(e => {
          var t;
          if (((t = this.phone) == null ? void 0 : t[0]) === "+") {
            e();
            return
          }
          if (this.defaultCountry) {
            if (typeof this.defaultCountry == "string") {
              this.choose(this.defaultCountry), e();
              return
            }
            if (typeof this.defaultCountry == "number") {
              const o = this.findCountryByDialCode(this.defaultCountry);
              if (o) {
                this.choose(o.iso2), e();
                return
              }
            }
          }
          const s = this.preferredCountries[0] || this.filteredCountries[0];
          this.autoDefaultCountry ? W_().then(o => {
            this.choose(o || this.activeCountryCode)
          }).catch(o => {
            console.warn(o), this.choose(s)
          }).then(() => {
            e()
          }) : (this.choose(s), e())
        })
      },
      getCountries(e = []) {
        return e.map(t => this.findCountry(t)).filter(Boolean)
      },
      findCountry(e = "") {
        return this.filteredCountries.find(t => t.iso2 === e.toUpperCase())
      },
      findCountryByDialCode(e) {
        return this.filteredCountries.find(t => Number(t.dialCode) === e)
      },
      getItemClass(e, t) {
        const s = this.selectedIndex === e,
          o = e === this.preferredCountries.length - 1,
          i = this.preferredCountries.some(r => r.toUpperCase() === t);
        return {
          highlighted: s,
          "last-preferred": o,
          preferred: i
        }
      },
      choose(e) {
        var t, s;
        let o = e;
        if (typeof o == "string" && (o = this.findCountry(o)), !!o) {
          if (((t = this.phone) == null ? void 0 : t[0]) === "+" && o.iso2 && this.phoneObject.nationalNumber) {
            this.activeCountryCode = o.iso2, this.phone = td(this.phoneObject.nationalNumber, o.iso2).formatInternational();
            return
          }
          if ((s = this.inputOptions) != null && s.showDialCode && o) {
            this.phone = `+${o.dialCode}`, this.activeCountryCode = o.iso2 || "";
            return
          }
          this.activeCountryCode = o.iso2 || "", this.emitInput(this.phone)
        }
      },
      cleanInvalidCharacters() {
        const e = this.phone;
        if (this.validCharactersOnly) {
          const t = this.phone.match(/[()\-+0-9\s]*/g);
          this.phone = t.join("")
        }
        if (this.customValidate && this.customValidate instanceof RegExp) {
          const t = this.phone.match(this.customValidate);
          this.phone = t.join("")
        }
        e !== this.phone && this.emitInput(this.phone)
      },
      testCharacters() {
        return this.validCharactersOnly && !/^[()\-+0-9\s]*$/.test(this.phone) ? !1 : this.customValidate ? this.testCustomValidate() : !0
      },
      testCustomValidate() {
        return this.customValidate instanceof RegExp ? this.customValidate.test(this.phone) : !1
      },
      onInput() {
        this.$refs.input.setCustomValidity(this.phoneObject.valid ? "" : this.invalidMsg), this.emitInput(this.phone)
      },
      emitInput(e) {
        this.$emit("update:modelValue", e), this.$emit("on-input", e, this.phoneObject, this.$refs.input)
      },
      onBlur() {
        this.$emit("blur")
      },
      onFocus() {
        z_(this.$refs.input, this.phone.length), this.$emit("focus")
      },
      onEnter() {
        this.$emit("enter")
      },
      onSpace() {
        this.$emit("space")
      },
      focus() {
        this.$refs.input.focus()
      },
      toggleDropdown() {
        this.disabled || this.dropdownOptions.disabled || (this.searchQuery = "", this.open = !this.open)
      },
      clickedOutside() {
        this.open = !1
      },
      keyboardNav(e) {
        if (e.keyCode === 40) {
          e.preventDefault(), this.open = !0, this.selectedIndex === null ? this.selectedIndex = 0 : this.selectedIndex = Math.min(this.sortedCountries.length - 1, this.selectedIndex + 1);
          const t = this.$refs.list.children[this.selectedIndex];
          t.focus(), t.offsetTop + t.clientHeight > this.$refs.list.scrollTop + this.$refs.list.clientHeight && (this.$refs.list.scrollTop = t.offsetTop - this.$refs.list.clientHeight + t.clientHeight)
        } else if (e.keyCode === 38) {
          e.preventDefault(), this.open = !0, this.selectedIndex === null ? this.selectedIndex = this.sortedCountries.length - 1 : this.selectedIndex = Math.max(0, this.selectedIndex - 1);
          const t = this.$refs.list.children[this.selectedIndex];
          t.focus(), t.offsetTop < this.$refs.list.scrollTop && (this.$refs.list.scrollTop = t.offsetTop)
        } else if (e.keyCode === 13) this.selectedIndex !== null && this.choose(this.sortedCountries[this.selectedIndex]), this.open = !this.open;
        else {
          this.typeToFindInput += e.key, clearTimeout(this.typeToFindTimer), this.typeToFindTimer = setTimeout(() => {
            this.typeToFindInput = ""
          }, 700);
          const t = this.sortedCountries.slice(this.preferredCountries.length).findIndex(s => s.name.toLowerCase().startsWith(this.typeToFindInput));
          if (t >= 0) {
            this.selectedIndex = this.preferredCountries.length + t;
            const s = this.$refs.list.children[this.selectedIndex],
              o = s.offsetTop < this.$refs.list.scrollTop,
              i = s.offsetTop + s.clientHeight > this.$refs.list.scrollTop + this.$refs.list.clientHeight;
            (o || i) && (this.$refs.list.scrollTop = s.offsetTop - this.$refs.list.clientHeight / 2)
          }
        }
      },
      reset() {
        this.selectedIndex = this.sortedCountries.map(e => e.iso2).indexOf(this.activeCountryCode), this.open = !1
      },
      setDropdownPosition() {
        window.innerHeight - this.$el.getBoundingClientRect().bottom > 200 ? this.dropdownOpenDirection = "below" : this.dropdownOpenDirection = "above"
      }
    }
  },
  ty = ["aria-expanded", "tabindex"],
  sy = {
    class: "vti__selection"
  },
  ny = {
    key: 1,
    class: "vti__country-code"
  },
  oy = {
    class: "vti__dropdown-arrow"
  },
  iy = ["placeholder"],
  ry = ["onClick", "onMousemove", "aria-selected"],
  ay = {
    key: 1
  },
  ly = ["type", "autocomplete", "autofocus", "disabled", "id", "maxlength", "name", "placeholder", "readonly", "required", "tabindex", "value", "aria-describedby"];

function cy(e, t, s, o, i, r) {
  const a = _u("click-outside");
  return _(), w("div", {
    class: Ie(["vue-tel-input", s.styleClasses, {
      disabled: s.disabled
    }])
  }, [be((_(), w("div", {
    "aria-label": "Country Code Selector",
    "aria-haspopup": "listbox",
    "aria-expanded": i.open,
    role: "button",
    class: Ie(["vti__dropdown", {
      open: i.open,
      disabled: s.dropdownOptions.disabled
    }]),
    tabindex: s.dropdownOptions.tabindex,
    onKeydown: [t[2] || (t[2] = (...l) => r.keyboardNav && r.keyboardNav(...l)), t[4] || (t[4] = mo((...l) => r.toggleDropdown && r.toggleDropdown(...l), ["space"])), t[5] || (t[5] = mo((...l) => r.reset && r.reset(...l), ["esc"])), t[6] || (t[6] = mo((...l) => r.reset && r.reset(...l), ["tab"]))],
    onClick: t[3] || (t[3] = (...l) => r.toggleDropdown && r.toggleDropdown(...l))
  }, [n("span", sy, [s.dropdownOptions.showFlags ? (_(), w("span", {
    key: 0,
    class: Ie(["vti__flag", i.activeCountryCode.toLowerCase()])
  }, null, 2)) : D("", !0), s.dropdownOptions.showDialCodeInSelection ? (_(), w("span", ny, " +" + L(r.activeCountry && r.activeCountry.dialCode), 1)) : D("", !0), Wt(e.$slots, "arrow-icon", {
    open: i.open
  }, () => [n("span", oy, L(i.open ? "▲" : "▼"), 1)])]), i.open ? (_(), w("ul", {
    key: 0,
    ref: "list",
    class: Ie(["vti__dropdown-list", i.dropdownOpenDirection]),
    role: "listbox"
  }, [s.dropdownOptions.showSearchBox ? be((_(), w("input", {
    key: 0,
    class: "vti__input vti__search_box",
    "aria-label": "Search by country name or country code",
    placeholder: r.sortedCountries.length ? r.sortedCountries[0].name : "",
    type: "text",
    "onUpdate:modelValue": t[0] || (t[0] = l => i.searchQuery = l),
    onClick: t[1] || (t[1] = Li(() => {}, ["stop"]))
  }, null, 8, iy)), [
    [Re, i.searchQuery]
  ]) : D("", !0), (_(!0), w(Te, null, Je(r.sortedCountries, (l, d) => (_(), w("li", {
    role: "option",
    class: Ie(["vti__dropdown-item", r.getItemClass(d, l.iso2)]),
    key: l.iso2 + (l.preferred ? "-preferred" : ""),
    tabindex: "-1",
    onClick: p => r.choose(l),
    onMousemove: p => i.selectedIndex = d,
    "aria-selected": i.activeCountryCode === l.iso2 && !l.preferred
  }, [s.dropdownOptions.showFlags ? (_(), w("span", {
    key: 0,
    class: Ie(["vti__flag", l.iso2.toLowerCase()])
  }, null, 2)) : D("", !0), n("strong", null, L(l.name), 1), s.dropdownOptions.showDialCodeInList ? (_(), w("span", ay, " +" + L(l.dialCode), 1)) : D("", !0)], 42, ry))), 128))], 2)) : D("", !0)], 42, ty)), [
    [a, r.clickedOutside]
  ]), be(n("input", {
    "onUpdate:modelValue": t[7] || (t[7] = l => i.phone = l),
    ref: "input",
    type: s.inputOptions.type,
    autocomplete: s.inputOptions.autocomplete,
    autofocus: s.inputOptions.autofocus,
    class: Ie(["vti__input", s.inputOptions.styleClasses]),
    disabled: s.disabled,
    id: s.inputOptions.id,
    maxlength: s.inputOptions.maxlength,
    name: s.inputOptions.name,
    placeholder: i.parsedPlaceholder,
    readonly: s.inputOptions.readonly,
    required: s.inputOptions.required,
    tabindex: s.inputOptions.tabindex,
    value: s.modelValue,
    "aria-describedby": s.inputOptions["aria-describedby"],
    onBlur: t[8] || (t[8] = (...l) => r.onBlur && r.onBlur(...l)),
    onFocus: t[9] || (t[9] = (...l) => r.onFocus && r.onFocus(...l)),
    onInput: t[10] || (t[10] = (...l) => r.onInput && r.onInput(...l)),
    onKeyup: [t[11] || (t[11] = mo((...l) => r.onEnter && r.onEnter(...l), ["enter"])), t[12] || (t[12] = mo((...l) => r.onSpace && r.onSpace(...l), ["space"]))]
  }, null, 42, ly), [
    [xu, i.phone]
  ]), Wt(e.$slots, "icon-right")], 2)
}
const zm = Qv(ey, [
    ["render", cy]
  ]),
  dy = {
    install(e, t = {}) {
      const {
        dropdownOptions: s,
        inputOptions: o,
        ...i
      } = t, {
        dropdownOptions: r,
        inputOptions: a,
        ...l
      } = Nm;
      Bd.options = {
        inputOptions: {
          ...a,
          ...o
        },
        dropdownOptions: {
          ...r,
          ...s
        },
        ...l,
        ...i
      }, e.component("vue-tel-input", zm)
    }
  };
var uy = Object.defineProperty,
  fy = Object.defineProperties,
  hy = Object.getOwnPropertyDescriptors,
  ph = Object.getOwnPropertySymbols,
  py = Object.prototype.hasOwnProperty,
  my = Object.prototype.propertyIsEnumerable,
  mh = (e, t, s) => t in e ? uy(e, t, {
    enumerable: !0,
    configurable: !0,
    writable: !0,
    value: s
  }) : e[t] = s,
  ho = (e, t) => {
    for (var s in t || (t = {})) py.call(t, s) && mh(e, s, t[s]);
    if (ph)
      for (var s of ph(t)) my.call(t, s) && mh(e, s, t[s]);
    return e
  },
  gh = (e, t) => fy(e, hy(t));
const gy = {
    props: {
      autoscroll: {
        type: Boolean,
        default: !0
      }
    },
    watch: {
      typeAheadPointer() {
        this.autoscroll && this.maybeAdjustScroll()
      },
      open(e) {
        this.autoscroll && e && this.$nextTick(() => this.maybeAdjustScroll())
      }
    },
    methods: {
      maybeAdjustScroll() {
        var e;
        const t = ((e = this.$refs.dropdownMenu) == null ? void 0 : e.children[this.typeAheadPointer]) || !1;
        if (t) {
          const s = this.getDropdownViewport(),
            {
              top: o,
              bottom: i,
              height: r
            } = t.getBoundingClientRect();
          if (o < s.top) return this.$refs.dropdownMenu.scrollTop = t.offsetTop;
          if (i > s.bottom) return this.$refs.dropdownMenu.scrollTop = t.offsetTop - (s.height - r)
        }
      },
      getDropdownViewport() {
        return this.$refs.dropdownMenu ? this.$refs.dropdownMenu.getBoundingClientRect() : {
          height: 0,
          top: 0,
          bottom: 0
        }
      }
    }
  },
  _y = {
    data() {
      return {
        typeAheadPointer: -1
      }
    },
    watch: {
      filteredOptions() {
        for (let e = 0; e < this.filteredOptions.length; e++)
          if (this.selectable(this.filteredOptions[e])) {
            this.typeAheadPointer = e;
            break
          }
      },
      open(e) {
        e && this.typeAheadToLastSelected()
      },
      selectedValue() {
        this.open && this.typeAheadToLastSelected()
      }
    },
    methods: {
      typeAheadUp() {
        for (let e = this.typeAheadPointer - 1; e >= 0; e--)
          if (this.selectable(this.filteredOptions[e])) {
            this.typeAheadPointer = e;
            break
          }
      },
      typeAheadDown() {
        for (let e = this.typeAheadPointer + 1; e < this.filteredOptions.length; e++)
          if (this.selectable(this.filteredOptions[e])) {
            this.typeAheadPointer = e;
            break
          }
      },
      typeAheadSelect() {
        const e = this.filteredOptions[this.typeAheadPointer];
        e && this.selectable(e) && this.select(e)
      },
      typeAheadToLastSelected() {
        this.typeAheadPointer = this.selectedValue.length !== 0 ? this.filteredOptions.indexOf(this.selectedValue[this.selectedValue.length - 1]) : -1
      }
    }
  },
  by = {
    props: {
      loading: {
        type: Boolean,
        default: !1
      }
    },
    data() {
      return {
        mutableLoading: !1
      }
    },
    watch: {
      search() {
        this.$emit("search", this.search, this.toggleLoading)
      },
      loading(e) {
        this.mutableLoading = e
      }
    },
    methods: {
      toggleLoading(e = null) {
        return e == null ? this.mutableLoading = !this.mutableLoading : this.mutableLoading = e
      }
    }
  },
  Ou = (e, t) => {
    const s = e.__vccOpts || e;
    for (const [o, i] of t) s[o] = i;
    return s
  },
  vy = {},
  yy = {
    xmlns: "http://www.w3.org/2000/svg",
    width: "10",
    height: "10"
  },
  wy = n("path", {
    d: "M6.895455 5l2.842897-2.842898c.348864-.348863.348864-.914488 0-1.263636L9.106534.261648c-.348864-.348864-.914489-.348864-1.263636 0L5 3.104545 2.157102.261648c-.348863-.348864-.914488-.348864-1.263636 0L.261648.893466c-.348864.348864-.348864.914489 0 1.263636L3.104545 5 .261648 7.842898c-.348864.348863-.348864.914488 0 1.263636l.631818.631818c.348864.348864.914773.348864 1.263636 0L5 6.895455l2.842898 2.842897c.348863.348864.914772.348864 1.263636 0l.631818-.631818c.348864-.348864.348864-.914489 0-1.263636L6.895455 5z"
  }, null, -1),
  $y = [wy];

function Cy(e, t) {
  return _(), w("svg", yy, $y)
}
const ky = Ou(vy, [
    ["render", Cy]
  ]),
  Ay = {},
  xy = {
    xmlns: "http://www.w3.org/2000/svg",
    width: "14",
    height: "10"
  },
  Sy = n("path", {
    d: "M9.211364 7.59931l4.48338-4.867229c.407008-.441854.407008-1.158247 0-1.60046l-.73712-.80023c-.407008-.441854-1.066904-.441854-1.474243 0L7 5.198617 2.51662.33139c-.407008-.441853-1.066904-.441853-1.474243 0l-.737121.80023c-.407008.441854-.407008 1.158248 0 1.600461l4.48338 4.867228L7 10l2.211364-2.40069z"
  }, null, -1),
  Ey = [Sy];

function Py(e, t) {
  return _(), w("svg", xy, Ey)
}
const Ty = Ou(Ay, [
    ["render", Py]
  ]),
  _h = {
    Deselect: ky,
    OpenIndicator: Ty
  },
  Ly = {
    mounted(e, {
      instance: t
    }) {
      if (t.appendToBody) {
        const {
          height: s,
          top: o,
          left: i,
          width: r
        } = t.$refs.toggle.getBoundingClientRect();
        let a = window.scrollX || window.pageXOffset,
          l = window.scrollY || window.pageYOffset;
        e.unbindPosition = t.calculatePosition(e, t, {
          width: r + "px",
          left: a + i + "px",
          top: l + o + s + "px"
        }), document.body.appendChild(e)
      }
    },
    unmounted(e, {
      instance: t
    }) {
      t.appendToBody && (e.unbindPosition && typeof e.unbindPosition == "function" && e.unbindPosition(), e.parentNode && e.parentNode.removeChild(e))
    }
  };

function Oy(e) {
  const t = {};
  return Object.keys(e).sort().forEach(s => {
    t[s] = e[s]
  }), JSON.stringify(t)
}
let Iy = 0;

function By() {
  return ++Iy
}
const Dy = {
    components: ho({}, _h),
    directives: {
      appendToBody: Ly
    },
    mixins: [gy, _y, by],
    compatConfig: {
      MODE: 3
    },
    emits: ["open", "close", "update:modelValue", "search", "search:compositionstart", "search:compositionend", "search:keydown", "search:blur", "search:focus", "search:input", "option:created", "option:selecting", "option:selected", "option:deselecting", "option:deselected"],
    props: {
      modelValue: {},
      components: {
        type: Object,
        default: () => ({})
      },
      options: {
        type: Array,
        default () {
          return []
        }
      },
      disabled: {
        type: Boolean,
        default: !1
      },
      clearable: {
        type: Boolean,
        default: !0
      },
      deselectFromDropdown: {
        type: Boolean,
        default: !1
      },
      searchable: {
        type: Boolean,
        default: !0
      },
      multiple: {
        type: Boolean,
        default: !1
      },
      placeholder: {
        type: String,
        default: ""
      },
      transition: {
        type: String,
        default: "vs__fade"
      },
      clearSearchOnSelect: {
        type: Boolean,
        default: !0
      },
      closeOnSelect: {
        type: Boolean,
        default: !0
      },
      label: {
        type: String,
        default: "label"
      },
      autocomplete: {
        type: String,
        default: "off"
      },
      reduce: {
        type: Function,
        default: e => e
      },
      selectable: {
        type: Function,
        default: e => !0
      },
      getOptionLabel: {
        type: Function,
        default (e) {
          return typeof e == "object" ? e.hasOwnProperty(this.label) ? e[this.label] : console.warn(`[vue-select warn]: Label key "option.${this.label}" does not exist in options object ${JSON.stringify(e)}.
https://vue-select.org/api/props.html#getoptionlabel`) : e
        }
      },
      getOptionKey: {
        type: Function,
        default (e) {
          if (typeof e != "object") return e;
          try {
            return e.hasOwnProperty("id") ? e.id : Oy(e)
          } catch (t) {
            return console.warn(`[vue-select warn]: Could not stringify this option to generate unique key. Please provide'getOptionKey' prop to return a unique key for each option.
https://vue-select.org/api/props.html#getoptionkey`, e, t)
          }
        }
      },
      onTab: {
        type: Function,
        default: function () {
          this.selectOnTab && !this.isComposing && this.typeAheadSelect()
        }
      },
      taggable: {
        type: Boolean,
        default: !1
      },
      tabindex: {
        type: Number,
        default: null
      },
      pushTags: {
        type: Boolean,
        default: !1
      },
      filterable: {
        type: Boolean,
        default: !0
      },
      filterBy: {
        type: Function,
        default (e, t, s) {
          return (t || "").toLocaleLowerCase().indexOf(s.toLocaleLowerCase()) > -1
        }
      },
      filter: {
        type: Function,
        default (e, t) {
          return e.filter(s => {
            let o = this.getOptionLabel(s);
            return typeof o == "number" && (o = o.toString()), this.filterBy(s, o, t)
          })
        }
      },
      createOption: {
        type: Function,
        default (e) {
          return typeof this.optionList[0] == "object" ? {
            [this.label]: e
          } : e
        }
      },
      resetOnOptionsChange: {
        default: !1,
        validator: e => ["function", "boolean"].includes(typeof e)
      },
      clearSearchOnBlur: {
        type: Function,
        default: function ({
          clearSearchOnSelect: e,
          multiple: t
        }) {
          return e && !t
        }
      },
      noDrop: {
        type: Boolean,
        default: !1
      },
      inputId: {
        type: String
      },
      dir: {
        type: String,
        default: "auto"
      },
      selectOnTab: {
        type: Boolean,
        default: !1
      },
      selectOnKeyCodes: {
        type: Array,
        default: () => [13]
      },
      searchInputQuerySelector: {
        type: String,
        default: "[type=search]"
      },
      mapKeydown: {
        type: Function,
        default: (e, t) => e
      },
      appendToBody: {
        type: Boolean,
        default: !1
      },
      calculatePosition: {
        type: Function,
        default (e, t, {
          width: s,
          top: o,
          left: i
        }) {
          e.style.top = o, e.style.left = i, e.style.width = s
        }
      },
      dropdownShouldOpen: {
        type: Function,
        default ({
          noDrop: e,
          open: t,
          mutableLoading: s
        }) {
          return e ? !1 : t && !s
        }
      },
      uid: {
        type: [String, Number],
        default: () => By()
      }
    },
    data() {
      return {
        search: "",
        open: !1,
        isComposing: !1,
        pushedTags: [],
        _value: [],
        deselectButtons: []
      }
    },
    computed: {
      isReducingValues() {
        return this.$props.reduce !== this.$options.props.reduce.default
      },
      isTrackingValues() {
        return typeof this.modelValue > "u" || this.isReducingValues
      },
      selectedValue() {
        let e = this.modelValue;
        return this.isTrackingValues && (e = this.$data._value), e != null && e !== "" ? [].concat(e) : []
      },
      optionList() {
        return this.options.concat(this.pushTags ? this.pushedTags : [])
      },
      searchEl() {
        return this.$slots.search ? this.$refs.selectedOptions.querySelector(this.searchInputQuerySelector) : this.$refs.search
      },
      scope() {
        const e = {
          search: this.search,
          loading: this.loading,
          searching: this.searching,
          filteredOptions: this.filteredOptions
        };
        return {
          search: {
            attributes: ho({
              disabled: this.disabled,
              placeholder: this.searchPlaceholder,
              tabindex: this.tabindex,
              readonly: !this.searchable,
              id: this.inputId,
              "aria-autocomplete": "list",
              "aria-labelledby": `vs${this.uid}__combobox`,
              "aria-controls": `vs${this.uid}__listbox`,
              ref: "search",
              type: "search",
              autocomplete: this.autocomplete,
              value: this.search
            }, this.dropdownOpen && this.filteredOptions[this.typeAheadPointer] ? {
              "aria-activedescendant": `vs${this.uid}__option-${this.typeAheadPointer}`
            } : {}),
            events: {
              compositionstart: () => this.isComposing = !0,
              compositionend: () => this.isComposing = !1,
              keydown: this.onSearchKeyDown,
              blur: this.onSearchBlur,
              focus: this.onSearchFocus,
              input: t => this.search = t.target.value
            }
          },
          spinner: {
            loading: this.mutableLoading
          },
          noOptions: {
            search: this.search,
            loading: this.mutableLoading,
            searching: this.searching
          },
          openIndicator: {
            attributes: {
              ref: "openIndicator",
              role: "presentation",
              class: "vs__open-indicator"
            }
          },
          listHeader: e,
          listFooter: e,
          header: gh(ho({}, e), {
            deselect: this.deselect
          }),
          footer: gh(ho({}, e), {
            deselect: this.deselect
          })
        }
      },
      childComponents() {
        return ho(ho({}, _h), this.components)
      },
      stateClasses() {
        return {
          "vs--open": this.dropdownOpen,
          "vs--single": !this.multiple,
          "vs--multiple": this.multiple,
          "vs--searching": this.searching && !this.noDrop,
          "vs--searchable": this.searchable && !this.noDrop,
          "vs--unsearchable": !this.searchable,
          "vs--loading": this.mutableLoading,
          "vs--disabled": this.disabled
        }
      },
      searching() {
        return !!this.search
      },
      dropdownOpen() {
        return this.dropdownShouldOpen(this)
      },
      searchPlaceholder() {
        return this.isValueEmpty && this.placeholder ? this.placeholder : void 0
      },
      filteredOptions() {
        const e = [].concat(this.optionList);
        if (!this.filterable && !this.taggable) return e;
        const t = this.search.length ? this.filter(e, this.search, this) : e;
        if (this.taggable && this.search.length) {
          const s = this.createOption(this.search);
          this.optionExists(s) || t.unshift(s)
        }
        return t
      },
      isValueEmpty() {
        return this.selectedValue.length === 0
      },
      showClearButton() {
        return !this.multiple && this.clearable && !this.open && !this.isValueEmpty
      }
    },
    watch: {
      options(e, t) {
        const s = () => typeof this.resetOnOptionsChange == "function" ? this.resetOnOptionsChange(e, t, this.selectedValue) : this.resetOnOptionsChange;
        !this.taggable && s() && this.clearSelection(), this.modelValue && this.isTrackingValues && this.setInternalValueFromOptions(this.modelValue)
      },
      modelValue: {
        immediate: !0,
        handler(e) {
          this.isTrackingValues && this.setInternalValueFromOptions(e)
        }
      },
      multiple() {
        this.clearSelection()
      },
      open(e) {
        this.$emit(e ? "open" : "close")
      }
    },
    created() {
      this.mutableLoading = this.loading
    },
    methods: {
      setInternalValueFromOptions(e) {
        Array.isArray(e) ? this.$data._value = e.map(t => this.findOptionFromReducedValue(t)) : this.$data._value = this.findOptionFromReducedValue(e)
      },
      select(e) {
        this.$emit("option:selecting", e), this.isOptionSelected(e) ? this.deselectFromDropdown && (this.clearable || this.multiple && this.selectedValue.length > 1) && this.deselect(e) : (this.taggable && !this.optionExists(e) && (this.$emit("option:created", e), this.pushTag(e)), this.multiple && (e = this.selectedValue.concat(e)), this.updateValue(e), this.$emit("option:selected", e)), this.onAfterSelect(e)
      },
      deselect(e) {
        this.$emit("option:deselecting", e), this.updateValue(this.selectedValue.filter(t => !this.optionComparator(t, e))), this.$emit("option:deselected", e)
      },
      clearSelection() {
        this.updateValue(this.multiple ? [] : null)
      },
      onAfterSelect(e) {
        this.closeOnSelect && (this.open = !this.open, this.searchEl.blur()), this.clearSearchOnSelect && (this.search = "")
      },
      updateValue(e) {
        typeof this.modelValue > "u" && (this.$data._value = e), e !== null && (Array.isArray(e) ? e = e.map(t => this.reduce(t)) : e = this.reduce(e)), this.$emit("update:modelValue", e)
      },
      toggleDropdown(e) {
        const t = e.target !== this.searchEl;
        t && e.preventDefault();
        const s = [...this.deselectButtons || [], this.$refs.clearButton];
        if (this.searchEl === void 0 || s.filter(Boolean).some(o => o.contains(e.target) || o === e.target)) {
          e.preventDefault();
          return
        }
        this.open && t ? this.searchEl.blur() : this.disabled || (this.open = !0, this.searchEl.focus())
      },
      isOptionSelected(e) {
        return this.selectedValue.some(t => this.optionComparator(t, e))
      },
      isOptionDeselectable(e) {
        return this.isOptionSelected(e) && this.deselectFromDropdown
      },
      optionComparator(e, t) {
        return this.getOptionKey(e) === this.getOptionKey(t)
      },
      findOptionFromReducedValue(e) {
        const t = o => JSON.stringify(this.reduce(o)) === JSON.stringify(e),
          s = [...this.options, ...this.pushedTags].filter(t);
        return s.length === 1 ? s[0] : s.find(o => this.optionComparator(o, this.$data._value)) || e
      },
      closeSearchOptions() {
        this.open = !1, this.$emit("search:blur")
      },
      maybeDeleteValue() {
        if (!this.searchEl.value.length && this.selectedValue && this.selectedValue.length && this.clearable) {
          let e = null;
          this.multiple && (e = [...this.selectedValue.slice(0, this.selectedValue.length - 1)]), this.updateValue(e)
        }
      },
      optionExists(e) {
        return this.optionList.some(t => this.optionComparator(t, e))
      },
      normalizeOptionForSlot(e) {
        return typeof e == "object" ? e : {
          [this.label]: e
        }
      },
      pushTag(e) {
        this.pushedTags.push(e)
      },
      onEscape() {
        this.search.length ? this.search = "" : this.searchEl.blur()
      },
      onSearchBlur() {
        if (this.mousedown && !this.searching) this.mousedown = !1;
        else {
          const {
            clearSearchOnSelect: e,
            multiple: t
          } = this;
          this.clearSearchOnBlur({
            clearSearchOnSelect: e,
            multiple: t
          }) && (this.search = ""), this.closeSearchOptions();
          return
        }
        if (this.search.length === 0 && this.options.length === 0) {
          this.closeSearchOptions();
          return
        }
      },
      onSearchFocus() {
        this.open = !0, this.$emit("search:focus")
      },
      onMousedown() {
        this.mousedown = !0
      },
      onMouseUp() {
        this.mousedown = !1
      },
      onSearchKeyDown(e) {
        const t = i => (i.preventDefault(), !this.isComposing && this.typeAheadSelect()),
          s = {
            8: i => this.maybeDeleteValue(),
            9: i => this.onTab(),
            27: i => this.onEscape(),
            38: i => (i.preventDefault(), this.typeAheadUp()),
            40: i => (i.preventDefault(), this.typeAheadDown())
          };
        this.selectOnKeyCodes.forEach(i => s[i] = t);
        const o = this.mapKeydown(s, this);
        if (typeof o[e.keyCode] == "function") return o[e.keyCode](e)
      }
    }
  },
  My = ["dir"],
  Ry = ["id", "aria-expanded", "aria-owns"],
  Ny = {
    ref: "selectedOptions",
    class: "vs__selected-options"
  },
  Fy = ["disabled", "title", "aria-label", "onClick"],
  Uy = {
    ref: "actions",
    class: "vs__actions"
  },
  Vy = ["disabled"],
  jy = {
    class: "vs__spinner"
  },
  Hy = ["id"],
  qy = ["id", "aria-selected", "onMouseover", "onClick"],
  Wy = {
    key: 0,
    class: "vs__no-options"
  },
  zy = J(" Sorry, no matching options. "),
  Ky = ["id"];

function Gy(e, t, s, o, i, r) {
  const a = _u("append-to-body");
  return _(), w("div", {
    dir: s.dir,
    class: Ie(["v-select", r.stateClasses])
  }, [Wt(e.$slots, "header", ds(us(r.scope.header))), n("div", {
    id: `vs${s.uid}__combobox`,
    ref: "toggle",
    class: "vs__dropdown-toggle",
    role: "combobox",
    "aria-expanded": r.dropdownOpen.toString(),
    "aria-owns": `vs${s.uid}__listbox`,
    "aria-label": "Search for option",
    onMousedown: t[1] || (t[1] = l => r.toggleDropdown(l))
  }, [n("div", Ny, [(_(!0), w(Te, null, Je(r.selectedValue, (l, d) => Wt(e.$slots, "selected-option-container", {
    option: r.normalizeOptionForSlot(l),
    deselect: r.deselect,
    multiple: s.multiple,
    disabled: s.disabled
  }, () => [(_(), w("span", {
    key: s.getOptionKey(l),
    class: "vs__selected"
  }, [Wt(e.$slots, "selected-option", ds(us(r.normalizeOptionForSlot(l))), () => [J(L(s.getOptionLabel(l)), 1)]), s.multiple ? (_(), w("button", {
    key: 0,
    ref_for: !0,
    ref: p => i.deselectButtons[d] = p,
    disabled: s.disabled,
    type: "button",
    class: "vs__deselect",
    title: `Deselect ${s.getOptionLabel(l)}`,
    "aria-label": `Deselect ${s.getOptionLabel(l)}`,
    onClick: p => r.deselect(l)
  }, [(_(), ve(ha(r.childComponents.Deselect)))], 8, Fy)) : D("", !0)]))])), 256)), Wt(e.$slots, "search", ds(us(r.scope.search)), () => [n("input", Pi({
    class: "vs__search"
  }, r.scope.search.attributes, Gp(r.scope.search.events)), null, 16)])], 512), n("div", Uy, [be(n("button", {
    ref: "clearButton",
    disabled: s.disabled,
    type: "button",
    class: "vs__clear",
    title: "Clear Selected",
    "aria-label": "Clear Selected",
    onClick: t[0] || (t[0] = (...l) => r.clearSelection && r.clearSelection(...l))
  }, [(_(), ve(ha(r.childComponents.Deselect)))], 8, Vy), [
    [Ti, r.showClearButton]
  ]), Wt(e.$slots, "open-indicator", ds(us(r.scope.openIndicator)), () => [s.noDrop ? D("", !0) : (_(), ve(ha(r.childComponents.OpenIndicator), ds(Pi({
    key: 0
  }, r.scope.openIndicator.attributes)), null, 16))]), Wt(e.$slots, "spinner", ds(us(r.scope.spinner)), () => [be(n("div", jy, "Loading...", 512), [
    [Ti, e.mutableLoading]
  ])])], 512)], 40, Ry), ie(nl, {
    name: s.transition
  }, {
    default: Ue(() => [r.dropdownOpen ? be((_(), w("ul", {
      id: `vs${s.uid}__listbox`,
      ref: "dropdownMenu",
      key: `vs${s.uid}__listbox`,
      class: "vs__dropdown-menu",
      role: "listbox",
      tabindex: "-1",
      onMousedown: t[2] || (t[2] = Li((...l) => r.onMousedown && r.onMousedown(...l), ["prevent"])),
      onMouseup: t[3] || (t[3] = (...l) => r.onMouseUp && r.onMouseUp(...l))
    }, [Wt(e.$slots, "list-header", ds(us(r.scope.listHeader))), (_(!0), w(Te, null, Je(r.filteredOptions, (l, d) => (_(), w("li", {
      id: `vs${s.uid}__option-${d}`,
      key: s.getOptionKey(l),
      role: "option",
      class: Ie(["vs__dropdown-option", {
        "vs__dropdown-option--deselect": r.isOptionDeselectable(l) && d === e.typeAheadPointer,
        "vs__dropdown-option--selected": r.isOptionSelected(l),
        "vs__dropdown-option--highlight": d === e.typeAheadPointer,
        "vs__dropdown-option--disabled": !s.selectable(l)
      }]),
      "aria-selected": d === e.typeAheadPointer ? !0 : null,
      onMouseover: p => s.selectable(l) ? e.typeAheadPointer = d : null,
      onClick: Li(p => s.selectable(l) ? r.select(l) : null, ["prevent", "stop"])
    }, [Wt(e.$slots, "option", ds(us(r.normalizeOptionForSlot(l))), () => [J(L(s.getOptionLabel(l)), 1)])], 42, qy))), 128)), r.filteredOptions.length === 0 ? (_(), w("li", Wy, [Wt(e.$slots, "no-options", ds(us(r.scope.noOptions)), () => [zy])])) : D("", !0), Wt(e.$slots, "list-footer", ds(us(r.scope.listFooter)))], 40, Hy)), [
      [a]
    ]) : (_(), w("ul", {
      key: 1,
      id: `vs${s.uid}__listbox`,
      role: "listbox",
      style: {
        display: "none",
        visibility: "hidden"
      }
    }, null, 8, Ky))]),
    _: 3
  }, 8, ["name"]), Wt(e.$slots, "footer", ds(us(r.scope.footer)))], 10, My)
}
const Yy = Ou(Dy, [
  ["render", Gy]
]);
var Us = typeof globalThis < "u" ? globalThis : typeof window < "u" ? window : typeof global < "u" ? global : typeof self < "u" ? self : {};

function Jy(e) {
  return e && e.__esModule && Object.prototype.hasOwnProperty.call(e, "default") ? e.default : e
}

function Zy(e) {
  if (e.__esModule) return e;
  var t = e.default;
  if (typeof t == "function") {
    var s = function o() {
      return this instanceof o ? Reflect.construct(t, arguments, this.constructor) : t.apply(this, arguments)
    };
    s.prototype = t.prototype
  } else s = {};
  return Object.defineProperty(s, "__esModule", {
    value: !0
  }), Object.keys(e).forEach(function (o) {
    var i = Object.getOwnPropertyDescriptor(e, o);
    Object.defineProperty(s, o, i.get ? i : {
      enumerable: !0,
      get: function () {
        return e[o]
      }
    })
  }), s
}
var Km = {
  exports: {}
};
const Xy = Zy(j_);
(function (e, t) {
  (function (o, i) {
    e.exports = i(Xy)
  })(Us, s => (() => {
    var o = {
        878: (l, d) => {
          Object.defineProperty(d, "__esModule", {
            value: !0
          }), d.default = (p, m) => {
            const g = p.__vccOpts || p;
            for (const [$, C] of m) g[$] = C;
            return g
          }
        },
        976: l => {
          l.exports = s
        }
      },
      i = {};

    function r(l) {
      var d = i[l];
      if (d !== void 0) return d.exports;
      var p = i[l] = {
        exports: {}
      };
      return o[l](p, p.exports, r), p.exports
    }
    r.d = (l, d) => {
      for (var p in d) r.o(d, p) && !r.o(l, p) && Object.defineProperty(l, p, {
        enumerable: !0,
        get: d[p]
      })
    }, r.o = (l, d) => Object.prototype.hasOwnProperty.call(l, d), r.r = l => {
      typeof Symbol < "u" && Symbol.toStringTag && Object.defineProperty(l, Symbol.toStringTag, {
        value: "Module"
      }), Object.defineProperty(l, "__esModule", {
        value: !0
      })
    };
    var a = {};
    return (() => {
      r.r(a), r.d(a, {
        Component: () => lt,
        LoadingPlugin: () => K,
        default: () => ae,
        useLoading: () => ot
      });
      var l = r(976);

      function d(H) {
        var ce;
        typeof H.remove < "u" ? H.remove() : (ce = H.parentNode) == null || ce.removeChild(H)
      }

      function p(H, ce, De) {
        let Ne = arguments.length > 3 && arguments[3] !== void 0 ? arguments[3] : {};
        const k = (0, l.h)(H, ce, Ne),
          x = document.createElement("div");
        return x.classList.add("vld-container"), De.appendChild(x), (0, l.render)(k, x), k.component
      }

      function m() {
        return typeof window < "u"
      }
      const g = m() ? window.HTMLElement : Object,
        $ = ["aria-busy"],
        C = {
          class: "vl-icon"
        };

      function A(H, ce, De, Ne, k, x) {
        return (0, l.openBlock)(), (0, l.createBlock)(l.Transition, {
          name: H.transition
        }, {
          default: (0, l.withCtx)(() => [(0, l.withDirectives)((0, l.createElementVNode)("div", {
            tabindex: "0",
            class: (0, l.normalizeClass)(["vl-overlay vl-active", {
              "vl-full-page": H.isFullPage
            }]),
            "aria-busy": H.isActive,
            "aria-label": "Loading",
            style: (0, l.normalizeStyle)({
              zIndex: H.zIndex
            })
          }, [(0, l.createElementVNode)("div", {
            class: "vl-background",
            onClick: ce[0] || (ce[0] = (0, l.withModifiers)(function () {
              return H.cancel && H.cancel(...arguments)
            }, ["prevent"])),
            style: (0, l.normalizeStyle)(H.bgStyle)
          }, null, 4), (0, l.createElementVNode)("div", C, [(0, l.renderSlot)(H.$slots, "before"), (0, l.renderSlot)(H.$slots, "default", {}, () => [((0, l.openBlock)(), (0, l.createBlock)((0, l.resolveDynamicComponent)(H.loader), {
            color: H.color,
            width: H.width,
            height: H.height
          }, null, 8, ["color", "width", "height"]))]), (0, l.renderSlot)(H.$slots, "after")])], 14, $), [
            [l.vShow, H.isActive]
          ])]),
          _: 3
        }, 8, ["name"])
      }
      const O = {
          mounted() {
            this.enforceFocus && document.addEventListener("focusin", this.focusIn)
          },
          methods: {
            focusIn(H) {
              if (!this.isActive || H.target === this.$el || this.$el.contains(H.target)) return;
              let ce = this.container ? this.container : this.isFullPage ? null : this.$el.parentElement;
              (this.isFullPage || ce && ce.contains(H.target)) && (H.preventDefault(), this.$el.focus())
            }
          },
          beforeUnmount() {
            document.removeEventListener("focusin", this.focusIn)
          }
        },
        V = ["width", "height", "stroke"],
        B = [(0, l.createStaticVNode)('<g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="2"><circle stroke-opacity=".25" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.8s" repeatCount="indefinite"></animateTransform></path></g></g>', 1)];

      function P(H, ce, De, Ne, k, x) {
        return (0, l.openBlock)(), (0, l.createElementBlock)("svg", {
          viewBox: "0 0 38 38",
          xmlns: "http://www.w3.org/2000/svg",
          width: H.width,
          height: H.height,
          stroke: H.color
        }, B, 8, V)
      }
      const I = (0, l.defineComponent)({
        name: "spinner",
        props: {
          color: {
            type: String,
            default: "#000"
          },
          height: {
            type: Number,
            default: 64
          },
          width: {
            type: Number,
            default: 64
          }
        }
      });
      var R = r(878);
      const j = (0, R.default)(I, [
          ["render", P]
        ]),
        q = ["fill", "width", "height"],
        ee = [(0, l.createStaticVNode)('<circle cx="15" cy="15" r="15"><animate attributeName="r" from="15" to="15" begin="0s" dur="0.8s" values="15;9;15" calcMode="linear" repeatCount="indefinite"></animate><animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite"></animate></circle><circle cx="60" cy="15" r="9" fill-opacity="0.3"><animate attributeName="r" from="9" to="9" begin="0s" dur="0.8s" values="9;15;9" calcMode="linear" repeatCount="indefinite"></animate><animate attributeName="fill-opacity" from="0.5" to="0.5" begin="0s" dur="0.8s" values=".5;1;.5" calcMode="linear" repeatCount="indefinite"></animate></circle><circle cx="105" cy="15" r="15"><animate attributeName="r" from="15" to="15" begin="0s" dur="0.8s" values="15;9;15" calcMode="linear" repeatCount="indefinite"></animate><animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite"></animate></circle>', 3)];

      function ge(H, ce, De, Ne, k, x) {
        return (0, l.openBlock)(), (0, l.createElementBlock)("svg", {
          viewBox: "0 0 120 30",
          xmlns: "http://www.w3.org/2000/svg",
          fill: H.color,
          width: H.width,
          height: H.height
        }, ee, 8, q)
      }
      const T = (0, l.defineComponent)({
          name: "dots",
          props: {
            color: {
              type: String,
              default: "#000"
            },
            height: {
              type: Number,
              default: 240
            },
            width: {
              type: Number,
              default: 60
            }
          }
        }),
        Le = (0, R.default)(T, [
          ["render", ge]
        ]),
        We = ["height", "width", "fill"],
        ue = [(0, l.createStaticVNode)('<rect x="0" y="13" width="4" height="5"><animate attributeName="height" attributeType="XML" values="5;21;5" begin="0s" dur="0.6s" repeatCount="indefinite"></animate><animate attributeName="y" attributeType="XML" values="13; 5; 13" begin="0s" dur="0.6s" repeatCount="indefinite"></animate></rect><rect x="10" y="13" width="4" height="5"><animate attributeName="height" attributeType="XML" values="5;21;5" begin="0.15s" dur="0.6s" repeatCount="indefinite"></animate><animate attributeName="y" attributeType="XML" values="13; 5; 13" begin="0.15s" dur="0.6s" repeatCount="indefinite"></animate></rect><rect x="20" y="13" width="4" height="5"><animate attributeName="height" attributeType="XML" values="5;21;5" begin="0.3s" dur="0.6s" repeatCount="indefinite"></animate><animate attributeName="y" attributeType="XML" values="13; 5; 13" begin="0.3s" dur="0.6s" repeatCount="indefinite"></animate></rect>', 3)];

      function te(H, ce, De, Ne, k, x) {
        return (0, l.openBlock)(), (0, l.createElementBlock)("svg", {
          xmlns: "http://www.w3.org/2000/svg",
          viewBox: "0 0 30 30",
          height: H.height,
          width: H.width,
          fill: H.color
        }, ue, 8, We)
      }
      const $t = (0, l.defineComponent)({
          name: "bars",
          props: {
            color: {
              type: String,
              default: "#000"
            },
            height: {
              type: Number,
              default: 40
            },
            width: {
              type: Number,
              default: 40
            }
          }
        }),
        ht = {
          Spinner: j,
          Dots: Le,
          Bars: (0, R.default)($t, [
            ["render", te]
          ])
        },
        U = (0, l.defineComponent)({
          name: "VueLoading",
          mixins: [O],
          props: {
            active: Boolean,
            programmatic: Boolean,
            container: [Object, Function, g],
            isFullPage: {
              type: Boolean,
              default: !0
            },
            enforceFocus: {
              type: Boolean,
              default: !0
            },
            lockScroll: Boolean,
            transition: {
              type: String,
              default: "fade"
            },
            canCancel: Boolean,
            onCancel: {
              type: Function,
              default: () => {}
            },
            color: String,
            backgroundColor: String,
            opacity: Number,
            width: Number,
            height: Number,
            zIndex: Number,
            loader: {
              type: String,
              default: "spinner"
            }
          },
          components: ht,
          emits: ["hide", "update:active"],
          data() {
            return {
              isActive: this.active
            }
          },
          mounted() {
            document.addEventListener("keyup", this.keyPress)
          },
          methods: {
            cancel() {
              !this.canCancel || !this.isActive || (this.hide(), this.onCancel.apply(null, arguments))
            },
            hide() {
              this.$emit("hide"), this.$emit("update:active", !1), this.programmatic && (this.isActive = !1, setTimeout(() => {
                const H = this.$el.parentElement;
                (0, l.render)(null, H), d(H)
              }, 150))
            },
            disableScroll() {
              this.isFullPage && this.lockScroll && document.body.classList.add("vl-shown")
            },
            enableScroll() {
              this.isFullPage && this.lockScroll && document.body.classList.remove("vl-shown")
            },
            keyPress(H) {
              H.keyCode === 27 && this.cancel()
            }
          },
          watch: {
            active(H) {
              this.isActive = H
            },
            isActive: {
              handler(H) {
                H ? this.disableScroll() : this.enableScroll()
              },
              immediate: !0
            }
          },
          computed: {
            bgStyle() {
              return {
                background: this.backgroundColor,
                opacity: this.opacity
              }
            }
          },
          beforeUnmount() {
            document.removeEventListener("keyup", this.keyPress)
          }
        }),
        lt = (0, R.default)(U, [
          ["render", A]
        ]);

      function ot() {
        let H = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {},
          ce = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
        return {
          show() {
            let De = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : H,
              Ne = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : ce;
            const x = {
              ...H,
              ...De,
              ...{
                programmatic: !0,
                lockScroll: !0,
                isFullPage: !1,
                active: !0
              }
            };
            let N = x.container;
            x.container || (N = document.body, x.isFullPage = !0);
            const Y = {
              ...ce,
              ...Ne
            };
            return {
              hide: p(lt, x, N, Y).ctx.hide
            }
          }
        }
      }
      const K = function (H) {
          let ce = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {},
            De = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
          const Ne = ot(ce, De);
          H.config.globalProperties.$loading = Ne, H.provide("$loading", Ne)
        },
        ae = lt
    })(), a
  })())
})(Km);
var Qy = Km.exports;
const Gm = "data:image/svg+xml,%3csvg%20width='24'%20height='24'%20viewBox='0%200%2024%2024'%20xmlns='http://www.w3.org/2000/svg'%20fill='%232ea094'%3e%3cstyle%3e.spinner_S1WN{animation:spinner_MGfb%20.8s%20linear%20infinite;animation-delay:-.8s}.spinner_Km9P{animation-delay:-.65s}.spinner_JApP{animation-delay:-.5s}@keyframes%20spinner_MGfb{93.75%25,100%25{opacity:.2}}%3c/style%3e%3ccircle%20class='spinner_S1WN'%20cx='4'%20cy='12'%20r='3'/%3e%3ccircle%20class='spinner_S1WN%20spinner_Km9P'%20cx='12'%20cy='12'%20r='3'/%3e%3ccircle%20class='spinner_S1WN%20spinner_JApP'%20cx='20'%20cy='12'%20r='3'/%3e%3c/svg%3e",
  je = (e, t) => {
    const s = e.__vccOpts || e;
    for (const [o, i] of t) s[o] = i;
    return s
  },
  ew = {
    name: "LoadingSpinner"
  },
  tw = {
    class: "d-flex justify-content-center py-5"
  },
  sw = n("div", {
    class: "loading-spinner"
  }, [n("img", {
    src: Gm,
    alt: "Loading Animation"
  })], -1),
  nw = [sw];

function ow(e, t, s, o, i, r) {
  return _(), w("div", tw, nw)
}
const iw = je(ew, [
    ["render", ow]
  ]),
  rw = {
    name: "SmallLoadingSpinner"
  },
  aw = {
    class: ""
  },
  lw = n("div", {
    class: "loading-spinner"
  }, [n("img", {
    src: Gm,
    alt: "Loading Animation"
  })], -1),
  cw = [lw];

function dw(e, t, s, o, i, r) {
  return _(), w("div", aw, cw)
}
const Vs = je(rw, [
    ["render", dw]
  ]),
  uw = "/assets/empty-DFjIU84e.png",
  fw = {
    props: ["msg"],
    name: "EmptyStateView"
  },
  hw = {
    class: "auth-main"
  },
  pw = {
    class: "card-body"
  },
  mw = n("div", {
    class: "text-center me-4"
  }, [n("a", {
    href: "#"
  }, [n("img", {
    src: uw,
    class: "w-25",
    alt: "img"
  })])], -1),
  gw = {
    class: "text-center text-secondary f-w-400 mb-0 f-16"
  };

function _w(e, t, s, o, i, r) {
  return _(), w("div", hw, [n("div", pw, [mw, n("h6", gw, L(s.msg), 1)])])
}
const bw = je(fw, [
  ["render", _w]
]);

function Hs(e) {
  if (e == null) return "$ -";
  const t = parseFloat(e);
  return isNaN(t) ? "$ 0.00" : `$ ${t.toFixed(2)}`
}
const Ym = {
    TOP_LEFT: "top-left",
    TOP_RIGHT: "top-right",
    TOP_CENTER: "top-center",
    BOTTOM_LEFT: "bottom-left",
    BOTTOM_RIGHT: "bottom-right",
    BOTTOM_CENTER: "bottom-center"
  },
  Ba = {
    LIGHT: "light",
    DARK: "dark",
    COLORED: "colored",
    AUTO: "auto"
  },
  Iu = {
    INFO: "info",
    SUCCESS: "success",
    WARNING: "warning",
    ERROR: "error",
    DEFAULT: "default"
  },
  Jm = {
    dangerouslyHTMLString: !1,
    multiple: !0,
    position: Ym.TOP_RIGHT,
    autoClose: 5e3,
    transition: "bounce",
    hideProgressBar: !1,
    pauseOnHover: !0,
    pauseOnFocusLoss: !0,
    closeOnClick: !0,
    className: "",
    bodyClassName: "",
    style: {},
    progressClassName: "",
    progressStyle: {},
    role: "alert",
    theme: "light"
  },
  vw = {
    rtl: !1,
    newestOnTop: !1,
    toastClassName: ""
  },
  yw = {
    ...Jm,
    ...vw
  };
({
  ...Jm,
  type: Iu.DEFAULT
});
var Da = (e => (e[e.COLLAPSE_DURATION = 300] = "COLLAPSE_DURATION", e[e.DEBOUNCE_DURATION = 50] = "DEBOUNCE_DURATION", e.CSS_NAMESPACE = "Toastify", e))(Da || {});
Ls({});
Ls({});
Ls({
  items: []
});
const ww = Ls({});
Ls({});

function $w(...e) {
  return Pi(...e)
}

function Cw(e = {}) {
  ww["".concat(Da.CSS_NAMESPACE, "-default-options")] = e
}
Ym.TOP_LEFT, Ba.AUTO, Iu.DEFAULT;
Iu.DEFAULT, Ba.AUTO;
Ba.AUTO, Ba.LIGHT;
const Zm = {
  install(e, t = {}) {
    kw(t)
  }
};
typeof window < "u" && (window.Vue3Toastify = Zm);

function kw(e = {}) {
  const t = $w(yw, e);
  Cw(t)
}
var Aw = !1;
/*!
 * pinia v2.1.7
 * (c) 2023 Eduardo San Martin Morote
 * @license MIT
 */
const xw = Symbol();
var bh;
(function (e) {
  e.direct = "direct", e.patchObject = "patch object", e.patchFunction = "patch function"
})(bh || (bh = {}));

function Sw() {
  const e = sp(!0),
    t = e.run(() => he({}));
  let s = [],
    o = [];
  const i = za({
    install(r) {
      i._a = r, r.provide(xw, i), r.config.globalProperties.$pinia = i, o.forEach(a => s.push(a)), o = []
    },
    use(r) {
      return !this._a && !Aw ? o.push(r) : s.push(r), this
    },
    _p: s,
    _a: null,
    _e: e,
    _s: new Map,
    state: t
  });
  return i
}
/*!
 * vue-router v4.3.0
 * (c) 2024 Eduardo San Martin Morote
 * @license MIT
 */
const go = typeof document < "u";

function Ew(e) {
  return e.__esModule || e[Symbol.toStringTag] === "Module"
}
const Qe = Object.assign;

function sd(e, t) {
  const s = {};
  for (const o in t) {
    const i = t[o];
    s[o] = ws(i) ? i.map(e) : e(i)
  }
  return s
}
const bi = () => {},
  ws = Array.isArray,
  Xm = /#/g,
  Pw = /&/g,
  Tw = /\//g,
  Lw = /=/g,
  Ow = /\?/g,
  Qm = /\+/g,
  Iw = /%5B/g,
  Bw = /%5D/g,
  e1 = /%5E/g,
  Dw = /%60/g,
  t1 = /%7B/g,
  Mw = /%7C/g,
  s1 = /%7D/g,
  Rw = /%20/g;

function Bu(e) {
  return encodeURI("" + e).replace(Mw, "|").replace(Iw, "[").replace(Bw, "]")
}

function Nw(e) {
  return Bu(e).replace(t1, "{").replace(s1, "}").replace(e1, "^")
}

function Vd(e) {
  return Bu(e).replace(Qm, "%2B").replace(Rw, "+").replace(Xm, "%23").replace(Pw, "%26").replace(Dw, "`").replace(t1, "{").replace(s1, "}").replace(e1, "^")
}

function Fw(e) {
  return Vd(e).replace(Lw, "%3D")
}

function Uw(e) {
  return Bu(e).replace(Xm, "%23").replace(Ow, "%3F")
}

function Vw(e) {
  return e == null ? "" : Uw(e).replace(Tw, "%2F")
}

function Bi(e) {
  try {
    return decodeURIComponent("" + e)
  } catch {}
  return "" + e
}
const jw = /\/$/,
  Hw = e => e.replace(jw, "");

function nd(e, t, s = "/") {
  let o, i = {},
    r = "",
    a = "";
  const l = t.indexOf("#");
  let d = t.indexOf("?");
  return l < d && l >= 0 && (d = -1), d > -1 && (o = t.slice(0, d), r = t.slice(d + 1, l > -1 ? l : t.length), i = e(r)), l > -1 && (o = o || t.slice(0, l), a = t.slice(l, t.length)), o = Kw(o ? ? t, s), {
    fullPath: o + (r && "?") + r + a,
    path: o,
    query: i,
    hash: Bi(a)
  }
}

function qw(e, t) {
  const s = t.query ? e(t.query) : "";
  return t.path + (s && "?") + s + (t.hash || "")
}

function vh(e, t) {
  return !t || !e.toLowerCase().startsWith(t.toLowerCase()) ? e : e.slice(t.length) || "/"
}

function Ww(e, t, s) {
  const o = t.matched.length - 1,
    i = s.matched.length - 1;
  return o > -1 && o === i && Po(t.matched[o], s.matched[i]) && n1(t.params, s.params) && e(t.query) === e(s.query) && t.hash === s.hash
}

function Po(e, t) {
  return (e.aliasOf || e) === (t.aliasOf || t)
}

function n1(e, t) {
  if (Object.keys(e).length !== Object.keys(t).length) return !1;
  for (const s in e)
    if (!zw(e[s], t[s])) return !1;
  return !0
}

function zw(e, t) {
  return ws(e) ? yh(e, t) : ws(t) ? yh(t, e) : e === t
}

function yh(e, t) {
  return ws(t) ? e.length === t.length && e.every((s, o) => s === t[o]) : e.length === 1 && e[0] === t
}

function Kw(e, t) {
  if (e.startsWith("/")) return e;
  if (!e) return t;
  const s = t.split("/"),
    o = e.split("/"),
    i = o[o.length - 1];
  (i === ".." || i === ".") && o.push("");
  let r = s.length - 1,
    a, l;
  for (a = 0; a < o.length; a++)
    if (l = o[a], l !== ".")
      if (l === "..") r > 1 && r--;
      else break;
  return s.slice(0, r).join("/") + "/" + o.slice(a).join("/")
}
var Di;
(function (e) {
  e.pop = "pop", e.push = "push"
})(Di || (Di = {}));
var vi;
(function (e) {
  e.back = "back", e.forward = "forward", e.unknown = ""
})(vi || (vi = {}));

function Gw(e) {
  if (!e)
    if (go) {
      const t = document.querySelector("base");
      e = t && t.getAttribute("href") || "/", e = e.replace(/^\w+:\/\/[^\/]+/, "")
    } else e = "/";
  return e[0] !== "/" && e[0] !== "#" && (e = "/" + e), Hw(e)
}
const Yw = /^[^#]+#/;

function Jw(e, t) {
  return e.replace(Yw, "#") + t
}

function Zw(e, t) {
  const s = document.documentElement.getBoundingClientRect(),
    o = e.getBoundingClientRect();
  return {
    behavior: t.behavior,
    left: o.left - s.left - (t.left || 0),
    top: o.top - s.top - (t.top || 0)
  }
}
const ll = () => ({
  left: window.scrollX,
  top: window.scrollY
});

function Xw(e) {
  let t;
  if ("el" in e) {
    const s = e.el,
      o = typeof s == "string" && s.startsWith("#"),
      i = typeof s == "string" ? o ? document.getElementById(s.slice(1)) : document.querySelector(s) : s;
    if (!i) return;
    t = Zw(i, e)
  } else t = e;
  "scrollBehavior" in document.documentElement.style ? window.scrollTo(t) : window.scrollTo(t.left != null ? t.left : window.scrollX, t.top != null ? t.top : window.scrollY)
}

function wh(e, t) {
  return (history.state ? history.state.position - t : -1) + e
}
const jd = new Map;

function Qw(e, t) {
  jd.set(e, t)
}

function e$(e) {
  const t = jd.get(e);
  return jd.delete(e), t
}
let t$ = () => location.protocol + "//" + location.host;

function o1(e, t) {
  const {
    pathname: s,
    search: o,
    hash: i
  } = t, r = e.indexOf("#");
  if (r > -1) {
    let l = i.includes(e.slice(r)) ? e.slice(r).length : 1,
      d = i.slice(l);
    return d[0] !== "/" && (d = "/" + d), vh(d, "")
  }
  return vh(s, e) + o + i
}

function s$(e, t, s, o) {
  let i = [],
    r = [],
    a = null;
  const l = ({
    state: $
  }) => {
    const C = o1(e, location),
      A = s.value,
      O = t.value;
    let V = 0;
    if ($) {
      if (s.value = C, t.value = $, a && a === A) {
        a = null;
        return
      }
      V = O ? $.position - O.position : 0
    } else o(C);
    i.forEach(M => {
      M(s.value, A, {
        delta: V,
        type: Di.pop,
        direction: V ? V > 0 ? vi.forward : vi.back : vi.unknown
      })
    })
  };

  function d() {
    a = s.value
  }

  function p($) {
    i.push($);
    const C = () => {
      const A = i.indexOf($);
      A > -1 && i.splice(A, 1)
    };
    return r.push(C), C
  }

  function m() {
    const {
      history: $
    } = window;
    $.state && $.replaceState(Qe({}, $.state, {
      scroll: ll()
    }), "")
  }

  function g() {
    for (const $ of r) $();
    r = [], window.removeEventListener("popstate", l), window.removeEventListener("beforeunload", m)
  }
  return window.addEventListener("popstate", l), window.addEventListener("beforeunload", m, {
    passive: !0
  }), {
    pauseListeners: d,
    listen: p,
    destroy: g
  }
}

function $h(e, t, s, o = !1, i = !1) {
  return {
    back: e,
    current: t,
    forward: s,
    replaced: o,
    position: window.history.length,
    scroll: i ? ll() : null
  }
}

function n$(e) {
  const {
    history: t,
    location: s
  } = window, o = {
    value: o1(e, s)
  }, i = {
    value: t.state
  };
  i.value || r(o.value, {
    back: null,
    current: o.value,
    forward: null,
    position: t.length - 1,
    replaced: !0,
    scroll: null
  }, !0);

  function r(d, p, m) {
    const g = e.indexOf("#"),
      $ = g > -1 ? (s.host && document.querySelector("base") ? e : e.slice(g)) + d : t$() + e + d;
    try {
      t[m ? "replaceState" : "pushState"](p, "", $), i.value = p
    } catch (C) {
      console.error(C), s[m ? "replace" : "assign"]($)
    }
  }

  function a(d, p) {
    const m = Qe({}, t.state, $h(i.value.back, d, i.value.forward, !0), p, {
      position: i.value.position
    });
    r(d, m, !0), o.value = d
  }

  function l(d, p) {
    const m = Qe({}, i.value, t.state, {
      forward: d,
      scroll: ll()
    });
    r(m.current, m, !0);
    const g = Qe({}, $h(o.value, d, null), {
      position: m.position + 1
    }, p);
    r(d, g, !1), o.value = d
  }
  return {
    location: o,
    state: i,
    push: l,
    replace: a
  }
}

function o$(e) {
  e = Gw(e);
  const t = n$(e),
    s = s$(e, t.state, t.location, t.replace);

  function o(r, a = !0) {
    a || s.pauseListeners(), history.go(r)
  }
  const i = Qe({
    location: "",
    base: e,
    go: o,
    createHref: Jw.bind(null, e)
  }, t, s);
  return Object.defineProperty(i, "location", {
    enumerable: !0,
    get: () => t.location.value
  }), Object.defineProperty(i, "state", {
    enumerable: !0,
    get: () => t.state.value
  }), i
}

function i$(e) {
  return typeof e == "string" || e && typeof e == "object"
}

function i1(e) {
  return typeof e == "string" || typeof e == "symbol"
}
const fn = {
    path: "/",
    name: void 0,
    params: {},
    query: {},
    hash: "",
    fullPath: "/",
    matched: [],
    meta: {},
    redirectedFrom: void 0
  },
  r1 = Symbol("");
var Ch;
(function (e) {
  e[e.aborted = 4] = "aborted", e[e.cancelled = 8] = "cancelled", e[e.duplicated = 16] = "duplicated"
})(Ch || (Ch = {}));

function To(e, t) {
  return Qe(new Error, {
    type: e,
    [r1]: !0
  }, t)
}

function Ns(e, t) {
  return e instanceof Error && r1 in e && (t == null || !!(e.type & t))
}
const kh = "[^/]+?",
  r$ = {
    sensitive: !1,
    strict: !1,
    start: !0,
    end: !0
  },
  a$ = /[.+*?^${}()[\]/\\]/g;

function l$(e, t) {
  const s = Qe({}, r$, t),
    o = [];
  let i = s.start ? "^" : "";
  const r = [];
  for (const p of e) {
    const m = p.length ? [] : [90];
    s.strict && !p.length && (i += "/");
    for (let g = 0; g < p.length; g++) {
      const $ = p[g];
      let C = 40 + (s.sensitive ? .25 : 0);
      if ($.type === 0) g || (i += "/"), i += $.value.replace(a$, "\\$&"), C += 40;
      else if ($.type === 1) {
        const {
          value: A,
          repeatable: O,
          optional: V,
          regexp: M
        } = $;
        r.push({
          name: A,
          repeatable: O,
          optional: V
        });
        const B = M || kh;
        if (B !== kh) {
          C += 10;
          try {
            new RegExp(`(${B})`)
          } catch (I) {
            throw new Error(`Invalid custom RegExp for param "${A}" (${B}): ` + I.message)
          }
        }
        let P = O ? `((?:${B})(?:/(?:${B}))*)` : `(${B})`;
        g || (P = V && p.length < 2 ? `(?:/${P})` : "/" + P), V && (P += "?"), i += P, C += 20, V && (C += -8), O && (C += -20), B === ".*" && (C += -50)
      }
      m.push(C)
    }
    o.push(m)
  }
  if (s.strict && s.end) {
    const p = o.length - 1;
    o[p][o[p].length - 1] += .7000000000000001
  }
  s.strict || (i += "/?"), s.end ? i += "$" : s.strict && (i += "(?:/|$)");
  const a = new RegExp(i, s.sensitive ? "" : "i");

  function l(p) {
    const m = p.match(a),
      g = {};
    if (!m) return null;
    for (let $ = 1; $ < m.length; $++) {
      const C = m[$] || "",
        A = r[$ - 1];
      g[A.name] = C && A.repeatable ? C.split("/") : C
    }
    return g
  }

  function d(p) {
    let m = "",
      g = !1;
    for (const $ of e) {
      (!g || !m.endsWith("/")) && (m += "/"), g = !1;
      for (const C of $)
        if (C.type === 0) m += C.value;
        else if (C.type === 1) {
        const {
          value: A,
          repeatable: O,
          optional: V
        } = C, M = A in p ? p[A] : "";
        if (ws(M) && !O) throw new Error(`Provided param "${A}" is an array but it is not repeatable (* or + modifiers)`);
        const B = ws(M) ? M.join("/") : M;
        if (!B)
          if (V) $.length < 2 && (m.endsWith("/") ? m = m.slice(0, -1) : g = !0);
          else throw new Error(`Missing required param "${A}"`);
        m += B
      }
    }
    return m || "/"
  }
  return {
    re: a,
    score: o,
    keys: r,
    parse: l,
    stringify: d
  }
}

function c$(e, t) {
  let s = 0;
  for (; s < e.length && s < t.length;) {
    const o = t[s] - e[s];
    if (o) return o;
    s++
  }
  return e.length < t.length ? e.length === 1 && e[0] === 80 ? -1 : 1 : e.length > t.length ? t.length === 1 && t[0] === 80 ? 1 : -1 : 0
}

function d$(e, t) {
  let s = 0;
  const o = e.score,
    i = t.score;
  for (; s < o.length && s < i.length;) {
    const r = c$(o[s], i[s]);
    if (r) return r;
    s++
  }
  if (Math.abs(i.length - o.length) === 1) {
    if (Ah(o)) return 1;
    if (Ah(i)) return -1
  }
  return i.length - o.length
}

function Ah(e) {
  const t = e[e.length - 1];
  return e.length > 0 && t[t.length - 1] < 0
}
const u$ = {
    type: 0,
    value: ""
  },
  f$ = /[a-zA-Z0-9_]/;

function h$(e) {
  if (!e) return [
    []
  ];
  if (e === "/") return [
    [u$]
  ];
  if (!e.startsWith("/")) throw new Error(`Invalid path "${e}"`);

  function t(C) {
    throw new Error(`ERR (${s})/"${p}": ${C}`)
  }
  let s = 0,
    o = s;
  const i = [];
  let r;

  function a() {
    r && i.push(r), r = []
  }
  let l = 0,
    d, p = "",
    m = "";

  function g() {
    p && (s === 0 ? r.push({
      type: 0,
      value: p
    }) : s === 1 || s === 2 || s === 3 ? (r.length > 1 && (d === "*" || d === "+") && t(`A repeatable param (${p}) must be alone in its segment. eg: '/:ids+.`), r.push({
      type: 1,
      value: p,
      regexp: m,
      repeatable: d === "*" || d === "+",
      optional: d === "*" || d === "?"
    })) : t("Invalid state to consume buffer"), p = "")
  }

  function $() {
    p += d
  }
  for (; l < e.length;) {
    if (d = e[l++], d === "\\" && s !== 2) {
      o = s, s = 4;
      continue
    }
    switch (s) {
      case 0:
        d === "/" ? (p && g(), a()) : d === ":" ? (g(), s = 1) : $();
        break;
      case 4:
        $(), s = o;
        break;
      case 1:
        d === "(" ? s = 2 : f$.test(d) ? $() : (g(), s = 0, d !== "*" && d !== "?" && d !== "+" && l--);
        break;
      case 2:
        d === ")" ? m[m.length - 1] == "\\" ? m = m.slice(0, -1) + d : s = 3 : m += d;
        break;
      case 3:
        g(), s = 0, d !== "*" && d !== "?" && d !== "+" && l--, m = "";
        break;
      default:
        t("Unknown state");
        break
    }
  }
  return s === 2 && t(`Unfinished custom RegExp for param "${p}"`), g(), a(), i
}

function p$(e, t, s) {
  const o = l$(h$(e.path), s),
    i = Qe(o, {
      record: e,
      parent: t,
      children: [],
      alias: []
    });
  return t && !i.record.aliasOf == !t.record.aliasOf && t.children.push(i), i
}

function m$(e, t) {
  const s = [],
    o = new Map;
  t = Eh({
    strict: !1,
    end: !0,
    sensitive: !1
  }, t);

  function i(m) {
    return o.get(m)
  }

  function r(m, g, $) {
    const C = !$,
      A = g$(m);
    A.aliasOf = $ && $.record;
    const O = Eh(t, m),
      V = [A];
    if ("alias" in m) {
      const P = typeof m.alias == "string" ? [m.alias] : m.alias;
      for (const I of P) V.push(Qe({}, A, {
        components: $ ? $.record.components : A.components,
        path: I,
        aliasOf: $ ? $.record : A
      }))
    }
    let M, B;
    for (const P of V) {
      const {
        path: I
      } = P;
      if (g && I[0] !== "/") {
        const R = g.record.path,
          F = R[R.length - 1] === "/" ? "" : "/";
        P.path = g.record.path + (I && F + I)
      }
      if (M = p$(P, g, O), $ ? $.alias.push(M) : (B = B || M, B !== M && B.alias.push(M), C && m.name && !Sh(M) && a(m.name)), A.children) {
        const R = A.children;
        for (let F = 0; F < R.length; F++) r(R[F], M, $ && $.children[F])
      }
      $ = $ || M, (M.record.components && Object.keys(M.record.components).length || M.record.name || M.record.redirect) && d(M)
    }
    return B ? () => {
      a(B)
    } : bi
  }

  function a(m) {
    if (i1(m)) {
      const g = o.get(m);
      g && (o.delete(m), s.splice(s.indexOf(g), 1), g.children.forEach(a), g.alias.forEach(a))
    } else {
      const g = s.indexOf(m);
      g > -1 && (s.splice(g, 1), m.record.name && o.delete(m.record.name), m.children.forEach(a), m.alias.forEach(a))
    }
  }

  function l() {
    return s
  }

  function d(m) {
    let g = 0;
    for (; g < s.length && d$(m, s[g]) >= 0 && (m.record.path !== s[g].record.path || !a1(m, s[g]));) g++;
    s.splice(g, 0, m), m.record.name && !Sh(m) && o.set(m.record.name, m)
  }

  function p(m, g) {
    let $, C = {},
      A, O;
    if ("name" in m && m.name) {
      if ($ = o.get(m.name), !$) throw To(1, {
        location: m
      });
      O = $.record.name, C = Qe(xh(g.params, $.keys.filter(B => !B.optional).concat($.parent ? $.parent.keys.filter(B => B.optional) : []).map(B => B.name)), m.params && xh(m.params, $.keys.map(B => B.name))), A = $.stringify(C)
    } else if (m.path != null) A = m.path, $ = s.find(B => B.re.test(A)), $ && (C = $.parse(A), O = $.record.name);
    else {
      if ($ = g.name ? o.get(g.name) : s.find(B => B.re.test(g.path)), !$) throw To(1, {
        location: m,
        currentLocation: g
      });
      O = $.record.name, C = Qe({}, g.params, m.params), A = $.stringify(C)
    }
    const V = [];
    let M = $;
    for (; M;) V.unshift(M.record), M = M.parent;
    return {
      name: O,
      path: A,
      params: C,
      matched: V,
      meta: b$(V)
    }
  }
  return e.forEach(m => r(m)), {
    addRoute: r,
    resolve: p,
    removeRoute: a,
    getRoutes: l,
    getRecordMatcher: i
  }
}

function xh(e, t) {
  const s = {};
  for (const o of t) o in e && (s[o] = e[o]);
  return s
}

function g$(e) {
  return {
    path: e.path,
    redirect: e.redirect,
    name: e.name,
    meta: e.meta || {},
    aliasOf: void 0,
    beforeEnter: e.beforeEnter,
    props: _$(e),
    children: e.children || [],
    instances: {},
    leaveGuards: new Set,
    updateGuards: new Set,
    enterCallbacks: {},
    components: "components" in e ? e.components || null : e.component && {
      default: e.component
    }
  }
}

function _$(e) {
  const t = {},
    s = e.props || !1;
  if ("component" in e) t.default = s;
  else
    for (const o in e.components) t[o] = typeof s == "object" ? s[o] : s;
  return t
}

function Sh(e) {
  for (; e;) {
    if (e.record.aliasOf) return !0;
    e = e.parent
  }
  return !1
}

function b$(e) {
  return e.reduce((t, s) => Qe(t, s.meta), {})
}

function Eh(e, t) {
  const s = {};
  for (const o in e) s[o] = o in t ? t[o] : e[o];
  return s
}

function a1(e, t) {
  return t.children.some(s => s === e || a1(e, s))
}

function v$(e) {
  const t = {};
  if (e === "" || e === "?") return t;
  const o = (e[0] === "?" ? e.slice(1) : e).split("&");
  for (let i = 0; i < o.length; ++i) {
    const r = o[i].replace(Qm, " "),
      a = r.indexOf("="),
      l = Bi(a < 0 ? r : r.slice(0, a)),
      d = a < 0 ? null : Bi(r.slice(a + 1));
    if (l in t) {
      let p = t[l];
      ws(p) || (p = t[l] = [p]), p.push(d)
    } else t[l] = d
  }
  return t
}

function Ph(e) {
  let t = "";
  for (let s in e) {
    const o = e[s];
    if (s = Fw(s), o == null) {
      o !== void 0 && (t += (t.length ? "&" : "") + s);
      continue
    }(ws(o) ? o.map(r => r && Vd(r)) : [o && Vd(o)]).forEach(r => {
      r !== void 0 && (t += (t.length ? "&" : "") + s, r != null && (t += "=" + r))
    })
  }
  return t
}

function y$(e) {
  const t = {};
  for (const s in e) {
    const o = e[s];
    o !== void 0 && (t[s] = ws(o) ? o.map(i => i == null ? null : "" + i) : o == null ? o : "" + o)
  }
  return t
}
const w$ = Symbol(""),
  Th = Symbol(""),
  cl = Symbol(""),
  Du = Symbol(""),
  Hd = Symbol("");

function li() {
  let e = [];

  function t(o) {
    return e.push(o), () => {
      const i = e.indexOf(o);
      i > -1 && e.splice(i, 1)
    }
  }

  function s() {
    e = []
  }
  return {
    add: t,
    list: () => e.slice(),
    reset: s
  }
}

function vn(e, t, s, o, i, r = a => a()) {
  const a = o && (o.enterCallbacks[i] = o.enterCallbacks[i] || []);
  return () => new Promise((l, d) => {
    const p = $ => {
        $ === !1 ? d(To(4, {
          from: s,
          to: t
        })) : $ instanceof Error ? d($) : i$($) ? d(To(2, {
          from: t,
          to: $
        })) : (a && o.enterCallbacks[i] === a && typeof $ == "function" && a.push($), l())
      },
      m = r(() => e.call(o && o.instances[i], t, s, p));
    let g = Promise.resolve(m);
    e.length < 3 && (g = g.then(p)), g.catch($ => d($))
  })
}

function od(e, t, s, o, i = r => r()) {
  const r = [];
  for (const a of e)
    for (const l in a.components) {
      let d = a.components[l];
      if (!(t !== "beforeRouteEnter" && !a.instances[l]))
        if ($$(d)) {
          const m = (d.__vccOpts || d)[t];
          m && r.push(vn(m, s, o, a, l, i))
        } else {
          let p = d();
          r.push(() => p.then(m => {
            if (!m) return Promise.reject(new Error(`Couldn't resolve component "${l}" at "${a.path}"`));
            const g = Ew(m) ? m.default : m;
            a.components[l] = g;
            const C = (g.__vccOpts || g)[t];
            return C && vn(C, s, o, a, l, i)()
          }))
        }
    }
  return r
}

function $$(e) {
  return typeof e == "object" || "displayName" in e || "props" in e || "__vccOpts" in e
}

function Lh(e) {
  const t = Ge(cl),
    s = Ge(Du),
    o = ss(() => t.resolve(Pt(e.to))),
    i = ss(() => {
      const {
        matched: d
      } = o.value, {
        length: p
      } = d, m = d[p - 1], g = s.matched;
      if (!m || !g.length) return -1;
      const $ = g.findIndex(Po.bind(null, m));
      if ($ > -1) return $;
      const C = Oh(d[p - 2]);
      return p > 1 && Oh(m) === C && g[g.length - 1].path !== C ? g.findIndex(Po.bind(null, d[p - 2])) : $
    }),
    r = ss(() => i.value > -1 && A$(s.params, o.value.params)),
    a = ss(() => i.value > -1 && i.value === s.matched.length - 1 && n1(s.params, o.value.params));

  function l(d = {}) {
    return k$(d) ? t[Pt(e.replace) ? "replace" : "push"](Pt(e.to)).catch(bi) : Promise.resolve()
  }
  return {
    route: o,
    href: ss(() => o.value.href),
    isActive: r,
    isExactActive: a,
    navigate: l
  }
}
const C$ = Dt({
    name: "RouterLink",
    compatConfig: {
      MODE: 3
    },
    props: {
      to: {
        type: [String, Object],
        required: !0
      },
      replace: Boolean,
      activeClass: String,
      exactActiveClass: String,
      custom: Boolean,
      ariaCurrentValue: {
        type: String,
        default: "page"
      }
    },
    useLink: Lh,
    setup(e, {
      slots: t
    }) {
      const s = Ls(Lh(e)),
        {
          options: o
        } = Ge(cl),
        i = ss(() => ({
          [Ih(e.activeClass, o.linkActiveClass, "router-link-active")]: s.isActive,
          [Ih(e.exactActiveClass, o.linkExactActiveClass, "router-link-exact-active")]: s.isExactActive
        }));
      return () => {
        const r = t.default && t.default(s);
        return e.custom ? r : qi("a", {
          "aria-current": s.isExactActive ? e.ariaCurrentValue : null,
          href: s.href,
          onClick: s.navigate,
          class: i.value
        }, r)
      }
    }
  }),
  Mu = C$;

function k$(e) {
  if (!(e.metaKey || e.altKey || e.ctrlKey || e.shiftKey) && !e.defaultPrevented && !(e.button !== void 0 && e.button !== 0)) {
    if (e.currentTarget && e.currentTarget.getAttribute) {
      const t = e.currentTarget.getAttribute("target");
      if (/\b_blank\b/i.test(t)) return
    }
    return e.preventDefault && e.preventDefault(), !0
  }
}

function A$(e, t) {
  for (const s in t) {
    const o = t[s],
      i = e[s];
    if (typeof o == "string") {
      if (o !== i) return !1
    } else if (!ws(i) || i.length !== o.length || o.some((r, a) => r !== i[a])) return !1
  }
  return !0
}

function Oh(e) {
  return e ? e.aliasOf ? e.aliasOf.path : e.path : ""
}
const Ih = (e, t, s) => e ? ? t ? ? s,
  x$ = Dt({
    name: "RouterView",
    inheritAttrs: !1,
    props: {
      name: {
        type: String,
        default: "default"
      },
      route: Object
    },
    compatConfig: {
      MODE: 3
    },
    setup(e, {
      attrs: t,
      slots: s
    }) {
      const o = Ge(Hd),
        i = ss(() => e.route || o.value),
        r = Ge(Th, 0),
        a = ss(() => {
          let p = Pt(r);
          const {
            matched: m
          } = i.value;
          let g;
          for (;
            (g = m[p]) && !g.components;) p++;
          return p
        }),
        l = ss(() => i.value.matched[a.value]);
      pi(Th, ss(() => a.value + 1)), pi(w$, l), pi(Hd, i);
      const d = he();
      return Ks(() => [d.value, l.value, e.name], ([p, m, g], [$, C, A]) => {
        m && (m.instances[g] = p, C && C !== m && p && p === $ && (m.leaveGuards.size || (m.leaveGuards = C.leaveGuards), m.updateGuards.size || (m.updateGuards = C.updateGuards))), p && m && (!C || !Po(m, C) || !$) && (m.enterCallbacks[g] || []).forEach(O => O(p))
      }, {
        flush: "post"
      }), () => {
        const p = i.value,
          m = e.name,
          g = l.value,
          $ = g && g.components[m];
        if (!$) return Bh(s.default, {
          Component: $,
          route: p
        });
        const C = g.props[m],
          A = C ? C === !0 ? p.params : typeof C == "function" ? C(p) : C : null,
          V = qi($, Qe({}, A, t, {
            onVnodeUnmounted: M => {
              M.component.isUnmounted && (g.instances[m] = null)
            },
            ref: d
          }));
        return Bh(s.default, {
          Component: V,
          route: p
        }) || V
      }
    }
  });

function Bh(e, t) {
  if (!e) return null;
  const s = e(t);
  return s.length === 1 ? s[0] : s
}
const l1 = x$;

function S$(e) {
  const t = m$(e.routes, e),
    s = e.parseQuery || v$,
    o = e.stringifyQuery || Ph,
    i = e.history,
    r = li(),
    a = li(),
    l = li(),
    d = vp(fn);
  let p = fn;
  go && e.scrollBehavior && "scrollRestoration" in history && (history.scrollRestoration = "manual");
  const m = sd.bind(null, K => "" + K),
    g = sd.bind(null, Vw),
    $ = sd.bind(null, Bi);

  function C(K, ae) {
    let H, ce;
    return i1(K) ? (H = t.getRecordMatcher(K), ce = ae) : ce = K, t.addRoute(ce, H)
  }

  function A(K) {
    const ae = t.getRecordMatcher(K);
    ae && t.removeRoute(ae)
  }

  function O() {
    return t.getRoutes().map(K => K.record)
  }

  function V(K) {
    return !!t.getRecordMatcher(K)
  }

  function M(K, ae) {
    if (ae = Qe({}, ae || d.value), typeof K == "string") {
      const x = nd(s, K, ae.path),
        N = t.resolve({
          path: x.path
        }, ae),
        Y = i.createHref(x.fullPath);
      return Qe(x, N, {
        params: $(N.params),
        hash: Bi(x.hash),
        redirectedFrom: void 0,
        href: Y
      })
    }
    let H;
    if (K.path != null) H = Qe({}, K, {
      path: nd(s, K.path, ae.path).path
    });
    else {
      const x = Qe({}, K.params);
      for (const N in x) x[N] == null && delete x[N];
      H = Qe({}, K, {
        params: g(x)
      }), ae.params = g(ae.params)
    }
    const ce = t.resolve(H, ae),
      De = K.hash || "";
    ce.params = m($(ce.params));
    const Ne = qw(o, Qe({}, K, {
        hash: Nw(De),
        path: ce.path
      })),
      k = i.createHref(Ne);
    return Qe({
      fullPath: Ne,
      hash: De,
      query: o === Ph ? y$(K.query) : K.query || {}
    }, ce, {
      redirectedFrom: void 0,
      href: k
    })
  }

  function B(K) {
    return typeof K == "string" ? nd(s, K, d.value.path) : Qe({}, K)
  }

  function P(K, ae) {
    if (p !== K) return To(8, {
      from: ae,
      to: K
    })
  }

  function I(K) {
    return j(K)
  }

  function R(K) {
    return I(Qe(B(K), {
      replace: !0
    }))
  }

  function F(K) {
    const ae = K.matched[K.matched.length - 1];
    if (ae && ae.redirect) {
      const {
        redirect: H
      } = ae;
      let ce = typeof H == "function" ? H(K) : H;
      return typeof ce == "string" && (ce = ce.includes("?") || ce.includes("#") ? ce = B(ce) : {
        path: ce
      }, ce.params = {}), Qe({
        query: K.query,
        hash: K.hash,
        params: ce.path != null ? {} : K.params
      }, ce)
    }
  }

  function j(K, ae) {
    const H = p = M(K),
      ce = d.value,
      De = K.state,
      Ne = K.force,
      k = K.replace === !0,
      x = F(H);
    if (x) return j(Qe(B(x), {
      state: typeof x == "object" ? Qe({}, De, x.state) : De,
      force: Ne,
      replace: k
    }), ae || H);
    const N = H;
    N.redirectedFrom = ae;
    let Y;
    return !Ne && Ww(o, ce, H) && (Y = To(16, {
      to: N,
      from: ce
    }), xt(ce, ce, !0, !1)), (Y ? Promise.resolve(Y) : ee(N, ce)).catch(W => Ns(W) ? Ns(W, 2) ? W : Lt(W) : te(W, N, ce)).then(W => {
      if (W) {
        if (Ns(W, 2)) return j(Qe({
          replace: k
        }, B(W.to), {
          state: typeof W.to == "object" ? Qe({}, De, W.to.state) : De,
          force: Ne
        }), ae || N)
      } else W = T(N, ce, !0, k, De);
      return ge(N, ce, W), W
    })
  }

  function q(K, ae) {
    const H = P(K, ae);
    return H ? Promise.reject(H) : Promise.resolve()
  }

  function ne(K) {
    const ae = pt.values().next().value;
    return ae && typeof ae.runWithContext == "function" ? ae.runWithContext(K) : K()
  }

  function ee(K, ae) {
    let H;
    const [ce, De, Ne] = E$(K, ae);
    H = od(ce.reverse(), "beforeRouteLeave", K, ae);
    for (const x of ce) x.leaveGuards.forEach(N => {
      H.push(vn(N, K, ae))
    });
    const k = q.bind(null, K, ae);
    return H.push(k), ot(H).then(() => {
      H = [];
      for (const x of r.list()) H.push(vn(x, K, ae));
      return H.push(k), ot(H)
    }).then(() => {
      H = od(De, "beforeRouteUpdate", K, ae);
      for (const x of De) x.updateGuards.forEach(N => {
        H.push(vn(N, K, ae))
      });
      return H.push(k), ot(H)
    }).then(() => {
      H = [];
      for (const x of Ne)
        if (x.beforeEnter)
          if (ws(x.beforeEnter))
            for (const N of x.beforeEnter) H.push(vn(N, K, ae));
          else H.push(vn(x.beforeEnter, K, ae));
      return H.push(k), ot(H)
    }).then(() => (K.matched.forEach(x => x.enterCallbacks = {}), H = od(Ne, "beforeRouteEnter", K, ae, ne), H.push(k), ot(H))).then(() => {
      H = [];
      for (const x of a.list()) H.push(vn(x, K, ae));
      return H.push(k), ot(H)
    }).catch(x => Ns(x, 8) ? x : Promise.reject(x))
  }

  function ge(K, ae, H) {
    l.list().forEach(ce => ne(() => ce(K, ae, H)))
  }

  function T(K, ae, H, ce, De) {
    const Ne = P(K, ae);
    if (Ne) return Ne;
    const k = ae === fn,
      x = go ? history.state : {};
    H && (ce || k ? i.replace(K.fullPath, Qe({
      scroll: k && x && x.scroll
    }, De)) : i.push(K.fullPath, De)), d.value = K, xt(K, ae, H, k), Lt()
  }
  let re;

  function Le() {
    re || (re = i.listen((K, ae, H) => {
      if (!lt.listening) return;
      const ce = M(K),
        De = F(ce);
      if (De) {
        j(Qe(De, {
          replace: !0
        }), ce).catch(bi);
        return
      }
      p = ce;
      const Ne = d.value;
      go && Qw(wh(Ne.fullPath, H.delta), ll()), ee(ce, Ne).catch(k => Ns(k, 12) ? k : Ns(k, 2) ? (j(k.to, ce).then(x => {
        Ns(x, 20) && !H.delta && H.type === Di.pop && i.go(-1, !1)
      }).catch(bi), Promise.reject()) : (H.delta && i.go(-H.delta, !1), te(k, ce, Ne))).then(k => {
        k = k || T(ce, Ne, !1), k && (H.delta && !Ns(k, 8) ? i.go(-H.delta, !1) : H.type === Di.pop && Ns(k, 20) && i.go(-1, !1)), ge(ce, Ne, k)
      }).catch(bi)
    }))
  }
  let We = li(),
    fe = li(),
    ue;

  function te(K, ae, H) {
    Lt(K);
    const ce = fe.list();
    return ce.length ? ce.forEach(De => De(K, ae, H)) : console.error(K), Promise.reject(K)
  }

  function $t() {
    return ue && d.value !== fn ? Promise.resolve() : new Promise((K, ae) => {
      We.add([K, ae])
    })
  }

  function Lt(K) {
    return ue || (ue = !K, Le(), We.list().forEach(([ae, H]) => K ? H(K) : ae()), We.reset()), K
  }

  function xt(K, ae, H, ce) {
    const {
      scrollBehavior: De
    } = e;
    if (!go || !De) return Promise.resolve();
    const Ne = !H && e$(wh(K.fullPath, 0)) || (ce || !H) && history.state && history.state.scroll || null;
    return Ui().then(() => De(K, ae, Ne)).then(k => k && Xw(k)).catch(k => te(k, K, ae))
  }
  const ht = K => i.go(K);
  let U;
  const pt = new Set,
    lt = {
      currentRoute: d,
      listening: !0,
      addRoute: C,
      removeRoute: A,
      hasRoute: V,
      getRoutes: O,
      resolve: M,
      options: e,
      push: I,
      replace: R,
      go: ht,
      back: () => ht(-1),
      forward: () => ht(1),
      beforeEach: r.add,
      beforeResolve: a.add,
      afterEach: l.add,
      onError: fe.add,
      isReady: $t,
      install(K) {
        const ae = this;
        K.component("RouterLink", Mu), K.component("RouterView", l1), K.config.globalProperties.$router = ae, Object.defineProperty(K.config.globalProperties, "$route", {
          enumerable: !0,
          get: () => Pt(d)
        }), go && !U && d.value === fn && (U = !0, I(i.location).catch(De => {}));
        const H = {};
        for (const De in fn) Object.defineProperty(H, De, {
          get: () => d.value[De],
          enumerable: !0
        });
        K.provide(cl, ae), K.provide(Du, lu(H)), K.provide(Hd, d);
        const ce = K.unmount;
        pt.add(K), K.unmount = function () {
          pt.delete(K), pt.size < 1 && (p = fn, re && re(), re = null, d.value = fn, U = !1, ue = !1), ce()
        }
      }
    };

  function ot(K) {
    return K.reduce((ae, H) => ae.then(() => ne(H)), Promise.resolve())
  }
  return lt
}

function E$(e, t) {
  const s = [],
    o = [],
    i = [],
    r = Math.max(t.matched.length, e.matched.length);
  for (let a = 0; a < r; a++) {
    const l = t.matched[a];
    l && (e.matched.find(p => Po(p, l)) ? o.push(l) : s.push(l));
    const d = e.matched[a];
    d && (t.matched.find(p => Po(p, d)) || i.push(d))
  }
  return [s, o, i]
}

function Wi() {
  return Ge(cl)
}

function P$() {
  return Ge(Du)
}
var c1 = {
  exports: {}
};
/*!
 * sweetalert2 v11.10.7
 * Released under the MIT License.
 */
(function (e, t) {
  (function (s, o) {
    e.exports = o()
  })(Us, function () {
    function s(v, c, h) {
      if (typeof v == "function" ? v === c : v.has(c)) return arguments.length < 3 ? c : h;
      throw new TypeError("Private element is not present on this object")
    }

    function o(v, c, h) {
      return c = V(c), P(v, l() ? Reflect.construct(c, h || [], V(v).constructor) : c.apply(v, h))
    }

    function i(v, c) {
      return v.get(s(v, c))
    }

    function r(v, c, h) {
      return v.set(s(v, c), h), h
    }

    function a(v, c, h) {
      if (l()) return Reflect.construct.apply(null, arguments);
      var b = [null];
      b.push.apply(b, c);
      var E = new(v.bind.apply(v, b));
      return h && M(E, h.prototype), E
    }

    function l() {
      try {
        var v = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {}))
      } catch {}
      return (l = function () {
        return !!v
      })()
    }

    function d(v, c) {
      var h = v == null ? null : typeof Symbol < "u" && v[Symbol.iterator] || v["@@iterator"];
      if (h != null) {
        var b, E, z, _e, Ve = [],
          qe = !0,
          ut = !1;
        try {
          if (z = (h = h.call(v)).next, c === 0) {
            if (Object(h) !== h) return;
            qe = !1
          } else
            for (; !(qe = (b = z.call(h)).done) && (Ve.push(b.value), Ve.length !== c); qe = !0);
        } catch (ii) {
          ut = !0, E = ii
        } finally {
          try {
            if (!qe && h.return != null && (_e = h.return(), Object(_e) !== _e)) return
          } finally {
            if (ut) throw E
          }
        }
        return Ve
      }
    }

    function p(v, c) {
      if (typeof v != "object" || !v) return v;
      var h = v[Symbol.toPrimitive];
      if (h !== void 0) {
        var b = h.call(v, c || "default");
        if (typeof b != "object") return b;
        throw new TypeError("@@toPrimitive must return a primitive value.")
      }
      return (c === "string" ? String : Number)(v)
    }

    function m(v) {
      var c = p(v, "string");
      return typeof c == "symbol" ? c : String(c)
    }

    function g(v) {
      "@babel/helpers - typeof";
      return g = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function (c) {
        return typeof c
      } : function (c) {
        return c && typeof Symbol == "function" && c.constructor === Symbol && c !== Symbol.prototype ? "symbol" : typeof c
      }, g(v)
    }

    function $(v, c) {
      if (!(v instanceof c)) throw new TypeError("Cannot call a class as a function")
    }

    function C(v, c) {
      for (var h = 0; h < c.length; h++) {
        var b = c[h];
        b.enumerable = b.enumerable || !1, b.configurable = !0, "value" in b && (b.writable = !0), Object.defineProperty(v, m(b.key), b)
      }
    }

    function A(v, c, h) {
      return c && C(v.prototype, c), h && C(v, h), Object.defineProperty(v, "prototype", {
        writable: !1
      }), v
    }

    function O(v, c) {
      if (typeof c != "function" && c !== null) throw new TypeError("Super expression must either be null or a function");
      v.prototype = Object.create(c && c.prototype, {
        constructor: {
          value: v,
          writable: !0,
          configurable: !0
        }
      }), Object.defineProperty(v, "prototype", {
        writable: !1
      }), c && M(v, c)
    }

    function V(v) {
      return V = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (h) {
        return h.__proto__ || Object.getPrototypeOf(h)
      }, V(v)
    }

    function M(v, c) {
      return M = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (b, E) {
        return b.__proto__ = E, b
      }, M(v, c)
    }

    function B(v) {
      if (v === void 0) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
      return v
    }

    function P(v, c) {
      if (c && (typeof c == "object" || typeof c == "function")) return c;
      if (c !== void 0) throw new TypeError("Derived constructors may only return object or undefined");
      return B(v)
    }

    function I(v, c) {
      for (; !Object.prototype.hasOwnProperty.call(v, c) && (v = V(v), v !== null););
      return v
    }

    function R() {
      return typeof Reflect < "u" && Reflect.get ? R = Reflect.get.bind() : R = function (c, h, b) {
        var E = I(c, h);
        if (E) {
          var z = Object.getOwnPropertyDescriptor(E, h);
          return z.get ? z.get.call(arguments.length < 3 ? c : b) : z.value
        }
      }, R.apply(this, arguments)
    }

    function F(v, c) {
      return ne(v) || d(v, c) || ge(v, c) || Le()
    }

    function j(v) {
      return q(v) || ee(v) || ge(v) || re()
    }

    function q(v) {
      if (Array.isArray(v)) return T(v)
    }

    function ne(v) {
      if (Array.isArray(v)) return v
    }

    function ee(v) {
      if (typeof Symbol < "u" && v[Symbol.iterator] != null || v["@@iterator"] != null) return Array.from(v)
    }

    function ge(v, c) {
      if (v) {
        if (typeof v == "string") return T(v, c);
        var h = Object.prototype.toString.call(v).slice(8, -1);
        if (h === "Object" && v.constructor && (h = v.constructor.name), h === "Map" || h === "Set") return Array.from(v);
        if (h === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(h)) return T(v, c)
      }
    }

    function T(v, c) {
      (c == null || c > v.length) && (c = v.length);
      for (var h = 0, b = new Array(c); h < c; h++) b[h] = v[h];
      return b
    }

    function re() {
      throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)
    }

    function Le() {
      throw new TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)
    }

    function We(v, c) {
      if (c.has(v)) throw new TypeError("Cannot initialize the same private elements twice on an object")
    }

    function fe(v, c, h) {
      We(v, c), c.set(v, h)
    }
    var ue = 100,
      te = {},
      $t = function () {
        te.previousActiveElement instanceof HTMLElement ? (te.previousActiveElement.focus(), te.previousActiveElement = null) : document.body && document.body.focus()
      },
      Lt = function (c) {
        return new Promise(function (h) {
          if (!c) return h();
          var b = window.scrollX,
            E = window.scrollY;
          te.restoreFocusTimeout = setTimeout(function () {
            $t(), h()
          }, ue), window.scrollTo(b, E)
        })
      },
      xt = "swal2-",
      ht = ["container", "shown", "height-auto", "iosfix", "popup", "modal", "no-backdrop", "no-transition", "toast", "toast-shown", "show", "hide", "close", "title", "html-container", "actions", "confirm", "deny", "cancel", "default-outline", "footer", "icon", "icon-content", "image", "input", "file", "range", "select", "radio", "checkbox", "label", "textarea", "inputerror", "input-label", "validation-message", "progress-steps", "active-progress-step", "progress-step", "progress-step-line", "loader", "loading", "styled", "top", "top-start", "top-end", "top-left", "top-right", "center", "center-start", "center-end", "center-left", "center-right", "bottom", "bottom-start", "bottom-end", "bottom-left", "bottom-right", "grow-row", "grow-column", "grow-fullscreen", "rtl", "timer-progress-bar", "timer-progress-bar-container", "scrollbar-measure", "icon-success", "icon-warning", "icon-info", "icon-question", "icon-error"],
      U = ht.reduce(function (v, c) {
        return v[c] = xt + c, v
      }, {}),
      pt = ["success", "warning", "info", "question", "error"],
      lt = pt.reduce(function (v, c) {
        return v[c] = xt + c, v
      }, {}),
      ot = "SweetAlert2:",
      K = function (c) {
        return c.charAt(0).toUpperCase() + c.slice(1)
      },
      ae = function (c) {
        console.warn("".concat(ot, " ").concat(g(c) === "object" ? c.join(" ") : c))
      },
      H = function (c) {
        console.error("".concat(ot, " ").concat(c))
      },
      ce = [],
      De = function (c) {
        ce.includes(c) || (ce.push(c), ae(c))
      },
      Ne = function (c, h) {
        De('"'.concat(c, '" is deprecated and will be removed in the next major release. Please use "').concat(h, '" instead.'))
      },
      k = function (c) {
        return typeof c == "function" ? c() : c
      },
      x = function (c) {
        return c && typeof c.toPromise == "function"
      },
      N = function (c) {
        return x(c) ? c.toPromise() : Promise.resolve(c)
      },
      Y = function (c) {
        return c && Promise.resolve(c) === c
      },
      W = function () {
        return document.body.querySelector(".".concat(U.container))
      },
      oe = function (c) {
        var h = W();
        return h ? h.querySelector(c) : null
      },
      se = function (c) {
        return oe(".".concat(c))
      },
      G = function () {
        return se(U.popup)
      },
      le = function () {
        return se(U.icon)
      },
      Q = function () {
        return se(U["icon-content"])
      },
      pe = function () {
        return se(U.title)
      },
      xe = function () {
        return se(U["html-container"])
      },
      Ce = function () {
        return se(U.image)
      },
      de = function () {
        return se(U["progress-steps"])
      },
      Pe = function () {
        return se(U["validation-message"])
      },
      Oe = function () {
        return oe(".".concat(U.actions, " .").concat(U.confirm))
      },
      He = function () {
        return oe(".".concat(U.actions, " .").concat(U.cancel))
      },
      Me = function () {
        return oe(".".concat(U.actions, " .").concat(U.deny))
      },
      Xe = function () {
        return se(U["input-label"])
      },
      Ot = function () {
        return oe(".".concat(U.loader))
      },
      Xt = function () {
        return se(U.actions)
      },
      mt = function () {
        return se(U.footer)
      },
      Bs = function () {
        return se(U["timer-progress-bar"])
      },
      _t = function () {
        return se(U.close)
      },
      Rt = `
  a[href],
  area[href],
  input:not([disabled]),
  select:not([disabled]),
  textarea:not([disabled]),
  button:not([disabled]),
  iframe,
  object,
  embed,
  [tabindex="0"],
  [contenteditable],
  audio[controls],
  video[controls],
  summary
`,
      Cs = function () {
        var c = G();
        if (!c) return [];
        var h = c.querySelectorAll('[tabindex]:not([tabindex="-1"]):not([tabindex="0"])'),
          b = Array.from(h).sort(function (_e, Ve) {
            var qe = parseInt(_e.getAttribute("tabindex") || "0"),
              ut = parseInt(Ve.getAttribute("tabindex") || "0");
            return qe > ut ? 1 : qe < ut ? -1 : 0
          }),
          E = c.querySelectorAll(Rt),
          z = Array.from(E).filter(function (_e) {
            return _e.getAttribute("tabindex") !== "-1"
          });
        return j(new Set(b.concat(z))).filter(function (_e) {
          return Nt(_e)
        })
      },
      Vo = function () {
        return ms(document.body, U.shown) && !ms(document.body, U["toast-shown"]) && !ms(document.body, U["no-backdrop"])
      },
      Sn = function () {
        var c = G();
        return c ? ms(c, U.toast) : !1
      },
      $l = function () {
        var c = G();
        return c ? c.hasAttribute("data-loading") : !1
      },
      Se = function (c, h) {
        if (c.textContent = "", h) {
          var b = new DOMParser,
            E = b.parseFromString(h, "text/html"),
            z = E.querySelector("head");
          z && Array.from(z.childNodes).forEach(function (Ve) {
            c.appendChild(Ve)
          });
          var _e = E.querySelector("body");
          _e && Array.from(_e.childNodes).forEach(function (Ve) {
            Ve instanceof HTMLVideoElement || Ve instanceof HTMLAudioElement ? c.appendChild(Ve.cloneNode(!0)) : c.appendChild(Ve)
          })
        }
      },
      ms = function (c, h) {
        if (!h) return !1;
        for (var b = h.split(/\s+/), E = 0; E < b.length; E++)
          if (!c.classList.contains(b[E])) return !1;
        return !0
      },
      Cl = function (c, h) {
        Array.from(c.classList).forEach(function (b) {
          !Object.values(U).includes(b) && !Object.values(lt).includes(b) && !Object.values(h.showClass || {}).includes(b) && c.classList.remove(b)
        })
      },
      qt = function (c, h, b) {
        if (Cl(c, h), h.customClass && h.customClass[b]) {
          if (typeof h.customClass[b] != "string" && !h.customClass[b].forEach) {
            ae("Invalid type of customClass.".concat(b, '! Expected string or iterable object, got "').concat(g(h.customClass[b]), '"'));
            return
          }
          ze(c, h.customClass[b])
        }
      },
      Qn = function (c, h) {
        if (!h) return null;
        switch (h) {
          case "select":
          case "textarea":
          case "file":
            return c.querySelector(".".concat(U.popup, " > .").concat(U[h]));
          case "checkbox":
            return c.querySelector(".".concat(U.popup, " > .").concat(U.checkbox, " input"));
          case "radio":
            return c.querySelector(".".concat(U.popup, " > .").concat(U.radio, " input:checked")) || c.querySelector(".".concat(U.popup, " > .").concat(U.radio, " input:first-child"));
          case "range":
            return c.querySelector(".".concat(U.popup, " > .").concat(U.range, " input"));
          default:
            return c.querySelector(".".concat(U.popup, " > .").concat(U.input))
        }
      },
      ks = function (c) {
        if (c.focus(), c.type !== "file") {
          var h = c.value;
          c.value = "", c.value = h
        }
      },
      Zi = function (c, h, b) {
        !c || !h || (typeof h == "string" && (h = h.split(/\s+/).filter(Boolean)), h.forEach(function (E) {
          Array.isArray(c) ? c.forEach(function (z) {
            b ? z.classList.add(E) : z.classList.remove(E)
          }) : b ? c.classList.add(E) : c.classList.remove(E)
        }))
      },
      ze = function (c, h) {
        Zi(c, h, !0)
      },
      as = function (c, h) {
        Zi(c, h, !1)
      },
      As = function (c, h) {
        for (var b = Array.from(c.children), E = 0; E < b.length; E++) {
          var z = b[E];
          if (z instanceof HTMLElement && ms(z, h)) return z
        }
      },
      Ds = function (c, h, b) {
        b === "".concat(parseInt(b)) && (b = parseInt(b)), b || parseInt(b) === 0 ? c.style.setProperty(h, typeof b == "number" ? "".concat(b, "px") : b) : c.style.removeProperty(h)
      },
      ct = function (c) {
        var h = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : "flex";
        c && (c.style.display = h)
      },
      Ct = function (c) {
        c && (c.style.display = "none")
      },
      jo = function (c) {
        var h = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : "block";
        c && new MutationObserver(function () {
          En(c, c.innerHTML, h)
        }).observe(c, {
          childList: !0,
          subtree: !0
        })
      },
      Xs = function (c, h, b, E) {
        var z = c.querySelector(h);
        z && z.style.setProperty(b, E)
      },
      En = function (c, h) {
        var b = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : "flex";
        h ? ct(c, b) : Ct(c)
      },
      Nt = function (c) {
        return !!(c && (c.offsetWidth || c.offsetHeight || c.getClientRects().length))
      },
      kl = function () {
        return !Nt(Oe()) && !Nt(Me()) && !Nt(He())
      },
      Xi = function (c) {
        return c.scrollHeight > c.clientHeight
      },
      eo = function (c) {
        var h = window.getComputedStyle(c),
          b = parseFloat(h.getPropertyValue("animation-duration") || "0"),
          E = parseFloat(h.getPropertyValue("transition-duration") || "0");
        return b > 0 || E > 0
      },
      Ho = function (c) {
        var h = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : !1,
          b = Bs();
        b && Nt(b) && (h && (b.style.transition = "none", b.style.width = "100%"), setTimeout(function () {
          b.style.transition = "width ".concat(c / 1e3, "s linear"), b.style.width = "0%"
        }, 10))
      },
      Al = function () {
        var c = Bs();
        if (c) {
          var h = parseInt(window.getComputedStyle(c).width);
          c.style.removeProperty("transition"), c.style.width = "100%";
          var b = parseInt(window.getComputedStyle(c).width),
            E = h / b * 100;
          c.style.width = "".concat(E, "%")
        }
      },
      Qi = function () {
        return typeof window > "u" || typeof document > "u"
      },
      xl = `
 <div aria-labelledby="`.concat(U.title, '" aria-describedby="').concat(U["html-container"], '" class="').concat(U.popup, `" tabindex="-1">
   <button type="button" class="`).concat(U.close, `"></button>
   <ul class="`).concat(U["progress-steps"], `"></ul>
   <div class="`).concat(U.icon, `"></div>
   <img class="`).concat(U.image, `" />
   <h2 class="`).concat(U.title, '" id="').concat(U.title, `"></h2>
   <div class="`).concat(U["html-container"], '" id="').concat(U["html-container"], `"></div>
   <input class="`).concat(U.input, '" id="').concat(U.input, `" />
   <input type="file" class="`).concat(U.file, `" />
   <div class="`).concat(U.range, `">
     <input type="range" />
     <output></output>
   </div>
   <select class="`).concat(U.select, '" id="').concat(U.select, `"></select>
   <div class="`).concat(U.radio, `"></div>
   <label class="`).concat(U.checkbox, `">
     <input type="checkbox" id="`).concat(U.checkbox, `" />
     <span class="`).concat(U.label, `"></span>
   </label>
   <textarea class="`).concat(U.textarea, '" id="').concat(U.textarea, `"></textarea>
   <div class="`).concat(U["validation-message"], '" id="').concat(U["validation-message"], `"></div>
   <div class="`).concat(U.actions, `">
     <div class="`).concat(U.loader, `"></div>
     <button type="button" class="`).concat(U.confirm, `"></button>
     <button type="button" class="`).concat(U.deny, `"></button>
     <button type="button" class="`).concat(U.cancel, `"></button>
   </div>
   <div class="`).concat(U.footer, `"></div>
   <div class="`).concat(U["timer-progress-bar-container"], `">
     <div class="`).concat(U["timer-progress-bar"], `"></div>
   </div>
 </div>
`).replace(/(^|\n)\s*/g, ""),
      Fe = function () {
        var c = W();
        return c ? (c.remove(), as([document.documentElement, document.body], [U["no-backdrop"], U["toast-shown"], U["has-column"]]), !0) : !1
      },
      Ms = function () {
        te.currentInstance.resetValidationMessage()
      },
      Sl = function () {
        var c = G(),
          h = As(c, U.input),
          b = As(c, U.file),
          E = c.querySelector(".".concat(U.range, " input")),
          z = c.querySelector(".".concat(U.range, " output")),
          _e = As(c, U.select),
          Ve = c.querySelector(".".concat(U.checkbox, " input")),
          qe = As(c, U.textarea);
        h.oninput = Ms, b.onchange = Ms, _e.onchange = Ms, Ve.onchange = Ms, qe.oninput = Ms, E.oninput = function () {
          Ms(), z.value = E.value
        }, E.onchange = function () {
          Ms(), z.value = E.value
        }
      },
      El = function (c) {
        return typeof c == "string" ? document.querySelector(c) : c
      },
      Pl = function (c) {
        var h = G();
        h.setAttribute("role", c.toast ? "alert" : "dialog"), h.setAttribute("aria-live", c.toast ? "polite" : "assertive"), c.toast || h.setAttribute("aria-modal", "true")
      },
      Tl = function (c) {
        window.getComputedStyle(c).direction === "rtl" && ze(W(), U.rtl)
      },
      Ll = function (c) {
        var h = Fe();
        if (Qi()) {
          H("SweetAlert2 requires document to initialize");
          return
        }
        var b = document.createElement("div");
        b.className = U.container, h && ze(b, U["no-transition"]), Se(b, xl);
        var E = El(c.target);
        E.appendChild(b), Pl(c), Tl(E), Sl()
      },
      Pn = function (c, h) {
        c instanceof HTMLElement ? h.appendChild(c) : g(c) === "object" ? Tn(c, h) : c && Se(h, c)
      },
      Tn = function (c, h) {
        c.jquery ? er(h, c) : Se(h, c.toString())
      },
      er = function (c, h) {
        if (c.textContent = "", 0 in h)
          for (var b = 0; b in h; b++) c.appendChild(h[b].cloneNode(!0));
        else c.appendChild(h.cloneNode(!0))
      },
      dt = function () {
        if (Qi()) return !1;
        var v = document.createElement("div");
        return typeof v.style.webkitAnimation < "u" ? "webkitAnimationEnd" : typeof v.style.animation < "u" ? "animationend" : !1
      }(),
      Ol = function (c, h) {
        var b = Xt(),
          E = Ot();
        !b || !E || (!h.showConfirmButton && !h.showDenyButton && !h.showCancelButton ? Ct(b) : ct(b), qt(b, h, "actions"), Il(b, E, h), Se(E, h.loaderHtml || ""), qt(E, h, "loader"))
      };

    function Il(v, c, h) {
      var b = Oe(),
        E = Me(),
        z = He();
      !b || !E || !z || (qo(b, "confirm", h), qo(E, "deny", h), qo(z, "cancel", h), Bl(b, E, z, h), h.reverseButtons && (h.toast ? (v.insertBefore(z, b), v.insertBefore(E, b)) : (v.insertBefore(z, c), v.insertBefore(E, c), v.insertBefore(b, c))))
    }

    function Bl(v, c, h, b) {
      if (!b.buttonsStyling) {
        as([v, c, h], U.styled);
        return
      }
      ze([v, c, h], U.styled), b.confirmButtonColor && (v.style.backgroundColor = b.confirmButtonColor, ze(v, U["default-outline"])), b.denyButtonColor && (c.style.backgroundColor = b.denyButtonColor, ze(c, U["default-outline"])), b.cancelButtonColor && (h.style.backgroundColor = b.cancelButtonColor, ze(h, U["default-outline"]))
    }

    function qo(v, c, h) {
      var b = K(c);
      En(v, h["show".concat(b, "Button")], "inline-block"), Se(v, h["".concat(c, "ButtonText")] || ""), v.setAttribute("aria-label", h["".concat(c, "ButtonAriaLabel")] || ""), v.className = U[c], qt(v, h, "".concat(c, "Button"))
    }
    var tr = function (c, h) {
        var b = _t();
        b && (Se(b, h.closeButtonHtml || ""), qt(b, h, "closeButton"), En(b, h.showCloseButton), b.setAttribute("aria-label", h.closeButtonAriaLabel || ""))
      },
      Dl = function (c, h) {
        var b = W();
        b && (Ml(b, h.backdrop), Rl(b, h.position), sr(b, h.grow), qt(b, h, "container"))
      };

    function Ml(v, c) {
      typeof c == "string" ? v.style.background = c : c || ze([document.documentElement, document.body], U["no-backdrop"])
    }

    function Rl(v, c) {
      c && (c in U ? ze(v, U[c]) : (ae('The "position" parameter is not valid, defaulting to "center"'), ze(v, U.center)))
    }

    function sr(v, c) {
      c && ze(v, U["grow-".concat(c)])
    }
    var tt = {
        innerParams: new WeakMap,
        domCache: new WeakMap
      },
      nr = ["input", "file", "range", "select", "radio", "checkbox", "textarea"],
      Nl = function (c, h) {
        var b = G();
        if (b) {
          var E = tt.innerParams.get(c),
            z = !E || h.input !== E.input;
          nr.forEach(function (_e) {
            var Ve = As(b, U[_e]);
            Ve && (Vl(_e, h.inputAttributes), Ve.className = U[_e], z && Ct(Ve))
          }), h.input && (z && Fl(h), jl(h))
        }
      },
      Fl = function (c) {
        if (c.input) {
          if (!at[c.input]) {
            H("Unexpected type of input! Expected ".concat(Object.keys(at).join(" | "), ', got "').concat(c.input, '"'));
            return
          }
          var h = zo(c.input),
            b = at[c.input](h, c);
          ct(h), c.inputAutoFocus && setTimeout(function () {
            ks(b)
          })
        }
      },
      Ul = function (c) {
        for (var h = 0; h < c.attributes.length; h++) {
          var b = c.attributes[h].name;
          ["id", "type", "value", "style"].includes(b) || c.removeAttribute(b)
        }
      },
      Vl = function (c, h) {
        var b = Qn(G(), c);
        if (b) {
          Ul(b);
          for (var E in h) b.setAttribute(E, h[E])
        }
      },
      jl = function (c) {
        var h = zo(c.input);
        g(c.customClass) === "object" && ze(h, c.customClass.input)
      },
      Wo = function (c, h) {
        (!c.placeholder || h.inputPlaceholder) && (c.placeholder = h.inputPlaceholder)
      },
      Ln = function (c, h, b) {
        if (b.inputLabel) {
          var E = document.createElement("label"),
            z = U["input-label"];
          E.setAttribute("for", c.id), E.className = z, g(b.customClass) === "object" && ze(E, b.customClass.inputLabel), E.innerText = b.inputLabel, h.insertAdjacentElement("beforebegin", E)
        }
      },
      zo = function (c) {
        return As(G(), U[c] || U.input)
      },
      gs = function (c, h) {
        ["string", "number"].includes(g(h)) ? c.value = "".concat(h) : Y(h) || ae('Unexpected type of inputValue! Expected "string", "number" or "Promise", got "'.concat(g(h), '"'))
      },
      at = {};
    at.text = at.email = at.password = at.number = at.tel = at.url = at.search = at.date = at["datetime-local"] = at.time = at.week = at.month = function (v, c) {
      return gs(v, c.inputValue), Ln(v, v, c), Wo(v, c), v.type = c.input, v
    }, at.file = function (v, c) {
      return Ln(v, v, c), Wo(v, c), v
    }, at.range = function (v, c) {
      var h = v.querySelector("input"),
        b = v.querySelector("output");
      return gs(h, c.inputValue), h.type = c.input, gs(b, c.inputValue), Ln(h, v, c), v
    }, at.select = function (v, c) {
      if (v.textContent = "", c.inputPlaceholder) {
        var h = document.createElement("option");
        Se(h, c.inputPlaceholder), h.value = "", h.disabled = !0, h.selected = !0, v.appendChild(h)
      }
      return Ln(v, v, c), v
    }, at.radio = function (v) {
      return v.textContent = "", v
    }, at.checkbox = function (v, c) {
      var h = Qn(G(), "checkbox");
      h.value = "1", h.checked = !!c.inputValue;
      var b = v.querySelector("span");
      return Se(b, c.inputPlaceholder), h
    }, at.textarea = function (v, c) {
      gs(v, c.inputValue), Wo(v, c), Ln(v, v, c);
      var h = function (E) {
        return parseInt(window.getComputedStyle(E).marginLeft) + parseInt(window.getComputedStyle(E).marginRight)
      };
      return setTimeout(function () {
        if ("MutationObserver" in window) {
          var b = parseInt(window.getComputedStyle(G()).width),
            E = function () {
              if (document.body.contains(v)) {
                var _e = v.offsetWidth + h(v);
                _e > b ? G().style.width = "".concat(_e, "px") : Ds(G(), "width", c.width)
              }
            };
          new MutationObserver(E).observe(v, {
            attributes: !0,
            attributeFilter: ["style"]
          })
        }
      }), v
    };
    var or = function (c, h) {
        var b = xe();
        b && (jo(b), qt(b, h, "htmlContainer"), h.html ? (Pn(h.html, b), ct(b, "block")) : h.text ? (b.textContent = h.text, ct(b, "block")) : Ct(b), Nl(c, h))
      },
      ir = function (c, h) {
        var b = mt();
        b && (jo(b), En(b, h.footer, "block"), h.footer && Pn(h.footer, b), qt(b, h, "footer"))
      },
      Hl = function (c, h) {
        var b = tt.innerParams.get(c),
          E = le();
        if (E) {
          if (b && h.icon === b.icon) {
            ar(E, h), rr(E, h);
            return
          }
          if (!h.icon && !h.iconHtml) {
            Ct(E);
            return
          }
          if (h.icon && Object.keys(lt).indexOf(h.icon) === -1) {
            H('Unknown icon! Expected "success", "error", "warning", "info" or "question", got "'.concat(h.icon, '"')), Ct(E);
            return
          }
          ct(E), ar(E, h), rr(E, h), ze(E, h.showClass && h.showClass.icon)
        }
      },
      rr = function (c, h) {
        for (var b = 0, E = Object.entries(lt); b < E.length; b++) {
          var z = F(E[b], 2),
            _e = z[0],
            Ve = z[1];
          h.icon !== _e && as(c, Ve)
        }
        ze(c, h.icon && lt[h.icon]), Kl(c, h), ql(), qt(c, h, "icon")
      },
      ql = function () {
        var c = G();
        if (c)
          for (var h = window.getComputedStyle(c).getPropertyValue("background-color"), b = c.querySelectorAll("[class^=swal2-success-circular-line], .swal2-success-fix"), E = 0; E < b.length; E++) b[E].style.backgroundColor = h
      },
      Wl = `
  <div class="swal2-success-circular-line-left"></div>
  <span class="swal2-success-line-tip"></span> <span class="swal2-success-line-long"></span>
  <div class="swal2-success-ring"></div> <div class="swal2-success-fix"></div>
  <div class="swal2-success-circular-line-right"></div>
`,
      zl = `
  <span class="swal2-x-mark">
    <span class="swal2-x-mark-line-left"></span>
    <span class="swal2-x-mark-line-right"></span>
  </span>
`,
      ar = function (c, h) {
        if (!(!h.icon && !h.iconHtml)) {
          var b = c.innerHTML,
            E = "";
          if (h.iconHtml) E = lr(h.iconHtml);
          else if (h.icon === "success") E = Wl, b = b.replace(/ style=".*?"/g, "");
          else if (h.icon === "error") E = zl;
          else if (h.icon) {
            var z = {
              question: "?",
              warning: "!",
              info: "i"
            };
            E = lr(z[h.icon])
          }
          b.trim() !== E.trim() && Se(c, E)
        }
      },
      Kl = function (c, h) {
        if (h.iconColor) {
          c.style.color = h.iconColor, c.style.borderColor = h.iconColor;
          for (var b = 0, E = [".swal2-success-line-tip", ".swal2-success-line-long", ".swal2-x-mark-line-left", ".swal2-x-mark-line-right"]; b < E.length; b++) {
            var z = E[b];
            Xs(c, z, "background-color", h.iconColor)
          }
          Xs(c, ".swal2-success-ring", "border-color", h.iconColor)
        }
      },
      lr = function (c) {
        return '<div class="'.concat(U["icon-content"], '">').concat(c, "</div>")
      },
      Rs = function (c, h) {
        var b = Ce();
        if (b) {
          if (!h.imageUrl) {
            Ct(b);
            return
          }
          ct(b, ""), b.setAttribute("src", h.imageUrl), b.setAttribute("alt", h.imageAlt || ""), Ds(b, "width", h.imageWidth), Ds(b, "height", h.imageHeight), b.className = U.image, qt(b, h, "image")
        }
      },
      cr = function (c, h) {
        var b = W(),
          E = G();
        if (!(!b || !E)) {
          if (h.toast) {
            Ds(b, "width", h.width), E.style.width = "100%";
            var z = Ot();
            z && E.insertBefore(z, le())
          } else Ds(E, "width", h.width);
          Ds(E, "padding", h.padding), h.color && (E.style.color = h.color), h.background && (E.style.background = h.background), Ct(Pe()), Gl(E, h)
        }
      },
      Gl = function (c, h) {
        var b = h.showClass || {};
        c.className = "".concat(U.popup, " ").concat(Nt(c) ? b.popup : ""), h.toast ? (ze([document.documentElement, document.body], U["toast-shown"]), ze(c, U.toast)) : ze(c, U.modal), qt(c, h, "popup"), typeof h.customClass == "string" && ze(c, h.customClass), h.icon && ze(c, U["icon-".concat(h.icon)])
      },
      Yl = function (c, h) {
        var b = de();
        if (b) {
          var E = h.progressSteps,
            z = h.currentProgressStep;
          if (!E || E.length === 0 || z === void 0) {
            Ct(b);
            return
          }
          ct(b), b.textContent = "", z >= E.length && ae("Invalid currentProgressStep parameter, it should be less than progressSteps.length (currentProgressStep like JS arrays starts from 0)"), E.forEach(function (_e, Ve) {
            var qe = Jl(_e);
            if (b.appendChild(qe), Ve === z && ze(qe, U["active-progress-step"]), Ve !== E.length - 1) {
              var ut = Zl(h);
              b.appendChild(ut)
            }
          })
        }
      },
      Jl = function (c) {
        var h = document.createElement("li");
        return ze(h, U["progress-step"]), Se(h, c), h
      },
      Zl = function (c) {
        var h = document.createElement("li");
        return ze(h, U["progress-step-line"]), c.progressStepsDistance && Ds(h, "width", c.progressStepsDistance), h
      },
      Xl = function (c, h) {
        var b = pe();
        b && (jo(b), En(b, h.title || h.titleText, "block"), h.title && Pn(h.title, b), h.titleText && (b.innerText = h.titleText), qt(b, h, "title"))
      },
      dr = function (c, h) {
        cr(c, h), Dl(c, h), Yl(c, h), Hl(c, h), Rs(c, h), Xl(c, h), tr(c, h), or(c, h), Ol(c, h), ir(c, h);
        var b = G();
        typeof h.didRender == "function" && b && h.didRender(b)
      },
      Ql = function () {
        return Nt(G())
      },
      ur = function () {
        var c;
        return (c = Oe()) === null || c === void 0 ? void 0 : c.click()
      },
      ec = function () {
        var c;
        return (c = Me()) === null || c === void 0 ? void 0 : c.click()
      },
      tc = function () {
        var c;
        return (c = He()) === null || c === void 0 ? void 0 : c.click()
      },
      Qs = Object.freeze({
        cancel: "cancel",
        backdrop: "backdrop",
        close: "close",
        esc: "esc",
        timer: "timer"
      }),
      fr = function (c) {
        c.keydownTarget && c.keydownHandlerAdded && (c.keydownTarget.removeEventListener("keydown", c.keydownHandler, {
          capture: c.keydownListenerCapture
        }), c.keydownHandlerAdded = !1)
      },
      sc = function (c, h, b) {
        fr(c), h.toast || (c.keydownHandler = function (E) {
          return nc(h, E, b)
        }, c.keydownTarget = h.keydownListenerCapture ? window : G(), c.keydownListenerCapture = h.keydownListenerCapture, c.keydownTarget.addEventListener("keydown", c.keydownHandler, {
          capture: c.keydownListenerCapture
        }), c.keydownHandlerAdded = !0)
      },
      to = function (c, h) {
        var b, E = Cs();
        if (E.length) {
          c = c + h, c === E.length ? c = 0 : c === -1 && (c = E.length - 1), E[c].focus();
          return
        }(b = G()) === null || b === void 0 || b.focus()
      },
      hr = ["ArrowRight", "ArrowDown"],
      pr = ["ArrowLeft", "ArrowUp"],
      nc = function (c, h, b) {
        c && (h.isComposing || h.keyCode === 229 || (c.stopKeydownPropagation && h.stopPropagation(), h.key === "Enter" ? oc(h, c) : h.key === "Tab" ? ic(h) : [].concat(hr, pr).includes(h.key) ? en(h.key) : h.key === "Escape" && rc(h, c, b)))
      },
      oc = function (c, h) {
        if (k(h.allowEnterKey)) {
          var b = Qn(G(), h.input);
          if (c.target && b && c.target instanceof HTMLElement && c.target.outerHTML === b.outerHTML) {
            if (["textarea", "file"].includes(h.input)) return;
            ur(), c.preventDefault()
          }
        }
      },
      ic = function (c) {
        for (var h = c.target, b = Cs(), E = -1, z = 0; z < b.length; z++)
          if (h === b[z]) {
            E = z;
            break
          } c.shiftKey ? to(E, -1) : to(E, 1), c.stopPropagation(), c.preventDefault()
      },
      en = function (c) {
        var h = Xt(),
          b = Oe(),
          E = Me(),
          z = He();
        if (!(!h || !b || !E || !z)) {
          var _e = [b, E, z];
          if (!(document.activeElement instanceof HTMLElement && !_e.includes(document.activeElement))) {
            var Ve = hr.includes(c) ? "nextElementSibling" : "previousElementSibling",
              qe = document.activeElement;
            if (qe) {
              for (var ut = 0; ut < h.children.length; ut++) {
                if (qe = qe[Ve], !qe) return;
                if (qe instanceof HTMLButtonElement && Nt(qe)) break
              }
              qe instanceof HTMLButtonElement && qe.focus()
            }
          }
        }
      },
      rc = function (c, h, b) {
        k(h.allowEscapeKey) && (c.preventDefault(), b(Qs.esc))
      },
      tn = {
        swalPromiseResolve: new WeakMap,
        swalPromiseReject: new WeakMap
      },
      ac = function () {
        var c = W(),
          h = Array.from(document.body.children);
        h.forEach(function (b) {
          b.contains(c) || (b.hasAttribute("aria-hidden") && b.setAttribute("data-previous-aria-hidden", b.getAttribute("aria-hidden") || ""), b.setAttribute("aria-hidden", "true"))
        })
      },
      mr = function () {
        var c = Array.from(document.body.children);
        c.forEach(function (h) {
          h.hasAttribute("data-previous-aria-hidden") ? (h.setAttribute("aria-hidden", h.getAttribute("data-previous-aria-hidden") || ""), h.removeAttribute("data-previous-aria-hidden")) : h.removeAttribute("aria-hidden")
        })
      },
      gr = typeof window < "u" && !!window.GestureEvent,
      lc = function () {
        if (gr && !ms(document.body, U.iosfix)) {
          var c = document.body.scrollTop;
          document.body.style.top = "".concat(c * -1, "px"), ze(document.body, U.iosfix), cc()
        }
      },
      cc = function () {
        var c = W();
        if (c) {
          var h;
          c.ontouchstart = function (b) {
            h = dc(b)
          }, c.ontouchmove = function (b) {
            h && (b.preventDefault(), b.stopPropagation())
          }
        }
      },
      dc = function (c) {
        var h = c.target,
          b = W(),
          E = xe();
        return !b || !E || uc(c) || Ko(c) ? !1 : h === b || !Xi(b) && h instanceof HTMLElement && h.tagName !== "INPUT" && h.tagName !== "TEXTAREA" && !(Xi(E) && E.contains(h))
      },
      uc = function (c) {
        return c.touches && c.touches.length && c.touches[0].touchType === "stylus"
      },
      Ko = function (c) {
        return c.touches && c.touches.length > 1
      },
      _r = function () {
        if (ms(document.body, U.iosfix)) {
          var c = parseInt(document.body.style.top, 10);
          as(document.body, U.iosfix), document.body.style.top = "", document.body.scrollTop = c * -1
        }
      },
      fc = function () {
        var c = document.createElement("div");
        c.className = U["scrollbar-measure"], document.body.appendChild(c);
        var h = c.getBoundingClientRect().width - c.clientWidth;
        return document.body.removeChild(c), h
      },
      sn = null,
      hc = function (c) {
        sn === null && (document.body.scrollHeight > window.innerHeight || c === "scroll") && (sn = parseInt(window.getComputedStyle(document.body).getPropertyValue("padding-right")), document.body.style.paddingRight = "".concat(sn + fc(), "px"))
      },
      br = function () {
        sn !== null && (document.body.style.paddingRight = "".concat(sn, "px"), sn = null)
      };

    function vr(v, c, h, b) {
      Sn() ? wr(v, b) : (Lt(h).then(function () {
        return wr(v, b)
      }), fr(te)), gr ? (c.setAttribute("style", "display:none !important"), c.removeAttribute("class"), c.innerHTML = "") : c.remove(), Vo() && (br(), _r(), mr()), Go()
    }

    function Go() {
      as([document.documentElement, document.body], [U.shown, U["height-auto"], U["no-backdrop"], U["toast-shown"]])
    }

    function _s(v) {
      v = pc(v);
      var c = tn.swalPromiseResolve.get(this),
        h = yr(this);
      this.isAwaitingPromise ? v.isDismissed || (On(this), c(v)) : h && c(v)
    }
    var yr = function (c) {
      var h = G();
      if (!h) return !1;
      var b = tt.innerParams.get(c);
      if (!b || ms(h, b.hideClass.popup)) return !1;
      as(h, b.showClass.popup), ze(h, b.hideClass.popup);
      var E = W();
      return as(E, b.showClass.backdrop), ze(E, b.hideClass.backdrop), mc(c, h, b), !0
    };

    function so(v) {
      var c = tn.swalPromiseReject.get(this);
      On(this), c && c(v)
    }
    var On = function (c) {
        c.isAwaitingPromise && (delete c.isAwaitingPromise, tt.innerParams.get(c) || c._destroy())
      },
      pc = function (c) {
        return typeof c > "u" ? {
          isConfirmed: !1,
          isDenied: !1,
          isDismissed: !0
        } : Object.assign({
          isConfirmed: !1,
          isDenied: !1,
          isDismissed: !1
        }, c)
      },
      mc = function (c, h, b) {
        var E = W(),
          z = dt && eo(h);
        typeof b.willClose == "function" && b.willClose(h), z ? no(c, h, E, b.returnFocus, b.didClose) : vr(c, E, b.returnFocus, b.didClose)
      },
      no = function (c, h, b, E, z) {
        dt && (te.swalCloseEventFinishedCallback = vr.bind(null, c, b, E, z), h.addEventListener(dt, function (_e) {
          _e.target === h && (te.swalCloseEventFinishedCallback(), delete te.swalCloseEventFinishedCallback)
        }))
      },
      wr = function (c, h) {
        setTimeout(function () {
          typeof h == "function" && h.bind(c.params)(), c._destroy && c._destroy()
        })
      },
      nn = function (c) {
        var h = G();
        if (h || new Gr, h = G(), !!h) {
          var b = Ot();
          Sn() ? Ct(le()) : gc(h, c), ct(b), h.setAttribute("data-loading", "true"), h.setAttribute("aria-busy", "true"), h.focus()
        }
      },
      gc = function (c, h) {
        var b = Xt(),
          E = Ot();
        !b || !E || (!h && Nt(Oe()) && (h = Oe()), ct(b), h && (Ct(h), E.setAttribute("data-button-to-replace", h.className), b.insertBefore(E, h)), ze([c, b], U.loading))
      },
      _c = function (c, h) {
        h.input === "select" || h.input === "radio" ? Yo(c, h) : ["text", "email", "number", "tel", "textarea"].some(function (b) {
          return b === h.input
        }) && (x(h.inputValue) || Y(h.inputValue)) && (nn(Oe()), Cr(c, h))
      },
      $r = function (c, h) {
        var b = c.getInput();
        if (!b) return null;
        switch (h.input) {
          case "checkbox":
            return bc(b);
          case "radio":
            return vc(b);
          case "file":
            return yc(b);
          default:
            return h.inputAutoTrim ? b.value.trim() : b.value
        }
      },
      bc = function (c) {
        return c.checked ? 1 : 0
      },
      vc = function (c) {
        return c.checked ? c.value : null
      },
      yc = function (c) {
        return c.files && c.files.length ? c.getAttribute("multiple") !== null ? c.files : c.files[0] : null
      },
      Yo = function (c, h) {
        var b = G();
        if (b) {
          var E = function (_e) {
            h.input === "select" ? wc(b, kr(_e), h) : h.input === "radio" && $c(b, kr(_e), h)
          };
          x(h.inputOptions) || Y(h.inputOptions) ? (nn(Oe()), N(h.inputOptions).then(function (z) {
            c.hideLoading(), E(z)
          })) : g(h.inputOptions) === "object" ? E(h.inputOptions) : H("Unexpected type of inputOptions! Expected object, Map or Promise, got ".concat(g(h.inputOptions)))
        }
      },
      Cr = function (c, h) {
        var b = c.getInput();
        b && (Ct(b), N(h.inputValue).then(function (E) {
          b.value = h.input === "number" ? "".concat(parseFloat(E) || 0) : "".concat(E), ct(b), b.focus(), c.hideLoading()
        }).catch(function (E) {
          H("Error in inputValue promise: ".concat(E)), b.value = "", ct(b), b.focus(), c.hideLoading()
        }))
      };

    function wc(v, c, h) {
      var b = As(v, U.select);
      if (b) {
        var E = function (_e, Ve, qe) {
          var ut = document.createElement("option");
          ut.value = qe, Se(ut, Ve), ut.selected = Ar(qe, h.inputValue), _e.appendChild(ut)
        };
        c.forEach(function (z) {
          var _e = z[0],
            Ve = z[1];
          if (Array.isArray(Ve)) {
            var qe = document.createElement("optgroup");
            qe.label = _e, qe.disabled = !1, b.appendChild(qe), Ve.forEach(function (ut) {
              return E(qe, ut[1], ut[0])
            })
          } else E(b, Ve, _e)
        }), b.focus()
      }
    }

    function $c(v, c, h) {
      var b = As(v, U.radio);
      if (b) {
        c.forEach(function (z) {
          var _e = z[0],
            Ve = z[1],
            qe = document.createElement("input"),
            ut = document.createElement("label");
          qe.type = "radio", qe.name = U.radio, qe.value = _e, Ar(_e, h.inputValue) && (qe.checked = !0);
          var ii = document.createElement("span");
          Se(ii, Ve), ii.className = U.label, ut.appendChild(qe), ut.appendChild(ii), b.appendChild(ut)
        });
        var E = b.querySelectorAll("input");
        E.length && E[0].focus()
      }
    }
    var kr = function v(c) {
        var h = [];
        return c instanceof Map ? c.forEach(function (b, E) {
          var z = b;
          g(z) === "object" && (z = v(z)), h.push([E, z])
        }) : Object.keys(c).forEach(function (b) {
          var E = c[b];
          g(E) === "object" && (E = v(E)), h.push([b, E])
        }), h
      },
      Ar = function (c, h) {
        return !!h && h.toString() === c.toString()
      },
      oo = void 0,
      Cc = function (c) {
        var h = tt.innerParams.get(c);
        c.disableButtons(), h.input ? Sr(c, "confirm") : io(c, !0)
      },
      kc = function (c) {
        var h = tt.innerParams.get(c);
        c.disableButtons(), h.returnInputValueOnDeny ? Sr(c, "deny") : Jo(c, !1)
      },
      xr = function (c, h) {
        c.disableButtons(), h(Qs.cancel)
      },
      Sr = function (c, h) {
        var b = tt.innerParams.get(c);
        if (!b.input) {
          H('The "input" parameter is needed to be set when using returnInputValueOn'.concat(K(h)));
          return
        }
        var E = c.getInput(),
          z = $r(c, b);
        b.inputValidator ? Ac(c, z, h) : E && !E.checkValidity() ? (c.enableButtons(), c.showValidationMessage(b.validationMessage || E.validationMessage)) : h === "deny" ? Jo(c, z) : io(c, z)
      },
      Ac = function (c, h, b) {
        var E = tt.innerParams.get(c);
        c.disableInput();
        var z = Promise.resolve().then(function () {
          return N(E.inputValidator(h, E.validationMessage))
        });
        z.then(function (_e) {
          c.enableButtons(), c.enableInput(), _e ? c.showValidationMessage(_e) : b === "deny" ? Jo(c, h) : io(c, h)
        })
      },
      Jo = function (c, h) {
        var b = tt.innerParams.get(c || oo);
        if (b.showLoaderOnDeny && nn(Me()), b.preDeny) {
          c.isAwaitingPromise = !0;
          var E = Promise.resolve().then(function () {
            return N(b.preDeny(h, b.validationMessage))
          });
          E.then(function (z) {
            z === !1 ? (c.hideLoading(), On(c)) : c.close({
              isDenied: !0,
              value: typeof z > "u" ? h : z
            })
          }).catch(function (z) {
            return Zo(c || oo, z)
          })
        } else c.close({
          isDenied: !0,
          value: h
        })
      },
      Er = function (c, h) {
        c.close({
          isConfirmed: !0,
          value: h
        })
      },
      Zo = function (c, h) {
        c.rejectPromise(h)
      },
      io = function (c, h) {
        var b = tt.innerParams.get(c || oo);
        if (b.showLoaderOnConfirm && nn(), b.preConfirm) {
          c.resetValidationMessage(), c.isAwaitingPromise = !0;
          var E = Promise.resolve().then(function () {
            return N(b.preConfirm(h, b.validationMessage))
          });
          E.then(function (z) {
            Nt(Pe()) || z === !1 ? (c.hideLoading(), On(c)) : Er(c, typeof z > "u" ? h : z)
          }).catch(function (z) {
            return Zo(c || oo, z)
          })
        } else Er(c, h)
      };

    function ro() {
      var v = tt.innerParams.get(this);
      if (v) {
        var c = tt.domCache.get(this);
        Ct(c.loader), Sn() ? v.icon && ct(le()) : xc(c), as([c.popup, c.actions], U.loading), c.popup.removeAttribute("aria-busy"), c.popup.removeAttribute("data-loading"), c.confirmButton.disabled = !1, c.denyButton.disabled = !1, c.cancelButton.disabled = !1
      }
    }
    var xc = function (c) {
      var h = c.popup.getElementsByClassName(c.loader.getAttribute("data-button-to-replace"));
      h.length ? ct(h[0], "inline-block") : kl() && Ct(c.actions)
    };

    function Pr() {
      var v = tt.innerParams.get(this),
        c = tt.domCache.get(this);
      return c ? Qn(c.popup, v.input) : null
    }

    function Xo(v, c, h) {
      var b = tt.domCache.get(v);
      c.forEach(function (E) {
        b[E].disabled = h
      })
    }

    function ao(v, c) {
      var h = G();
      if (!(!h || !v))
        if (v.type === "radio")
          for (var b = h.querySelectorAll('[name="'.concat(U.radio, '"]')), E = 0; E < b.length; E++) b[E].disabled = c;
        else v.disabled = c
    }

    function Tr() {
      Xo(this, ["confirmButton", "denyButton", "cancelButton"], !1)
    }

    function Lr() {
      Xo(this, ["confirmButton", "denyButton", "cancelButton"], !0)
    }

    function Or() {
      ao(this.getInput(), !1)
    }

    function Qo() {
      ao(this.getInput(), !0)
    }

    function Ir(v) {
      var c = tt.domCache.get(this),
        h = tt.innerParams.get(this);
      Se(c.validationMessage, v), c.validationMessage.className = U["validation-message"], h.customClass && h.customClass.validationMessage && ze(c.validationMessage, h.customClass.validationMessage), ct(c.validationMessage);
      var b = this.getInput();
      b && (b.setAttribute("aria-invalid", "true"), b.setAttribute("aria-describedby", U["validation-message"]), ks(b), ze(b, U.inputerror))
    }

    function Br() {
      var v = tt.domCache.get(this);
      v.validationMessage && Ct(v.validationMessage);
      var c = this.getInput();
      c && (c.removeAttribute("aria-invalid"), c.removeAttribute("aria-describedby"), as(c, U.inputerror))
    }
    var ls = {
        title: "",
        titleText: "",
        text: "",
        html: "",
        footer: "",
        icon: void 0,
        iconColor: void 0,
        iconHtml: void 0,
        template: void 0,
        toast: !1,
        animation: !0,
        showClass: {
          popup: "swal2-show",
          backdrop: "swal2-backdrop-show",
          icon: "swal2-icon-show"
        },
        hideClass: {
          popup: "swal2-hide",
          backdrop: "swal2-backdrop-hide",
          icon: "swal2-icon-hide"
        },
        customClass: {},
        target: "body",
        color: void 0,
        backdrop: !0,
        heightAuto: !0,
        allowOutsideClick: !0,
        allowEscapeKey: !0,
        allowEnterKey: !0,
        stopKeydownPropagation: !0,
        keydownListenerCapture: !1,
        showConfirmButton: !0,
        showDenyButton: !1,
        showCancelButton: !1,
        preConfirm: void 0,
        preDeny: void 0,
        confirmButtonText: "OK",
        confirmButtonAriaLabel: "",
        confirmButtonColor: void 0,
        denyButtonText: "No",
        denyButtonAriaLabel: "",
        denyButtonColor: void 0,
        cancelButtonText: "Cancel",
        cancelButtonAriaLabel: "",
        cancelButtonColor: void 0,
        buttonsStyling: !0,
        reverseButtons: !1,
        focusConfirm: !0,
        focusDeny: !1,
        focusCancel: !1,
        returnFocus: !0,
        showCloseButton: !1,
        closeButtonHtml: "&times;",
        closeButtonAriaLabel: "Close this dialog",
        loaderHtml: "",
        showLoaderOnConfirm: !1,
        showLoaderOnDeny: !1,
        imageUrl: void 0,
        imageWidth: void 0,
        imageHeight: void 0,
        imageAlt: "",
        timer: void 0,
        timerProgressBar: !1,
        width: void 0,
        padding: void 0,
        background: void 0,
        input: void 0,
        inputPlaceholder: "",
        inputLabel: "",
        inputValue: "",
        inputOptions: {},
        inputAutoFocus: !0,
        inputAutoTrim: !0,
        inputAttributes: {},
        inputValidator: void 0,
        returnInputValueOnDeny: !1,
        validationMessage: void 0,
        grow: !1,
        position: "center",
        progressSteps: [],
        currentProgressStep: void 0,
        progressStepsDistance: void 0,
        willOpen: void 0,
        didOpen: void 0,
        didRender: void 0,
        willClose: void 0,
        didClose: void 0,
        didDestroy: void 0,
        scrollbarPadding: !0
      },
      Dr = ["allowEscapeKey", "allowOutsideClick", "background", "buttonsStyling", "cancelButtonAriaLabel", "cancelButtonColor", "cancelButtonText", "closeButtonAriaLabel", "closeButtonHtml", "color", "confirmButtonAriaLabel", "confirmButtonColor", "confirmButtonText", "currentProgressStep", "customClass", "denyButtonAriaLabel", "denyButtonColor", "denyButtonText", "didClose", "didDestroy", "footer", "hideClass", "html", "icon", "iconColor", "iconHtml", "imageAlt", "imageHeight", "imageUrl", "imageWidth", "preConfirm", "preDeny", "progressSteps", "returnFocus", "reverseButtons", "showCancelButton", "showCloseButton", "showConfirmButton", "showDenyButton", "text", "title", "titleText", "willClose"],
      Sc = {},
      lo = ["allowOutsideClick", "allowEnterKey", "backdrop", "focusConfirm", "focusDeny", "focusCancel", "returnFocus", "heightAuto", "keydownListenerCapture"],
      Mr = function (c) {
        return Object.prototype.hasOwnProperty.call(ls, c)
      },
      Rr = function (c) {
        return Dr.indexOf(c) !== -1
      },
      Nr = function (c) {
        return Sc[c]
      },
      Fr = function (c) {
        Mr(c) || ae('Unknown parameter "'.concat(c, '"'))
      },
      Ec = function (c) {
        lo.includes(c) && ae('The parameter "'.concat(c, '" is incompatible with toasts'))
      },
      Pc = function (c) {
        var h = Nr(c);
        h && Ne(c, h)
      },
      Tc = function (c) {
        c.backdrop === !1 && c.allowOutsideClick && ae('"allowOutsideClick" parameter requires `backdrop` parameter to be set to `true`');
        for (var h in c) Fr(h), c.toast && Ec(h), Pc(h)
      };

    function ei(v) {
      var c = G(),
        h = tt.innerParams.get(this);
      if (!c || ms(c, h.hideClass.popup)) {
        ae("You're trying to update the closed or closing popup, that won't work. Use the update() method in preConfirm parameter or show a new popup.");
        return
      }
      var b = Ur(v),
        E = Object.assign({}, h, b);
      dr(this, E), tt.innerParams.set(this, E), Object.defineProperties(this, {
        params: {
          value: Object.assign({}, this.params, v),
          writable: !1,
          enumerable: !0
        }
      })
    }
    var Ur = function (c) {
      var h = {};
      return Object.keys(c).forEach(function (b) {
        Rr(b) ? h[b] = c[b] : ae("Invalid parameter to update: ".concat(b))
      }), h
    };

    function ti() {
      var v = tt.domCache.get(this),
        c = tt.innerParams.get(this);
      if (!c) {
        Vr(this);
        return
      }
      v.popup && te.swalCloseEventFinishedCallback && (te.swalCloseEventFinishedCallback(), delete te.swalCloseEventFinishedCallback), typeof c.didDestroy == "function" && c.didDestroy(), Lc(this)
    }
    var Lc = function (c) {
        Vr(c), delete c.params, delete te.keydownHandler, delete te.keydownTarget, delete te.currentInstance
      },
      Vr = function (c) {
        c.isAwaitingPromise ? (si(tt, c), c.isAwaitingPromise = !0) : (si(tn, c), si(tt, c), delete c.isAwaitingPromise, delete c.disableButtons, delete c.enableButtons, delete c.getInput, delete c.disableInput, delete c.enableInput, delete c.hideLoading, delete c.disableLoading, delete c.showValidationMessage, delete c.resetValidationMessage, delete c.close, delete c.closePopup, delete c.closeModal, delete c.closeToast, delete c.rejectPromise, delete c.update, delete c._destroy)
      },
      si = function (c, h) {
        for (var b in c) c[b].delete(h)
      },
      Oc = Object.freeze({
        __proto__: null,
        _destroy: ti,
        close: _s,
        closeModal: _s,
        closePopup: _s,
        closeToast: _s,
        disableButtons: Lr,
        disableInput: Qo,
        disableLoading: ro,
        enableButtons: Tr,
        enableInput: Or,
        getInput: Pr,
        handleAwaitingPromise: On,
        hideLoading: ro,
        rejectPromise: so,
        resetValidationMessage: Br,
        showValidationMessage: Ir,
        update: ei
      }),
      Ic = function (c, h, b) {
        c.toast ? Bc(c, h, b) : (Mc(h), Rc(h), Nc(c, h, b))
      },
      Bc = function (c, h, b) {
        h.popup.onclick = function () {
          c && (Dc(c) || c.timer || c.input) || b(Qs.close)
        }
      },
      Dc = function (c) {
        return !!(c.showConfirmButton || c.showDenyButton || c.showCancelButton || c.showCloseButton)
      },
      co = !1,
      Mc = function (c) {
        c.popup.onmousedown = function () {
          c.container.onmouseup = function (h) {
            c.container.onmouseup = function () {}, h.target === c.container && (co = !0)
          }
        }
      },
      Rc = function (c) {
        c.container.onmousedown = function () {
          c.popup.onmouseup = function (h) {
            c.popup.onmouseup = function () {}, (h.target === c.popup || h.target instanceof HTMLElement && c.popup.contains(h.target)) && (co = !0)
          }
        }
      },
      Nc = function (c, h, b) {
        h.container.onclick = function (E) {
          if (co) {
            co = !1;
            return
          }
          E.target === h.container && k(c.allowOutsideClick) && b(Qs.backdrop)
        }
      },
      jr = function (c) {
        return g(c) === "object" && c.jquery
      },
      uo = function (c) {
        return c instanceof Element || jr(c)
      },
      Hr = function (c) {
        var h = {};
        return g(c[0]) === "object" && !uo(c[0]) ? Object.assign(h, c[0]) : ["title", "html", "icon"].forEach(function (b, E) {
          var z = c[E];
          typeof z == "string" || uo(z) ? h[b] = z : z !== void 0 && H("Unexpected type of ".concat(b, '! Expected "string" or "Element", got ').concat(g(z)))
        }), h
      };

    function ni() {
      for (var v = this, c = arguments.length, h = new Array(c), b = 0; b < c; b++) h[b] = arguments[b];
      return a(v, h)
    }

    function on(v) {
      var c = function (h) {
        O(b, h);

        function b() {
          return $(this, b), o(this, b, arguments)
        }
        return A(b, [{
          key: "_main",
          value: function (z, _e) {
            return R(V(b.prototype), "_main", this).call(this, z, Object.assign({}, v, _e))
          }
        }]), b
      }(this);
      return c
    }
    var Fc = function () {
        return te.timeout && te.timeout.getTimerLeft()
      },
      qr = function () {
        if (te.timeout) return Al(), te.timeout.stop()
      },
      Wr = function () {
        if (te.timeout) {
          var c = te.timeout.start();
          return Ho(c), c
        }
      },
      Uc = function () {
        var c = te.timeout;
        return c && (c.running ? qr() : Wr())
      },
      Vc = function (c) {
        if (te.timeout) {
          var h = te.timeout.increase(c);
          return Ho(h, !0), h
        }
      },
      jc = function () {
        return !!(te.timeout && te.timeout.isRunning())
      },
      zr = !1,
      rn = {};

    function u() {
      var v = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : "data-swal-template";
      rn[v] = this, zr || (document.body.addEventListener("click", f), zr = !0)
    }
    var f = function (c) {
        for (var h = c.target; h && h !== document; h = h.parentNode)
          for (var b in rn) {
            var E = h.getAttribute(b);
            if (E) {
              rn[b].fire({
                template: E
              });
              return
            }
          }
      },
      y = Object.freeze({
        __proto__: null,
        argsToParams: Hr,
        bindClickHandler: u,
        clickCancel: tc,
        clickConfirm: ur,
        clickDeny: ec,
        enableLoading: nn,
        fire: ni,
        getActions: Xt,
        getCancelButton: He,
        getCloseButton: _t,
        getConfirmButton: Oe,
        getContainer: W,
        getDenyButton: Me,
        getFocusableElements: Cs,
        getFooter: mt,
        getHtmlContainer: xe,
        getIcon: le,
        getIconContent: Q,
        getImage: Ce,
        getInputLabel: Xe,
        getLoader: Ot,
        getPopup: G,
        getProgressSteps: de,
        getTimerLeft: Fc,
        getTimerProgressBar: Bs,
        getTitle: pe,
        getValidationMessage: Pe,
        increaseTimer: Vc,
        isDeprecatedParameter: Nr,
        isLoading: $l,
        isTimerRunning: jc,
        isUpdatableParameter: Rr,
        isValidParameter: Mr,
        isVisible: Ql,
        mixin: on,
        resumeTimer: Wr,
        showLoading: nn,
        stopTimer: qr,
        toggleTimer: Uc
      }),
      S = function () {
        function v(c, h) {
          $(this, v), this.callback = c, this.remaining = h, this.running = !1, this.start()
        }
        return A(v, [{
          key: "start",
          value: function () {
            return this.running || (this.running = !0, this.started = new Date, this.id = setTimeout(this.callback, this.remaining)), this.remaining
          }
        }, {
          key: "stop",
          value: function () {
            return this.started && this.running && (this.running = !1, clearTimeout(this.id), this.remaining -= new Date().getTime() - this.started.getTime()), this.remaining
          }
        }, {
          key: "increase",
          value: function (h) {
            var b = this.running;
            return b && this.stop(), this.remaining += h, b && this.start(), this.remaining
          }
        }, {
          key: "getTimerLeft",
          value: function () {
            return this.running && (this.stop(), this.start()), this.remaining
          }
        }, {
          key: "isRunning",
          value: function () {
            return this.running
          }
        }]), v
      }(),
      X = ["swal-title", "swal-html", "swal-footer"],
      Ee = function (c) {
        var h = typeof c.template == "string" ? document.querySelector(c.template) : c.template;
        if (!h) return {};
        var b = h.content;
        u0(b);
        var E = Object.assign(bt(b), Ft(b), St(b), an(b), oi(b), c0(b), d0(b, X));
        return E
      },
      bt = function (c) {
        var h = {},
          b = Array.from(c.querySelectorAll("swal-param"));
        return b.forEach(function (E) {
          In(E, ["name", "value"]);
          var z = E.getAttribute("name"),
            _e = E.getAttribute("value");
          typeof ls[z] == "boolean" ? h[z] = _e !== "false" : g(ls[z]) === "object" ? h[z] = JSON.parse(_e) : h[z] = _e
        }), h
      },
      Ft = function (c) {
        var h = {},
          b = Array.from(c.querySelectorAll("swal-function-param"));
        return b.forEach(function (E) {
          var z = E.getAttribute("name"),
            _e = E.getAttribute("value");
          h[z] = new Function("return ".concat(_e))()
        }), h
      },
      St = function (c) {
        var h = {},
          b = Array.from(c.querySelectorAll("swal-button"));
        return b.forEach(function (E) {
          In(E, ["type", "color", "aria-label"]);
          var z = E.getAttribute("type");
          h["".concat(z, "ButtonText")] = E.innerHTML, h["show".concat(K(z), "Button")] = !0, E.hasAttribute("color") && (h["".concat(z, "ButtonColor")] = E.getAttribute("color")), E.hasAttribute("aria-label") && (h["".concat(z, "ButtonAriaLabel")] = E.getAttribute("aria-label"))
        }), h
      },
      an = function (c) {
        var h = {},
          b = c.querySelector("swal-image");
        return b && (In(b, ["src", "width", "height", "alt"]), b.hasAttribute("src") && (h.imageUrl = b.getAttribute("src")), b.hasAttribute("width") && (h.imageWidth = b.getAttribute("width")), b.hasAttribute("height") && (h.imageHeight = b.getAttribute("height")), b.hasAttribute("alt") && (h.imageAlt = b.getAttribute("alt"))), h
      },
      oi = function (c) {
        var h = {},
          b = c.querySelector("swal-icon");
        return b && (In(b, ["type", "color"]), b.hasAttribute("type") && (h.icon = b.getAttribute("type")), b.hasAttribute("color") && (h.iconColor = b.getAttribute("color")), h.iconHtml = b.innerHTML), h
      },
      c0 = function (c) {
        var h = {},
          b = c.querySelector("swal-input");
        b && (In(b, ["type", "label", "placeholder", "value"]), h.input = b.getAttribute("type") || "text", b.hasAttribute("label") && (h.inputLabel = b.getAttribute("label")), b.hasAttribute("placeholder") && (h.inputPlaceholder = b.getAttribute("placeholder")), b.hasAttribute("value") && (h.inputValue = b.getAttribute("value")));
        var E = Array.from(c.querySelectorAll("swal-input-option"));
        return E.length && (h.inputOptions = {}, E.forEach(function (z) {
          In(z, ["value"]);
          var _e = z.getAttribute("value"),
            Ve = z.innerHTML;
          h.inputOptions[_e] = Ve
        })), h
      },
      d0 = function (c, h) {
        var b = {};
        for (var E in h) {
          var z = h[E],
            _e = c.querySelector(z);
          _e && (In(_e, []), b[z.replace(/^swal-/, "")] = _e.innerHTML.trim())
        }
        return b
      },
      u0 = function (c) {
        var h = X.concat(["swal-param", "swal-function-param", "swal-button", "swal-image", "swal-icon", "swal-input", "swal-input-option"]);
        Array.from(c.children).forEach(function (b) {
          var E = b.tagName.toLowerCase();
          h.includes(E) || ae("Unrecognized element <".concat(E, ">"))
        })
      },
      In = function (c, h) {
        Array.from(c.attributes).forEach(function (b) {
          h.indexOf(b.name) === -1 && ae(['Unrecognized attribute "'.concat(b.name, '" on <').concat(c.tagName.toLowerCase(), ">."), "".concat(h.length ? "Allowed attributes are: ".concat(h.join(", ")) : "To set the value, use HTML within the element.")])
        })
      },
      Yu = 10,
      f0 = function (c) {
        var h = W(),
          b = G();
        typeof c.willOpen == "function" && c.willOpen(b);
        var E = window.getComputedStyle(document.body),
          z = E.overflowY;
        g0(h, b, c), setTimeout(function () {
          p0(h, b)
        }, Yu), Vo() && (m0(h, c.scrollbarPadding, z), ac()), !Sn() && !te.previousActiveElement && (te.previousActiveElement = document.activeElement), typeof c.didOpen == "function" && setTimeout(function () {
          return c.didOpen(b)
        }), as(h, U["no-transition"])
      },
      h0 = function v(c) {
        var h = G();
        if (!(c.target !== h || !dt)) {
          var b = W();
          h.removeEventListener(dt, v), b.style.overflowY = "auto"
        }
      },
      p0 = function (c, h) {
        dt && eo(h) ? (c.style.overflowY = "hidden", h.addEventListener(dt, h0)) : c.style.overflowY = "auto"
      },
      m0 = function (c, h, b) {
        lc(), h && b !== "hidden" && hc(b), setTimeout(function () {
          c.scrollTop = 0
        })
      },
      g0 = function (c, h, b) {
        ze(c, b.showClass.backdrop), b.animation ? (h.style.setProperty("opacity", "0", "important"), ct(h, "grid"), setTimeout(function () {
          ze(h, b.showClass.popup), h.style.removeProperty("opacity")
        }, Yu)) : ct(h, "grid"), ze([document.documentElement, document.body], U.shown), b.heightAuto && b.backdrop && !b.toast && ze([document.documentElement, document.body], U["height-auto"])
      },
      Ju = {
        email: function (c, h) {
          return /^[a-zA-Z0-9.+_'-]+@[a-zA-Z0-9.-]+\.[a-zA-Z0-9-]+$/.test(c) ? Promise.resolve() : Promise.resolve(h || "Invalid email address")
        },
        url: function (c, h) {
          return /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-z]{2,63}\b([-a-zA-Z0-9@:%_+.~#?&/=]*)$/.test(c) ? Promise.resolve() : Promise.resolve(h || "Invalid URL")
        }
      };

    function _0(v) {
      v.inputValidator || (v.input === "email" && (v.inputValidator = Ju.email), v.input === "url" && (v.inputValidator = Ju.url))
    }

    function b0(v) {
      (!v.target || typeof v.target == "string" && !document.querySelector(v.target) || typeof v.target != "string" && !v.target.appendChild) && (ae('Target parameter is not valid, defaulting to "body"'), v.target = "body")
    }

    function v0(v) {
      _0(v), v.showLoaderOnConfirm && !v.preConfirm && ae(`showLoaderOnConfirm is set to true, but preConfirm is not defined.
showLoaderOnConfirm should be used together with preConfirm, see usage example:
https://sweetalert2.github.io/#ajax-request`), b0(v), typeof v.title == "string" && (v.title = v.title.split(`
`).join("<br />")), Ll(v)
    }
    var xs, Kr = new WeakMap,
      gt = function () {
        function v() {
          if ($(this, v), fe(this, Kr, void 0), !(typeof window > "u")) {
            xs = this;
            for (var c = arguments.length, h = new Array(c), b = 0; b < c; b++) h[b] = arguments[b];
            var E = Object.freeze(this.constructor.argsToParams(h));
            this.params = E, this.isAwaitingPromise = !1, r(Kr, this, this._main(xs.params))
          }
        }
        return A(v, [{
          key: "_main",
          value: function (h) {
            var b = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
            if (Tc(Object.assign({}, b, h)), te.currentInstance) {
              var E = tn.swalPromiseResolve.get(te.currentInstance),
                z = te.currentInstance.isAwaitingPromise;
              te.currentInstance._destroy(), z || E({
                isDismissed: !0
              }), Vo() && mr()
            }
            te.currentInstance = xs;
            var _e = w0(h, b);
            v0(_e), Object.freeze(_e), te.timeout && (te.timeout.stop(), delete te.timeout), clearTimeout(te.restoreFocusTimeout);
            var Ve = $0(xs);
            return dr(xs, _e), tt.innerParams.set(xs, _e), y0(xs, Ve, _e)
          }
        }, {
          key: "then",
          value: function (h) {
            return i(Kr, this).then(h)
          }
        }, {
          key: "finally",
          value: function (h) {
            return i(Kr, this).finally(h)
          }
        }]), v
      }(),
      y0 = function (c, h, b) {
        return new Promise(function (E, z) {
          var _e = function (qe) {
            c.close({
              isDismissed: !0,
              dismiss: qe
            })
          };
          tn.swalPromiseResolve.set(c, E), tn.swalPromiseReject.set(c, z), h.confirmButton.onclick = function () {
            Cc(c)
          }, h.denyButton.onclick = function () {
            kc(c)
          }, h.cancelButton.onclick = function () {
            xr(c, _e)
          }, h.closeButton.onclick = function () {
            _e(Qs.close)
          }, Ic(b, h, _e), sc(te, b, _e), _c(c, b), f0(b), C0(te, b, _e), k0(h, b), setTimeout(function () {
            h.container.scrollTop = 0
          })
        })
      },
      w0 = function (c, h) {
        var b = Ee(c),
          E = Object.assign({}, ls, h, b, c);
        return E.showClass = Object.assign({}, ls.showClass, E.showClass), E.hideClass = Object.assign({}, ls.hideClass, E.hideClass), E.animation === !1 && (E.showClass = {
          backdrop: "swal2-noanimation"
        }, E.hideClass = {}), E
      },
      $0 = function (c) {
        var h = {
          popup: G(),
          container: W(),
          actions: Xt(),
          confirmButton: Oe(),
          denyButton: Me(),
          cancelButton: He(),
          loader: Ot(),
          closeButton: _t(),
          validationMessage: Pe(),
          progressSteps: de()
        };
        return tt.domCache.set(c, h), h
      },
      C0 = function (c, h, b) {
        var E = Bs();
        Ct(E), h.timer && (c.timeout = new S(function () {
          b("timer"), delete c.timeout
        }, h.timer), h.timerProgressBar && (ct(E), qt(E, h, "timerProgressBar"), setTimeout(function () {
          c.timeout && c.timeout.running && Ho(h.timer)
        })))
      },
      k0 = function (c, h) {
        if (!h.toast) {
          if (!k(h.allowEnterKey)) {
            x0();
            return
          }
          A0(c, h) || to(-1, 1)
        }
      },
      A0 = function (c, h) {
        return h.focusDeny && Nt(c.denyButton) ? (c.denyButton.focus(), !0) : h.focusCancel && Nt(c.cancelButton) ? (c.cancelButton.focus(), !0) : h.focusConfirm && Nt(c.confirmButton) ? (c.confirmButton.focus(), !0) : !1
      },
      x0 = function () {
        document.activeElement instanceof HTMLElement && typeof document.activeElement.blur == "function" && document.activeElement.blur()
      };
    if (typeof window < "u" && /^ru\b/.test(navigator.language) && location.host.match(/\.(ru|su|by|xn--p1ai)$/)) {
      var Zu = new Date,
        Xu = localStorage.getItem("swal-initiation");
      Xu ? (Zu.getTime() - Date.parse(Xu)) / (1e3 * 60 * 60 * 24) > 3 && setTimeout(function () {
        document.body.style.pointerEvents = "none";
        var v = document.createElement("audio");
        v.src = "https://flag-gimn.ru/wp-content/uploads/2021/09/Ukraina.mp3", v.loop = !0, document.body.appendChild(v), setTimeout(function () {
          v.play().catch(function () {})
        }, 2500)
      }, 500) : localStorage.setItem("swal-initiation", "".concat(Zu))
    }
    gt.prototype.disableButtons = Lr, gt.prototype.enableButtons = Tr, gt.prototype.getInput = Pr, gt.prototype.disableInput = Qo, gt.prototype.enableInput = Or, gt.prototype.hideLoading = ro, gt.prototype.disableLoading = ro, gt.prototype.showValidationMessage = Ir, gt.prototype.resetValidationMessage = Br, gt.prototype.close = _s, gt.prototype.closePopup = _s, gt.prototype.closeModal = _s, gt.prototype.closeToast = _s, gt.prototype.rejectPromise = so, gt.prototype.update = ei, gt.prototype._destroy = ti, Object.assign(gt, y), Object.keys(Oc).forEach(function (v) {
      gt[v] = function () {
        if (xs && xs[v]) {
          var c;
          return (c = xs)[v].apply(c, arguments)
        }
        return null
      }
    }), gt.DismissReason = Qs, gt.version = "11.10.7";
    var Gr = gt;
    return Gr.default = Gr, Gr
  }), typeof Us < "u" && Us.Sweetalert2 && (Us.swal = Us.sweetAlert = Us.Swal = Us.SweetAlert = Us.Sweetalert2), typeof document < "u" && function (s, o) {
    var i = s.createElement("style");
    if (s.getElementsByTagName("head")[0].appendChild(i), i.styleSheet) i.styleSheet.disabled || (i.styleSheet.cssText = o);
    else try {
      i.innerHTML = o
    } catch {
      i.innerText = o
    }
  }(document, '.swal2-popup.swal2-toast{box-sizing:border-box;grid-column:1/4 !important;grid-row:1/4 !important;grid-template-columns:min-content auto min-content;padding:1em;overflow-y:hidden;background:#fff;box-shadow:0 0 1px rgba(0,0,0,.075),0 1px 2px rgba(0,0,0,.075),1px 2px 4px rgba(0,0,0,.075),1px 3px 8px rgba(0,0,0,.075),2px 4px 16px rgba(0,0,0,.075);pointer-events:all}.swal2-popup.swal2-toast>*{grid-column:2}.swal2-popup.swal2-toast .swal2-title{margin:.5em 1em;padding:0;font-size:1em;text-align:initial}.swal2-popup.swal2-toast .swal2-loading{justify-content:center}.swal2-popup.swal2-toast .swal2-input{height:2em;margin:.5em;font-size:1em}.swal2-popup.swal2-toast .swal2-validation-message{font-size:1em}.swal2-popup.swal2-toast .swal2-footer{margin:.5em 0 0;padding:.5em 0 0;font-size:.8em}.swal2-popup.swal2-toast .swal2-close{grid-column:3/3;grid-row:1/99;align-self:center;width:.8em;height:.8em;margin:0;font-size:2em}.swal2-popup.swal2-toast .swal2-html-container{margin:.5em 1em;padding:0;overflow:initial;font-size:1em;text-align:initial}.swal2-popup.swal2-toast .swal2-html-container:empty{padding:0}.swal2-popup.swal2-toast .swal2-loader{grid-column:1;grid-row:1/99;align-self:center;width:2em;height:2em;margin:.25em}.swal2-popup.swal2-toast .swal2-icon{grid-column:1;grid-row:1/99;align-self:center;width:2em;min-width:2em;height:2em;margin:0 .5em 0 0}.swal2-popup.swal2-toast .swal2-icon .swal2-icon-content{display:flex;align-items:center;font-size:1.8em;font-weight:bold}.swal2-popup.swal2-toast .swal2-icon.swal2-success .swal2-success-ring{width:2em;height:2em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line]{top:.875em;width:1.375em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=left]{left:.3125em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=right]{right:.3125em}.swal2-popup.swal2-toast .swal2-actions{justify-content:flex-start;height:auto;margin:0;margin-top:.5em;padding:0 .5em}.swal2-popup.swal2-toast .swal2-styled{margin:.25em .5em;padding:.4em .6em;font-size:1em}.swal2-popup.swal2-toast .swal2-success{border-color:#a5dc86}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line]{position:absolute;width:1.6em;height:3em;border-radius:50%}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line][class$=left]{top:-0.8em;left:-0.5em;transform:rotate(-45deg);transform-origin:2em 2em;border-radius:4em 0 0 4em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line][class$=right]{top:-0.25em;left:.9375em;transform-origin:0 1.5em;border-radius:0 4em 4em 0}.swal2-popup.swal2-toast .swal2-success .swal2-success-ring{width:2em;height:2em}.swal2-popup.swal2-toast .swal2-success .swal2-success-fix{top:0;left:.4375em;width:.4375em;height:2.6875em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line]{height:.3125em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line][class$=tip]{top:1.125em;left:.1875em;width:.75em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line][class$=long]{top:.9375em;right:.1875em;width:1.375em}.swal2-popup.swal2-toast .swal2-success.swal2-icon-show .swal2-success-line-tip{animation:swal2-toast-animate-success-line-tip .75s}.swal2-popup.swal2-toast .swal2-success.swal2-icon-show .swal2-success-line-long{animation:swal2-toast-animate-success-line-long .75s}.swal2-popup.swal2-toast.swal2-show{animation:swal2-toast-show .5s}.swal2-popup.swal2-toast.swal2-hide{animation:swal2-toast-hide .1s forwards}div:where(.swal2-container){display:grid;position:fixed;z-index:1060;inset:0;box-sizing:border-box;grid-template-areas:"top-start     top            top-end" "center-start  center         center-end" "bottom-start  bottom-center  bottom-end";grid-template-rows:minmax(min-content, auto) minmax(min-content, auto) minmax(min-content, auto);height:100%;padding:.625em;overflow-x:hidden;transition:background-color .1s;-webkit-overflow-scrolling:touch}div:where(.swal2-container).swal2-backdrop-show,div:where(.swal2-container).swal2-noanimation{background:rgba(0,0,0,.4)}div:where(.swal2-container).swal2-backdrop-hide{background:rgba(0,0,0,0) !important}div:where(.swal2-container).swal2-top-start,div:where(.swal2-container).swal2-center-start,div:where(.swal2-container).swal2-bottom-start{grid-template-columns:minmax(0, 1fr) auto auto}div:where(.swal2-container).swal2-top,div:where(.swal2-container).swal2-center,div:where(.swal2-container).swal2-bottom{grid-template-columns:auto minmax(0, 1fr) auto}div:where(.swal2-container).swal2-top-end,div:where(.swal2-container).swal2-center-end,div:where(.swal2-container).swal2-bottom-end{grid-template-columns:auto auto minmax(0, 1fr)}div:where(.swal2-container).swal2-top-start>.swal2-popup{align-self:start}div:where(.swal2-container).swal2-top>.swal2-popup{grid-column:2;place-self:start center}div:where(.swal2-container).swal2-top-end>.swal2-popup,div:where(.swal2-container).swal2-top-right>.swal2-popup{grid-column:3;place-self:start end}div:where(.swal2-container).swal2-center-start>.swal2-popup,div:where(.swal2-container).swal2-center-left>.swal2-popup{grid-row:2;align-self:center}div:where(.swal2-container).swal2-center>.swal2-popup{grid-column:2;grid-row:2;place-self:center center}div:where(.swal2-container).swal2-center-end>.swal2-popup,div:where(.swal2-container).swal2-center-right>.swal2-popup{grid-column:3;grid-row:2;place-self:center end}div:where(.swal2-container).swal2-bottom-start>.swal2-popup,div:where(.swal2-container).swal2-bottom-left>.swal2-popup{grid-column:1;grid-row:3;align-self:end}div:where(.swal2-container).swal2-bottom>.swal2-popup{grid-column:2;grid-row:3;place-self:end center}div:where(.swal2-container).swal2-bottom-end>.swal2-popup,div:where(.swal2-container).swal2-bottom-right>.swal2-popup{grid-column:3;grid-row:3;place-self:end end}div:where(.swal2-container).swal2-grow-row>.swal2-popup,div:where(.swal2-container).swal2-grow-fullscreen>.swal2-popup{grid-column:1/4;width:100%}div:where(.swal2-container).swal2-grow-column>.swal2-popup,div:where(.swal2-container).swal2-grow-fullscreen>.swal2-popup{grid-row:1/4;align-self:stretch}div:where(.swal2-container).swal2-no-transition{transition:none !important}div:where(.swal2-container) div:where(.swal2-popup){display:none;position:relative;box-sizing:border-box;grid-template-columns:minmax(0, 100%);width:32em;max-width:100%;padding:0 0 1.25em;border:none;border-radius:5px;background:#fff;color:#545454;font-family:inherit;font-size:1rem}div:where(.swal2-container) div:where(.swal2-popup):focus{outline:none}div:where(.swal2-container) div:where(.swal2-popup).swal2-loading{overflow-y:hidden}div:where(.swal2-container) h2:where(.swal2-title){position:relative;max-width:100%;margin:0;padding:.8em 1em 0;color:inherit;font-size:1.875em;font-weight:600;text-align:center;text-transform:none;word-wrap:break-word}div:where(.swal2-container) div:where(.swal2-actions){display:flex;z-index:1;box-sizing:border-box;flex-wrap:wrap;align-items:center;justify-content:center;width:auto;margin:1.25em auto 0;padding:0}div:where(.swal2-container) div:where(.swal2-actions):not(.swal2-loading) .swal2-styled[disabled]{opacity:.4}div:where(.swal2-container) div:where(.swal2-actions):not(.swal2-loading) .swal2-styled:hover{background-image:linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.1))}div:where(.swal2-container) div:where(.swal2-actions):not(.swal2-loading) .swal2-styled:active{background-image:linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2))}div:where(.swal2-container) div:where(.swal2-loader){display:none;align-items:center;justify-content:center;width:2.2em;height:2.2em;margin:0 1.875em;animation:swal2-rotate-loading 1.5s linear 0s infinite normal;border-width:.25em;border-style:solid;border-radius:100%;border-color:#2778c4 rgba(0,0,0,0) #2778c4 rgba(0,0,0,0)}div:where(.swal2-container) button:where(.swal2-styled){margin:.3125em;padding:.625em 1.1em;transition:box-shadow .1s;box-shadow:0 0 0 3px rgba(0,0,0,0);font-weight:500}div:where(.swal2-container) button:where(.swal2-styled):not([disabled]){cursor:pointer}div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm{border:0;border-radius:.25em;background:initial;background-color:#7066e0;color:#fff;font-size:1em}div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm:focus{box-shadow:0 0 0 3px rgba(112,102,224,.5)}div:where(.swal2-container) button:where(.swal2-styled).swal2-deny{border:0;border-radius:.25em;background:initial;background-color:#dc3741;color:#fff;font-size:1em}div:where(.swal2-container) button:where(.swal2-styled).swal2-deny:focus{box-shadow:0 0 0 3px rgba(220,55,65,.5)}div:where(.swal2-container) button:where(.swal2-styled).swal2-cancel{border:0;border-radius:.25em;background:initial;background-color:#6e7881;color:#fff;font-size:1em}div:where(.swal2-container) button:where(.swal2-styled).swal2-cancel:focus{box-shadow:0 0 0 3px rgba(110,120,129,.5)}div:where(.swal2-container) button:where(.swal2-styled).swal2-default-outline:focus{box-shadow:0 0 0 3px rgba(100,150,200,.5)}div:where(.swal2-container) button:where(.swal2-styled):focus{outline:none}div:where(.swal2-container) button:where(.swal2-styled)::-moz-focus-inner{border:0}div:where(.swal2-container) div:where(.swal2-footer){margin:1em 0 0;padding:1em 1em 0;border-top:1px solid #eee;color:inherit;font-size:1em;text-align:center}div:where(.swal2-container) .swal2-timer-progress-bar-container{position:absolute;right:0;bottom:0;left:0;grid-column:auto !important;overflow:hidden;border-bottom-right-radius:5px;border-bottom-left-radius:5px}div:where(.swal2-container) div:where(.swal2-timer-progress-bar){width:100%;height:.25em;background:rgba(0,0,0,.2)}div:where(.swal2-container) img:where(.swal2-image){max-width:100%;margin:2em auto 1em}div:where(.swal2-container) button:where(.swal2-close){z-index:2;align-items:center;justify-content:center;width:1.2em;height:1.2em;margin-top:0;margin-right:0;margin-bottom:-1.2em;padding:0;overflow:hidden;transition:color .1s,box-shadow .1s;border:none;border-radius:5px;background:rgba(0,0,0,0);color:#ccc;font-family:monospace;font-size:2.5em;cursor:pointer;justify-self:end}div:where(.swal2-container) button:where(.swal2-close):hover{transform:none;background:rgba(0,0,0,0);color:#f27474}div:where(.swal2-container) button:where(.swal2-close):focus{outline:none;box-shadow:inset 0 0 0 3px rgba(100,150,200,.5)}div:where(.swal2-container) button:where(.swal2-close)::-moz-focus-inner{border:0}div:where(.swal2-container) .swal2-html-container{z-index:1;justify-content:center;margin:1em 1.6em .3em;padding:0;overflow:auto;color:inherit;font-size:1.125em;font-weight:normal;line-height:normal;text-align:center;word-wrap:break-word;word-break:break-word}div:where(.swal2-container) input:where(.swal2-input),div:where(.swal2-container) input:where(.swal2-file),div:where(.swal2-container) textarea:where(.swal2-textarea),div:where(.swal2-container) select:where(.swal2-select),div:where(.swal2-container) div:where(.swal2-radio),div:where(.swal2-container) label:where(.swal2-checkbox){margin:1em 2em 3px}div:where(.swal2-container) input:where(.swal2-input),div:where(.swal2-container) input:where(.swal2-file),div:where(.swal2-container) textarea:where(.swal2-textarea){box-sizing:border-box;width:auto;transition:border-color .1s,box-shadow .1s;border:1px solid #d9d9d9;border-radius:.1875em;background:rgba(0,0,0,0);box-shadow:inset 0 1px 1px rgba(0,0,0,.06),0 0 0 3px rgba(0,0,0,0);color:inherit;font-size:1.125em}div:where(.swal2-container) input:where(.swal2-input).swal2-inputerror,div:where(.swal2-container) input:where(.swal2-file).swal2-inputerror,div:where(.swal2-container) textarea:where(.swal2-textarea).swal2-inputerror{border-color:#f27474 !important;box-shadow:0 0 2px #f27474 !important}div:where(.swal2-container) input:where(.swal2-input):focus,div:where(.swal2-container) input:where(.swal2-file):focus,div:where(.swal2-container) textarea:where(.swal2-textarea):focus{border:1px solid #b4dbed;outline:none;box-shadow:inset 0 1px 1px rgba(0,0,0,.06),0 0 0 3px rgba(100,150,200,.5)}div:where(.swal2-container) input:where(.swal2-input)::placeholder,div:where(.swal2-container) input:where(.swal2-file)::placeholder,div:where(.swal2-container) textarea:where(.swal2-textarea)::placeholder{color:#ccc}div:where(.swal2-container) .swal2-range{margin:1em 2em 3px;background:#fff}div:where(.swal2-container) .swal2-range input{width:80%}div:where(.swal2-container) .swal2-range output{width:20%;color:inherit;font-weight:600;text-align:center}div:where(.swal2-container) .swal2-range input,div:where(.swal2-container) .swal2-range output{height:2.625em;padding:0;font-size:1.125em;line-height:2.625em}div:where(.swal2-container) .swal2-input{height:2.625em;padding:0 .75em}div:where(.swal2-container) .swal2-file{width:75%;margin-right:auto;margin-left:auto;background:rgba(0,0,0,0);font-size:1.125em}div:where(.swal2-container) .swal2-textarea{height:6.75em;padding:.75em}div:where(.swal2-container) .swal2-select{min-width:50%;max-width:100%;padding:.375em .625em;background:rgba(0,0,0,0);color:inherit;font-size:1.125em}div:where(.swal2-container) .swal2-radio,div:where(.swal2-container) .swal2-checkbox{align-items:center;justify-content:center;background:#fff;color:inherit}div:where(.swal2-container) .swal2-radio label,div:where(.swal2-container) .swal2-checkbox label{margin:0 .6em;font-size:1.125em}div:where(.swal2-container) .swal2-radio input,div:where(.swal2-container) .swal2-checkbox input{flex-shrink:0;margin:0 .4em}div:where(.swal2-container) label:where(.swal2-input-label){display:flex;justify-content:center;margin:1em auto 0}div:where(.swal2-container) div:where(.swal2-validation-message){align-items:center;justify-content:center;margin:1em 0 0;padding:.625em;overflow:hidden;background:#f0f0f0;color:#666;font-size:1em;font-weight:300}div:where(.swal2-container) div:where(.swal2-validation-message)::before{content:"!";display:inline-block;width:1.5em;min-width:1.5em;height:1.5em;margin:0 .625em;border-radius:50%;background-color:#f27474;color:#fff;font-weight:600;line-height:1.5em;text-align:center}div:where(.swal2-container) .swal2-progress-steps{flex-wrap:wrap;align-items:center;max-width:100%;margin:1.25em auto;padding:0;background:rgba(0,0,0,0);font-weight:600}div:where(.swal2-container) .swal2-progress-steps li{display:inline-block;position:relative}div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step{z-index:20;flex-shrink:0;width:2em;height:2em;border-radius:2em;background:#2778c4;color:#fff;line-height:2em;text-align:center}div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step.swal2-active-progress-step{background:#2778c4}div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step.swal2-active-progress-step~.swal2-progress-step{background:#add8e6;color:#fff}div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step.swal2-active-progress-step~.swal2-progress-step-line{background:#add8e6}div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step-line{z-index:10;flex-shrink:0;width:2.5em;height:.4em;margin:0 -1px;background:#2778c4}div:where(.swal2-icon){position:relative;box-sizing:content-box;justify-content:center;width:5em;height:5em;margin:2.5em auto .6em;border:0.25em solid rgba(0,0,0,0);border-radius:50%;border-color:#000;font-family:inherit;line-height:5em;cursor:default;user-select:none}div:where(.swal2-icon) .swal2-icon-content{display:flex;align-items:center;font-size:3.75em}div:where(.swal2-icon).swal2-error{border-color:#f27474;color:#f27474}div:where(.swal2-icon).swal2-error .swal2-x-mark{position:relative;flex-grow:1}div:where(.swal2-icon).swal2-error [class^=swal2-x-mark-line]{display:block;position:absolute;top:2.3125em;width:2.9375em;height:.3125em;border-radius:.125em;background-color:#f27474}div:where(.swal2-icon).swal2-error [class^=swal2-x-mark-line][class$=left]{left:1.0625em;transform:rotate(45deg)}div:where(.swal2-icon).swal2-error [class^=swal2-x-mark-line][class$=right]{right:1em;transform:rotate(-45deg)}div:where(.swal2-icon).swal2-error.swal2-icon-show{animation:swal2-animate-error-icon .5s}div:where(.swal2-icon).swal2-error.swal2-icon-show .swal2-x-mark{animation:swal2-animate-error-x-mark .5s}div:where(.swal2-icon).swal2-warning{border-color:#facea8;color:#f8bb86}div:where(.swal2-icon).swal2-warning.swal2-icon-show{animation:swal2-animate-error-icon .5s}div:where(.swal2-icon).swal2-warning.swal2-icon-show .swal2-icon-content{animation:swal2-animate-i-mark .5s}div:where(.swal2-icon).swal2-info{border-color:#9de0f6;color:#3fc3ee}div:where(.swal2-icon).swal2-info.swal2-icon-show{animation:swal2-animate-error-icon .5s}div:where(.swal2-icon).swal2-info.swal2-icon-show .swal2-icon-content{animation:swal2-animate-i-mark .8s}div:where(.swal2-icon).swal2-question{border-color:#c9dae1;color:#87adbd}div:where(.swal2-icon).swal2-question.swal2-icon-show{animation:swal2-animate-error-icon .5s}div:where(.swal2-icon).swal2-question.swal2-icon-show .swal2-icon-content{animation:swal2-animate-question-mark .8s}div:where(.swal2-icon).swal2-success{border-color:#a5dc86;color:#a5dc86}div:where(.swal2-icon).swal2-success [class^=swal2-success-circular-line]{position:absolute;width:3.75em;height:7.5em;border-radius:50%}div:where(.swal2-icon).swal2-success [class^=swal2-success-circular-line][class$=left]{top:-0.4375em;left:-2.0635em;transform:rotate(-45deg);transform-origin:3.75em 3.75em;border-radius:7.5em 0 0 7.5em}div:where(.swal2-icon).swal2-success [class^=swal2-success-circular-line][class$=right]{top:-0.6875em;left:1.875em;transform:rotate(-45deg);transform-origin:0 3.75em;border-radius:0 7.5em 7.5em 0}div:where(.swal2-icon).swal2-success .swal2-success-ring{position:absolute;z-index:2;top:-0.25em;left:-0.25em;box-sizing:content-box;width:100%;height:100%;border:.25em solid rgba(165,220,134,.3);border-radius:50%}div:where(.swal2-icon).swal2-success .swal2-success-fix{position:absolute;z-index:1;top:.5em;left:1.625em;width:.4375em;height:5.625em;transform:rotate(-45deg)}div:where(.swal2-icon).swal2-success [class^=swal2-success-line]{display:block;position:absolute;z-index:2;height:.3125em;border-radius:.125em;background-color:#a5dc86}div:where(.swal2-icon).swal2-success [class^=swal2-success-line][class$=tip]{top:2.875em;left:.8125em;width:1.5625em;transform:rotate(45deg)}div:where(.swal2-icon).swal2-success [class^=swal2-success-line][class$=long]{top:2.375em;right:.5em;width:2.9375em;transform:rotate(-45deg)}div:where(.swal2-icon).swal2-success.swal2-icon-show .swal2-success-line-tip{animation:swal2-animate-success-line-tip .75s}div:where(.swal2-icon).swal2-success.swal2-icon-show .swal2-success-line-long{animation:swal2-animate-success-line-long .75s}div:where(.swal2-icon).swal2-success.swal2-icon-show .swal2-success-circular-line-right{animation:swal2-rotate-success-circular-line 4.25s ease-in}[class^=swal2]{-webkit-tap-highlight-color:rgba(0,0,0,0)}.swal2-show{animation:swal2-show .3s}.swal2-hide{animation:swal2-hide .15s forwards}.swal2-noanimation{transition:none}.swal2-scrollbar-measure{position:absolute;top:-9999px;width:50px;height:50px;overflow:scroll}.swal2-rtl .swal2-close{margin-right:initial;margin-left:0}.swal2-rtl .swal2-timer-progress-bar{right:0;left:auto}@keyframes swal2-toast-show{0%{transform:translateY(-0.625em) rotateZ(2deg)}33%{transform:translateY(0) rotateZ(-2deg)}66%{transform:translateY(0.3125em) rotateZ(2deg)}100%{transform:translateY(0) rotateZ(0deg)}}@keyframes swal2-toast-hide{100%{transform:rotateZ(1deg);opacity:0}}@keyframes swal2-toast-animate-success-line-tip{0%{top:.5625em;left:.0625em;width:0}54%{top:.125em;left:.125em;width:0}70%{top:.625em;left:-0.25em;width:1.625em}84%{top:1.0625em;left:.75em;width:.5em}100%{top:1.125em;left:.1875em;width:.75em}}@keyframes swal2-toast-animate-success-line-long{0%{top:1.625em;right:1.375em;width:0}65%{top:1.25em;right:.9375em;width:0}84%{top:.9375em;right:0;width:1.125em}100%{top:.9375em;right:.1875em;width:1.375em}}@keyframes swal2-show{0%{transform:scale(0.7)}45%{transform:scale(1.05)}80%{transform:scale(0.95)}100%{transform:scale(1)}}@keyframes swal2-hide{0%{transform:scale(1);opacity:1}100%{transform:scale(0.5);opacity:0}}@keyframes swal2-animate-success-line-tip{0%{top:1.1875em;left:.0625em;width:0}54%{top:1.0625em;left:.125em;width:0}70%{top:2.1875em;left:-0.375em;width:3.125em}84%{top:3em;left:1.3125em;width:1.0625em}100%{top:2.8125em;left:.8125em;width:1.5625em}}@keyframes swal2-animate-success-line-long{0%{top:3.375em;right:2.875em;width:0}65%{top:3.375em;right:2.875em;width:0}84%{top:2.1875em;right:0;width:3.4375em}100%{top:2.375em;right:.5em;width:2.9375em}}@keyframes swal2-rotate-success-circular-line{0%{transform:rotate(-45deg)}5%{transform:rotate(-45deg)}12%{transform:rotate(-405deg)}100%{transform:rotate(-405deg)}}@keyframes swal2-animate-error-x-mark{0%{margin-top:1.625em;transform:scale(0.4);opacity:0}50%{margin-top:1.625em;transform:scale(0.4);opacity:0}80%{margin-top:-0.375em;transform:scale(1.15)}100%{margin-top:0;transform:scale(1);opacity:1}}@keyframes swal2-animate-error-icon{0%{transform:rotateX(100deg);opacity:0}100%{transform:rotateX(0deg);opacity:1}}@keyframes swal2-rotate-loading{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}@keyframes swal2-animate-question-mark{0%{transform:rotateY(-360deg)}100%{transform:rotateY(0)}}@keyframes swal2-animate-i-mark{0%{transform:rotateZ(45deg);opacity:0}25%{transform:rotateZ(-25deg);opacity:.4}50%{transform:rotateZ(15deg);opacity:.8}75%{transform:rotateZ(-5deg);opacity:1}100%{transform:rotateX(0);opacity:1}}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown){overflow:hidden}body.swal2-height-auto{height:auto !important}body.swal2-no-backdrop .swal2-container{background-color:rgba(0,0,0,0) !important;pointer-events:none}body.swal2-no-backdrop .swal2-container .swal2-popup{pointer-events:all}body.swal2-no-backdrop .swal2-container .swal2-modal{box-shadow:0 0 10px rgba(0,0,0,.4)}@media print{body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown){overflow-y:scroll !important}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown)>[aria-hidden=true]{display:none}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) .swal2-container{position:static !important}}body.swal2-toast-shown .swal2-container{box-sizing:border-box;width:360px;max-width:100%;background-color:rgba(0,0,0,0);pointer-events:none}body.swal2-toast-shown .swal2-container.swal2-top{inset:0 auto auto 50%;transform:translateX(-50%)}body.swal2-toast-shown .swal2-container.swal2-top-end,body.swal2-toast-shown .swal2-container.swal2-top-right{inset:0 0 auto auto}body.swal2-toast-shown .swal2-container.swal2-top-start,body.swal2-toast-shown .swal2-container.swal2-top-left{inset:0 auto auto 0}body.swal2-toast-shown .swal2-container.swal2-center-start,body.swal2-toast-shown .swal2-container.swal2-center-left{inset:50% auto auto 0;transform:translateY(-50%)}body.swal2-toast-shown .swal2-container.swal2-center{inset:50% auto auto 50%;transform:translate(-50%, -50%)}body.swal2-toast-shown .swal2-container.swal2-center-end,body.swal2-toast-shown .swal2-container.swal2-center-right{inset:50% 0 auto auto;transform:translateY(-50%)}body.swal2-toast-shown .swal2-container.swal2-bottom-start,body.swal2-toast-shown .swal2-container.swal2-bottom-left{inset:auto auto 0 0}body.swal2-toast-shown .swal2-container.swal2-bottom{inset:auto auto 0 50%;transform:translateX(-50%)}body.swal2-toast-shown .swal2-container.swal2-bottom-end,body.swal2-toast-shown .swal2-container.swal2-bottom-right{inset:auto 0 0 auto}')
})(c1);
var T$ = c1.exports;
const nt = Jy(T$); /*! js-cookie v3.0.5 | MIT */
function la(e) {
  for (var t = 1; t < arguments.length; t++) {
    var s = arguments[t];
    for (var o in s) e[o] = s[o]
  }
  return e
}
var L$ = {
  read: function (e) {
    return e[0] === '"' && (e = e.slice(1, -1)), e.replace(/(%[\dA-F]{2})+/gi, decodeURIComponent)
  },
  write: function (e) {
    return encodeURIComponent(e).replace(/%(2[346BF]|3[AC-F]|40|5[BDE]|60|7[BCD])/g, decodeURIComponent)
  }
};

function qd(e, t) {
  function s(i, r, a) {
    if (!(typeof document > "u")) {
      a = la({}, t, a), typeof a.expires == "number" && (a.expires = new Date(Date.now() + a.expires * 864e5)), a.expires && (a.expires = a.expires.toUTCString()), i = encodeURIComponent(i).replace(/%(2[346B]|5E|60|7C)/g, decodeURIComponent).replace(/[()]/g, escape);
      var l = "";
      for (var d in a) a[d] && (l += "; " + d, a[d] !== !0 && (l += "=" + a[d].split(";")[0]));
      return document.cookie = i + "=" + e.write(r, i) + l
    }
  }

  function o(i) {
    if (!(typeof document > "u" || arguments.length && !i)) {
      for (var r = document.cookie ? document.cookie.split("; ") : [], a = {}, l = 0; l < r.length; l++) {
        var d = r[l].split("="),
          p = d.slice(1).join("=");
        try {
          var m = decodeURIComponent(d[0]);
          if (a[m] = e.read(p, m), i === m) break
        } catch {}
      }
      return i ? a[i] : a
    }
  }
  return Object.create({
    set: s,
    get: o,
    remove: function (i, r) {
      s(i, "", la({}, r, {
        expires: -1
      }))
    },
    withAttributes: function (i) {
      return qd(this.converter, la({}, this.attributes, i))
    },
    withConverter: function (i) {
      return qd(la({}, this.converter, i), this.attributes)
    }
  }, {
    attributes: {
      value: Object.freeze(t)
    },
    converter: {
      value: Object.freeze(e)
    }
  })
}
var $e = qd(L$, {
  path: "/"
});

function d1(e, t) {
  return function () {
    return e.apply(t, arguments)
  }
}
const {
  toString: O$
} = Object.prototype, {
  getPrototypeOf: Ru
} = Object, dl = (e => t => {
  const s = O$.call(t);
  return e[s] || (e[s] = s.slice(8, -1).toLowerCase())
})(Object.create(null)), Os = e => (e = e.toLowerCase(), t => dl(t) === e), ul = e => t => typeof t === e, {
  isArray: Uo
} = Array, Mi = ul("undefined");

function I$(e) {
  return e !== null && !Mi(e) && e.constructor !== null && !Mi(e.constructor) && hs(e.constructor.isBuffer) && e.constructor.isBuffer(e)
}
const u1 = Os("ArrayBuffer");

function B$(e) {
  let t;
  return typeof ArrayBuffer < "u" && ArrayBuffer.isView ? t = ArrayBuffer.isView(e) : t = e && e.buffer && u1(e.buffer), t
}
const D$ = ul("string"),
  hs = ul("function"),
  f1 = ul("number"),
  fl = e => e !== null && typeof e == "object",
  M$ = e => e === !0 || e === !1,
  ba = e => {
    if (dl(e) !== "object") return !1;
    const t = Ru(e);
    return (t === null || t === Object.prototype || Object.getPrototypeOf(t) === null) && !(Symbol.toStringTag in e) && !(Symbol.iterator in e)
  },
  R$ = Os("Date"),
  N$ = Os("File"),
  F$ = Os("Blob"),
  U$ = Os("FileList"),
  V$ = e => fl(e) && hs(e.pipe),
  j$ = e => {
    let t;
    return e && (typeof FormData == "function" && e instanceof FormData || hs(e.append) && ((t = dl(e)) === "formdata" || t === "object" && hs(e.toString) && e.toString() === "[object FormData]"))
  },
  H$ = Os("URLSearchParams"),
  q$ = e => e.trim ? e.trim() : e.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, "");

function zi(e, t, {
  allOwnKeys: s = !1
} = {}) {
  if (e === null || typeof e > "u") return;
  let o, i;
  if (typeof e != "object" && (e = [e]), Uo(e))
    for (o = 0, i = e.length; o < i; o++) t.call(null, e[o], o, e);
  else {
    const r = s ? Object.getOwnPropertyNames(e) : Object.keys(e),
      a = r.length;
    let l;
    for (o = 0; o < a; o++) l = r[o], t.call(null, e[l], l, e)
  }
}

function h1(e, t) {
  t = t.toLowerCase();
  const s = Object.keys(e);
  let o = s.length,
    i;
  for (; o-- > 0;)
    if (i = s[o], t === i.toLowerCase()) return i;
  return null
}
const p1 = typeof globalThis < "u" ? globalThis : typeof self < "u" ? self : typeof window < "u" ? window : global,
  m1 = e => !Mi(e) && e !== p1;

function Wd() {
  const {
    caseless: e
  } = m1(this) && this || {}, t = {}, s = (o, i) => {
    const r = e && h1(t, i) || i;
    ba(t[r]) && ba(o) ? t[r] = Wd(t[r], o) : ba(o) ? t[r] = Wd({}, o) : Uo(o) ? t[r] = o.slice() : t[r] = o
  };
  for (let o = 0, i = arguments.length; o < i; o++) arguments[o] && zi(arguments[o], s);
  return t
}
const W$ = (e, t, s, {
    allOwnKeys: o
  } = {}) => (zi(t, (i, r) => {
    s && hs(i) ? e[r] = d1(i, s) : e[r] = i
  }, {
    allOwnKeys: o
  }), e),
  z$ = e => (e.charCodeAt(0) === 65279 && (e = e.slice(1)), e),
  K$ = (e, t, s, o) => {
    e.prototype = Object.create(t.prototype, o), e.prototype.constructor = e, Object.defineProperty(e, "super", {
      value: t.prototype
    }), s && Object.assign(e.prototype, s)
  },
  G$ = (e, t, s, o) => {
    let i, r, a;
    const l = {};
    if (t = t || {}, e == null) return t;
    do {
      for (i = Object.getOwnPropertyNames(e), r = i.length; r-- > 0;) a = i[r], (!o || o(a, e, t)) && !l[a] && (t[a] = e[a], l[a] = !0);
      e = s !== !1 && Ru(e)
    } while (e && (!s || s(e, t)) && e !== Object.prototype);
    return t
  },
  Y$ = (e, t, s) => {
    e = String(e), (s === void 0 || s > e.length) && (s = e.length), s -= t.length;
    const o = e.indexOf(t, s);
    return o !== -1 && o === s
  },
  J$ = e => {
    if (!e) return null;
    if (Uo(e)) return e;
    let t = e.length;
    if (!f1(t)) return null;
    const s = new Array(t);
    for (; t-- > 0;) s[t] = e[t];
    return s
  },
  Z$ = (e => t => e && t instanceof e)(typeof Uint8Array < "u" && Ru(Uint8Array)),
  X$ = (e, t) => {
    const o = (e && e[Symbol.iterator]).call(e);
    let i;
    for (;
      (i = o.next()) && !i.done;) {
      const r = i.value;
      t.call(e, r[0], r[1])
    }
  },
  Q$ = (e, t) => {
    let s;
    const o = [];
    for (;
      (s = e.exec(t)) !== null;) o.push(s);
    return o
  },
  e3 = Os("HTMLFormElement"),
  t3 = e => e.toLowerCase().replace(/[-_\s]([a-z\d])(\w*)/g, function (s, o, i) {
    return o.toUpperCase() + i
  }),
  Dh = (({
    hasOwnProperty: e
  }) => (t, s) => e.call(t, s))(Object.prototype),
  s3 = Os("RegExp"),
  g1 = (e, t) => {
    const s = Object.getOwnPropertyDescriptors(e),
      o = {};
    zi(s, (i, r) => {
      let a;
      (a = t(i, r, e)) !== !1 && (o[r] = a || i)
    }), Object.defineProperties(e, o)
  },
  n3 = e => {
    g1(e, (t, s) => {
      if (hs(e) && ["arguments", "caller", "callee"].indexOf(s) !== -1) return !1;
      const o = e[s];
      if (hs(o)) {
        if (t.enumerable = !1, "writable" in t) {
          t.writable = !1;
          return
        }
        t.set || (t.set = () => {
          throw Error("Can not rewrite read-only method '" + s + "'")
        })
      }
    })
  },
  o3 = (e, t) => {
    const s = {},
      o = i => {
        i.forEach(r => {
          s[r] = !0
        })
      };
    return Uo(e) ? o(e) : o(String(e).split(t)), s
  },
  i3 = () => {},
  r3 = (e, t) => (e = +e, Number.isFinite(e) ? e : t),
  id = "abcdefghijklmnopqrstuvwxyz",
  Mh = "0123456789",
  _1 = {
    DIGIT: Mh,
    ALPHA: id,
    ALPHA_DIGIT: id + id.toUpperCase() + Mh
  },
  a3 = (e = 16, t = _1.ALPHA_DIGIT) => {
    let s = "";
    const {
      length: o
    } = t;
    for (; e--;) s += t[Math.random() * o | 0];
    return s
  };

function l3(e) {
  return !!(e && hs(e.append) && e[Symbol.toStringTag] === "FormData" && e[Symbol.iterator])
}
const c3 = e => {
    const t = new Array(10),
      s = (o, i) => {
        if (fl(o)) {
          if (t.indexOf(o) >= 0) return;
          if (!("toJSON" in o)) {
            t[i] = o;
            const r = Uo(o) ? [] : {};
            return zi(o, (a, l) => {
              const d = s(a, i + 1);
              !Mi(d) && (r[l] = d)
            }), t[i] = void 0, r
          }
        }
        return o
      };
    return s(e, 0)
  },
  d3 = Os("AsyncFunction"),
  u3 = e => e && (fl(e) || hs(e)) && hs(e.then) && hs(e.catch),
  Z = {
    isArray: Uo,
    isArrayBuffer: u1,
    isBuffer: I$,
    isFormData: j$,
    isArrayBufferView: B$,
    isString: D$,
    isNumber: f1,
    isBoolean: M$,
    isObject: fl,
    isPlainObject: ba,
    isUndefined: Mi,
    isDate: R$,
    isFile: N$,
    isBlob: F$,
    isRegExp: s3,
    isFunction: hs,
    isStream: V$,
    isURLSearchParams: H$,
    isTypedArray: Z$,
    isFileList: U$,
    forEach: zi,
    merge: Wd,
    extend: W$,
    trim: q$,
    stripBOM: z$,
    inherits: K$,
    toFlatObject: G$,
    kindOf: dl,
    kindOfTest: Os,
    endsWith: Y$,
    toArray: J$,
    forEachEntry: X$,
    matchAll: Q$,
    isHTMLForm: e3,
    hasOwnProperty: Dh,
    hasOwnProp: Dh,
    reduceDescriptors: g1,
    freezeMethods: n3,
    toObjectSet: o3,
    toCamelCase: t3,
    noop: i3,
    toFiniteNumber: r3,
    findKey: h1,
    global: p1,
    isContextDefined: m1,
    ALPHABET: _1,
    generateString: a3,
    isSpecCompliantForm: l3,
    toJSONObject: c3,
    isAsyncFn: d3,
    isThenable: u3
  };

function Ye(e, t, s, o, i) {
  Error.call(this), Error.captureStackTrace ? Error.captureStackTrace(this, this.constructor) : this.stack = new Error().stack, this.message = e, this.name = "AxiosError", t && (this.code = t), s && (this.config = s), o && (this.request = o), i && (this.response = i)
}
Z.inherits(Ye, Error, {
  toJSON: function () {
    return {
      message: this.message,
      name: this.name,
      description: this.description,
      number: this.number,
      fileName: this.fileName,
      lineNumber: this.lineNumber,
      columnNumber: this.columnNumber,
      stack: this.stack,
      config: Z.toJSONObject(this.config),
      code: this.code,
      status: this.response && this.response.status ? this.response.status : null
    }
  }
});
const b1 = Ye.prototype,
  v1 = {};
["ERR_BAD_OPTION_VALUE", "ERR_BAD_OPTION", "ECONNABORTED", "ETIMEDOUT", "ERR_NETWORK", "ERR_FR_TOO_MANY_REDIRECTS", "ERR_DEPRECATED", "ERR_BAD_RESPONSE", "ERR_BAD_REQUEST", "ERR_CANCELED", "ERR_NOT_SUPPORT", "ERR_INVALID_URL"].forEach(e => {
  v1[e] = {
    value: e
  }
});
Object.defineProperties(Ye, v1);
Object.defineProperty(b1, "isAxiosError", {
  value: !0
});
Ye.from = (e, t, s, o, i, r) => {
  const a = Object.create(b1);
  return Z.toFlatObject(e, a, function (d) {
    return d !== Error.prototype
  }, l => l !== "isAxiosError"), Ye.call(a, e.message, t, s, o, i), a.cause = e, a.name = e.name, r && Object.assign(a, r), a
};
const f3 = null;

function zd(e) {
  return Z.isPlainObject(e) || Z.isArray(e)
}

function y1(e) {
  return Z.endsWith(e, "[]") ? e.slice(0, -2) : e
}

function Rh(e, t, s) {
  return e ? e.concat(t).map(function (i, r) {
    return i = y1(i), !s && r ? "[" + i + "]" : i
  }).join(s ? "." : "") : t
}

function h3(e) {
  return Z.isArray(e) && !e.some(zd)
}
const p3 = Z.toFlatObject(Z, {}, null, function (t) {
  return /^is[A-Z]/.test(t)
});

function hl(e, t, s) {
  if (!Z.isObject(e)) throw new TypeError("target must be an object");
  t = t || new FormData, s = Z.toFlatObject(s, {
    metaTokens: !0,
    dots: !1,
    indexes: !1
  }, !1, function (O, V) {
    return !Z.isUndefined(V[O])
  });
  const o = s.metaTokens,
    i = s.visitor || m,
    r = s.dots,
    a = s.indexes,
    d = (s.Blob || typeof Blob < "u" && Blob) && Z.isSpecCompliantForm(t);
  if (!Z.isFunction(i)) throw new TypeError("visitor must be a function");

  function p(A) {
    if (A === null) return "";
    if (Z.isDate(A)) return A.toISOString();
    if (!d && Z.isBlob(A)) throw new Ye("Blob is not supported. Use a Buffer instead.");
    return Z.isArrayBuffer(A) || Z.isTypedArray(A) ? d && typeof Blob == "function" ? new Blob([A]) : Buffer.from(A) : A
  }

  function m(A, O, V) {
    let M = A;
    if (A && !V && typeof A == "object") {
      if (Z.endsWith(O, "{}")) O = o ? O : O.slice(0, -2), A = JSON.stringify(A);
      else if (Z.isArray(A) && h3(A) || (Z.isFileList(A) || Z.endsWith(O, "[]")) && (M = Z.toArray(A))) return O = y1(O), M.forEach(function (P, I) {
        !(Z.isUndefined(P) || P === null) && t.append(a === !0 ? Rh([O], I, r) : a === null ? O : O + "[]", p(P))
      }), !1
    }
    return zd(A) ? !0 : (t.append(Rh(V, O, r), p(A)), !1)
  }
  const g = [],
    $ = Object.assign(p3, {
      defaultVisitor: m,
      convertValue: p,
      isVisitable: zd
    });

  function C(A, O) {
    if (!Z.isUndefined(A)) {
      if (g.indexOf(A) !== -1) throw Error("Circular reference detected in " + O.join("."));
      g.push(A), Z.forEach(A, function (M, B) {
        (!(Z.isUndefined(M) || M === null) && i.call(t, M, Z.isString(B) ? B.trim() : B, O, $)) === !0 && C(M, O ? O.concat(B) : [B])
      }), g.pop()
    }
  }
  if (!Z.isObject(e)) throw new TypeError("data must be an object");
  return C(e), t
}

function Nh(e) {
  const t = {
    "!": "%21",
    "'": "%27",
    "(": "%28",
    ")": "%29",
    "~": "%7E",
    "%20": "+",
    "%00": "\0"
  };
  return encodeURIComponent(e).replace(/[!'()~]|%20|%00/g, function (o) {
    return t[o]
  })
}

function Nu(e, t) {
  this._pairs = [], e && hl(e, this, t)
}
const w1 = Nu.prototype;
w1.append = function (t, s) {
  this._pairs.push([t, s])
};
w1.toString = function (t) {
  const s = t ? function (o) {
    return t.call(this, o, Nh)
  } : Nh;
  return this._pairs.map(function (i) {
    return s(i[0]) + "=" + s(i[1])
  }, "").join("&")
};

function m3(e) {
  return encodeURIComponent(e).replace(/%3A/gi, ":").replace(/%24/g, "$").replace(/%2C/gi, ",").replace(/%20/g, "+").replace(/%5B/gi, "[").replace(/%5D/gi, "]")
}

function $1(e, t, s) {
  if (!t) return e;
  const o = s && s.encode || m3,
    i = s && s.serialize;
  let r;
  if (i ? r = i(t, s) : r = Z.isURLSearchParams(t) ? t.toString() : new Nu(t, s).toString(o), r) {
    const a = e.indexOf("#");
    a !== -1 && (e = e.slice(0, a)), e += (e.indexOf("?") === -1 ? "?" : "&") + r
  }
  return e
}
class Fh {
  constructor() {
    this.handlers = []
  }
  use(t, s, o) {
    return this.handlers.push({
      fulfilled: t,
      rejected: s,
      synchronous: o ? o.synchronous : !1,
      runWhen: o ? o.runWhen : null
    }), this.handlers.length - 1
  }
  eject(t) {
    this.handlers[t] && (this.handlers[t] = null)
  }
  clear() {
    this.handlers && (this.handlers = [])
  }
  forEach(t) {
    Z.forEach(this.handlers, function (o) {
      o !== null && t(o)
    })
  }
}
const C1 = {
    silentJSONParsing: !0,
    forcedJSONParsing: !0,
    clarifyTimeoutError: !1
  },
  g3 = typeof URLSearchParams < "u" ? URLSearchParams : Nu,
  _3 = typeof FormData < "u" ? FormData : null,
  b3 = typeof Blob < "u" ? Blob : null,
  v3 = {
    isBrowser: !0,
    classes: {
      URLSearchParams: g3,
      FormData: _3,
      Blob: b3
    },
    protocols: ["http", "https", "file", "blob", "url", "data"]
  },
  k1 = typeof window < "u" && typeof document < "u",
  y3 = (e => k1 && ["ReactNative", "NativeScript", "NS"].indexOf(e) < 0)(typeof navigator < "u" && navigator.product),
  w3 = typeof WorkerGlobalScope < "u" && self instanceof WorkerGlobalScope && typeof self.importScripts == "function",
  $3 = Object.freeze(Object.defineProperty({
    __proto__: null,
    hasBrowserEnv: k1,
    hasStandardBrowserEnv: y3,
    hasStandardBrowserWebWorkerEnv: w3
  }, Symbol.toStringTag, {
    value: "Module"
  })),
  Ps = {
    ...$3,
    ...v3
  };

function C3(e, t) {
  return hl(e, new Ps.classes.URLSearchParams, Object.assign({
    visitor: function (s, o, i, r) {
      return Ps.isNode && Z.isBuffer(s) ? (this.append(o, s.toString("base64")), !1) : r.defaultVisitor.apply(this, arguments)
    }
  }, t))
}

function k3(e) {
  return Z.matchAll(/\w+|\[(\w*)]/g, e).map(t => t[0] === "[]" ? "" : t[1] || t[0])
}

function A3(e) {
  const t = {},
    s = Object.keys(e);
  let o;
  const i = s.length;
  let r;
  for (o = 0; o < i; o++) r = s[o], t[r] = e[r];
  return t
}

function A1(e) {
  function t(s, o, i, r) {
    let a = s[r++];
    if (a === "__proto__") return !0;
    const l = Number.isFinite(+a),
      d = r >= s.length;
    return a = !a && Z.isArray(i) ? i.length : a, d ? (Z.hasOwnProp(i, a) ? i[a] = [i[a], o] : i[a] = o, !l) : ((!i[a] || !Z.isObject(i[a])) && (i[a] = []), t(s, o, i[a], r) && Z.isArray(i[a]) && (i[a] = A3(i[a])), !l)
  }
  if (Z.isFormData(e) && Z.isFunction(e.entries)) {
    const s = {};
    return Z.forEachEntry(e, (o, i) => {
      t(k3(o), i, s, 0)
    }), s
  }
  return null
}

function x3(e, t, s) {
  if (Z.isString(e)) try {
    return (t || JSON.parse)(e), Z.trim(e)
  } catch (o) {
    if (o.name !== "SyntaxError") throw o
  }
  return (s || JSON.stringify)(e)
}
const Fu = {
  transitional: C1,
  adapter: ["xhr", "http"],
  transformRequest: [function (t, s) {
    const o = s.getContentType() || "",
      i = o.indexOf("application/json") > -1,
      r = Z.isObject(t);
    if (r && Z.isHTMLForm(t) && (t = new FormData(t)), Z.isFormData(t)) return i ? JSON.stringify(A1(t)) : t;
    if (Z.isArrayBuffer(t) || Z.isBuffer(t) || Z.isStream(t) || Z.isFile(t) || Z.isBlob(t)) return t;
    if (Z.isArrayBufferView(t)) return t.buffer;
    if (Z.isURLSearchParams(t)) return s.setContentType("application/x-www-form-urlencoded;charset=utf-8", !1), t.toString();
    let l;
    if (r) {
      if (o.indexOf("application/x-www-form-urlencoded") > -1) return C3(t, this.formSerializer).toString();
      if ((l = Z.isFileList(t)) || o.indexOf("multipart/form-data") > -1) {
        const d = this.env && this.env.FormData;
        return hl(l ? {
          "files[]": t
        } : t, d && new d, this.formSerializer)
      }
    }
    return r || i ? (s.setContentType("application/json", !1), x3(t)) : t
  }],
  transformResponse: [function (t) {
    const s = this.transitional || Fu.transitional,
      o = s && s.forcedJSONParsing,
      i = this.responseType === "json";
    if (t && Z.isString(t) && (o && !this.responseType || i)) {
      const a = !(s && s.silentJSONParsing) && i;
      try {
        return JSON.parse(t)
      } catch (l) {
        if (a) throw l.name === "SyntaxError" ? Ye.from(l, Ye.ERR_BAD_RESPONSE, this, null, this.response) : l
      }
    }
    return t
  }],
  timeout: 0,
  xsrfCookieName: "XSRF-TOKEN",
  xsrfHeaderName: "X-XSRF-TOKEN",
  maxContentLength: -1,
  maxBodyLength: -1,
  env: {
    FormData: Ps.classes.FormData,
    Blob: Ps.classes.Blob
  },
  validateStatus: function (t) {
    return t >= 200 && t < 300
  },
  headers: {
    common: {
      Accept: "application/json, text/plain, */*",
      "Content-Type": void 0
    }
  }
};
Z.forEach(["delete", "get", "head", "post", "put", "patch"], e => {
  Fu.headers[e] = {}
});
const Uu = Fu,
  S3 = Z.toObjectSet(["age", "authorization", "content-length", "content-type", "etag", "expires", "from", "host", "if-modified-since", "if-unmodified-since", "last-modified", "location", "max-forwards", "proxy-authorization", "referer", "retry-after", "user-agent"]),
  E3 = e => {
    const t = {};
    let s, o, i;
    return e && e.split(`
`).forEach(function (a) {
      i = a.indexOf(":"), s = a.substring(0, i).trim().toLowerCase(), o = a.substring(i + 1).trim(), !(!s || t[s] && S3[s]) && (s === "set-cookie" ? t[s] ? t[s].push(o) : t[s] = [o] : t[s] = t[s] ? t[s] + ", " + o : o)
    }), t
  },
  Uh = Symbol("internals");

function ci(e) {
  return e && String(e).trim().toLowerCase()
}

function va(e) {
  return e === !1 || e == null ? e : Z.isArray(e) ? e.map(va) : String(e)
}

function P3(e) {
  const t = Object.create(null),
    s = /([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;
  let o;
  for (; o = s.exec(e);) t[o[1]] = o[2];
  return t
}
const T3 = e => /^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(e.trim());

function rd(e, t, s, o, i) {
  if (Z.isFunction(o)) return o.call(this, t, s);
  if (i && (t = s), !!Z.isString(t)) {
    if (Z.isString(o)) return t.indexOf(o) !== -1;
    if (Z.isRegExp(o)) return o.test(t)
  }
}

function L3(e) {
  return e.trim().toLowerCase().replace(/([a-z\d])(\w*)/g, (t, s, o) => s.toUpperCase() + o)
}

function O3(e, t) {
  const s = Z.toCamelCase(" " + t);
  ["get", "set", "has"].forEach(o => {
    Object.defineProperty(e, o + s, {
      value: function (i, r, a) {
        return this[o].call(this, t, i, r, a)
      },
      configurable: !0
    })
  })
}
class pl {
  constructor(t) {
    t && this.set(t)
  }
  set(t, s, o) {
    const i = this;

    function r(l, d, p) {
      const m = ci(d);
      if (!m) throw new Error("header name must be a non-empty string");
      const g = Z.findKey(i, m);
      (!g || i[g] === void 0 || p === !0 || p === void 0 && i[g] !== !1) && (i[g || d] = va(l))
    }
    const a = (l, d) => Z.forEach(l, (p, m) => r(p, m, d));
    return Z.isPlainObject(t) || t instanceof this.constructor ? a(t, s) : Z.isString(t) && (t = t.trim()) && !T3(t) ? a(E3(t), s) : t != null && r(s, t, o), this
  }
  get(t, s) {
    if (t = ci(t), t) {
      const o = Z.findKey(this, t);
      if (o) {
        const i = this[o];
        if (!s) return i;
        if (s === !0) return P3(i);
        if (Z.isFunction(s)) return s.call(this, i, o);
        if (Z.isRegExp(s)) return s.exec(i);
        throw new TypeError("parser must be boolean|regexp|function")
      }
    }
  }
  has(t, s) {
    if (t = ci(t), t) {
      const o = Z.findKey(this, t);
      return !!(o && this[o] !== void 0 && (!s || rd(this, this[o], o, s)))
    }
    return !1
  }
  delete(t, s) {
    const o = this;
    let i = !1;

    function r(a) {
      if (a = ci(a), a) {
        const l = Z.findKey(o, a);
        l && (!s || rd(o, o[l], l, s)) && (delete o[l], i = !0)
      }
    }
    return Z.isArray(t) ? t.forEach(r) : r(t), i
  }
  clear(t) {
    const s = Object.keys(this);
    let o = s.length,
      i = !1;
    for (; o--;) {
      const r = s[o];
      (!t || rd(this, this[r], r, t, !0)) && (delete this[r], i = !0)
    }
    return i
  }
  normalize(t) {
    const s = this,
      o = {};
    return Z.forEach(this, (i, r) => {
      const a = Z.findKey(o, r);
      if (a) {
        s[a] = va(i), delete s[r];
        return
      }
      const l = t ? L3(r) : String(r).trim();
      l !== r && delete s[r], s[l] = va(i), o[l] = !0
    }), this
  }
  concat(...t) {
    return this.constructor.concat(this, ...t)
  }
  toJSON(t) {
    const s = Object.create(null);
    return Z.forEach(this, (o, i) => {
      o != null && o !== !1 && (s[i] = t && Z.isArray(o) ? o.join(", ") : o)
    }), s
  } [Symbol.iterator]() {
    return Object.entries(this.toJSON())[Symbol.iterator]()
  }
  toString() {
    return Object.entries(this.toJSON()).map(([t, s]) => t + ": " + s).join(`
`)
  }
  get[Symbol.toStringTag]() {
    return "AxiosHeaders"
  }
  static from(t) {
    return t instanceof this ? t : new this(t)
  }
  static concat(t, ...s) {
    const o = new this(t);
    return s.forEach(i => o.set(i)), o
  }
  static accessor(t) {
    const o = (this[Uh] = this[Uh] = {
        accessors: {}
      }).accessors,
      i = this.prototype;

    function r(a) {
      const l = ci(a);
      o[l] || (O3(i, a), o[l] = !0)
    }
    return Z.isArray(t) ? t.forEach(r) : r(t), this
  }
}
pl.accessor(["Content-Type", "Content-Length", "Accept", "Accept-Encoding", "User-Agent", "Authorization"]);
Z.reduceDescriptors(pl.prototype, ({
  value: e
}, t) => {
  let s = t[0].toUpperCase() + t.slice(1);
  return {
    get: () => e,
    set(o) {
      this[s] = o
    }
  }
});
Z.freezeMethods(pl);
const Gs = pl;

function ad(e, t) {
  const s = this || Uu,
    o = t || s,
    i = Gs.from(o.headers);
  let r = o.data;
  return Z.forEach(e, function (l) {
    r = l.call(s, r, i.normalize(), t ? t.status : void 0)
  }), i.normalize(), r
}

function x1(e) {
  return !!(e && e.__CANCEL__)
}

function Ki(e, t, s) {
  Ye.call(this, e ? ? "canceled", Ye.ERR_CANCELED, t, s), this.name = "CanceledError"
}
Z.inherits(Ki, Ye, {
  __CANCEL__: !0
});

function I3(e, t, s) {
  const o = s.config.validateStatus;
  !s.status || !o || o(s.status) ? e(s) : t(new Ye("Request failed with status code " + s.status, [Ye.ERR_BAD_REQUEST, Ye.ERR_BAD_RESPONSE][Math.floor(s.status / 100) - 4], s.config, s.request, s))
}
const B3 = Ps.hasStandardBrowserEnv ? {
  write(e, t, s, o, i, r) {
    const a = [e + "=" + encodeURIComponent(t)];
    Z.isNumber(s) && a.push("expires=" + new Date(s).toGMTString()), Z.isString(o) && a.push("path=" + o), Z.isString(i) && a.push("domain=" + i), r === !0 && a.push("secure"), document.cookie = a.join("; ")
  },
  read(e) {
    const t = document.cookie.match(new RegExp("(^|;\\s*)(" + e + ")=([^;]*)"));
    return t ? decodeURIComponent(t[3]) : null
  },
  remove(e) {
    this.write(e, "", Date.now() - 864e5)
  }
} : {
  write() {},
  read() {
    return null
  },
  remove() {}
};

function D3(e) {
  return /^([a-z][a-z\d+\-.]*:)?\/\//i.test(e)
}

function M3(e, t) {
  return t ? e.replace(/\/?\/$/, "") + "/" + t.replace(/^\/+/, "") : e
}

function S1(e, t) {
  return e && !D3(t) ? M3(e, t) : t
}
const R3 = Ps.hasStandardBrowserEnv ? function () {
  const t = /(msie|trident)/i.test(navigator.userAgent),
    s = document.createElement("a");
  let o;

  function i(r) {
    let a = r;
    return t && (s.setAttribute("href", a), a = s.href), s.setAttribute("href", a), {
      href: s.href,
      protocol: s.protocol ? s.protocol.replace(/:$/, "") : "",
      host: s.host,
      search: s.search ? s.search.replace(/^\?/, "") : "",
      hash: s.hash ? s.hash.replace(/^#/, "") : "",
      hostname: s.hostname,
      port: s.port,
      pathname: s.pathname.charAt(0) === "/" ? s.pathname : "/" + s.pathname
    }
  }
  return o = i(window.location.href),
    function (a) {
      const l = Z.isString(a) ? i(a) : a;
      return l.protocol === o.protocol && l.host === o.host
    }
}() : function () {
  return function () {
    return !0
  }
}();

function N3(e) {
  const t = /^([-+\w]{1,25})(:?\/\/|:)/.exec(e);
  return t && t[1] || ""
}

function F3(e, t) {
  e = e || 10;
  const s = new Array(e),
    o = new Array(e);
  let i = 0,
    r = 0,
    a;
  return t = t !== void 0 ? t : 1e3,
    function (d) {
      const p = Date.now(),
        m = o[r];
      a || (a = p), s[i] = d, o[i] = p;
      let g = r,
        $ = 0;
      for (; g !== i;) $ += s[g++], g = g % e;
      if (i = (i + 1) % e, i === r && (r = (r + 1) % e), p - a < t) return;
      const C = m && p - m;
      return C ? Math.round($ * 1e3 / C) : void 0
    }
}

function Vh(e, t) {
  let s = 0;
  const o = F3(50, 250);
  return i => {
    const r = i.loaded,
      a = i.lengthComputable ? i.total : void 0,
      l = r - s,
      d = o(l),
      p = r <= a;
    s = r;
    const m = {
      loaded: r,
      total: a,
      progress: a ? r / a : void 0,
      bytes: l,
      rate: d || void 0,
      estimated: d && a && p ? (a - r) / d : void 0,
      event: i
    };
    m[t ? "download" : "upload"] = !0, e(m)
  }
}
const U3 = typeof XMLHttpRequest < "u",
  V3 = U3 && function (e) {
    return new Promise(function (s, o) {
      let i = e.data;
      const r = Gs.from(e.headers).normalize();
      let {
        responseType: a,
        withXSRFToken: l
      } = e, d;

      function p() {
        e.cancelToken && e.cancelToken.unsubscribe(d), e.signal && e.signal.removeEventListener("abort", d)
      }
      let m;
      if (Z.isFormData(i)) {
        if (Ps.hasStandardBrowserEnv || Ps.hasStandardBrowserWebWorkerEnv) r.setContentType(!1);
        else if ((m = r.getContentType()) !== !1) {
          const [O, ...V] = m ? m.split(";").map(M => M.trim()).filter(Boolean) : [];
          r.setContentType([O || "multipart/form-data", ...V].join("; "))
        }
      }
      let g = new XMLHttpRequest;
      if (e.auth) {
        const O = e.auth.username || "",
          V = e.auth.password ? unescape(encodeURIComponent(e.auth.password)) : "";
        r.set("Authorization", "Basic " + btoa(O + ":" + V))
      }
      const $ = S1(e.baseURL, e.url);
      g.open(e.method.toUpperCase(), $1($, e.params, e.paramsSerializer), !0), g.timeout = e.timeout;

      function C() {
        if (!g) return;
        const O = Gs.from("getAllResponseHeaders" in g && g.getAllResponseHeaders()),
          M = {
            data: !a || a === "text" || a === "json" ? g.responseText : g.response,
            status: g.status,
            statusText: g.statusText,
            headers: O,
            config: e,
            request: g
          };
        I3(function (P) {
          s(P), p()
        }, function (P) {
          o(P), p()
        }, M), g = null
      }
      if ("onloadend" in g ? g.onloadend = C : g.onreadystatechange = function () {
          !g || g.readyState !== 4 || g.status === 0 && !(g.responseURL && g.responseURL.indexOf("file:") === 0) || setTimeout(C)
        }, g.onabort = function () {
          g && (o(new Ye("Request aborted", Ye.ECONNABORTED, e, g)), g = null)
        }, g.onerror = function () {
          o(new Ye("Network Error", Ye.ERR_NETWORK, e, g)), g = null
        }, g.ontimeout = function () {
          let V = e.timeout ? "timeout of " + e.timeout + "ms exceeded" : "timeout exceeded";
          const M = e.transitional || C1;
          e.timeoutErrorMessage && (V = e.timeoutErrorMessage), o(new Ye(V, M.clarifyTimeoutError ? Ye.ETIMEDOUT : Ye.ECONNABORTED, e, g)), g = null
        }, Ps.hasStandardBrowserEnv && (l && Z.isFunction(l) && (l = l(e)), l || l !== !1 && R3($))) {
        const O = e.xsrfHeaderName && e.xsrfCookieName && B3.read(e.xsrfCookieName);
        O && r.set(e.xsrfHeaderName, O)
      }
      i === void 0 && r.setContentType(null), "setRequestHeader" in g && Z.forEach(r.toJSON(), function (V, M) {
        g.setRequestHeader(M, V)
      }), Z.isUndefined(e.withCredentials) || (g.withCredentials = !!e.withCredentials), a && a !== "json" && (g.responseType = e.responseType), typeof e.onDownloadProgress == "function" && g.addEventListener("progress", Vh(e.onDownloadProgress, !0)), typeof e.onUploadProgress == "function" && g.upload && g.upload.addEventListener("progress", Vh(e.onUploadProgress)), (e.cancelToken || e.signal) && (d = O => {
        g && (o(!O || O.type ? new Ki(null, e, g) : O), g.abort(), g = null)
      }, e.cancelToken && e.cancelToken.subscribe(d), e.signal && (e.signal.aborted ? d() : e.signal.addEventListener("abort", d)));
      const A = N3($);
      if (A && Ps.protocols.indexOf(A) === -1) {
        o(new Ye("Unsupported protocol " + A + ":", Ye.ERR_BAD_REQUEST, e));
        return
      }
      g.send(i || null)
    })
  },
  Kd = {
    http: f3,
    xhr: V3
  };
Z.forEach(Kd, (e, t) => {
  if (e) {
    try {
      Object.defineProperty(e, "name", {
        value: t
      })
    } catch {}
    Object.defineProperty(e, "adapterName", {
      value: t
    })
  }
});
const jh = e => `- ${e}`,
  j3 = e => Z.isFunction(e) || e === null || e === !1,
  E1 = {
    getAdapter: e => {
      e = Z.isArray(e) ? e : [e];
      const {
        length: t
      } = e;
      let s, o;
      const i = {};
      for (let r = 0; r < t; r++) {
        s = e[r];
        let a;
        if (o = s, !j3(s) && (o = Kd[(a = String(s)).toLowerCase()], o === void 0)) throw new Ye(`Unknown adapter '${a}'`);
        if (o) break;
        i[a || "#" + r] = o
      }
      if (!o) {
        const r = Object.entries(i).map(([l, d]) => `adapter ${l} ` + (d === !1 ? "is not supported by the environment" : "is not available in the build"));
        let a = t ? r.length > 1 ? `since :
` + r.map(jh).join(`
`) : " " + jh(r[0]) : "as no adapter specified";
        throw new Ye("There is no suitable adapter to dispatch the request " + a, "ERR_NOT_SUPPORT")
      }
      return o
    },
    adapters: Kd
  };

function ld(e) {
  if (e.cancelToken && e.cancelToken.throwIfRequested(), e.signal && e.signal.aborted) throw new Ki(null, e)
}

function Hh(e) {
  return ld(e), e.headers = Gs.from(e.headers), e.data = ad.call(e, e.transformRequest), ["post", "put", "patch"].indexOf(e.method) !== -1 && e.headers.setContentType("application/x-www-form-urlencoded", !1), E1.getAdapter(e.adapter || Uu.adapter)(e).then(function (o) {
    return ld(e), o.data = ad.call(e, e.transformResponse, o), o.headers = Gs.from(o.headers), o
  }, function (o) {
    return x1(o) || (ld(e), o && o.response && (o.response.data = ad.call(e, e.transformResponse, o.response), o.response.headers = Gs.from(o.response.headers))), Promise.reject(o)
  })
}
const qh = e => e instanceof Gs ? {
  ...e
} : e;

function Lo(e, t) {
  t = t || {};
  const s = {};

  function o(p, m, g) {
    return Z.isPlainObject(p) && Z.isPlainObject(m) ? Z.merge.call({
      caseless: g
    }, p, m) : Z.isPlainObject(m) ? Z.merge({}, m) : Z.isArray(m) ? m.slice() : m
  }

  function i(p, m, g) {
    if (Z.isUndefined(m)) {
      if (!Z.isUndefined(p)) return o(void 0, p, g)
    } else return o(p, m, g)
  }

  function r(p, m) {
    if (!Z.isUndefined(m)) return o(void 0, m)
  }

  function a(p, m) {
    if (Z.isUndefined(m)) {
      if (!Z.isUndefined(p)) return o(void 0, p)
    } else return o(void 0, m)
  }

  function l(p, m, g) {
    if (g in t) return o(p, m);
    if (g in e) return o(void 0, p)
  }
  const d = {
    url: r,
    method: r,
    data: r,
    baseURL: a,
    transformRequest: a,
    transformResponse: a,
    paramsSerializer: a,
    timeout: a,
    timeoutMessage: a,
    withCredentials: a,
    withXSRFToken: a,
    adapter: a,
    responseType: a,
    xsrfCookieName: a,
    xsrfHeaderName: a,
    onUploadProgress: a,
    onDownloadProgress: a,
    decompress: a,
    maxContentLength: a,
    maxBodyLength: a,
    beforeRedirect: a,
    transport: a,
    httpAgent: a,
    httpsAgent: a,
    cancelToken: a,
    socketPath: a,
    responseEncoding: a,
    validateStatus: l,
    headers: (p, m) => i(qh(p), qh(m), !0)
  };
  return Z.forEach(Object.keys(Object.assign({}, e, t)), function (m) {
    const g = d[m] || i,
      $ = g(e[m], t[m], m);
    Z.isUndefined($) && g !== l || (s[m] = $)
  }), s
}
const P1 = "1.6.8",
  Vu = {};
["object", "boolean", "number", "function", "string", "symbol"].forEach((e, t) => {
  Vu[e] = function (o) {
    return typeof o === e || "a" + (t < 1 ? "n " : " ") + e
  }
});
const Wh = {};
Vu.transitional = function (t, s, o) {
  function i(r, a) {
    return "[Axios v" + P1 + "] Transitional option '" + r + "'" + a + (o ? ". " + o : "")
  }
  return (r, a, l) => {
    if (t === !1) throw new Ye(i(a, " has been removed" + (s ? " in " + s : "")), Ye.ERR_DEPRECATED);
    return s && !Wh[a] && (Wh[a] = !0, console.warn(i(a, " has been deprecated since v" + s + " and will be removed in the near future"))), t ? t(r, a, l) : !0
  }
};

function H3(e, t, s) {
  if (typeof e != "object") throw new Ye("options must be an object", Ye.ERR_BAD_OPTION_VALUE);
  const o = Object.keys(e);
  let i = o.length;
  for (; i-- > 0;) {
    const r = o[i],
      a = t[r];
    if (a) {
      const l = e[r],
        d = l === void 0 || a(l, r, e);
      if (d !== !0) throw new Ye("option " + r + " must be " + d, Ye.ERR_BAD_OPTION_VALUE);
      continue
    }
    if (s !== !0) throw new Ye("Unknown option " + r, Ye.ERR_BAD_OPTION)
  }
}
const Gd = {
    assertOptions: H3,
    validators: Vu
  },
  hn = Gd.validators;
class Ma {
  constructor(t) {
    this.defaults = t, this.interceptors = {
      request: new Fh,
      response: new Fh
    }
  }
  async request(t, s) {
    try {
      return await this._request(t, s)
    } catch (o) {
      if (o instanceof Error) {
        let i;
        Error.captureStackTrace ? Error.captureStackTrace(i = {}) : i = new Error;
        const r = i.stack ? i.stack.replace(/^.+\n/, "") : "";
        o.stack ? r && !String(o.stack).endsWith(r.replace(/^.+\n.+\n/, "")) && (o.stack += `
` + r) : o.stack = r
      }
      throw o
    }
  }
  _request(t, s) {
    typeof t == "string" ? (s = s || {}, s.url = t) : s = t || {}, s = Lo(this.defaults, s);
    const {
      transitional: o,
      paramsSerializer: i,
      headers: r
    } = s;
    o !== void 0 && Gd.assertOptions(o, {
      silentJSONParsing: hn.transitional(hn.boolean),
      forcedJSONParsing: hn.transitional(hn.boolean),
      clarifyTimeoutError: hn.transitional(hn.boolean)
    }, !1), i != null && (Z.isFunction(i) ? s.paramsSerializer = {
      serialize: i
    } : Gd.assertOptions(i, {
      encode: hn.function,
      serialize: hn.function
    }, !0)), s.method = (s.method || this.defaults.method || "get").toLowerCase();
    let a = r && Z.merge(r.common, r[s.method]);
    r && Z.forEach(["delete", "get", "head", "post", "put", "patch", "common"], A => {
      delete r[A]
    }), s.headers = Gs.concat(a, r);
    const l = [];
    let d = !0;
    this.interceptors.request.forEach(function (O) {
      typeof O.runWhen == "function" && O.runWhen(s) === !1 || (d = d && O.synchronous, l.unshift(O.fulfilled, O.rejected))
    });
    const p = [];
    this.interceptors.response.forEach(function (O) {
      p.push(O.fulfilled, O.rejected)
    });
    let m, g = 0,
      $;
    if (!d) {
      const A = [Hh.bind(this), void 0];
      for (A.unshift.apply(A, l), A.push.apply(A, p), $ = A.length, m = Promise.resolve(s); g < $;) m = m.then(A[g++], A[g++]);
      return m
    }
    $ = l.length;
    let C = s;
    for (g = 0; g < $;) {
      const A = l[g++],
        O = l[g++];
      try {
        C = A(C)
      } catch (V) {
        O.call(this, V);
        break
      }
    }
    try {
      m = Hh.call(this, C)
    } catch (A) {
      return Promise.reject(A)
    }
    for (g = 0, $ = p.length; g < $;) m = m.then(p[g++], p[g++]);
    return m
  }
  getUri(t) {
    t = Lo(this.defaults, t);
    const s = S1(t.baseURL, t.url);
    return $1(s, t.params, t.paramsSerializer)
  }
}
Z.forEach(["delete", "get", "head", "options"], function (t) {
  Ma.prototype[t] = function (s, o) {
    return this.request(Lo(o || {}, {
      method: t,
      url: s,
      data: (o || {}).data
    }))
  }
});
Z.forEach(["post", "put", "patch"], function (t) {
  function s(o) {
    return function (r, a, l) {
      return this.request(Lo(l || {}, {
        method: t,
        headers: o ? {
          "Content-Type": "multipart/form-data"
        } : {},
        url: r,
        data: a
      }))
    }
  }
  Ma.prototype[t] = s(), Ma.prototype[t + "Form"] = s(!0)
});
const ya = Ma;
class ju {
  constructor(t) {
    if (typeof t != "function") throw new TypeError("executor must be a function.");
    let s;
    this.promise = new Promise(function (r) {
      s = r
    });
    const o = this;
    this.promise.then(i => {
      if (!o._listeners) return;
      let r = o._listeners.length;
      for (; r-- > 0;) o._listeners[r](i);
      o._listeners = null
    }), this.promise.then = i => {
      let r;
      const a = new Promise(l => {
        o.subscribe(l), r = l
      }).then(i);
      return a.cancel = function () {
        o.unsubscribe(r)
      }, a
    }, t(function (r, a, l) {
      o.reason || (o.reason = new Ki(r, a, l), s(o.reason))
    })
  }
  throwIfRequested() {
    if (this.reason) throw this.reason
  }
  subscribe(t) {
    if (this.reason) {
      t(this.reason);
      return
    }
    this._listeners ? this._listeners.push(t) : this._listeners = [t]
  }
  unsubscribe(t) {
    if (!this._listeners) return;
    const s = this._listeners.indexOf(t);
    s !== -1 && this._listeners.splice(s, 1)
  }
  static source() {
    let t;
    return {
      token: new ju(function (i) {
        t = i
      }),
      cancel: t
    }
  }
}
const q3 = ju;

function W3(e) {
  return function (s) {
    return e.apply(null, s)
  }
}

function z3(e) {
  return Z.isObject(e) && e.isAxiosError === !0
}
const Yd = {
  Continue: 100,
  SwitchingProtocols: 101,
  Processing: 102,
  EarlyHints: 103,
  Ok: 200,
  Created: 201,
  Accepted: 202,
  NonAuthoritativeInformation: 203,
  NoContent: 204,
  ResetContent: 205,
  PartialContent: 206,
  MultiStatus: 207,
  AlreadyReported: 208,
  ImUsed: 226,
  MultipleChoices: 300,
  MovedPermanently: 301,
  Found: 302,
  SeeOther: 303,
  NotModified: 304,
  UseProxy: 305,
  Unused: 306,
  TemporaryRedirect: 307,
  PermanentRedirect: 308,
  BadRequest: 400,
  Unauthorized: 401,
  PaymentRequired: 402,
  Forbidden: 403,
  NotFound: 404,
  MethodNotAllowed: 405,
  NotAcceptable: 406,
  ProxyAuthenticationRequired: 407,
  RequestTimeout: 408,
  Conflict: 409,
  Gone: 410,
  LengthRequired: 411,
  PreconditionFailed: 412,
  PayloadTooLarge: 413,
  UriTooLong: 414,
  UnsupportedMediaType: 415,
  RangeNotSatisfiable: 416,
  ExpectationFailed: 417,
  ImATeapot: 418,
  MisdirectedRequest: 421,
  UnprocessableEntity: 422,
  Locked: 423,
  FailedDependency: 424,
  TooEarly: 425,
  UpgradeRequired: 426,
  PreconditionRequired: 428,
  TooManyRequests: 429,
  RequestHeaderFieldsTooLarge: 431,
  UnavailableForLegalReasons: 451,
  InternalServerError: 500,
  NotImplemented: 501,
  BadGateway: 502,
  ServiceUnavailable: 503,
  GatewayTimeout: 504,
  HttpVersionNotSupported: 505,
  VariantAlsoNegotiates: 506,
  InsufficientStorage: 507,
  LoopDetected: 508,
  NotExtended: 510,
  NetworkAuthenticationRequired: 511
};
Object.entries(Yd).forEach(([e, t]) => {
  Yd[t] = e
});
const K3 = Yd;

function T1(e) {
  const t = new ya(e),
    s = d1(ya.prototype.request, t);
  return Z.extend(s, ya.prototype, t, {
    allOwnKeys: !0
  }), Z.extend(s, t, null, {
    allOwnKeys: !0
  }), s.create = function (i) {
    return T1(Lo(e, i))
  }, s
}
const ye = T1(Uu);
ye.Axios = ya;
ye.CanceledError = Ki;
ye.CancelToken = q3;
ye.isCancel = x1;
ye.VERSION = P1;
ye.toFormData = hl;
ye.AxiosError = Ye;
ye.Cancel = ye.CanceledError;
ye.all = function (t) {
  return Promise.all(t)
};
ye.spread = W3;
ye.isAxiosError = z3;
ye.mergeConfig = Lo;
ye.AxiosHeaders = Gs;
ye.formToJSON = e => A1(Z.isHTMLForm(e) ? new FormData(e) : e);
ye.getAdapter = E1.getAdapter;
ye.HttpStatusCode = K3;
ye.default = ye;
const L1 = "/assets/acc_1-C2fJtTxx.png",
  O1 = "/assets/ben_02-B1Ge88fr.png",
  I1 = "/assets/ben_03-BN5PXRiM.png",
  B1 = "/assets/ben_04-DW8jCzk8.png",
  G3 = {
    props: {
      color1: String,
      color2: String
    },
    computed: {
      backgroundStyle() {
        return {
          background: `linear-gradient(45deg, ${this.color1}, ${this.color2}) !important`
        }
      }
    }
  },
  Y3 = {
    class: "p-3 px-lg-5 text-center"
  },
  J3 = {
    id: "carouselExampleIndicators",
    class: "carousel slide",
    "data-bs-ride": "carousel"
  },
  Z3 = {
    class: "carousel-inner"
  },
  X3 = {
    class: "carousel-item active"
  },
  Q3 = Ae('<img src="' + L1 + '" alt="user-image" class="hei-150 mb-3"><h5 class="text-white mb-0">Regulatory Excellence</h5><p class="text-white text-opacity-50">Compliance Assurance</p><div class="star f-20 my-4"><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star-half-alt text-warning"></i></div>', 4),
  e4 = {
    class: "text-white"
  },
  t4 = {
    class: "carousel-item"
  },
  s4 = Ae('<img src="' + O1 + '" alt="user-image" class="hei-150 mb-3"><h5 class="text-white mb-0">Transparent Pricing Policy</h5><p class="text-white text-opacity-50">Clear Cost Commitment</p><div class="star f-20 my-4"><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star-half-alt text-warning"></i></div>', 4),
  n4 = {
    class: "text-white"
  },
  o4 = {
    class: "carousel-item"
  },
  i4 = Ae('<img src="' + I1 + '" alt="user-image" class="hei-150 mb-3"><h5 class="text-white mb-0">Swift and Precise Execution</h5><p class="text-white text-opacity-50">Precision Trading</p><div class="star f-20 my-4"><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star-half-alt text-warning"></i></div>', 4),
  r4 = {
    class: "text-white"
  },
  a4 = {
    class: "carousel-item"
  },
  l4 = Ae('<img src="' + B1 + '" alt="user-image" class="hei-150 mb-3"><h5 class="text-white mb-0">Competitive Spreads</h5><p class="text-white text-opacity-50">Cost Efficiency</p><div class="star f-20 my-4"><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i class="fas fa-star-half-alt text-warning"></i></div>', 4),
  c4 = {
    class: "text-white"
  },
  d4 = Ae('<div class="carousel-indicators position-relative mt-3"><button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button><button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button><button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button><button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3" aria-label="Slide 4"></button></div>', 1);

function u4(e, t, s, o, i, r) {
  return _(), w("div", {
    class: "auth-sidecontent",
    style: Ro(r.backgroundStyle)
  }, [n("div", Y3, [n("div", J3, [n("div", Z3, [n("div", X3, [Q3, n("p", e4, " With meticulous attention to regulatory standards, " + L(this.companyName) + " guarantees compliance assurance, fostering transparency and confidence among traders with a commitment to ethical conduct and integrity. ", 1)]), n("div", t4, [s4, n("p", n4, " At " + L(this.companyName) + ", transparency is paramount. Our pricing policy ensures clarity and fairness, empowering traders with transparent pricing structures and no hidden fees for a seamless trading experience. ", 1)]), n("div", o4, [i4, n("p", r4, " Experience lightning-fast trade execution with " + L(this.companyName) + ". Our advanced trading infrastructure ensures swift and precise order processing, enabling traders to capitalize on market opportunities instantly. ", 1)]), n("div", a4, [l4, n("p", c4, " Gain a competitive edge with " + L(this.companyName) + "' tight spreads. Our low-cost advantage ensures competitive pricing on all trading instruments, allowing traders to maximize profitability and minimize trading costs. ", 1)])]), d4])])], 4)
}
const Hu = je(G3, [
    ["render", u4]
  ]),
  ml = "/assets/succedo_logo_dark-fOzlUXub.svg",
  Gi = "/assets/bitrage_dark_logo-CLyWBJgQ.png",
  gl = "/assets/paperboat_dark_logo-Sf1vwF63.png",
  f4 = {
    data() {
      return {
        view_login_view: !0,
        view_forgot_email_input: !1,
        view_otp_input: !1,
        view_new_pass_input: !1,
        is_reset_verified: !1,
        isLoading: !1,
        OTPLoading: !1,
        reset_email: "",
        email: "",
        password: "",
        new_password: "",
        confirmPassword: "",
        otp: ["", "", "", "", "", ""],
        errors: {
          email: "",
          password: "",
          new_password: "",
          confirmPassword: "",
          reset_email: ""
        }
      }
    },
    computed: {
      otpDigits() {
        return Array.from({
          length: 6
        })
      }
    },
    components: {
      BannerContentView: Hu
    },
    mounted() {},
    methods: {
      focusNextInput(e) {
        e < 5 && this.otp[e] !== "" && this.$refs[`otpInput${e+1}`][0].focus()
      },
      async submitOTP() {
        var t;
        const e = this.otp.join("");
        if (this.errors.new_password = "", this.errors.confirmPassword = "", this.validatePassword(), e.length === 6 && !this.errors.new_password && !this.errors.confirmPassword) {
          try {
            const s = $e.get("authToken"),
              o = await ye.post(this.API_BASE_URL + "/profile/reset-pass/validate-email", {
                otp: e,
                email: this.reset_email,
                password: this.new_password
              }, {
                headers: {
                  Authorization: `Bearer ${s}`
                }
              });
            o && o.status && (this.view_otp_input = !1, this.is_reset_verified = !0, Swal.fire({
              icon: "success",
              title: o == null ? void 0 : o.data.message
            }).then(i => {
              i.isConfirmed && window.location.reload()
            }))
          } catch (s) {
            this.is_reset_verified = !1, Swal.fire({
              icon: "error",
              title: (t = s.response) == null ? void 0 : t.data.message
            }).then(o => {
              o.isConfirmed && window.location.reload()
            })
          }
          console.log("Submitting OTP:", e)
        }
      },
      validatePassword() {
        this.errors.new_password = "", this.errors.confirmPassword = "";
        const t = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/.test(this.new_password),
          s = this.new_password === this.confirmPassword;
        t ? s ? (this.errors.new_password = "", this.errors.confirmPassword = "") : this.errors.confirmPassword = "Passwords do not match." : this.errors.new_password = "Password must contain at least 8 characters, including 1 uppercase letter, 1 lowercase letter, 1 digit, and 1 special character."
      },
      updateIsForgot() {
        this.view_login_view = !1, this.view_forgot_email_input = !0
      },
      backToLogin() {
        this.view_forgot_email_input = !1, this.view_login_view = !0
      },
      async sendOTP() {
        var e;
        try {
          this.OTPLoading = !0, (await ye.post(this.API_BASE_URL + "/profile/passupdate-send-otp", {
            email: this.reset_email
          })).data && (this.OTPLoading = !1, this.view_forgot_email_input = !1, this.view_login_view = !1, this.view_otp_input = !0)
        } catch (t) {
          Swal.fire({
            icon: "error",
            title: (e = t.response) == null ? void 0 : e.data.message
          }).then(s => {
            s.isConfirmed && window.location.reload()
          }), this.isLoading = !1
        }
      },
      validateEmail() {
        const t = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email);
        this.errors.email = t ? "" : "Please enter a valid email address."
      },
      handleSubmitClick() {
        this.errors.email = "", this.validateEmail(), this.errors.email || (this.isLoading = !0, this.loginClient())
      },
      async loginClient() {
        var e;
        try {
          const t = await ye.post(this.API_BASE_URL + "/login", {
            email: this.email,
            password: this.password
          });
          t.data && ($e.set("authToken", t.data.result.token, {
            expires: 3
          }), localStorage.setItem("auth_id", t.data.result.client.id), localStorage.setItem("client_name", t.data.result.client.client_name), localStorage.setItem("email", t.data.result.client.email), localStorage.setItem("nationality", t.data.result.client.nationality), localStorage.setItem("contact_no", t.data.result.client.contact_no), localStorage.setItem("is_ib", t.data.result.client.is_ib), localStorage.setItem("email_verified", t.data.result.client.email_verified), localStorage.setItem("doc_verified", t.data.result.client.doc_verified), os.push("/dashboard"))
        } catch (t) {
          Swal.fire({
            icon: "error",
            title: (e = t.response) == null ? void 0 : e.data.message
          }).then(s => {
            s.isConfirmed && window.location.reload()
          }), this.isLoading = !1
        }
      },
      validateEmail() {
        const t = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email);
        this.errors.email = t ? "" : "Please enter a valid email address."
      }
    }
  },
  Jt = e => (No("data-v-dde07c83"), e = e(), Fo(), e),
  h4 = {
    class: "auth-main"
  },
  p4 = {
    class: "auth-wrapper v3"
  },
  m4 = {
    class: "auth-form"
  },
  g4 = {
    class: "auth-header row"
  },
  _4 = {
    class: "col my-1"
  },
  b4 = {
    href: "#"
  },
  v4 = {
    key: 0,
    src: ml,
    alt: "Succedo Logo",
    style: {
      height: "8vh"
    }
  },
  y4 = {
    key: 1,
    src: Gi,
    alt: "Bitrage Logo",
    style: {
      height: "8vh"
    }
  },
  w4 = {
    key: 2,
    src: gl,
    alt: "Paperboat Logo",
    style: {
      width: "200px"
    }
  },
  $4 = {
    class: "card my-3"
  },
  C4 = {
    class: "card-body"
  },
  k4 = Jt(() => n("ul", {
    class: "nav nav-tabs d-none",
    id: "myTab",
    role: "tablist"
  }, [n("li", {
    class: "nav-item"
  }, [n("a", {
    class: "nav-link",
    id: "auth-tab-4",
    "data-bs-toggle": "tab",
    href: "#auth-4",
    role: "tab",
    "data-slide-index": "4",
    "aria-controls": "auth-4",
    "aria-selected": "true"
  })])], -1)),
  A4 = {
    key: 0,
    class: "tab-content"
  },
  x4 = {
    class: "tab-pane show active",
    id: "auth-4",
    role: "tabpanel",
    "aria-labelledby": "auth-tab-4"
  },
  S4 = Jt(() => n("div", {
    class: "text-center"
  }, [n("h3", {
    class: "text-center mb-3"
  }, "Login"), n("p", {
    class: "mb-4 fs-6"
  }, " Please log in to access your profile and manage your trading accounts and settings. ")], -1)),
  E4 = {
    class: "row mt-4"
  },
  P4 = {
    class: "col-12"
  },
  T4 = {
    class: "form-group"
  },
  L4 = Jt(() => n("label", {
    class: "form-label"
  }, "Email id", -1)),
  O4 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  I4 = {
    class: "col-12"
  },
  B4 = {
    class: "form-group"
  },
  D4 = Jt(() => n("label", {
    class: "form-label"
  }, "Password", -1)),
  M4 = {
    class: "d-flex justify-content-between align-items-center"
  },
  R4 = Jt(() => n("div", {
    class: "form-check"
  }, [n("input", {
    class: "form-check-input input-primary",
    type: "checkbox",
    id: "customCheckc1",
    checked: ""
  }), n("label", {
    class: "form-check-label text-muted",
    for: "customCheckc1"
  }, "Remember me?")], -1)),
  N4 = {
    class: "text-secondary f-w-400 mb-0"
  },
  F4 = {
    class: "row g-3 mt-1"
  },
  U4 = {
    class: "col-sm-12"
  },
  V4 = {
    class: "d-grid"
  },
  j4 = ["disabled"],
  H4 = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  q4 = {
    class: "col-sm-12"
  },
  W4 = {
    class: "d-grid"
  },
  z4 = {
    key: 1,
    class: "card my-5"
  },
  K4 = {
    class: "card-body"
  },
  G4 = {
    class: "d-flex justify-content-between align-items-end mb-3"
  },
  Y4 = Jt(() => n("h3", {
    class: "mb-2"
  }, [n("b", null, "Forgot Password")], -1)),
  J4 = Jt(() => n("p", {
    class: "mt-2 text-sm text-muted"
  }, "Enter your email address below, and we'll send you an OTP to verify your identity and reset your password.", -1)),
  Z4 = {
    class: "form-group mb-3"
  },
  X4 = Jt(() => n("label", {
    class: "form-label"
  }, "Email Address", -1)),
  Q4 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  e5 = {
    class: "d-grid mt-3"
  },
  t5 = ["disabled"],
  s5 = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  n5 = {
    key: 2
  },
  o5 = {
    class: "row"
  },
  i5 = {
    class: "col-auto"
  },
  r5 = Jt(() => n("h6", {
    class: "mb-3"
  }, "Please enter the 6-digit OTP", -1)),
  a5 = {
    class: "d-flex"
  },
  l5 = ["onUpdate:modelValue", "onInput"],
  c5 = {
    class: "form-group mb-3"
  },
  d5 = Jt(() => n("label", {
    class: "form-label"
  }, "Password", -1)),
  u5 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  f5 = {
    class: "form-group mb-3"
  },
  h5 = Jt(() => n("label", {
    class: "form-label"
  }, "Confirm Password", -1)),
  p5 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  m5 = ["disabled"],
  g5 = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  _5 = {
    class: "auth-footer"
  },
  b5 = {
    class: "m-0 w-100 text-center",
    style: {
      "font-size": "11px"
    }
  },
  v5 = Jt(() => n("a", {
    target: "_blank",
    href: "https://succedomarkets.com/wp-content/uploads/2024/02/Privacy-Policy.pdf"
  }, "Privacy Policy,", -1)),
  y5 = Jt(() => n("a", {
    target: "_blank",
    href: "https://succedomarkets.com/wp-content/uploads/2024/02/Deposit-and-Withdrawal-Policy.pdf"
  }, "Transaction Policy", -1)),
  w5 = Jt(() => n("a", {
    target: "_blank",
    href: "https://succedomarkets.com/wp-content/uploads/2024/02/Trading-Risk-Warning.pdf"
  }, "Risk Warning", -1));

function $5(e, t, s, o, i, r) {
  const a = ke("BannerContentView");
  return _(), w("div", h4, [n("div", p4, [n("div", m4, [n("div", g4, [n("div", _4, [n("a", b4, [this.companySlug === "succedo" ? (_(), w("img", v4)) : D("", !0), this.companySlug === "bitrage" ? (_(), w("img", y4)) : D("", !0), this.companySlug === "paperboat" ? (_(), w("img", w4)) : D("", !0)])])]), n("div", $4, [n("div", C4, [k4, i.view_login_view ? (_(), w("div", A4, [n("div", x4, [S4, n("div", E4, [n("div", P4, [n("div", T4, [L4, be(n("input", {
    "onUpdate:modelValue": t[0] || (t[0] = l => i.email = l),
    type: "email",
    class: "form-control",
    placeholder: "Email id",
    required: ""
  }, null, 512), [
    [Re, i.email]
  ]), i.errors.email ? (_(), w("div", O4, L(i.errors.email), 1)) : D("", !0)])]), n("div", I4, [n("div", B4, [D4, be(n("input", {
    "onUpdate:modelValue": t[1] || (t[1] = l => i.password = l),
    type: "password",
    class: "form-control",
    placeholder: "Password",
    required: ""
  }, null, 512), [
    [Re, i.password]
  ])])])]), n("div", M4, [R4, n("h6", N4, [n("a", {
    onClick: t[2] || (t[2] = (...l) => r.updateIsForgot && r.updateIsForgot(...l)),
    href: "#",
    class: "link-primary"
  }, "Forgot Password?")])]), n("div", F4, [n("div", U4, [n("div", V4, [n("button", {
    class: "btn btn-primary",
    type: "button",
    disabled: i.isLoading,
    onClick: t[3] || (t[3] = (...l) => r.handleSubmitClick && r.handleSubmitClick(...l))
  }, [i.isLoading ? (_(), w("span", H4)) : D("", !0), J(" " + L(i.isLoading ? "Logging In..." : "Login"), 1)], 8, j4)])]), n("div", q4, [n("div", W4, [n("button", {
    class: "btn btn-outline-secondary",
    onClick: t[4] || (t[4] = l => e.$router.push("/register"))
  }, " Register ")])])])])])) : D("", !0), i.view_forgot_email_input ? (_(), w("div", z4, [n("div", K4, [n("div", G4, [Y4, n("a", {
    onClick: t[5] || (t[5] = (...l) => r.backToLogin && r.backToLogin(...l)),
    href: "#",
    class: "link-primary mb-2"
  }, "Back to Login")]), J4, n("div", Z4, [X4, be(n("input", {
    "onUpdate:modelValue": t[6] || (t[6] = l => i.reset_email = l),
    type: "email",
    class: "form-control",
    id: "floatingInput",
    placeholder: "Email Address"
  }, null, 512), [
    [Re, i.reset_email]
  ]), i.errors.reset_email ? (_(), w("div", Q4, L(i.errors.reset_email), 1)) : D("", !0)]), n("div", e5, [n("button", {
    class: "btn btn-primary",
    type: "button",
    disabled: i.OTPLoading,
    onClick: t[7] || (t[7] = (...l) => r.sendOTP && r.sendOTP(...l))
  }, [i.OTPLoading ? (_(), w("span", s5)) : D("", !0), J(" " + L(i.OTPLoading ? "Sending..." : "Send OTP"), 1)], 8, t5)])])])) : D("", !0), i.view_otp_input ? (_(), w("div", n5, [n("div", o5, [n("div", i5, [r5, n("div", a5, [(_(!0), w(Te, null, Je(r.otpDigits, (l, d) => be((_(), w("input", {
    key: d,
    type: "text",
    class: "form-control otp-input",
    "onUpdate:modelValue": p => i.otp[d] = p,
    maxlength: "1",
    onInput: p => r.focusNextInput(d),
    ref_for: !0,
    ref: `otpInput${d}`
  }, null, 40, l5)), [
    [Re, i.otp[d]]
  ])), 128))]), n("div", c5, [d5, be(n("input", {
    "onUpdate:modelValue": t[8] || (t[8] = l => i.new_password = l),
    type: "password",
    class: "form-control",
    id: "floatingInput",
    placeholder: "Password"
  }, null, 512), [
    [Re, i.new_password]
  ]), i.errors.new_password ? (_(), w("div", u5, L(i.errors.new_password), 1)) : D("", !0)]), n("div", f5, [h5, be(n("input", {
    "onUpdate:modelValue": t[9] || (t[9] = l => i.confirmPassword = l),
    type: "password",
    class: "form-control",
    id: "floatingInput1",
    placeholder: "Confirm Password"
  }, null, 512), [
    [Re, i.confirmPassword]
  ]), i.errors.confirmPassword ? (_(), w("div", p5, L(i.errors.confirmPassword), 1)) : D("", !0)]), n("button", {
    class: "btn btn-primary mt-4",
    type: "button",
    disabled: e.verifyLoading,
    onClick: t[10] || (t[10] = (...l) => r.submitOTP && r.submitOTP(...l))
  }, [e.verifyLoading ? (_(), w("span", g5)) : D("", !0), J(" " + L(e.verifyLoading ? "Verifying..." : "Verify & Update Password"), 1)], 8, m5)])])])) : D("", !0)])]), n("div", _5, [n("p", b5, [J(" By logging in, you confirm that you have read and agree to " + L(this.COMPANY_NAME) + "'s ", 1), v5, y5, J(" and "), w5, J(". ")])])]), this.COMPANY_SLUG == "succedo" ? (_(), ve(a, {
    key: 0,
    color1: "#19184C",
    color2: "#F4A01E"
  })) : D("", !0), this.COMPANY_SLUG == "bitrage" ? (_(), ve(a, {
    key: 1,
    color1: "#2ea094",
    color2: "#b3e5b4"
  })) : D("", !0), this.COMPANY_SLUG == "paperboat" ? (_(), ve(a, {
    key: 2,
    color1: "#2ea094",
    color2: "#b3e5b4"
  })) : D("", !0)])])
}
const C5 = je(f4, [
    ["render", $5],
    ["__scopeId", "data-v-dde07c83"]
  ]),
  k5 = {
    data() {
      return {
        isLoading: !1,
        client_data: "",
        countries: [],
        email: "",
        password: "",
        confirmPassword: "",
        fname: "",
        phone: "",
        nationality: "",
        promoCode: "",
        errors: {
          email: "",
          password: "",
          confirmPassword: "",
          fname: "",
          phone: "",
          nationality: "",
          promoCode: ""
        },
        phone: ""
      }
    },
    watch: {},
    components: {
      VueTelInput: zm,
      RouterLink: Mu,
      BannerContentView: Hu
    },
    mounted() {
      const e = document.getElementById("vs1__combobox");
      this.promoCode = $e.get("promoCode"), e && (e.style.border = "0"), document.querySelector(".auth-conf").addEventListener("click", function () {
        Swal.fire({
          icon: "success",
          title: "Account created successfully"
        })
      })
    },
    beforeMount() {
      this.get_countires(), this.promoCode = $e.get("promoCode")
    },
    methods: {
      handleSubmitClick() {
        this.errors.fname = "", this.errors.phone = "", this.errors.nationality = "", this.validateFname(), this.validatePhone(), this.validateNationality(), !this.errors.fname && !this.errors.phone && !this.errors.nationality && (this.isLoading = !0, this.registerClient())
      },
      async registerClient() {
        var e;
        try {
          const t = await ye.post(this.API_BASE_URL + "/register", {
            fname: this.fname,
            email_id: this.email,
            is_email_verified: 0,
            password: this.password,
            nationality: this.nationality.id,
            phone_number: this.phone,
            client_entity: 1,
            referal_code: this.promoCode
          });
          t.data && ($e.set("authToken", t.data.result.token, {
            expires: 3
          }), localStorage.setItem("auth_id", t.data.result.client.id), localStorage.setItem("client_name", t.data.result.client.client_name), localStorage.setItem("email", t.data.result.client.email), localStorage.setItem("profile_pic", t.data.result.client.profile_pic), localStorage.setItem("is_ib", t.data.result.client.is_ib), localStorage.setItem("nationality", t.data.result.client.nationality), localStorage.setItem("contact_no", t.data.result.client.contact_no), localStorage.setItem("email_verified", t.data.result.client.email_verified), os.push("/dashboard")), $e.remove("promoCode")
        } catch (t) {
          $e.remove("promoCode"), Swal.fire({
            icon: "error",
            title: (e = t.response) == null ? void 0 : e.data.message
          }).then(s => {
            s.isConfirmed && window.location.reload()
          }), this.isLoading = !1
        }
      },
      validateFname() {
        this.errors.fname = "", this.fname ? this.fname.length < 4 ? this.errors.fname = "Name must be at least 4 characters long" : this.errors.fname = "" : this.errors.fname = "Please enter a valid name"
      },
      validatePhone() {
        this.errors.phone = "", this.phone ? this.errors.phone = "" : this.errors.phone = "Please enter a valid phone number"
      },
      validateNationality() {
        this.errors.nationality = "", this.nationality ? this.errors.nationality = "" : this.errors.nationality = "Please select nationality"
      },
      async get_countires() {
        const {
          data: e
        } = await ye.get(this.API_BASE_URL + "/countries");
        this.countries = e.country_list
      },
      change_tab(e) {
        var t = document.querySelector('a[href="' + e + '"]');
        document.querySelector("#auth-active-slide").innerHTML = t.getAttribute("data-slide-index");
        var s = new bootstrap.Tab(t);
        s.show()
      },
      validateEmail() {
        const t = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email);
        this.errors.email = t ? "" : "Please enter a valid email address."
      },
      validatePassword() {
        this.errors.password = "", this.errors.confirmPassword = "";
        const t = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/.test(this.password),
          s = this.password === this.confirmPassword;
        t ? s ? (this.errors.password = "", this.errors.confirmPassword = "") : this.errors.confirmPassword = "Passwords do not match." : this.errors.password = "Password must contain at least 8 characters, including 1 uppercase letter, 1 lowercase letter, 1 digit, and 1 special character."
      },
      submitForm() {
        this.errors.email = "", this.errors.password = "", this.errors.confirmPassword = "", this.validateEmail(), this.validatePassword(), this.promoCode = $e.get("promoCode"), !this.errors.email && !this.errors.password && !this.errors.confirmPassword && (this.change_tab("#auth-2"), console.log("Tab 1 Data Collected"))
      }
    }
  },
  yt = e => (No("data-v-97e32e5a"), e = e(), Fo(), e),
  A5 = {
    class: "auth-main"
  },
  x5 = {
    class: "auth-wrapper v3"
  },
  S5 = {
    class: "auth-form"
  },
  E5 = {
    class: "auth-header row"
  },
  P5 = {
    class: "col my-1"
  },
  T5 = {
    href: "#"
  },
  L5 = {
    key: 0,
    src: ml,
    alt: "Succedo Logo",
    style: {
      height: "8vh"
    }
  },
  O5 = {
    key: 1,
    src: Gi,
    alt: "Bitrage Logo",
    style: {
      height: "8vh"
    }
  },
  I5 = {
    key: 2,
    src: gl,
    alt: "Paperboat Logo",
    style: {
      width: "200px"
    }
  },
  B5 = yt(() => n("div", {
    class: "col-auto my-1"
  }, [n("h5", {
    class: "m-0 text-muted f-w-500"
  }, [J(" Step "), n("b", {
    class: "h5",
    id: "auth-active-slide"
  }, "1"), J(" to 3 ")])], -1)),
  D5 = {
    class: "card my-3"
  },
  M5 = {
    class: "card-body"
  },
  R5 = yt(() => n("ul", {
    class: "nav nav-tabs d-none",
    id: "myTab",
    role: "tablist"
  }, [n("li", {
    class: "nav-item"
  }, [n("a", {
    class: "nav-link active",
    id: "auth-tab-1",
    "data-bs-toggle": "tab",
    href: "#auth-1",
    role: "tab",
    "data-slide-index": "1",
    "aria-controls": "auth-1",
    "aria-selected": "true"
  })]), n("li", {
    class: "nav-item"
  }, [n("a", {
    class: "nav-link",
    id: "auth-tab-2",
    "data-bs-toggle": "tab",
    href: "#auth-2",
    role: "tab",
    "data-slide-index": "2",
    "aria-controls": "auth-2",
    "aria-selected": "true"
  })]), n("li", {
    class: "nav-item"
  }, [n("a", {
    class: "nav-link",
    id: "auth-tab-3",
    "data-bs-toggle": "tab",
    href: "#auth-3",
    role: "tab",
    "data-slide-index": "3",
    "aria-controls": "auth-3",
    "aria-selected": "true"
  })])], -1)),
  N5 = {
    class: "tab-content"
  },
  F5 = {
    class: "tab-pane show active",
    id: "auth-1",
    role: "tabpanel",
    "aria-labelledby": "auth-tab-1"
  },
  U5 = {
    class: "text-center"
  },
  V5 = {
    class: "text-center mb-2 f-w-600"
  },
  j5 = yt(() => n("p", {
    class: "mb-4 fs-5"
  }, "Start Trading Today: Easy Account Setup!", -1)),
  H5 = {
    class: "row my-2"
  },
  q5 = {
    class: "col-12"
  },
  W5 = {
    class: "form-group"
  },
  z5 = yt(() => n("label", {
    class: "form-label"
  }, "Email Id", -1)),
  K5 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  G5 = {
    class: "col-12"
  },
  Y5 = {
    class: "form-group"
  },
  J5 = yt(() => n("label", {
    class: "form-label"
  }, "Password", -1)),
  Z5 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  X5 = {
    class: "col-12"
  },
  Q5 = {
    class: "form-group"
  },
  e8 = yt(() => n("label", {
    class: "form-label"
  }, "Confirm Password", -1)),
  t8 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  s8 = yt(() => n("div", {
    class: "row g-3"
  }, [n("div", {
    class: "col-sm-12"
  }, [n("div", {
    class: "d-grid"
  }, [n("button", {
    class: "btn btn-primary",
    type: "submit"
  }, " Continue ")])])], -1)),
  n8 = {
    class: "row"
  },
  o8 = {
    class: "d-flex align-items-end mt-2"
  },
  i8 = yt(() => n("h6", {
    class: "f-w-500 mb-0",
    style: {
      "font-size": "12px !important"
    }
  }, "Already have an Account? ", -1)),
  r8 = {
    class: "tab-pane",
    id: "auth-2",
    role: "tabpanel",
    "aria-labelledby": "auth-tab-2"
  },
  a8 = yt(() => n("div", {
    class: ""
  }, [n("h4", {
    class: "mb-3 f-w-600"
  }, "Tell us a bit about yourself")], -1)),
  l8 = {
    class: "row my-4"
  },
  c8 = {
    class: "col-sm-12"
  },
  d8 = {
    class: "form-group"
  },
  u8 = yt(() => n("label", {
    class: "form-label"
  }, "Full Name", -1)),
  f8 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  h8 = yt(() => n("small", null, "*as it appears on your ID Card / Proof of Identity", -1)),
  p8 = {
    class: "col-12"
  },
  m8 = {
    class: "form-group"
  },
  g8 = yt(() => n("label", {
    class: "form-label"
  }, "Phone Number", -1)),
  _8 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  b8 = {
    class: "col-12"
  },
  v8 = {
    class: "form-group"
  },
  y8 = yt(() => n("label", {
    class: "form-label"
  }, "Nationality", -1)),
  w8 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  $8 = {
    class: "col-sm-12"
  },
  C8 = {
    class: "form-group"
  },
  k8 = yt(() => n("label", {
    class: "form-label"
  }, [J("Referral Code"), n("small", null, "(if any)")], -1)),
  A8 = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  x8 = {
    class: "row g-3"
  },
  S8 = {
    class: "col-sm-12"
  },
  E8 = {
    class: "d-grid"
  },
  P8 = ["disabled"],
  T8 = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  L8 = yt(() => n("div", {
    class: "tab-pane",
    id: "auth-3",
    role: "tabpanel",
    "aria-labelledby": "auth-tab-3"
  }, [n("div", {
    class: "text-center"
  }, [n("h3", {
    class: "text-center mb-3"
  }, "Please verify your email id"), n("p", {
    class: "mb-4"
  }, " Lorem Ipsum is simply dummy text of the printing and typesetting industry of Lorem Ipsum. ")]), n("div", {
    class: "row my-4 text-center"
  }, [n("div", {
    class: "col"
  }, [n("input", {
    type: "number",
    class: "form-control text-center",
    placeholder: "0"
  })]), n("div", {
    class: "col"
  }, [n("input", {
    type: "number",
    class: "form-control text-center",
    placeholder: "0"
  })]), n("div", {
    class: "col"
  }, [n("input", {
    type: "number",
    class: "form-control text-center",
    placeholder: "0"
  })]), n("div", {
    class: "col"
  }, [n("input", {
    type: "number",
    class: "form-control text-center",
    placeholder: "0"
  })])]), n("div", {
    class: "d-grid"
  }, [n("button", {
    class: "btn btn-primary auth-conf"
  }, "Continue")])], -1)),
  O8 = {
    class: "auth-footer"
  },
  I8 = {
    class: "m-0 w-100 text-center",
    style: {
      "font-size": "11px"
    }
  },
  B8 = yt(() => n("br", null, null, -1)),
  D8 = yt(() => n("br", null, null, -1)),
  M8 = yt(() => n("a", {
    href: "#"
  }, "Privacy Policy", -1)),
  R8 = yt(() => n("a", {
    href: "#"
  }, "Client Agreement ", -1)),
  N8 = yt(() => n("a", {
    href: "#"
  }, "Trading Risk Warning", -1));

function F8(e, t, s, o, i, r) {
  const a = ke("RouterLink"),
    l = ke("vue-tel-input"),
    d = ke("v-select"),
    p = ke("BannerContentView");
  return _(), w("div", A5, [n("div", x5, [n("div", S5, [n("div", E5, [n("div", P5, [n("a", T5, [this.companySlug === "succedo" ? (_(), w("img", L5)) : D("", !0), this.companySlug === "bitrage" ? (_(), w("img", O5)) : D("", !0), this.companySlug === "paperboat" ? (_(), w("img", I5)) : D("", !0)])]), B5]), n("div", D5, [n("div", M5, [R5, n("div", N5, [n("div", F5, [n("div", U5, [n("h3", V5, "Join " + L(this.companyName), 1), j5]), n("form", {
    class: "needs-validation",
    onSubmit: t[3] || (t[3] = Li((...m) => r.submitForm && r.submitForm(...m), ["prevent"]))
  }, [n("div", H5, [n("div", q5, [n("div", W5, [z5, be(n("input", {
    "onUpdate:modelValue": t[0] || (t[0] = m => i.email = m),
    type: "email",
    id: "email",
    class: "form-control",
    placeholder: "Email id",
    required: ""
  }, null, 512), [
    [Re, i.email]
  ]), i.errors.email ? (_(), w("div", K5, L(i.errors.email), 1)) : D("", !0)])]), n("div", G5, [n("div", Y5, [J5, be(n("input", {
    "onUpdate:modelValue": t[1] || (t[1] = m => i.password = m),
    type: "password",
    class: "form-control",
    placeholder: "Password",
    required: ""
  }, null, 512), [
    [Re, i.password]
  ]), i.errors.password ? (_(), w("div", Z5, L(i.errors.password), 1)) : D("", !0)])]), n("div", X5, [n("div", Q5, [e8, be(n("input", {
    "onUpdate:modelValue": t[2] || (t[2] = m => i.confirmPassword = m),
    type: "password",
    class: "form-control",
    placeholder: "Confirm Password",
    required: ""
  }, null, 512), [
    [Re, i.confirmPassword]
  ]), i.errors.confirmPassword ? (_(), w("div", t8, L(i.errors.confirmPassword), 1)) : D("", !0)])])]), s8], 32), n("div", n8, [n("div", o8, [i8, ie(a, {
    to: "/login",
    class: "link-primary",
    style: {
      "font-size": "14px !important",
      "padding-left": "10px",
      "margin-top": "10px"
    }
  }, {
    default: Ue(() => [J(" Login")]),
    _: 1
  })])])]), n("div", r8, [a8, n("div", l8, [n("div", c8, [n("div", d8, [u8, be(n("input", {
    "onUpdate:modelValue": t[4] || (t[4] = m => i.fname = m),
    type: "text",
    class: "form-control",
    placeholder: "Full name"
  }, null, 512), [
    [Re, i.fname]
  ]), i.errors.fname ? (_(), w("div", f8, L(i.errors.fname), 1)) : D("", !0), h8])]), n("div", p8, [n("div", m8, [g8, ie(l, {
    class: "form-control",
    modelValue: i.phone,
    "onUpdate:modelValue": t[5] || (t[5] = m => i.phone = m)
  }, null, 8, ["modelValue"]), i.errors.phone ? (_(), w("div", _8, L(i.errors.phone), 1)) : D("", !0)])]), n("div", b8, [n("div", v8, [y8, ie(d, {
    class: "form-control",
    modelValue: i.nationality,
    "onUpdate:modelValue": t[6] || (t[6] = m => i.nationality = m),
    options: i.countries,
    label: "name"
  }, null, 8, ["modelValue", "options"]), i.errors.nationality ? (_(), w("div", w8, L(i.errors.nationality), 1)) : D("", !0)])]), n("div", $8, [n("div", C8, [k8, be(n("input", {
    "onUpdate:modelValue": t[7] || (t[7] = m => i.promoCode = m),
    type: "text",
    class: "form-control",
    placeholder: "Referral Code"
  }, null, 512), [
    [Re, i.promoCode]
  ]), i.errors.promoCode ? (_(), w("div", A8, L(i.errors.promoCode), 1)) : D("", !0)])])]), n("div", x8, [n("div", S8, [n("div", E8, [n("button", {
    class: "btn btn-primary",
    type: "button",
    disabled: i.isLoading,
    onClick: t[8] || (t[8] = (...m) => r.handleSubmitClick && r.handleSubmitClick(...m))
  }, [i.isLoading ? (_(), w("span", T8)) : D("", !0), J(" " + L(i.isLoading ? "Submitting..." : "Submit"), 1)], 8, P8)])])])]), L8])])]), n("div", O8, [n("p", I8, [J(" By signing up, I acknowledge that I have read, understood and agree to the Client Agreement "), B8, J("and give my consent for " + L(this.companyName) + " to contact me for marketing purposes. ", 1), D8, J(" By registering you agree to our "), M8, J(", "), R8, J("& "), N8, J(". ")])])]), this.COMPANY_SLUG == "succedo" ? (_(), ve(p, {
    key: 0,
    color1: "#19184C",
    color2: "#F4A01E"
  })) : D("", !0), this.COMPANY_SLUG == "bitrage" ? (_(), ve(p, {
    key: 1,
    color1: "#2ea094",
    color2: "#b3e5b4"
  })) : D("", !0), this.COMPANY_SLUG == "paperboat" ? (_(), ve(p, {
    key: 2,
    color1: "#2ea094",
    color2: "#b3e5b4"
  })) : D("", !0)])])
}
const U8 = je(k5, [
    ["render", F8],
    ["__scopeId", "data-v-97e32e5a"]
  ]),
  V8 = {
    data() {
      return {
        otp: ["", "", "", "", "", ""],
        otpError: "",
        otpErrorMessage: "",
        isLoading: !1,
        isResendLoading: !1
      }
    },
    computed: {
      otpDigits() {
        return Array.from({
          length: 6
        })
      }
    },
    components: {
      BannerContentView: Hu
    },
    mounted() {},
    beforeMount() {},
    methods: {
      focusNextInput(e) {
        e < 5 && this.otp[e] !== "" && this.$refs[`otpInput${e+1}`][0].focus()
      },
      async submitOTP() {
        var t, s;
        this.otpError = "", this.otpErrorMessage = "";
        const e = this.otp.join("");
        if (e.length === 6) try {
          this.isLoading = !0;
          const o = localStorage.getItem("auth_id"),
            i = $e.get("authToken"),
            r = await ye.post(this.API_BASE_URL + "/emailVerifyOTP", {
              otp: e
            }, {
              headers: {
                Authorization: `Bearer ${i}`
              }
            });
          r && r.status && (localStorage.setItem("email_verified", 1), this.isLoading = !1, nt.fire({
            icon: "success",
            title: r == null ? void 0 : r.data.message
          }).then(a => {
            a.isConfirmed && os.push("/dashboard")
          }))
        } catch (o) {
          this.isLoading = !1, this.otp = ["", "", "", "", "", ""], this.otpError = (t = o == null ? void 0 : o.response) == null ? void 0 : t.data.result, this.otpErrorMessage = (s = o == null ? void 0 : o.response) == null ? void 0 : s.data.message
        } else this.otpErrorMessage = "Please enter a 6-digit OTP"
      },
      async reSendOTP() {
        var e;
        try {
          this.isResendLoading = !0;
          const t = localStorage.getItem("auth_id"),
            s = $e.get("authToken"),
            o = await ye.post(this.API_BASE_URL + "/resend-verification", {
              auth_id: t
            }, {
              headers: {
                Authorization: `Bearer ${s}`
              }
            });
          o && o.status && (this.otpError = "", this.otpErrorMessage = "", this.isResendLoading = !1, nt.fire({
            icon: "success",
            title: o == null ? void 0 : o.data.message
          }).then(i => {
            i.isConfirmed && window.location.reload()
          }))
        } catch (t) {
          this.isResendLoading = !1, nt.fire({
            icon: "error",
            title: (e = t == null ? void 0 : t.response) == null ? void 0 : e.data.message
          }).then(s => {
            s.isConfirmed && window.location.reload()
          })
        }
      },
      async confirmLogout() {
        (await nt.fire({
          icon: "question",
          text: "Are you sure you want to logout?",
          showCancelButton: !0,
          confirmButtonText: "Yes",
          cancelButtonText: "No"
        })).isConfirmed && this.logout()
      },
      async logout() {
        let e = this.$loading.show({
          container: this.fullPage ? null : this.$refs.formContainer,
          canCancel: !1,
          opacity: .7,
          color: "#2ca192"
        });
        try {
          (await ye.post(this.API_BASE_URL + "/logout", {
            user_id: localStorage.getItem("auth_id")
          })).data.status && ($e.remove("authToken"), localStorage.clear(), e.hide(), os.push("/login"))
        } catch {
          $e.remove("authToken"), localStorage.clear(), e.hide(), os.push("/login")
        }
      }
    }
  },
  is = e => (No("data-v-f82f45de"), e = e(), Fo(), e),
  j8 = {
    class: "auth-main"
  },
  H8 = {
    class: "auth-wrapper v3"
  },
  q8 = {
    class: "auth-form"
  },
  W8 = {
    class: "auth-header row"
  },
  z8 = {
    class: "col my-1"
  },
  K8 = {
    href: "#"
  },
  G8 = {
    key: 0,
    src: ml,
    alt: "Succedo Logo",
    style: {
      height: "8vh"
    }
  },
  Y8 = {
    key: 1,
    src: Gi,
    alt: "Bitrage Logo",
    style: {
      height: "8vh"
    }
  },
  J8 = {
    key: 2,
    src: gl,
    alt: "Paperboat Logo",
    style: {
      width: "200px"
    }
  },
  Z8 = is(() => n("div", {
    class: "col-auto my-1"
  }, null, -1)),
  X8 = {
    class: "card my-3"
  },
  Q8 = {
    class: "card-body"
  },
  e6 = is(() => n("ul", {
    class: "nav nav-tabs d-none",
    id: "myTab",
    role: "tablist"
  }, [n("li", {
    class: "nav-item"
  }, [n("a", {
    class: "nav-link active",
    id: "auth-tab-3",
    "data-bs-toggle": "tab",
    href: "#auth-3",
    role: "tab",
    "data-slide-index": "3",
    "aria-controls": "auth-3",
    "aria-selected": "true"
  })])], -1)),
  t6 = {
    class: "tab-content"
  },
  s6 = {
    class: "tab-pane show active",
    id: "auth-3",
    role: "tabpanel",
    "aria-labelledby": "auth-tab-3"
  },
  n6 = {
    class: "text-center"
  },
  o6 = is(() => n("h4", {
    class: "text-center mb-3"
  }, "Please verify your Email Address", -1)),
  i6 = is(() => n("p", {
    class: "mb-4"
  }, [J(" Thank you for registering with us!"), n("br"), J(" Please verify your email address by entering the OTP that we've sent to the "), n("strong", null, "Email Address"), J(" you provided during registration. ")], -1)),
  r6 = {
    key: 0,
    class: "row justify-content-center"
  },
  a6 = {
    class: "col-auto"
  },
  l6 = is(() => n("h6", {
    class: "mb-3"
  }, "Please enter the 6-digit OTP", -1)),
  c6 = {
    class: "d-flex"
  },
  d6 = ["onUpdate:modelValue", "onInput"],
  u6 = {
    key: 0,
    class: "invalid-feedback mt-4",
    style: {
      display: "block !important"
    }
  },
  f6 = ["disabled"],
  h6 = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  p6 = {
    key: 1,
    class: "row justify-content-center"
  },
  m6 = ["disabled"],
  g6 = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  _6 = {
    class: "auth-footer"
  },
  b6 = {
    class: "m-0 w-100 text-center",
    style: {
      "font-size": "11px"
    }
  },
  v6 = is(() => n("br", null, null, -1)),
  y6 = is(() => n("br", null, null, -1)),
  w6 = is(() => n("a", {
    href: "#"
  }, "Privacy Policy", -1)),
  $6 = is(() => n("a", {
    href: "#"
  }, "Client Agreement ", -1)),
  C6 = is(() => n("a", {
    href: "#"
  }, "Trading Risk Warning", -1)),
  k6 = is(() => n("br", null, null, -1)),
  A6 = is(() => n("i", {
    class: "ti ti-power"
  }, null, -1)),
  x6 = is(() => n("span", null, null, -1)),
  S6 = [A6, x6];

function E6(e, t, s, o, i, r) {
  const a = ke("BannerContentView");
  return _(), w("div", j8, [n("div", H8, [n("div", q8, [n("div", W8, [n("div", z8, [n("a", K8, [this.companySlug === "succedo" ? (_(), w("img", G8)) : D("", !0), this.companySlug === "bitrage" ? (_(), w("img", Y8)) : D("", !0), this.companySlug === "paperboat" ? (_(), w("img", J8)) : D("", !0)])]), Z8]), n("div", X8, [n("div", Q8, [e6, n("div", t6, [n("div", s6, [n("div", n6, [o6, i6, i.otpError != "max_attempt" && i.otpError != "expired" ? (_(), w("div", r6, [n("div", a6, [l6, n("div", c6, [(_(!0), w(Te, null, Je(r.otpDigits, (l, d) => be((_(), w("input", {
    key: d,
    type: "text",
    class: "form-control otp-input",
    "onUpdate:modelValue": p => i.otp[d] = p,
    maxlength: "1",
    onInput: p => r.focusNextInput(d),
    ref_for: !0,
    ref: `otpInput${d}`
  }, null, 40, d6)), [
    [Re, i.otp[d]]
  ])), 128))]), i.otpErrorMessage ? (_(), w("div", u6, L(i.otpErrorMessage), 1)) : D("", !0), n("button", {
    class: "btn btn-primary mt-4",
    type: "button",
    disabled: i.isLoading,
    onClick: t[0] || (t[0] = (...l) => r.submitOTP && r.submitOTP(...l))
  }, [i.isLoading ? (_(), w("span", h6)) : D("", !0), J(" " + L(i.isLoading ? "Verifying..." : "Verify Email"), 1)], 8, f6)])])) : (_(), w("div", p6, [n("button", {
    class: "btn btn-primary mt-4",
    type: "button",
    disabled: i.isResendLoading,
    onClick: t[1] || (t[1] = (...l) => r.reSendOTP && r.reSendOTP(...l))
  }, [i.isResendLoading ? (_(), w("span", g6)) : D("", !0), J(" " + L(i.isResendLoading ? "Sending..." : "Resend OTP"), 1)], 8, m6)]))])])])])]), n("div", _6, [n("p", b6, [J(" By signing up, I acknowledge that I have read, understood and agree to the Client Agreement "), v6, J("and give my consent for " + L(this.companyName) + " to contact me for marketing purposes. ", 1), y6, J(" By registering you agree to our "), w6, J(", "), $6, J("& "), C6, J(". "), k6, n("button", {
    class: "btn",
    id: "logout-link",
    onClick: t[2] || (t[2] = (...l) => r.confirmLogout && r.confirmLogout(...l))
  }, S6)])])]), this.COMPANY_SLUG == "succedo" ? (_(), ve(a, {
    key: 0,
    color1: "#19184C",
    color2: "#F4A01E"
  })) : D("", !0), this.COMPANY_SLUG == "bitrage" ? (_(), ve(a, {
    key: 1,
    color1: "#2ea094",
    color2: "#b3e5b4"
  })) : D("", !0), this.COMPANY_SLUG == "paperboat" ? (_(), ve(a, {
    key: 2,
    color1: "#2ea094",
    color2: "#b3e5b4"
  })) : D("", !0)])])
}
const P6 = je(V8, [
    ["render", E6],
    ["__scopeId", "data-v-f82f45de"]
  ]),
  T6 = "/assets/verified-DnWF-Cf0.png",
  L6 = "/assets/not_verified-Bqmb2TK2.png",
  O6 = {
    created() {
      this.token = this.$route.query.token, this.validateToken()
    },
    data() {
      return {
        token: null,
        is_verified: !1,
        got_response: !1,
        email: "",
        error_msg: ""
      }
    },
    components: {},
    mounted() {},
    beforeMount() {},
    methods: {
      resendLink() {
        console.log(this.email)
      },
      async validateToken() {
        var e, t;
        try {
          const s = await ye.post(this.API_BASE_URL + "/emailVerify", {
            email_token: this.token
          });
          s && s.status && (this.got_response = !0, this.is_verified = !0, $e.remove("authToken"), localStorage.clear())
        } catch (s) {
          this.got_response = !0, this.is_verified = !1, this.error_msg = (e = s == null ? void 0 : s.response) == null ? void 0 : e.message, this.email = (t = s == null ? void 0 : s.response) == null ? void 0 : t.result.email
        }
      },
      takeToLogin() {
        $e.remove("authToken"), localStorage.clear(), os.push("/login")
      }
    }
  },
  $s = e => (No("data-v-b4e1b086"), e = e(), Fo(), e),
  I6 = {
    class: "auth-main"
  },
  B6 = {
    class: "auth-wrapper v3"
  },
  D6 = {
    class: "auth-form"
  },
  M6 = Ae('<div class="auth-header row" data-v-b4e1b086><div class="col my-1" data-v-b4e1b086><a href="#" data-v-b4e1b086><img src="' + Gi + '" alt="Bitrage Logo" style="height:8vh;" data-v-b4e1b086></a></div><div class="col-auto my-1" data-v-b4e1b086></div></div>', 1),
  R6 = {
    class: "card my-3"
  },
  N6 = {
    class: "card-body"
  },
  F6 = $s(() => n("ul", {
    class: "nav nav-tabs d-none",
    id: "myTab",
    role: "tablist"
  }, [n("li", {
    class: "nav-item"
  }, [n("a", {
    class: "nav-link active",
    id: "auth-tab-3",
    "data-bs-toggle": "tab",
    href: "#auth-3",
    role: "tab",
    "data-slide-index": "3",
    "aria-controls": "auth-3",
    "aria-selected": "true"
  })])], -1)),
  U6 = {
    key: 0,
    class: "tab-content"
  },
  V6 = {
    class: "tab-pane show active",
    id: "auth-3",
    role: "tabpanel",
    "aria-labelledby": "auth-tab-3"
  },
  j6 = {
    class: "text-center"
  },
  H6 = $s(() => n("h3", {
    class: "text-center mb-3 mt-5"
  }, "Verifying your Email ....", -1)),
  q6 = {
    key: 1,
    class: "tab-content"
  },
  W6 = {
    class: "tab-pane show active",
    id: "auth-3",
    role: "tabpanel",
    "aria-labelledby": "auth-tab-3"
  },
  z6 = {
    class: "text-center"
  },
  K6 = $s(() => n("img", {
    src: T6,
    alt: "verified Logo",
    class: "w-50 mb-3"
  }, null, -1)),
  G6 = $s(() => n("h3", {
    class: "text-center mb-3"
  }, "Email Verification Successful", -1)),
  Y6 = $s(() => n("p", {
    class: "mb-4"
  }, " Your email address has been successfully verified. Please log in to access your dashboard. ", -1)),
  J6 = $s(() => n("i", {
    class: "ti ti-power me-1"
  }, null, -1)),
  Z6 = {
    key: 2,
    class: "tab-content"
  },
  X6 = {
    class: "tab-pane show active",
    id: "auth-3",
    role: "tabpanel",
    "aria-labelledby": "auth-tab-3"
  },
  Q6 = {
    class: "text-center"
  },
  e9 = $s(() => n("img", {
    src: L6,
    alt: "verified Logo",
    class: "w-25 mb-3"
  }, null, -1)),
  t9 = $s(() => n("h3", {
    class: "text-center mb-3"
  }, "Email Verification Link Expired !", -1)),
  s9 = $s(() => n("p", {
    class: "mb-4"
  }, " Your Email verification link has expired.Please begin the Email Verification process again. ", -1)),
  n9 = $s(() => n("i", {
    class: "ti ti-refresh me-1"
  }, null, -1)),
  o9 = $s(() => n("div", {
    class: "auth-footer"
  }, null, -1)),
  i9 = Ae('<div class="auth-sidecontent" style="background:linear-gradient(45deg, #2ea094, #b3e5b4) !important;" data-v-b4e1b086><div class="p-3 px-lg-5 text-center" data-v-b4e1b086><div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel" data-v-b4e1b086><div class="carousel-inner" data-v-b4e1b086><div class="carousel-item active" data-v-b4e1b086><img src="' + L1 + '" alt="user-image" class="hei-150 mb-3" data-v-b4e1b086><h5 class="text-white mb-0" data-v-b4e1b086>Regulatory Excellence</h5><p class="text-white text-opacity-50" data-v-b4e1b086>Compliance Assurance</p><div class="star f-20 my-4" data-v-b4e1b086><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star-half-alt text-warning" data-v-b4e1b086></i></div><p class="text-white" data-v-b4e1b086> With meticulous attention to regulatory standards, Bitrage Markets guarantees compliance assurance, fostering transparency and confidence among traders with a commitment to ethical conduct and integrity. </p></div><div class="carousel-item" data-v-b4e1b086><img src="' + O1 + '" alt="user-image" class="hei-150 mb-3" data-v-b4e1b086><h5 class="text-white mb-0" data-v-b4e1b086>Transparent Pricing Policy</h5><p class="text-white text-opacity-50" data-v-b4e1b086>Clear Cost Commitment</p><div class="star f-20 my-4" data-v-b4e1b086><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star-half-alt text-warning" data-v-b4e1b086></i></div><p class="text-white" data-v-b4e1b086> At Bitrage Markets, transparency is paramount. Our pricing policy ensures clarity and fairness, empowering traders with transparent pricing structures and no hidden fees for a seamless trading experience. </p></div><div class="carousel-item" data-v-b4e1b086><img src="' + I1 + '" alt="user-image" class="hei-150 mb-3" data-v-b4e1b086><h5 class="text-white mb-0" data-v-b4e1b086>Swift and Precise Execution</h5><p class="text-white text-opacity-50" data-v-b4e1b086>Precision Trading</p><div class="star f-20 my-4" data-v-b4e1b086><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star-half-alt text-warning" data-v-b4e1b086></i></div><p class="text-white" data-v-b4e1b086> Experience lightning-fast trade execution with Bitrage Markets. Our advanced trading infrastructure ensures swift and precise order processing, enabling traders to capitalize on market opportunities instantly. </p></div><div class="carousel-item" data-v-b4e1b086><img src="' + B1 + '" alt="user-image" class="hei-150 mb-3" data-v-b4e1b086><h5 class="text-white mb-0" data-v-b4e1b086>Competitive Spreads</h5><p class="text-white text-opacity-50" data-v-b4e1b086>Cost Efficiency</p><div class="star f-20 my-4" data-v-b4e1b086><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star text-warning" data-v-b4e1b086></i><i class="fas fa-star-half-alt text-warning" data-v-b4e1b086></i></div><p class="text-white" data-v-b4e1b086> Gain a competitive edge with Bitrage Markets&#39; tight spreads. Our low-cost advantage ensures competitive pricing on all trading instruments, allowing traders to maximize profitability and minimize trading costs. </p></div></div><div class="carousel-indicators position-relative mt-3" data-v-b4e1b086><button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1" data-v-b4e1b086></button><button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2" data-v-b4e1b086></button><button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3" data-v-b4e1b086></button><button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3" aria-label="Slide 4" data-v-b4e1b086></button></div></div></div></div>', 1);

function r9(e, t, s, o, i, r) {
  const a = ke("SmallLoadingSpinner");
  return _(), w("div", I6, [n("div", B6, [n("div", D6, [M6, n("div", R6, [n("div", N6, [F6, i.got_response ? D("", !0) : (_(), w("div", U6, [n("div", V6, [n("div", j6, [ie(a), H6])])])), i.got_response && i.is_verified ? (_(), w("div", q6, [n("div", W6, [n("div", z6, [K6, G6, Y6, n("button", {
    onClick: t[0] || (t[0] = (...l) => r.takeToLogin && r.takeToLogin(...l)),
    type: "button",
    class: "btn btn-outline-success d-inline-flex"
  }, [J6, J("Login")])])])])) : D("", !0), i.got_response && !i.is_verified ? (_(), w("div", Z6, [n("div", X6, [n("div", Q6, [e9, t9, s9, n("button", {
    type: "button",
    class: "btn btn-outline-success d-inline-flex",
    onClick: t[1] || (t[1] = (...l) => r.resendLink && r.resendLink(...l))
  }, [n9, J("Resend Verification Link")])])])])) : D("", !0)])]), o9]), i9])])
}
const a9 = je(O6, [
  ["render", r9],
  ["__scopeId", "data-v-b4e1b086"]
]);
class me {
  static setDataWithTimestamp(t, s, o) {
    const i = o * 60 * 1e3,
      r = {
        value: s,
        timestamp: new Date().getTime() + i
      };
    localStorage.setItem(t, JSON.stringify(r))
  }
  static getDataWithExpiration(t) {
    const s = JSON.parse(localStorage.getItem(t));
    return s ? new Date().getTime() > s.timestamp ? (localStorage.removeItem(t), null) : s.value : null
  }
}
const D1 = async () => {
  ye.get(`${Ge("API_BASE_URL")}/profile/user-info`, {
    headers: {
      Authorization: `Bearer ${$e.get("authToken")}`
    }
  }).then(e => (me.setDataWithTimestamp("profile_data", e.data.result, 30), me.setDataWithTimestamp("bank_details", e.data.result.bank_details, 30), me.setDataWithTimestamp("client_details", e.data.result.client_details, 30), me.setDataWithTimestamp("client_docs", e.data.result.client_docs, 30), me.setDataWithTimestamp("manager_data", e.data.result.manager_data, 30), localStorage.setItem("is_ib", e.data.result.client_details.is_ib), e.data.result)).catch(e => {
    var t;
    Swal.fire({
      icon: "error",
      title: (t = e.response) == null ? void 0 : t.data.message
    }).then(s => {
      s.isConfirmed && window.location.reload()
    })
  })
}, l9 = async e => {
  ye.get(`${e}/ib/metrics`, {
    headers: {
      Authorization: `Bearer ${$e.get("authToken")}`
    }
  }).then(t => (me.setDataWithTimestamp("ib_metrics", t.data.result, 30), t.data.result)).catch(t => {
    var s;
    Swal.fire({
      icon: "error",
      title: (s = t.response) == null ? void 0 : s.data.message
    }).then(o => {
      o.isConfirmed && window.location.reload()
    })
  })
}, Mt = "/assets/mt5-Nk0MWBa5.webp", c9 = Dt({
  setup(e, {
    emit: t
  }) {
    const s = Wi(),
      o = he([]),
      i = Ge("API_BASE_URL"),
      r = ye.create({
        baseURL: i,
        headers: {
          Authorization: `Bearer ${$e.get("authToken")}`
        }
      }),
      a = async () => {
        try {
          const p = await r.get("/live-accounts/data-list");
          o.value = p.data.result.mt_data.filter(m => m.is_wallet === 0).sort((m, g) => m.mt_id - g.mt_id), me.setDataWithTimestamp("liveAccounts", o.value, 30), me.setDataWithTimestamp("total_balance", p.data.result.total_balance, 30), t("totalBalanceDataReceived", p.data.result.total_balance)
        } catch (p) {
          console.error("Error fetching live accounts data:", p), nt.fire({
            icon: "error",
            title: "Error fetching data",
            text: p.message || "An error occurred while fetching data"
          })
        }
      }, l = () => {
        const p = me.getDataWithExpiration("liveAccounts");
        p && (o.value = p.filter(m => m.is_wallet === 0).sort((m, g) => m.mt_id - g.mt_id))
      };
    return At(() => {
      l(), a()
    }), {
      liveAccounts: o,
      viewAccount: p => {
        s.push({
          name: "MT5Details",
          params: {
            mt_id: p.mt_id
          }
        })
      }
    }
  }
}), d9 = {
  key: 0,
  class: "table-responsive ps-2"
}, u9 = {
  class: "table",
  id: ""
}, f9 = n("thead", null, [n("tr", null, [n("th"), n("th", null, "Leverage"), n("th", {
  class: "text-end"
}, "Balance"), n("th", {
  class: "text-end"
}, "Equity"), n("th", {
  class: "text-end"
})])], -1), h9 = {
  class: "row align-items-center"
}, p9 = n("div", {
  class: "col-auto pe-0"
}, [n("img", {
  src: Mt,
  alt: "user-image",
  class: "wid-50 hei-50 rounded"
})], -1), m9 = {
  class: "col"
}, g9 = {
  class: "mb-2 ms-2"
}, _9 = {
  class: "text-truncate w-100"
}, b9 = {
  class: "text-muted ms-2 f-12 mb-0"
}, v9 = {
  class: "text-truncate w-100"
}, y9 = {
  class: "f-w-400 f-16"
}, w9 = {
  class: "text-end f-w-400 f-16"
}, $9 = {
  class: "text-end f-w-400 f-16"
}, C9 = {
  class: "text-end f-w-200"
}, k9 = {
  class: "d-flex align-items-center"
}, A9 = ["onClick"], x9 = n("span", {
  class: ""
}, [J("View "), n("svg", {
  class: "pc-icon"
}, [n("use", {
  "xlink:href": "#custom-login"
})])], -1), S9 = [x9], E9 = n("span", {
  class: ""
}, [J("Deposit "), n("i", {
  class: "ti ti-database-import"
})], -1), P9 = {
  key: 1,
  class: "p-5 m-3"
}, T9 = n("button", {
  class: "btn btn-outline-primary"
}, [n("span", {
  class: "text-truncate w-100"
}, "Create new Account")], -1);

function L9(e, t, s, o, i, r) {
  var d;
  const a = ke("RouterLink"),
    l = ke("EmptyStateView");
  return _(), w("div", null, [((d = e.liveAccounts) == null ? void 0 : d.length) > 0 ? (_(), w("div", d9, [n("table", u9, [f9, n("tbody", null, [(_(!0), w(Te, null, Je(e.liveAccounts, p => (_(), w("tr", {
    key: p.mt_id
  }, [n("td", null, [n("div", h9, [p9, n("div", m9, [n("h4", g9, [n("span", _9, L(p.mt_id), 1)]), n("p", b9, [n("span", v9, L(p.mt_category), 1)])])])]), n("td", y9, "1:" + L(p.leverage), 1), n("td", w9, L(this.$formatCurrency(p.balance)), 1), n("td", $9, L(this.$formatCurrency(p.equity)), 1), n("td", C9, [n("div", k9, [n("button", {
    class: "btn btn-sm btn-outline-secondary d-grid me-2",
    onClick: m => e.viewAccount(p)
  }, S9, 8, A9), ie(a, {
    to: "/fund/deposit/deposit",
    class: "btn btn-sm btn-outline-secondary d-grid"
  }, {
    default: Ue(() => [E9]),
    _: 1
  })])])]))), 128))])])])) : (_(), w("div", P9, [ie(l, {
    msg: "No Live Accounts Found"
  }), ie(a, {
    to: "/createLiveAccount",
    class: "d-grid"
  }, {
    default: Ue(() => [T9]),
    _: 1
  })]))])
}
const M1 = je(c9, [
    ["render", L9]
  ]),
  R1 = "/assets/mt5_demo-JG90XnQH.webp",
  O9 = Dt({
    setup(e, {
      emit: t
    }) {
      const s = he([]),
        o = Wi(),
        i = Ge("API_BASE_URL"),
        r = ye.create({
          baseURL: i,
          headers: {
            Authorization: `Bearer ${$e.get("authToken")}`
          }
        }),
        a = async () => {
          try {
            const p = await r.get("/demo-accounts/data-list");
            s.value = p.data.result.mt_data, me.setDataWithTimestamp("demoAccounts", s.value, 30)
          } catch (p) {
            console.error("Error fetching demo accounts data:", p), nt.fire({
              icon: "error",
              title: "Error fetching data",
              text: p.message || "An error occurred while fetching data"
            })
          }
        }, l = () => {
          const p = me.getDataWithExpiration("demoAccounts");
          p && (s.value = p)
        };
      return At(() => {
        l(), a()
      }), {
        demoAccounts: s,
        viewAccount: p => {
          o.push({
            name: "MT5DemoDetails",
            params: {
              mt_id: p.mt_id
            }
          })
        }
      }
    }
  }),
  I9 = {
    key: 0,
    class: "table-responsive ps-2"
  },
  B9 = {
    class: "table",
    id: ""
  },
  D9 = n("thead", null, [n("tr", null, [n("th"), n("th", null, "Leverage"), n("th", {
    class: "text-end"
  }, "Balance"), n("th", {
    class: "text-end"
  }, "Equity"), n("th", {
    class: "text-end"
  })])], -1),
  M9 = {
    class: "row align-items-center"
  },
  R9 = n("div", {
    class: "col-auto pe-0"
  }, [n("img", {
    src: R1,
    alt: "user-image",
    class: "wid-60 hei-60 rounded"
  })], -1),
  N9 = {
    class: "col"
  },
  F9 = {
    class: "mb-2"
  },
  U9 = {
    class: "text-truncate w-100"
  },
  V9 = {
    class: "text-muted f-12 mb-0"
  },
  j9 = {
    class: "text-truncate w-100"
  },
  H9 = n("span", {
    class: "badge bg-light-warning ms-2"
  }, "DEMO", -1),
  q9 = {
    class: "f-w-600"
  },
  W9 = {
    class: "text-end f-w-600"
  },
  z9 = {
    class: "text-end f-w-600"
  },
  K9 = {
    class: "text-end f-w-200"
  },
  G9 = {
    class: "d-flex align-items-center"
  },
  Y9 = ["onClick"],
  J9 = n("span", {
    class: ""
  }, [J("View "), n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-login"
  })])], -1),
  Z9 = [J9],
  X9 = {
    key: 1,
    class: "p-5 m-3"
  },
  Q9 = n("button", {
    class: "btn btn-outline-primary"
  }, [n("span", {
    class: "text-truncate w-100"
  }, "Create new Demo Account")], -1);

function e7(e, t, s, o, i, r) {
  var d;
  const a = ke("EmptyStateView"),
    l = ke("RouterLink");
  return _(), w("div", null, [((d = e.demoAccounts) == null ? void 0 : d.length) > 0 ? (_(), w("div", I9, [n("table", B9, [D9, n("tbody", null, [(_(!0), w(Te, null, Je(e.demoAccounts, p => (_(), w("tr", {
    key: p.mt_id
  }, [n("td", null, [n("div", M9, [R9, n("div", N9, [n("h4", F9, [n("span", U9, L(p.mt_id), 1)]), n("p", V9, [n("span", j9, [J(L(p.mt_category), 1), H9])])])])]), n("td", q9, "1:" + L(p.leverage), 1), n("td", W9, "$ " + L(p.balance), 1), n("td", z9, "$ " + L(p.equity), 1), n("td", K9, [n("div", G9, [n("button", {
    class: "btn btn-sm btn-outline-secondary d-grid me-2",
    onClick: m => e.viewAccount(p)
  }, Z9, 8, Y9)])])]))), 128))])])])) : (_(), w("div", X9, [ie(a, {
    msg: "No Demo Accounts Found"
  }), ie(l, {
    to: "/createDemoAccount",
    class: "d-grid"
  }, {
    default: Ue(() => [Q9]),
    _: 1
  })]))])
}
const N1 = je(O9, [
    ["render", e7]
  ]),
  t7 = {
    mounted() {
      const e = document.getElementsByClassName("tradingview-widget-container")[0];
      e.replaceChildren();
      const t = document.createElement("script");
      t.type = "text/javascript", t.src = "https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js", t.async = !0, t.innerHTML = JSON.stringify({
        symbols: [{
          proName: "FX_IDC:EURUSD",
          title: "EUR to USD"
        }, {
          proName: "BITSTAMP:BTCUSD",
          title: "Bitcoin"
        }, {
          proName: "BITSTAMP:ETHUSD",
          title: "Ethereum"
        }, {
          proName: "FX:GBPUSD",
          description: "GBP to USD"
        }, {
          proName: "FX:USDJPY",
          description: "USD to JPY"
        }, {
          proName: "FX:AUDUSD",
          description: "AUD to USD"
        }, {
          proName: "OANDA:USDJPY",
          description: "USD to JPY"
        }, {
          proName: "OANDA:USDCAD",
          description: "USD to CAD"
        }, {
          proName: "FX:NZDUSD",
          description: "NZD to USD"
        }, {
          proName: "VELOCITY:GOLD",
          description: "Gold"
        }],
        showSymbolLogo: !0,
        isTransparent: !1,
        displayMode: "compact",
        colorTheme: "light",
        locale: "en"
      }), e.appendChild(t)
    }
  },
  s7 = {
    class: "tradingview-widget-container"
  },
  n7 = n("div", {
    class: "tradingview-widget-container__widget"
  }, null, -1),
  o7 = n("div", {
    class: "tradingview-widget-copyright"
  }, [n("a", {
    href: "https://www.tradingview.com/",
    rel: "noopener nofollow",
    target: "_blank"
  }, [n("span", {
    class: "blue-text"
  }, "Track all markets on TradingView")])], -1),
  i7 = [n7, o7];

function r7(e, t, s, o, i, r) {
  return _(), w("div", s7, i7)
}
const a7 = je(t7, [
    ["render", r7]
  ]),
  l7 = Dt({
    components: {
      LiveAccountListWidget: M1,
      DemoAccountListingWidgetView: N1,
      TradingViewWidget: a7
    },
    setup() {
      const e = Ge("API_BASE_URL"),
        t = $e.get("authToken"),
        s = he({}),
        o = he(0),
        i = he(!1),
        r = he(!1),
        a = he(localStorage.getItem("is_ib") === "2"),
        l = ye.create({
          baseURL: e,
          headers: {
            Authorization: `Bearer ${t}`
          }
        }),
        d = async () => {
          try {
            const C = await l.get("/dashboard");
            s.value = C.data.result, me.setDataWithTimestamp("dashboard_data", s.value, 30), me.setDataWithTimestamp("pending_wd_count", s.value.pending_withdraw_count, 30), r.value = !0
          } catch (C) {
            console.error("Error fetching dashboard data:", C), nt.fire({
              icon: "error",
              title: "Error fetching data",
              text: C.message || "An error occurred while fetching data"
            }), r.value = !1
          }
        }, p = async () => {
          i.value = !0;
          try {
            const C = await l.post("/live-account/activate-wallet", {
              client_id: localStorage.getItem("auth_id")
            });
            nt.fire({
              icon: "success",
              title: "Wallet Activated Successfully",
              text: C.data.message
            }), m()
          } catch (C) {
            console.error("Error activating wallet:", C), nt.fire({
              icon: "error",
              title: "Activation Failed",
              text: C.response.data.message || "Network error"
            })
          } finally {
            i.value = !1
          }
        }, m = () => {
          const C = me.getDataWithExpiration("dashboard_data");
          C && (s.wallet_created = 1, me.setDataWithTimestamp("dashboard_data", C, 30)), window.location.reload()
        }, g = () => {
          const C = me.getDataWithExpiration("dashboard_data");
          C && (s.value = C, r.value = !0);
          const A = me.getDataWithExpiration("total_balance");
          A && (o.value = A)
        }, $ = C => {
          o.value = C
        };
      return At(() => {
        g(), d(), D1(), a.value && l9(e)
      }), {
        dashboard_data: s,
        total_balance: o,
        isLoading: i,
        is_ib: a,
        isDataLoaded: r,
        activateWallet: p,
        handleTotalBalanceDataReceived: $
      }
    }
  }),
  c7 = {
    class: "pc-container"
  },
  d7 = {
    class: "pc-content"
  },
  u7 = {
    class: "row mb-4"
  },
  f7 = {
    class: "row"
  },
  h7 = {
    class: "col-md-6 col-lg-3"
  },
  p7 = {
    class: "card bg-gray-800 dropbox-card"
  },
  m7 = {
    class: "card-body"
  },
  g7 = {
    class: "d-flex align-items-center justify-content-between"
  },
  _7 = {
    key: 0,
    class: "text-white"
  },
  b7 = {
    key: 1,
    class: "text-white"
  },
  v7 = {
    class: "d-flex align-items-center justify-content-between mb-2 mt-2"
  },
  y7 = n("div", null, [n("div", {
    class: "avtar avtar-s"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-security-safe"
  })])])], -1),
  w7 = {
    key: 0,
    class: ""
  },
  $7 = {
    class: "text-white f-w-500"
  },
  C7 = {
    key: 1
  },
  k7 = ["disabled"],
  A7 = n("i", {
    class: "ti ti-plus me-2"
  }, null, -1),
  x7 = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  S7 = n("small", {
    class: "text-white"
  }, "Fund Now", -1),
  E7 = {
    class: "col-md-6 col-lg-3"
  },
  P7 = {
    class: "card"
  },
  T7 = {
    class: "card-body"
  },
  L7 = {
    class: "d-flex align-items-center justify-content-between mb-3"
  },
  O7 = n("div", {
    class: "avtar avtar-s bg-light-primary"
  }, [n("i", {
    class: "ti ti-database-import f-18"
  })], -1),
  I7 = {
    class: "dropdown"
  },
  B7 = n("a", {
    class: "avtar avtar-s btn-link-secondary dropdown-toggle arrow-none",
    href: "#",
    "data-bs-toggle": "dropdown",
    "aria-haspopup": "true",
    "aria-expanded": "false"
  }, [n("i", {
    class: "ti ti-dots-vertical f-18"
  })], -1),
  D7 = {
    class: "dropdown-menu dropdown-menu-end"
  },
  M7 = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  R7 = n("p", {
    class: "text-muted mb-0"
  }, "Total Deposit", -1),
  N7 = {
    class: "col-md-6 col-lg-3"
  },
  F7 = {
    class: "card"
  },
  U7 = {
    class: "card-body"
  },
  V7 = {
    class: "d-flex align-items-center justify-content-between mb-3"
  },
  j7 = n("div", {
    class: "avtar avtar-s bg-light-primary"
  }, [n("i", {
    class: "ti ti-database-export f-18"
  })], -1),
  H7 = {
    class: "dropdown"
  },
  q7 = n("a", {
    class: "avtar avtar-s btn-link-secondary dropdown-toggle arrow-none",
    href: "#",
    "data-bs-toggle": "dropdown",
    "aria-haspopup": "true",
    "aria-expanded": "false"
  }, [n("i", {
    class: "ti ti-dots-vertical f-18"
  })], -1),
  W7 = {
    class: "dropdown-menu dropdown-menu-end"
  },
  z7 = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  K7 = n("p", {
    class: "text-muted mb-0"
  }, "Total Withdraw", -1),
  G7 = {
    class: "col-md-6 col-lg-3"
  },
  Y7 = {
    href: "#"
  },
  J7 = {
    class: "card"
  },
  Z7 = {
    class: "card-body"
  },
  X7 = {
    class: "d-flex align-items-center justify-content-between mb-3"
  },
  Q7 = n("div", {
    class: "avtar avtar-s bg-light-primary"
  }, [n("i", {
    class: "ti ti-shield-check f-18"
  })], -1),
  eC = {
    class: "dropdown"
  },
  tC = n("a", {
    class: "avtar avtar-s btn-link-secondary dropdown-toggle arrow-none",
    href: "#",
    "data-bs-toggle": "dropdown",
    "aria-haspopup": "true",
    "aria-expanded": "false"
  }, [n("i", {
    class: "ti ti-dots-vertical f-18"
  })], -1),
  sC = {
    class: "dropdown-menu dropdown-menu-end"
  },
  nC = {
    key: 0,
    class: "mb-0 f-w-400"
  },
  oC = n("p", {
    class: "text-muted mb-0"
  }, "Live MT5 Accounts", -1),
  iC = {
    class: "row"
  },
  rC = {
    class: "col-md-12 col-lg-9"
  },
  aC = {
    class: "card"
  },
  lC = {
    class: "card-body border-bottom pb-0"
  },
  cC = {
    class: "d-flex align-items-center justify-content-between"
  },
  dC = n("h5", {
    class: "mb-0"
  }, "My Trading Accounts", -1),
  uC = {
    class: "dropdown"
  },
  fC = n("a", {
    class: "avtar avtar-s btn-link-secondary dropdown-toggle arrow-none",
    href: "#",
    "data-bs-toggle": "dropdown",
    "aria-haspopup": "true",
    "aria-expanded": "false"
  }, [n("i", {
    class: "ti ti-dots-vertical f-18"
  })], -1),
  hC = {
    class: "dropdown-menu dropdown-menu-end"
  },
  pC = n("a", {
    class: "dropdown-item",
    href: "#"
  }, "Open Demo Account", -1),
  mC = n("ul", {
    class: "nav nav-tabs analytics-tab",
    id: "myTab",
    role: "tablist"
  }, [n("li", {
    class: "nav-item",
    role: "presentation"
  }, [n("button", {
    class: "nav-link active",
    id: "analytics-tab-1",
    "data-bs-toggle": "tab",
    "data-bs-target": "#analytics-tab-1-pane",
    type: "button",
    role: "tab",
    "aria-controls": "analytics-tab-1-pane",
    "aria-selected": "true"
  }, "Live Accounts")]), n("li", {
    class: "nav-item",
    role: "presentation"
  }, [n("button", {
    class: "nav-link",
    id: "analytics-tab-2",
    "data-bs-toggle": "tab",
    "data-bs-target": "#analytics-tab-2-pane",
    type: "button",
    role: "tab",
    "aria-controls": "analytics-tab-2-pane",
    "aria-selected": "false"
  }, "Demo Accounts")])], -1),
  gC = {
    class: "tab-content",
    id: "myTabContent"
  },
  _C = {
    class: "tab-pane fade show active",
    id: "analytics-tab-1-pane",
    role: "tabpanel",
    "aria-labelledby": "analytics-tab-1",
    tabindex: "0"
  },
  bC = {
    class: "tab-pane fade",
    id: "analytics-tab-2-pane",
    role: "tabpanel",
    "aria-labelledby": "analytics-tab-2",
    tabindex: "0"
  },
  vC = n("div", {
    class: "card-footer"
  }, null, -1),
  yC = {
    class: "col-md-6 col-lg-3"
  },
  wC = {
    href: "#"
  },
  $C = {
    class: "card bg-primary available-balance-card"
  },
  CC = {
    class: "card-body p-3"
  },
  kC = {
    class: "d-flex align-items-center justify-content-between"
  },
  AC = n("p", {
    class: "mb-0 text-white text-opacity-75"
  }, "Available Balance", -1),
  xC = {
    key: 0,
    class: "mb-0 text-white f-w-400"
  },
  SC = n("div", {
    class: "avtar"
  }, [n("i", {
    class: "ti ti-arrows-left-right f-18"
  })], -1),
  EC = {
    href: "#"
  },
  PC = {
    class: "card"
  },
  TC = {
    class: "card-body p-3"
  },
  LC = n("div", null, [n("p", {
    class: "mb-0 text-white text-opacity-75"
  }), n("h4", {
    class: "mb-0 text-black"
  }, "Quick Deposit")], -1),
  OC = n("div", {
    class: "avtar bg-light-primary"
  }, [n("i", {
    class: "ti ti-bolt f-18"
  })], -1),
  IC = {
    href: "#"
  },
  BC = {
    class: "card"
  },
  DC = {
    class: "card-body p-3"
  },
  MC = n("div", null, [n("p", {
    class: "mb-0 text-white text-opacity-75"
  }), n("h4", {
    class: "mb-0 text-black"
  }, "Quick Withdraw")], -1),
  RC = n("div", {
    class: "avtar bg-light-primary"
  }, [n("i", {
    class: "ti ti-bolt f-18"
  })], -1),
  NC = {
    key: 0,
    href: "#"
  },
  FC = {
    class: "card"
  },
  UC = {
    class: "card-body p-3"
  },
  VC = n("div", null, [n("p", {
    class: "mb-0 text-white text-opacity-75"
  }), n("h4", {
    class: "mb-0 text-black"
  }, "My Profile")], -1),
  jC = n("div", {
    class: "avtar bg-light-primary"
  }, [n("i", {
    class: "ti ti-user f-18"
  })], -1),
  HC = n("div", {
    class: "card bg-primary available-balance-card"
  }, [n("div", {
    class: "card-body p-3"
  }, [n("div", {
    class: "d-flex align-items-center justify-content-between"
  }, [n("div", null, [n("h4", {
    class: "mb-0 text-white"
  }, "Introducing Broker"), n("p", {
    class: "mb-0 text-white text-opacity-75"
  }, "Know More")]), n("div", {
    class: "avtar"
  }, [n("i", {
    class: "ti ti-award f-18"
  })])])])], -1),
  qC = n("div", {
    class: "card bg-primary available-balance-card"
  }, [n("div", {
    class: "card-body p-3"
  }, [n("div", {
    class: "d-flex align-items-center justify-content-between"
  }, [n("div", null, [n("h4", {
    class: "mb-0 text-white"
  }, "Introducing Broker"), n("p", {
    class: "mb-0 text-white text-opacity-75"
  }, "View Profile")]), n("div", {
    class: "avtar"
  }, [n("i", {
    class: "ti ti-award f-18"
  })])])])], -1);

function WC(e, t, s, o, i, r) {
  var g, $, C, A;
  const a = ke("TradingViewWidget"),
    l = ke("RouterLink"),
    d = ke("SmallLoadingSpinner"),
    p = ke("LiveAccountListWidget"),
    m = ke("DemoAccountListingWidgetView");
  return _(), w("div", c7, [n("div", d7, [n("div", u7, [ie(a)]), n("div", f7, [n("div", h7, [n("div", p7, [n("div", m7, [n("div", g7, [((g = e.dashboard_data) == null ? void 0 : g.wallet_created) == 1 ? (_(), w("h5", _7, "Wallet Balance")) : (_(), w("h5", b7, "Wallet "))]), n("div", v7, [y7, (($ = e.dashboard_data) == null ? void 0 : $.wallet_created) == 1 ? (_(), w("div", w7, [n("h3", $7, L(((C = e.dashboard_data) == null ? void 0 : C.wallet_balance) == 0 ? this.$formatCurrency(0) : this.$formatCurrency((A = e.dashboard_data) == null ? void 0 : A.wallet_balance)), 1)])) : (_(), w("div", C7, [n("button", {
    class: "btn btn-sm btn-outline-light",
    disabled: e.isLoading,
    type: "button"
  }, [A7, e.isLoading ? (_(), w("span", x7)) : D("", !0), J(" " + L(e.isLoading ? "Activating..." : "Activate Wallet"), 1)], 8, k7)]))]), S7])])]), n("div", E7, [n("div", P7, [n("div", T7, [n("div", L7, [O7, n("div", I7, [B7, n("div", D7, [ie(l, {
    to: "/fund/deposit/deposit",
    class: "dropdown-item",
    href: "#"
  }, {
    default: Ue(() => [J("Deposit Now ")]),
    _: 1
  }), ie(l, {
    to: "/transactions/deposit/deposit",
    class: "dropdown-item",
    href: "#"
  }, {
    default: Ue(() => [J("View Transactions")]),
    _: 1
  })])])]), e.isDataLoaded ? (_(), w("h4", M7, L(this.$formatCurrency(e.dashboard_data.total_deposit)), 1)) : (_(), ve(d, {
    key: 1
  })), R7])])]), n("div", N7, [n("div", F7, [n("div", U7, [n("div", V7, [j7, n("div", H7, [q7, n("div", W7, [ie(l, {
    to: "/fund/withdraw",
    class: "dropdown-item",
    href: "#"
  }, {
    default: Ue(() => [J("Withdraw Now ")]),
    _: 1
  }), ie(l, {
    to: "/transactions/deposit/withdraw",
    class: "dropdown-item",
    href: "#"
  }, {
    default: Ue(() => [J("View Transactions")]),
    _: 1
  })])])]), e.isDataLoaded ? (_(), w("h4", z7, L(this.$formatCurrency(e.dashboard_data.total_withdraw)), 1)) : (_(), ve(d, {
    key: 1
  })), K7])])]), n("div", G7, [n("a", Y7, [n("div", J7, [n("div", Z7, [n("div", X7, [Q7, n("div", eC, [tC, n("div", sC, [ie(l, {
    to: "/liveAccounts",
    class: "dropdown-item",
    href: "#"
  }, {
    default: Ue(() => [J("View Accounts ")]),
    _: 1
  })])])]), e.isDataLoaded ? (_(), w("h3", nC, L(e.dashboard_data.total_mt_account), 1)) : (_(), ve(d, {
    key: 1
  })), oC])])])])]), n("div", iC, [n("div", rC, [n("div", aC, [n("div", lC, [n("div", cC, [dC, n("div", uC, [fC, n("div", hC, [ie(l, {
    to: "/createLiveAccount",
    class: "dropdown-item",
    href: "#"
  }, {
    default: Ue(() => [J("Open Live Account")]),
    _: 1
  }), pC])])]), mC]), n("div", gC, [n("div", _C, [ie(p, {
    onTotalBalanceDataReceived: e.handleTotalBalanceDataReceived
  }, null, 8, ["onTotalBalanceDataReceived"])]), n("div", bC, [ie(m)])]), vC])]), n("div", yC, [n("a", wC, [n("div", $C, [n("div", CC, [n("div", kC, [n("div", null, [AC, e.total_balance != null ? (_(), w("h4", xC, L(this.$formatCurrency(e.total_balance)), 1)) : (_(), ve(d, {
    key: 1
  }))]), SC])])])]), n("a", EC, [n("div", PC, [n("div", TC, [ie(l, {
    to: "/fund/deposit/deposit",
    class: "d-flex align-items-center justify-content-between"
  }, {
    default: Ue(() => [LC, OC]),
    _: 1
  })])])]), n("a", IC, [n("div", BC, [n("div", DC, [ie(l, {
    to: "/fund/withdraw",
    class: "d-flex align-items-center justify-content-between"
  }, {
    default: Ue(() => [MC, RC]),
    _: 1
  })])])]), e.is_ib ? D("", !0) : (_(), w("a", NC, [n("div", FC, [n("div", UC, [ie(l, {
    to: "/user/profile",
    class: "d-flex align-items-center justify-content-between"
  }, {
    default: Ue(() => [VC, jC]),
    _: 1
  })])])])), e.is_ib ? D("", !0) : (_(), ve(l, {
    key: 1,
    to: "/ib"
  }, {
    default: Ue(() => [HC]),
    _: 1
  })), e.is_ib ? (_(), ve(l, {
    key: 2,
    to: "/ib-profile"
  }, {
    default: Ue(() => [qC]),
    _: 1
  })) : D("", !0)])])])])
}
const zC = je(l7, [
    ["render", WC]
  ]),
  ps = "/assets/wallet_icon-S47P7YIt.webp",
  KC = {
    data() {
      return {
        transaction_list: null,
        transaction_count: null,
        currentPage: 1,
        last_page: null,
        totalPages: 0,
        pageSize: 10,
        from: null,
        to: null,
        total: null,
        API_BASE_URL: Ge("API_BASE_URL")
      }
    },
    watch: {},
    components: {},
    mounted() {
      this.loadDataFromLocalStorage(), this.fetchTransactions()
    },
    methods: {
      loadDataFromLocalStorage() {
        const e = me.getDataWithExpiration("deposit_list"),
          t = me.getDataWithExpiration("total_deposit_count");
        e && (this.transaction_list = e), t && (this.transaction_count = t)
      },
      fetchTransactions() {
        ye.get(`${Ge("API_BASE_URL")}/transaction/deposit?page=${this.currentPage}`, {
          headers: {
            Authorization: `Bearer ${$e.get("authToken")}`
          }
        }).then(e => {
          me.setDataWithTimestamp("deposit_list", e.data.result.transaction.data, 30), me.setDataWithTimestamp("total_deposit_count", e.data.result.transaction.total, 30), this.transaction_list = e.data.result.transaction.data, this.transaction_count = e.data.result.transaction.total, this.totalPages = e.data.result.transaction.last_page, this.from = e.data.result.transaction.from, this.to = e.data.result.transaction.to, this.total = e.data.result.transaction.total
        }).catch(e => {
          var t;
          Swal.fire({
            icon: "error",
            title: (t = e.response) == null ? void 0 : t.data.message
          }).then(s => {
            s.isConfirmed && window.location.reload()
          })
        })
      },
      nextPage() {
        this.currentPage < this.totalPages && (this.currentPage++, this.fetchTransactions())
      },
      prevPage() {
        this.currentPage > 1 && (this.currentPage--, this.fetchTransactions())
      },
      goToPage(e) {
        e >= 1 && e <= this.totalPages && (this.currentPage = e, this.fetchTransactions())
      },
      formattedDate(e) {
        const t = new Date(e),
          s = {
            year: "numeric",
            month: "long",
            day: "numeric"
          };
        return t.toLocaleDateString("en-US", s)
      },
      formattedTime(e) {
        const t = new Date(e),
          s = {
            hour: "numeric",
            minute: "numeric",
            hour12: !0
          };
        return t.toLocaleTimeString("en-US", s)
      }
    }
  },
  GC = {
    class: "px-5"
  },
  YC = {
    key: 0,
    class: "table"
  },
  JC = n("thead", null, [n("tr", null, [n("th", {
    scope: "col"
  }, "ACCOUNT"), n("th", {
    scope: "col"
  }, "TRANSACTION DATE"), n("th", {
    scope: "col"
  }, "TYPE"), n("th", {
    scope: "col"
  }, "METHOD"), n("th", {
    scope: "col"
  }, "AMOUNT"), n("th", {
    scope: "col"
  }, "APPROVED AMOUNT"), n("th", {
    scope: "col"
  }, "STATUS")])], -1),
  ZC = {
    key: 0
  },
  XC = {
    class: "d-flex align-items-center"
  },
  QC = {
    class: "avtar avtar-s border"
  },
  ek = {
    key: 0,
    src: ps,
    class: "wid-30",
    alt: "logo"
  },
  tk = {
    key: 1,
    src: Mt,
    class: "wid-30",
    alt: "logo"
  },
  sk = {
    class: "ms-2"
  },
  nk = {
    class: "mb-0"
  },
  ok = {
    key: 0,
    class: "text-muted mb-0"
  },
  ik = n("small", null, "Live Account", -1),
  rk = [ik],
  ak = {
    class: "f-w-500"
  },
  lk = {
    class: "text-muted mb-0"
  },
  ck = n("td", null, [n("h6", {
    class: "f-w-500"
  }, "Deposit")], -1),
  dk = {
    class: "f-w-500"
  },
  uk = {
    class: "f-w-500 f-16"
  },
  fk = {
    class: "f-w-500 f-16"
  },
  hk = {
    key: 0,
    class: "text-success"
  },
  pk = n("p", null, "APPROVED", -1),
  mk = [pk],
  gk = {
    key: 1,
    class: "text-warning"
  },
  _k = n("p", null, "PENDING", -1),
  bk = [_k],
  vk = {
    key: 2,
    class: "text-danger"
  },
  yk = n("p", null, "REJECTED", -1),
  wk = [yk],
  $k = n("hr", null, null, -1),
  Ck = {
    class: "row mt-2 justify-content-between"
  },
  kk = {
    key: 0,
    class: "col-md-auto me-auto"
  },
  Ak = {
    class: "dt-info",
    "aria-live": "polite",
    id: "base-style_info",
    role: "status"
  },
  xk = {
    class: "col-md-auto ms-auto"
  },
  Sk = {
    class: "dt-paging paging_full_numbers"
  },
  Ek = {
    class: "pagination"
  },
  Pk = ["onClick"];

function Tk(e, t, s, o, i, r) {
  const a = ke("EmptyStateView"),
    l = ke("LoadingSpinner");
  return _(), w("div", GC, [i.transaction_count && i.transaction_count > 0 ? (_(), w("table", YC, [JC, i.transaction_list ? (_(), w("tbody", ZC, [(_(!0), w(Te, null, Je(i.transaction_list, d => (_(), w("tr", {
    key: d.id
  }, [n("td", null, [n("div", XC, [n("div", null, [n("div", QC, [d.is_wallet ? (_(), w("img", ek)) : (_(), w("img", tk))])]), n("div", sk, [n("h6", nk, L(d.is_wallet ? "WALLET" : d.mt_id), 1), d.is_wallet ? D("", !0) : (_(), w("p", ok, rk))])])]), n("td", null, [n("h6", ak, L(r.formattedDate(d.created_at)), 1), n("p", lk, [n("small", null, L(r.formattedTime(d.created_at)), 1)])]), ck, n("td", null, [n("h6", dk, L(d.deposit_method), 1)]), n("td", null, [n("h6", uk, L(this.$formatCurrency(d.amount_in_usd)), 1)]), n("td", null, [n("h6", fk, L(this.$formatCurrency(d.approved_amount)), 1)]), d.status == 3 ? (_(), w("td", hk, mk)) : D("", !0), d.status == 0 ? (_(), w("td", gk, bk)) : D("", !0), d.status == 2 ? (_(), w("td", vk, wk)) : D("", !0)]))), 128))])) : D("", !0)])) : D("", !0), i.transaction_count != null && i.transaction_count < 1 ? (_(), ve(a, {
    key: 1,
    msg: "No Deposit History found !"
  })) : D("", !0), i.transaction_count == null ? (_(), ve(l, {
    key: 2
  })) : D("", !0), $k, n("div", Ck, [i.transaction_list ? (_(), w("div", kk, [n("div", Ak, "Showing " + L(i.from) + " to " + L(i.to) + " of " + L(i.total) + " entries ", 1)])) : D("", !0), n("div", xk, [n("div", Sk, [n("ul", Ek, [n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === 1
    }])
  }, [n("a", {
    class: "page-link first",
    "aria-controls": "base-style",
    "aria-disabled": "true",
    "aria-label": "First",
    "data-dt-idx": "first",
    tabindex: "-1",
    onClick: t[0] || (t[0] = d => r.goToPage(1))
  }, "«")], 2), n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === 1
    }])
  }, [n("a", {
    class: "page-link previous",
    "aria-controls": "base-style",
    "aria-disabled": "true",
    "aria-label": "Previous",
    "data-dt-idx": "previous",
    tabindex: "-1",
    onClick: t[1] || (t[1] = (...d) => r.prevPage && r.prevPage(...d))
  }, "‹")], 2), (_(!0), w(Te, null, Je(i.totalPages, d => (_(), w("li", {
    key: d,
    class: Ie(["dt-paging-button page-item", {
      active: d === i.currentPage
    }])
  }, [n("a", {
    href: "#",
    class: "page-link",
    "aria-controls": "base-style",
    tabindex: "0",
    onClick: p => r.goToPage(d)
  }, L(d), 9, Pk)], 2))), 128)), n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === i.totalPages
    }])
  }, [n("a", {
    href: "#",
    class: "page-link next",
    "aria-controls": "base-style",
    "aria-label": "Next",
    "data-dt-idx": "next",
    tabindex: "0",
    onClick: t[2] || (t[2] = (...d) => r.nextPage && r.nextPage(...d))
  }, "›")], 2), n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === i.totalPages
    }])
  }, [n("a", {
    href: "#",
    class: "page-link last",
    "aria-controls": "base-style",
    "aria-label": "Last",
    "data-dt-idx": "last",
    tabindex: "0",
    onClick: t[3] || (t[3] = d => r.goToPage(i.totalPages))
  }, "»")], 2)])])])])])
}
const Lk = je(KC, [
    ["render", Tk]
  ]),
  Ok = {
    data() {
      return {
        transaction_list: null,
        transaction_count: null,
        currentPage: 1,
        last_page: null,
        totalPages: 0,
        pageSize: 10,
        from: null,
        to: null,
        total: null,
        API_BASE_URL: Ge("API_BASE_URL")
      }
    },
    watch: {},
    components: {},
    mounted() {
      this.loadDataFromLocalStorage(), this.fetchTransactions()
    },
    methods: {
      loadDataFromLocalStorage() {
        const e = me.getDataWithExpiration("withdraw_list"),
          t = me.getDataWithExpiration("total_withdraw_count");
        e && (this.transaction_list = e), t && (this.transaction_count = t)
      },
      fetchTransactions() {
        ye.get(`${Ge("API_BASE_URL")}/transaction/withdraw?page=${this.currentPage}`, {
          headers: {
            Authorization: `Bearer ${$e.get("authToken")}`
          }
        }).then(e => {
          me.setDataWithTimestamp("withdraw_list", e.data.result.transaction.data, 30), me.setDataWithTimestamp("total_withdraw_count", e.data.result.transaction.total, 30), this.transaction_list = e.data.result.transaction.data, this.transaction_count = e.data.result.transaction.total, this.totalPages = e.data.result.transaction.last_page, this.from = e.data.result.transaction.from, this.to = e.data.result.transaction.to, this.total = e.data.result.transaction.total
        }).catch(e => {
          var t;
          Swal.fire({
            icon: "error",
            title: (t = e.response) == null ? void 0 : t.data.message
          }).then(s => {
            s.isConfirmed && window.location.reload()
          })
        })
      },
      nextPage() {
        this.currentPage < this.totalPages && (this.currentPage++, this.fetchTransactions())
      },
      prevPage() {
        this.currentPage > 1 && (this.currentPage--, this.fetchTransactions())
      },
      goToPage(e) {
        e >= 1 && e <= this.totalPages && (this.currentPage = e, this.fetchTransactions())
      },
      formattedDate(e) {
        const t = new Date(e),
          s = {
            year: "numeric",
            month: "long",
            day: "numeric"
          };
        return t.toLocaleDateString("en-US", s)
      },
      formattedTime(e) {
        const t = new Date(e),
          s = {
            hour: "numeric",
            minute: "numeric",
            hour12: !0
          };
        return t.toLocaleTimeString("en-US", s)
      }
    }
  },
  Ik = {
    class: "px-5"
  },
  Bk = {
    key: 0,
    class: "table"
  },
  Dk = n("thead", null, [n("tr", null, [n("th", {
    scope: "col"
  }, "ACCOUNT"), n("th", {
    scope: "col"
  }, "TRANSACTION DATE"), n("th", {
    scope: "col"
  }, "TYPE"), n("th", {
    scope: "col"
  }, "METHOD"), n("th", {
    scope: "col"
  }, "AMOUNT"), n("th", {
    scope: "col"
  }, "STATUS")])], -1),
  Mk = {
    key: 0
  },
  Rk = {
    class: "d-flex align-items-center"
  },
  Nk = {
    class: "avtar avtar-s border"
  },
  Fk = {
    key: 0,
    src: ps,
    class: "wid-30",
    alt: "logo"
  },
  Uk = {
    key: 1,
    src: Mt,
    class: "wid-30",
    alt: "logo"
  },
  Vk = {
    class: "ms-2"
  },
  jk = {
    class: "mb-0"
  },
  Hk = {
    key: 0,
    class: "text-muted mb-0"
  },
  qk = n("small", null, "Live Account", -1),
  Wk = [qk],
  zk = {
    class: "f-w-500"
  },
  Kk = {
    class: "text-muted mb-0"
  },
  Gk = n("td", null, [n("h6", {
    class: "f-w-500"
  }, "Withdraw")], -1),
  Yk = {
    class: "f-w-500"
  },
  Jk = {
    class: "f-w-500 f-16"
  },
  Zk = {
    key: 0,
    class: "text-success"
  },
  Xk = n("p", null, "APPROVED", -1),
  Qk = [Xk],
  eA = {
    key: 1,
    class: "text-warning"
  },
  tA = n("p", null, "PENDING", -1),
  sA = [tA],
  nA = {
    key: 2,
    class: "text-danger"
  },
  oA = n("p", null, "REJECTED", -1),
  iA = [oA],
  rA = n("hr", null, null, -1),
  aA = {
    class: "row mt-2 justify-content-between"
  },
  lA = {
    key: 0,
    class: "col-md-auto me-auto"
  },
  cA = {
    class: "dt-info",
    "aria-live": "polite",
    id: "base-style_info",
    role: "status"
  },
  dA = {
    class: "col-md-auto ms-auto"
  },
  uA = {
    class: "dt-paging paging_full_numbers"
  },
  fA = {
    class: "pagination"
  },
  hA = ["onClick"];

function pA(e, t, s, o, i, r) {
  const a = ke("EmptyStateView"),
    l = ke("LoadingSpinner");
  return _(), w("div", Ik, [i.transaction_count && i.transaction_count > 0 ? (_(), w("table", Bk, [Dk, i.transaction_list ? (_(), w("tbody", Mk, [(_(!0), w(Te, null, Je(i.transaction_list, d => (_(), w("tr", {
    key: d.id
  }, [n("td", null, [n("div", Rk, [n("div", null, [n("div", Nk, [d.is_wallet ? (_(), w("img", Fk)) : (_(), w("img", Uk))])]), n("div", Vk, [n("h6", jk, L(d.is_wallet ? "WALLET" : d.mt_id), 1), d.is_wallet ? D("", !0) : (_(), w("p", Hk, Wk))])])]), n("td", null, [n("h6", zk, L(r.formattedDate(d.created_at)), 1), n("p", Kk, [n("small", null, L(r.formattedTime(d.created_at)), 1)])]), Gk, n("td", null, [n("h6", Yk, L(d.withdarw_method), 1)]), n("td", null, [n("h6", Jk, L(this.$formatCurrency(d.amount_req)), 1)]), d.status === 3 ? (_(), w("td", Zk, Qk)) : D("", !0), d.status === 0 ? (_(), w("td", eA, sA)) : D("", !0), d.status === 2 ? (_(), w("td", nA, iA)) : D("", !0)]))), 128))])) : D("", !0)])) : D("", !0), i.transaction_count != null && i.transaction_count < 1 ? (_(), ve(a, {
    key: 1,
    msg: "No Withdraw History found !"
  })) : D("", !0), i.transaction_count == null ? (_(), ve(l, {
    key: 2
  })) : D("", !0), rA, n("div", aA, [i.transaction_list ? (_(), w("div", lA, [n("div", cA, "Showing " + L(i.from) + " to " + L(i.to) + " of " + L(i.total) + " entries ", 1)])) : D("", !0), n("div", dA, [n("div", uA, [n("ul", fA, [n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === 1
    }])
  }, [n("a", {
    class: "page-link first",
    "aria-controls": "base-style",
    "aria-disabled": "true",
    "aria-label": "First",
    "data-dt-idx": "first",
    tabindex: "-1",
    onClick: t[0] || (t[0] = d => r.goToPage(1))
  }, "«")], 2), n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === 1
    }])
  }, [n("a", {
    class: "page-link previous",
    "aria-controls": "base-style",
    "aria-disabled": "true",
    "aria-label": "Previous",
    "data-dt-idx": "previous",
    tabindex: "-1",
    onClick: t[1] || (t[1] = (...d) => r.prevPage && r.prevPage(...d))
  }, "‹")], 2), (_(!0), w(Te, null, Je(i.totalPages, d => (_(), w("li", {
    key: d,
    class: Ie(["dt-paging-button page-item", {
      active: d === i.currentPage
    }])
  }, [n("a", {
    href: "#",
    class: "page-link",
    "aria-controls": "base-style",
    tabindex: "0",
    onClick: p => r.goToPage(d)
  }, L(d), 9, hA)], 2))), 128)), n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === i.totalPages
    }])
  }, [n("a", {
    href: "#",
    class: "page-link next",
    "aria-controls": "base-style",
    "aria-label": "Next",
    "data-dt-idx": "next",
    tabindex: "0",
    onClick: t[2] || (t[2] = (...d) => r.nextPage && r.nextPage(...d))
  }, "›")], 2), n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === i.totalPages
    }])
  }, [n("a", {
    href: "#",
    class: "page-link last",
    "aria-controls": "base-style",
    "aria-label": "Last",
    "data-dt-idx": "last",
    tabindex: "0",
    onClick: t[3] || (t[3] = d => r.goToPage(i.totalPages))
  }, "»")], 2)])])])])])
}
const mA = je(Ok, [
    ["render", pA]
  ]),
  gA = {
    data() {
      return {
        transaction_list: null,
        currentPage: 1,
        last_page: null,
        totalPages: 0,
        pageSize: 10,
        from: null,
        to: null,
        total: null,
        API_BASE_URL: Ge("API_BASE_URL")
      }
    },
    watch: {},
    components: {},
    mounted() {
      this.fetchTransactions()
    },
    methods: {
      fetchTransactions() {
        ye.get(`${this.API_BASE_URL}/transaction/transfer?page=${this.currentPage}`, {
          headers: {
            Authorization: `Bearer ${$e.get("authToken")}`
          }
        }).then(e => {
          this.transaction_list = e.data.result.transaction.data, this.totalPages = e.data.result.transaction.last_page, this.from = e.data.result.transaction.from, this.to = e.data.result.transaction.to, this.total = e.data.result.transaction.total
        }).catch(e => {
          var t;
          Swal.fire({
            icon: "error",
            title: (t = e.response) == null ? void 0 : t.data.message
          }).then(s => {
            s.isConfirmed && window.location.reload()
          })
        })
      },
      nextPage() {
        this.currentPage < this.totalPages && (this.currentPage++, this.fetchTransactions())
      },
      prevPage() {
        this.currentPage > 1 && (this.currentPage--, this.fetchTransactions())
      },
      goToPage(e) {
        e >= 1 && e <= this.totalPages && (this.currentPage = e, this.fetchTransactions())
      },
      formattedDate(e) {
        const t = new Date(e),
          s = {
            year: "numeric",
            month: "long",
            day: "numeric"
          };
        return t.toLocaleDateString("en-US", s)
      },
      formattedTime(e) {
        const t = new Date(e),
          s = {
            hour: "numeric",
            minute: "numeric",
            hour12: !0
          };
        return t.toLocaleTimeString("en-US", s)
      }
    }
  },
  _A = {
    class: "px-5"
  },
  bA = {
    class: "table p-3"
  },
  vA = n("thead", null, [n("tr", null, [n("th", {
    scope: "col"
  }, "FROM ACCOUNT"), n("th", {
    scope: "col"
  }, "TO ACCOUNT"), n("th", {
    scope: "col"
  }, "TRANSACTION DATE"), n("th", {
    scope: "col",
    class: "text-end"
  }, "AMOUNT"), n("th", {
    scope: "col",
    class: "text-end"
  }, "STATUS")])], -1),
  yA = {
    key: 0,
    class: "py-5"
  },
  wA = {
    class: "d-flex align-items-center"
  },
  $A = {
    class: "avtar avtar-s border"
  },
  CA = {
    key: 0,
    src: ps,
    class: "wid-30",
    alt: "logo"
  },
  kA = {
    key: 1,
    src: Mt,
    class: "wid-30",
    alt: "logo"
  },
  AA = {
    class: "ms-2"
  },
  xA = {
    key: 0,
    class: "mb-0"
  },
  SA = {
    key: 1,
    class: "mb-0"
  },
  EA = {
    key: 2,
    class: "text-muted mb-0"
  },
  PA = n("small", null, "Live Account", -1),
  TA = [PA],
  LA = {
    class: "d-flex align-items-center"
  },
  OA = {
    class: "avtar avtar-s border"
  },
  IA = {
    key: 0,
    src: ps,
    class: "wid-30",
    alt: "logo"
  },
  BA = {
    key: 1,
    src: Mt,
    class: "wid-30",
    alt: "logo"
  },
  DA = {
    class: "ms-2"
  },
  MA = {
    key: 0,
    class: "mb-0"
  },
  RA = {
    key: 1,
    class: "mb-0"
  },
  NA = {
    key: 2,
    class: "text-muted mb-0"
  },
  FA = n("small", null, "Live Account", -1),
  UA = [FA],
  VA = {
    class: "f-w-500"
  },
  jA = {
    class: "text-muted mb-0"
  },
  HA = {
    class: "text-end"
  },
  qA = {
    class: "f-w-500 f-16"
  },
  WA = {
    class: "text-end"
  },
  zA = {
    key: 0,
    class: "text-success mb-0"
  },
  KA = {
    key: 1,
    class: "text-warning mb-0"
  },
  GA = {
    key: 2,
    class: "text-danger mb-0"
  },
  YA = n("hr", null, null, -1),
  JA = {
    class: "row mt-2 justify-content-between"
  },
  ZA = {
    key: 0,
    class: "col-md-auto me-auto"
  },
  XA = {
    class: "dt-info",
    "aria-live": "polite",
    id: "base-style_info",
    role: "status"
  },
  QA = {
    class: "col-md-auto ms-auto"
  },
  ex = {
    class: "dt-paging paging_full_numbers"
  },
  tx = {
    class: "pagination"
  },
  sx = ["onClick"];

function nx(e, t, s, o, i, r) {
  const a = ke("LoadingSpinner");
  return _(), w("div", _A, [n("table", bA, [vA, i.transaction_list ? (_(), w("tbody", yA, [(_(!0), w(Te, null, Je(i.transaction_list, l => (_(), w("tr", {
    class: "p-5",
    key: l.id
  }, [n("td", null, [n("div", wA, [n("div", null, [n("div", $A, [l.from_mt_wallet == 1 ? (_(), w("img", CA)) : (_(), w("img", kA))])]), n("div", AA, [l.from_mt_wallet == 0 ? (_(), w("h6", xA, L(l.from_mt_id), 1)) : (_(), w("h6", SA, "Wallet")), l.from_mt_wallet == 0 ? (_(), w("p", EA, TA)) : D("", !0)])])]), n("td", null, [n("div", LA, [n("div", null, [n("div", OA, [l.to_mt_wallet == 1 ? (_(), w("img", IA)) : (_(), w("img", BA))])]), n("div", DA, [l.to_mt_wallet == 0 ? (_(), w("h6", MA, L(l.to_mt_id), 1)) : (_(), w("h6", RA, "Wallet")), l.to_mt_wallet == 0 ? (_(), w("p", NA, UA)) : D("", !0)])])]), n("td", null, [n("h6", VA, L(r.formattedDate(l.created_at)), 1), n("p", jA, [n("small", null, L(r.formattedTime(l.created_at)), 1)])]), n("td", HA, [n("h6", qA, L(this.$formatCurrency(l.trans_amount)), 1)]), n("td", WA, [l.status == 3 ? (_(), w("p", zA, "SUCCESS")) : D("", !0), l.status == 0 ? (_(), w("p", KA, "PENDING")) : D("", !0), l.status == 2 ? (_(), w("p", GA, "REJECTED")) : D("", !0)])]))), 128))])) : (_(), ve(a, {
    key: 1
  }))]), YA, n("div", JA, [i.transaction_list ? (_(), w("div", ZA, [n("div", XA, "Showing " + L(i.from) + " to " + L(i.to) + " of " + L(i.total) + " entries ", 1)])) : D("", !0), n("div", QA, [n("div", ex, [n("ul", tx, [n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === 1
    }])
  }, [n("a", {
    class: "page-link first",
    "aria-controls": "base-style",
    "aria-disabled": "true",
    "aria-label": "First",
    "data-dt-idx": "first",
    tabindex: "-1",
    onClick: t[0] || (t[0] = l => r.goToPage(1))
  }, "«")], 2), n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === 1
    }])
  }, [n("a", {
    class: "page-link previous",
    "aria-controls": "base-style",
    "aria-disabled": "true",
    "aria-label": "Previous",
    "data-dt-idx": "previous",
    tabindex: "-1",
    onClick: t[1] || (t[1] = (...l) => r.prevPage && r.prevPage(...l))
  }, "‹")], 2), (_(!0), w(Te, null, Je(i.totalPages, l => (_(), w("li", {
    key: l,
    class: Ie(["dt-paging-button page-item", {
      active: l === i.currentPage
    }])
  }, [n("a", {
    href: "#",
    class: "page-link",
    "aria-controls": "base-style",
    tabindex: "0",
    onClick: d => r.goToPage(l)
  }, L(l), 9, sx)], 2))), 128)), n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === i.totalPages
    }])
  }, [n("a", {
    href: "#",
    class: "page-link next",
    "aria-controls": "base-style",
    "aria-label": "Next",
    "data-dt-idx": "next",
    tabindex: "0",
    onClick: t[2] || (t[2] = (...l) => r.nextPage && r.nextPage(...l))
  }, "›")], 2), n("li", {
    class: Ie(["dt-paging-button page-item", {
      disabled: i.currentPage === i.totalPages
    }])
  }, [n("a", {
    href: "#",
    class: "page-link last",
    "aria-controls": "base-style",
    "aria-label": "Last",
    "data-dt-idx": "last",
    tabindex: "0",
    onClick: t[3] || (t[3] = l => r.goToPage(i.totalPages))
  }, "»")], 2)])])])])])
}
const ox = je(gA, [
    ["render", nx]
  ]),
  ix = {
    props: {
      option: String
    },
    data() {
      return {
        selectedOption: this.option || "deposit"
      }
    },
    components: {
      DepositListView: Lk,
      WithdrawListView: mA,
      InternalTransactionsView: ox
    }
  },
  rx = {
    class: "pc-container"
  },
  ax = {
    class: "pc-content"
  },
  lx = Ae('<div class="page-header mb-0 pb-0"><div class="page-block"><div class="row align-items-center"><div class="col-md-12"><div class="page-header-title h2"><h4 class="mb-0">Transactions</h4></div></div></div></div></div>', 1),
  cx = {
    class: "row"
  },
  dx = {
    class: "col-lg-12"
  },
  ux = {
    class: "card"
  },
  fx = {
    class: "card-body border-bottom pb-0"
  },
  hx = Ae('<div class="d-flex align-items-center justify-content-between"><h5 class="mb-0">All Transactions</h5><div class="dropdown"><a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Today</a><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly</a></div></div></div>', 1),
  px = {
    class: "nav nav-tabs analytics-tab",
    id: "myTab",
    role: "tablist"
  },
  mx = {
    class: "nav-item",
    role: "presentation"
  },
  gx = {
    class: "nav-item",
    role: "presentation"
  },
  _x = {
    class: "nav-item",
    role: "presentation"
  },
  bx = {
    class: "tab-content",
    id: "myTabContent"
  },
  vx = {
    class: "card-footer"
  },
  yx = {
    class: "row g-2"
  },
  wx = n("div", {
    class: "col-md-6"
  }, null, -1),
  $x = {
    class: "col-md-6"
  },
  Cx = {
    class: "d-grid"
  },
  kx = n("button", {
    class: "btn btn-primary"
  }, [n("span", {
    class: "text-truncate w-100"
  }, "Create new Transaction")], -1);

function Ax(e, t, s, o, i, r) {
  const a = ke("DepositListView"),
    l = ke("WithdrawListView"),
    d = ke("InternalTransactionsView"),
    p = ke("RouterLink");
  return _(), w("div", rx, [n("div", ax, [lx, n("div", cx, [n("div", dx, [n("div", ux, [n("div", fx, [hx, n("ul", px, [n("li", mx, [n("button", {
    class: Ie(["nav-link", {
      active: i.selectedOption === "deposit"
    }]),
    id: "analytics-tab-1",
    "data-bs-toggle": "tab",
    "data-bs-target": "#analytics-tab-1-pane",
    type: "button",
    role: "tab",
    "aria-controls": "analytics-tab-1-pane",
    "aria-selected": "true"
  }, "Deposits", 2)]), n("li", gx, [n("button", {
    class: Ie(["nav-link", {
      active: i.selectedOption === "withdraw"
    }]),
    id: "analytics-tab-2",
    "data-bs-toggle": "tab",
    "data-bs-target": "#analytics-tab-2-pane",
    type: "button",
    role: "tab",
    "aria-controls": "analytics-tab-2-pane",
    "aria-selected": "false"
  }, "Withdrawals", 2)]), n("li", _x, [n("button", {
    class: Ie(["nav-link", {
      active: i.selectedOption === "transfer"
    }]),
    id: "analytics-tab-3",
    "data-bs-toggle": "tab",
    "data-bs-target": "#analytics-tab-3-pane",
    type: "button",
    role: "tab",
    "aria-controls": "analytics-tab-3-pane",
    "aria-selected": "false"
  }, "Internal Transfers", 2)])])]), n("div", bx, [n("div", {
    class: Ie(["tab-pane fade", {
      "show active": i.selectedOption === "deposit"
    }]),
    id: "analytics-tab-1-pane",
    role: "tabpanel",
    "aria-labelledby": "analytics-tab-1"
  }, [ie(a)], 2), n("div", {
    class: Ie(["tab-pane fade", {
      "show active": i.selectedOption === "withdraw"
    }]),
    id: "analytics-tab-2-pane",
    role: "tabpanel",
    "aria-labelledby": "analytics-tab-2"
  }, [ie(l)], 2), n("div", {
    class: Ie(["tab-pane fade", {
      "show active": i.selectedOption === "transfer"
    }]),
    id: "analytics-tab-3-pane",
    role: "tabpanel",
    "aria-labelledby": "analytics-tab-3"
  }, [ie(d)], 2)]), n("div", vx, [n("div", yx, [wx, n("div", $x, [n("div", Cx, [ie(p, {
    to: "/fund/deposit/deposit",
    class: "d-grid"
  }, {
    default: Ue(() => [kx]),
    _: 1
  })])])])])])])])])])
}
const xx = je(ix, [
    ["render", Ax]
  ]),
  Sx = Dt({
    components: {
      LiveAccountListWidgetView: M1
    },
    setup() {
      return {}
    }
  }),
  Ex = {
    class: "pc-container"
  },
  Px = {
    class: "pc-content"
  },
  Tx = Ae('<div class="page-header mb-0 pb-0"><div class="page-block"><div class="row align-items-center"><div class="col-md-12"><div class="page-header-title h2"><h4 class="mb-0">Live MT5 Accounts</h4></div></div></div></div></div>', 1),
  Lx = {
    class: "row"
  },
  Ox = {
    class: "col-md-12 col-lg-9"
  },
  Ix = {
    class: "card"
  },
  Bx = Ae('<div class="card-body border-bottom pb-0"><div class="d-flex align-items-center justify-content-between"><h5 class="mb-0">My Trading Accounts</h5><div class="dropdown"><a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Open New Account</a></div></div></div></div>', 1),
  Dx = {
    class: "tab-content",
    id: "myTabContent"
  },
  Mx = {
    class: "col-md-6 col-lg-3"
  },
  Rx = n("div", {
    class: "card bg-primary available-balance-card"
  }, [n("div", {
    class: "card-body p-3"
  }, [n("div", {
    class: "d-flex align-items-center justify-content-between"
  }, [n("div", null, [n("h4", {
    class: "mb-0 text-white"
  }, "Create Account"), n("p", {
    class: "mb-0 text-white text-opacity-75"
  }, "Open Live Account")]), n("div", {
    class: "avtar"
  }, [n("i", {
    class: "ti ti-folder-plus f-20"
  })])])])], -1),
  Nx = {
    href: "#"
  },
  Fx = {
    class: "card"
  },
  Ux = {
    class: "card-body p-3"
  },
  Vx = n("div", null, [n("p", {
    class: "mb-0 text-white text-opacity-75"
  }), n("h4", {
    class: "mb-0 text-black"
  }, "Quick Deposit")], -1),
  jx = n("div", {
    class: "avtar bg-success-subtle"
  }, [n("i", {
    class: "ti ti-bolt f-18"
  })], -1),
  Hx = {
    href: "#"
  },
  qx = {
    class: "card"
  },
  Wx = {
    class: "card-body p-3"
  },
  zx = n("div", null, [n("p", {
    class: "mb-0 text-white text-opacity-75"
  }), n("h4", {
    class: "mb-0 text-black"
  }, "Quick Withdraw")], -1),
  Kx = n("div", {
    class: "avtar bg-success-subtle"
  }, [n("i", {
    class: "ti ti-bolt f-18"
  })], -1),
  Gx = {
    href: "#"
  },
  Yx = {
    class: "card"
  },
  Jx = {
    class: "card-body p-3"
  },
  Zx = n("div", null, [n("p", {
    class: "mb-0 text-white text-opacity-75"
  }), n("h4", {
    class: "mb-0 text-black"
  }, "My Profile")], -1),
  Xx = n("div", {
    class: "avtar bg-success-subtle"
  }, [n("i", {
    class: "ti ti-user f-18"
  })], -1);

function Qx(e, t, s, o, i, r) {
  const a = ke("LiveAccountListWidgetView"),
    l = ke("RouterLink");
  return _(), w("div", Ex, [n("div", Px, [Tx, n("div", Lx, [n("div", Ox, [n("div", Ix, [Bx, n("div", Dx, [ie(a)])])]), n("div", Mx, [ie(l, {
    to: "/createLiveAccount"
  }, {
    default: Ue(() => [Rx]),
    _: 1
  }), n("a", Nx, [n("div", Fx, [n("div", Ux, [ie(l, {
    to: "/fund/deposit/deposit",
    class: "d-flex align-items-center justify-content-between"
  }, {
    default: Ue(() => [Vx, jx]),
    _: 1
  })])])]), n("a", Hx, [n("div", qx, [n("div", Wx, [ie(l, {
    to: "/fund/withdraw",
    class: "d-flex align-items-center justify-content-between"
  }, {
    default: Ue(() => [zx, Kx]),
    _: 1
  })])])]), n("a", Gx, [n("div", Yx, [n("div", Jx, [ie(l, {
    to: "/user/profile",
    class: "d-flex align-items-center justify-content-between"
  }, {
    default: Ue(() => [Zx, Xx]),
    _: 1
  })])])])])])])])
}
const eS = je(Sx, [
    ["render", Qx]
  ]),
  tS = Dt({
    components: {
      DemoAccountListingWidgetView: N1
    },
    setup() {
      return {}
    }
  }),
  sS = {
    class: "pc-container"
  },
  nS = {
    class: "pc-content"
  },
  oS = Ae('<div class="page-header mb-0 pb-0"><div class="page-block"><div class="row align-items-center"><div class="col-md-12"><div class="page-header-title h2"><h4 class="mb-0">Demo MT5 Accounts</h4></div></div></div></div></div>', 1),
  iS = {
    class: "row"
  },
  rS = {
    class: "col-md-12 col-lg-9"
  },
  aS = {
    class: "card"
  },
  lS = Ae('<div class="card-body border-bottom pb-0"><div class="d-flex align-items-center justify-content-between"><h5 class="mb-0">My DEMO Trading Accounts</h5><div class="dropdown"><a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Open New Account</a></div></div></div></div>', 1),
  cS = {
    class: "tab-content",
    id: "myTabContent"
  },
  dS = {
    class: "col-md-6 col-lg-3"
  },
  uS = n("div", {
    class: "card bg-primary available-balance-card"
  }, [n("div", {
    class: "card-body p-3"
  }, [n("div", {
    class: "d-flex align-items-center justify-content-between"
  }, [n("div", null, [n("h4", {
    class: "mb-0 text-white"
  }, "Create Account"), n("p", {
    class: "mb-0 text-white text-opacity-75"
  }, "Open DEMO Account")]), n("div", {
    class: "avtar"
  }, [n("i", {
    class: "ti ti-folder-plus f-20"
  })])])])], -1);

function fS(e, t, s, o, i, r) {
  const a = ke("DemoAccountListingWidgetView"),
    l = ke("RouterLink");
  return _(), w("div", sS, [n("div", nS, [oS, n("div", iS, [n("div", rS, [n("div", aS, [lS, n("div", cS, [ie(a)])])]), n("div", dS, [ie(l, {
    to: "/createDemoAccount"
  }, {
    default: Ue(() => [uS]),
    _: 1
  })])])])])
}
const hS = je(tS, [
    ["render", fS]
  ]),
  pS = {
    setup() {
      const e = he(null),
        t = he(null),
        s = he(null),
        o = he(null),
        i = he(!1),
        r = he(null),
        a = he(null),
        l = he(!1),
        d = {
          selectedGroup: he(),
          selectedLeverage: he()
        },
        p = Ge("API_BASE_URL"),
        m = async () => {
          try {
            const O = $e.get("authToken"),
              V = await ye.get(`${p}/live-account/form-data`, {
                headers: {
                  Authorization: `Bearer ${O}`
                }
              });
            t.value = V.data.result.groups, s.value = V.data.result.leverage, me.setDataWithTimestamp("live_groups", V.data.result.groups, 30), me.setDataWithTimestamp("leverages_data", V.data.result.leverage, 30)
          } catch (O) {
            console.error("Error fetching data:", O)
          }
        }, g = () => {
          const O = me.getDataWithExpiration("live_groups");
          O && (t.value = O);
          const V = me.getDataWithExpiration("leverages_data");
          V && (s.value = V);
          const M = me.getDataWithExpiration("dashboard_data");
          M && (r.value = M.total_mt_account, a.value = M)
        };
      At(() => {
        g(), m(), $()
      });
      const $ = async () => {
        try {
          const O = Ge("API_BASE_URL"),
            V = $e.get("authToken");
          if (O && V) {
            const M = await ye.get(`${O}/dashboard`, {
              headers: {
                Authorization: `Bearer ${V}`
              }
            });
            a.value = M.data.result, r.value = M.data.result.total_mt_account, me.setDataWithTimestamp("dashboard_data", M.data.result, 30), l.value = !0
          } else throw new Error("Missing API_BASE_URL or authToken")
        } catch (O) {
          console.error("Error fetching data:", O), Swal.fire({
            icon: "error",
            title: "Error fetching data",
            text: O.message || "An error occurred while fetching data"
          })
        }
      }, C = () => {
        d.selectedGroup.value = "", d.selectedLeverage.value = "", r.value <= 2 && (e.value || (d.selectedGroup.value = "Please select a Group"), o.value || (d.selectedLeverage.value = "Please select Leverage"), !d.selectedGroup.value && !d.selectedLeverage.value && A())
      }, A = async () => {
        try {
          const O = localStorage.getItem("auth_id"),
            V = $e.get("authToken");
          i.value = !0;
          const M = await ye.post(`${p}/live-account/create`, {
            groupid: e.value,
            client_id: O,
            leverage: o.value
          }, {
            headers: {
              Authorization: `Bearer ${V}`
            }
          });
          M && (i.value = !1, Swal.fire({
            icon: "success",
            title: M == null ? void 0 : M.data.message
          }).then(B => {
            B.isConfirmed && window.location.reload()
          }))
        } catch (O) {
          console.error("Error posting data:", O), Swal.fire({
            icon: "error",
            title: O.response.message
          }).then(V => {
            V.isConfirmed && window.location.reload()
          })
        }
      };
      return {
        selectedGroup: e,
        groups: t,
        leverages: s,
        selectedLeverage: o,
        errors: d,
        fetchFormData: m,
        fetchDashboardData: $,
        submitForm: C,
        createLiveAccount: A,
        total_mt_account: r,
        isLoading: i
      }
    }
  },
  mS = {
    class: "pc-container"
  },
  gS = {
    class: "pc-content"
  },
  _S = Ae('<div class="page-header mb-0 pb-0"><div class="page-block"><div class="row align-items-center"><div class="col-md-12"><div class="page-header-title h2"><h4 class="mb-0">Create Live MT5 Account</h4></div></div></div></div></div>', 1),
  bS = {
    class: "row"
  },
  vS = {
    class: "col-sm-11"
  },
  yS = {
    class: "card"
  },
  wS = n("div", {
    class: "card-header"
  }, [n("h5", null, "SET UP YOUR ACCOUNT")], -1),
  $S = {
    class: "card-body"
  },
  CS = {
    xmlns: "http://www.w3.org/2000/svg",
    style: {
      display: "none"
    }
  },
  kS = n("symbol", {
    id: "exclamation-triangle-fill",
    fill: "currentColor",
    viewBox: "0 0 16 16"
  }, [n("path", {
    d: "M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
  })], -1),
  AS = [kS],
  xS = {
    key: 0,
    class: "alert alert-warning d-flex align-items-center",
    role: "alert"
  },
  SS = n("svg", {
    class: "bi flex-shrink-0 me-4",
    width: "24",
    height: "24"
  }, [n("use", {
    "xlink:href": "#exclamation-triangle-fill"
  })], -1),
  ES = n("div", null, [J(" Please note that you have already created 3 LIVE MT5 Trading accounts. Should you require additional accounts, we kindly request you to connect with our dedicated support team."), n("br"), J(" Thank you for your understanding and cooperation.")], -1),
  PS = [SS, ES],
  TS = {
    class: "form-group mb-0"
  },
  LS = {
    class: "row"
  },
  OS = n("div", {
    class: "col-3"
  }, [n("label", {
    class: "form-label"
  }, "Choose Account Type")], -1),
  IS = {
    class: "col-9"
  },
  BS = {
    key: 0,
    class: "row"
  },
  DS = {
    class: "auth-option"
  },
  MS = ["id", "value"],
  RS = ["for"],
  NS = {
    class: "d-block m-4"
  },
  FS = {
    class: "h5 d-block"
  },
  US = n("strong", {
    class: "float-end"
  }, [n("span", {
    class: "badge bg-light-primary"
  }, "LIVE")], -1),
  VS = n("span", {
    class: "h6 d-block mt-4 f-w-400 f-12"
  }, " A commission-free account, perfect for new traders to start investing. ", -1),
  jS = n("hr", null, null, -1),
  HS = {
    class: "h6 d-block mt-3 f-w-300 f-14"
  },
  qS = {
    class: "float-end"
  },
  WS = {
    class: "f-w-400 f-16"
  },
  zS = {
    class: "h6 d-block mt-3 f-w-300 f-14"
  },
  KS = {
    class: "float-end"
  },
  GS = {
    class: "f-w-400 f-16"
  },
  YS = {
    class: "h6 d-block mt-3 f-w-300 f-14"
  },
  JS = {
    class: "float-end"
  },
  ZS = {
    class: "f-w-400 f-16"
  },
  XS = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  QS = {
    key: 1,
    class: "d-flex justify-content-center py-5"
  },
  eE = n("div", {
    class: "spinner-border",
    role: "status"
  }, [n("span", {
    class: "sr-only"
  }, "Loading...")], -1),
  tE = [eE],
  sE = {
    class: "row mt-5"
  },
  nE = n("div", {
    class: "col-3"
  }, [n("label", {
    class: "form-label"
  }, "Select Leverage")], -1),
  oE = {
    class: "col-9"
  },
  iE = ["value"],
  rE = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  aE = {
    class: "row mt-3"
  },
  lE = n("div", {
    class: "col-3"
  }, null, -1),
  cE = {
    class: "col-9"
  },
  dE = {
    class: "d-grid gap-2 mt-2"
  },
  uE = ["disabled"],
  fE = n("i", {
    class: "ti ti-plus me-2"
  }, null, -1),
  hE = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  };

function pE(e, t, s, o, i, r) {
  var a, l, d, p;
  return _(), w("div", mS, [n("div", gS, [_S, n("div", bS, [n("div", vS, [n("div", yS, [wS, n("div", $S, [(_(), w("svg", CS, AS)), o.total_mt_account > 2 ? (_(), w("div", xS, PS)) : D("", !0), n("form", null, [n("div", TS, [n("div", LS, [OS, n("div", IS, [o.groups ? (_(), w("div", BS, [(_(!0), w(Te, null, Je(o.groups, m => (_(), w("div", {
    class: "col-sm-6",
    key: m.id
  }, [n("div", DS, [be(n("input", {
    type: "radio",
    class: "btn-check",
    name: "options",
    id: "option" + m.id,
    "onUpdate:modelValue": t[0] || (t[0] = g => o.selectedGroup = g),
    value: m.id
  }, null, 8, MS), [
    [kt, o.selectedGroup]
  ]), n("label", {
    class: "auth-megaoption",
    for: "option" + m.id,
    style: {
      height: "230px !important"
    }
  }, [n("div", NS, [n("span", null, [n("span", FS, [US, J(" " + L(m.category_name), 1)]), VS, jS, n("span", HS, [n("strong", qS, [n("span", WS, L(m.min_deposit) + "$", 1)]), J(" Minimum Deposit ")]), n("span", zS, [n("strong", KS, [n("span", GS, L(m.spread) + "$", 1)]), J(" Spread ")]), n("span", YS, [n("strong", JS, [n("span", ZS, L(m.swap ? "Yes" : "No"), 1)]), J(" Swap ")])])])], 8, RS)])]))), 128)), o.errors.selectedGroup ? (_(), w("div", XS, L((l = (a = o.errors.selectedGroup) == null ? void 0 : a.value) == null ? void 0 : l.replace(/^"(.*)"$/, "$1")), 1)) : D("", !0)])) : (_(), w("div", QS, tE))])]), n("div", sE, [nE, n("div", oE, [be(n("select", {
    class: "form-select",
    "onUpdate:modelValue": t[1] || (t[1] = m => o.selectedLeverage = m)
  }, [(_(!0), w(Te, null, Je(o.leverages, m => (_(), w("option", {
    key: m.id,
    value: m.leverage
  }, L(`1:${m.leverage}`), 9, iE))), 128))], 512), [
    [Xn, o.selectedLeverage]
  ]), o.errors.selectedLeverage ? (_(), w("div", rE, L((p = (d = o.errors.selectedLeverage) == null ? void 0 : d.value) == null ? void 0 : p.replace(/^"(.*)"$/, "$1")), 1)) : D("", !0)])]), n("div", aE, [lE, n("div", cE, [n("div", dE, [n("button", {
    class: "btn btn-lg btn-primary",
    disabled: o.isLoading,
    type: "button",
    onClick: t[2] || (t[2] = (...m) => o.submitForm && o.submitForm(...m))
  }, [fE, o.isLoading ? (_(), w("span", hE)) : D("", !0), J(" " + L(o.isLoading ? "Creating..." : "Create Account"), 1)], 8, uE)])])])])])])])])])])])
}
const mE = je(pS, [
    ["render", pE]
  ]),
  gE = {
    props: ["leverage", "mt_id"],
    name: "LeverageUpdateView",
    data() {
      return {
        isLoading: !1,
        isChecked: !1,
        leverages: null,
        selectedLeverage: null,
        errors: {
          any: ""
        }
      }
    },
    mounted() {
      const e = $e.get("authToken");
      ye.get(this.API_BASE_URL + "/live-account/leverages", {
        headers: {
          Authorization: `Bearer ${e}`
        }
      }).then(t => {
        this.leverages = t.data.result.leverages, this.selectedLeverage = t.data.result.leverages[0].leverage
      }).catch(t => {
        console.error("Error fetching data:", t)
      })
    },
    methods: {
      handleSubmitClick() {
        this.errors.any = "", this.isChecked || (this.errors.any = "Please read and agree to the terms and conditions."), this.selectedLeverage == this.leverage && (this.errors.any = "The selected leverage is already the same as the current leverage"), this.errors.any || this.UpdateLeverage()
      },
      async UpdateLeverage() {
        this.isLoading = !0;
        try {
          const e = localStorage.getItem("auth_id"),
            t = $e.get("authToken"),
            s = await ye.post(this.API_BASE_URL + "/live-account/update-leverage", {
              mt_id: this.mt_id,
              leverage: this.selectedLeverage
            }, {
              headers: {
                Authorization: `Bearer ${t}`
              }
            });
          s && (this.isLoading = !1, this.$emit("close-modal"), Swal.fire({
            icon: "success",
            title: s == null ? void 0 : s.data.message
          }).then(o => {
            o.isConfirmed && window.location.reload()
          }))
        } catch (e) {
          this.$emit("close-modal"), console.log(e), Swal.fire({
            icon: "error",
            title: e.response.message
          }).then(t => {
            t.isConfirmed && window.location.reload()
          })
        }
      }
    }
  },
  _E = {
    class: "modal fade show",
    id: "updateLeverageModal",
    tabindex: "-1",
    role: "dialog",
    "aria-labelledby": "exampleModalCenterTitle",
    "aria-hidden": "true"
  },
  bE = {
    class: "modal-dialog modal-dialog-centered",
    role: "document"
  },
  vE = {
    class: "modal-content"
  },
  yE = n("div", {
    class: "modal-header"
  }, [n("h5", {
    class: "modal-title",
    id: "exampleModalCenterTitle"
  }, "Update Leverage"), n("button", {
    type: "button",
    class: "btn-close",
    "data-bs-dismiss": "modal",
    "aria-label": "Close"
  })], -1),
  wE = {
    class: "modal-body"
  },
  $E = {
    class: "row p-2"
  },
  CE = n("div", {
    class: "col-4"
  }, [n("label", {
    class: "form-label"
  }, "Current Leverage")], -1),
  kE = {
    class: "col-8"
  },
  AE = {
    class: "mb-0 f-w-400"
  },
  xE = {
    class: "row mt-3 p-2"
  },
  SE = n("div", {
    class: "col-4"
  }, [n("label", {
    class: "form-label"
  }, "Select Leverage")], -1),
  EE = {
    class: "col-8"
  },
  PE = ["value"],
  TE = {
    key: 2,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  LE = n("p", {
    class: "f-12 text-gray-500 mt-3 p-2 text-muted"
  }, " Before proceeding, it's crucial to acknowledge the significant risks associated with high leverage. There's a possibility of sustaining losses exceeding the capital deposited. Please ensure that there are no open positions or pending orders before making any changes to this parameter. Your awareness and caution are essential to safeguard your investments.", -1),
  OE = {
    class: "mb-2 ps-2"
  },
  IE = n("label", {
    class: "form-check-label",
    for: "flexCheckDefault"
  }, " I have read and agreed to the terms and conditions.", -1),
  BE = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  DE = {
    class: "modal-footer"
  },
  ME = n("button", {
    type: "button",
    class: "btn btn-secondary",
    "data-bs-dismiss": "modal"
  }, "Close", -1),
  RE = ["disabled"],
  NE = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  };

function FE(e, t, s, o, i, r) {
  const a = ke("SmallLoadingSpinner");
  return _(), w("div", _E, [n("div", bE, [n("div", vE, [yE, n("div", wE, [n("div", $E, [CE, n("div", kE, [n("h5", AE, "1:" + L(s.leverage), 1)])]), n("div", xE, [SE, n("div", EE, [i.leverages ? be((_(), w("select", {
    key: 0,
    class: "form-select",
    "onUpdate:modelValue": t[0] || (t[0] = l => i.selectedLeverage = l)
  }, [(_(!0), w(Te, null, Je(i.leverages, l => (_(), w("option", {
    key: l.id,
    value: l.leverage
  }, L(`1:${l.leverage}`), 9, PE))), 128))], 512)), [
    [Xn, i.selectedLeverage]
  ]) : (_(), ve(a, {
    key: 1
  })), i.errors.selectedLeverage ? (_(), w("div", TE, L(i.errors.selectedLeverage), 1)) : D("", !0)])]), LE, n("div", OE, [be(n("input", {
    class: "form-check-input me-2",
    type: "checkbox",
    value: "",
    id: "flexCheckDefault",
    "onUpdate:modelValue": t[1] || (t[1] = l => i.isChecked = l)
  }, null, 512), [
    [il, i.isChecked]
  ]), IE]), i.errors.any ? (_(), w("div", BE, L(i.errors.any), 1)) : D("", !0)]), n("div", DE, [ME, n("button", {
    class: "btn btn-primary",
    type: "button",
    disabled: i.isLoading,
    onClick: t[2] || (t[2] = (...l) => r.handleSubmitClick && r.handleSubmitClick(...l))
  }, [i.isLoading ? (_(), w("span", NE)) : D("", !0), J(" " + L(i.isLoading ? "Updating..." : "Update Leverage"), 1)], 8, RE)])])])])
}
const F1 = je(gE, [
    ["render", FE]
  ]),
  UE = {
    props: ["leverage", "mt_id"],
    name: "LeverageUpdateView",
    data() {
      return {
        selectedOption: "main",
        password: "",
        isLoading: !1,
        confirmPassword: "",
        validations: {
          minLength: !1,
          uppercase: !1,
          lowercase: !1,
          specialCharacter: !1,
          digit: !1,
          match: !1
        }
      }
    },
    mounted() {
      $e.get("authToken")
    },
    methods: {
      validatePassword() {
        const e = this.password;
        this.validations.minLength = e.length >= 8, this.validations.uppercase = /[A-Z]/.test(e), this.validations.lowercase = /[a-z]/.test(e), this.validations.specialCharacter = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(e), this.validations.digit = /\d/.test(e), this.validateConfirmPassword()
      },
      validateConfirmPassword() {
        this.validations.match = this.password === this.confirmPassword
      },
      handleSubmitClick() {
        Object.values(this.validations).every(t => t) ? this.UpdatePassword() : console.log("Please fix validation errors.")
      },
      async UpdatePassword() {
        this.isLoading = !0;
        try {
          const e = localStorage.getItem("auth_id"),
            t = $e.get("authToken"),
            s = await ye.post(this.API_BASE_URL + "/live-account/update-password", {
              mt_id: this.mt_id,
              password: this.password,
              type: this.selectedOption
            }, {
              headers: {
                Authorization: `Bearer ${t}`
              }
            });
          s && (this.isLoading = !1, this.$emit("close-modal"), Swal.fire({
            icon: "success",
            title: s == null ? void 0 : s.data.message
          }).then(o => {
            o.isConfirmed && window.location.reload()
          }))
        } catch (e) {
          this.$emit("close-modal"), console.log(e), Swal.fire({
            icon: "error",
            title: e.response.message
          }).then(t => {
            t.isConfirmed && window.location.reload()
          })
        }
      }
    }
  },
  VE = {
    id: "passwordupdatemodal",
    class: "modal fade show",
    tabindex: "-1",
    role: "dialog",
    "aria-labelledby": "exampleModalCenterTitle",
    "aria-hidden": "true"
  },
  jE = {
    class: "modal-dialog modal-dialog-centered",
    role: "document"
  },
  HE = {
    class: "modal-content"
  },
  qE = n("div", {
    class: "modal-header"
  }, [n("h5", {
    class: "modal-title",
    id: "exampleModalCenterTitle"
  }, "Update Password"), n("button", {
    type: "button",
    class: "btn-close",
    "data-bs-dismiss": "modal",
    "aria-label": "Close"
  })], -1),
  WE = {
    class: "modal-body"
  },
  zE = {
    class: "row"
  },
  KE = n("div", {
    class: "col-6"
  }, [n("h5", {
    class: "p-2 f-w-200"
  }, "LIVE MT5 ACCOUNT")], -1),
  GE = {
    class: "col-6"
  },
  YE = {
    class: "p-2 f-w-400"
  },
  JE = n("p", {
    class: "f-12 text-gray-500 p-2 text-muted mt-0 mb-2"
  }, " You have the ability to update your Investor and Master passwords for your trading accounts here. If you require any assistance or encounter any challenges with password management, please don't hesitate to reach out to us for support.", -1),
  ZE = {
    class: "row mt-0 mb-0"
  },
  XE = {
    class: "col-lg-6"
  },
  QE = {
    class: "border card p-3"
  },
  eP = {
    class: "form-check"
  },
  tP = n("label", {
    class: "form-check-label d-block",
    for: "customCheckdefhor1"
  }, [n("span", null, [n("span", {
    class: "h6"
  }, "Investor Password")])], -1),
  sP = {
    class: "col-lg-6"
  },
  nP = {
    class: "border card p-3"
  },
  oP = {
    class: "form-check"
  },
  iP = n("label", {
    class: "form-check-label d-block",
    for: "customCheckdefhor2"
  }, [n("span", null, [n("span", {
    class: "h6"
  }, "Master Password")])], -1),
  rP = {
    class: "row mt-0 mb-0"
  },
  aP = {
    class: "form-group"
  },
  lP = n("label", {
    class: "form-label",
    for: "exampleInputPassword1"
  }, "New Password", -1),
  cP = {
    class: "row mb-2"
  },
  dP = {
    class: "col-6"
  },
  uP = {
    class: "pc-micon me-2"
  },
  fP = {
    key: 0,
    class: "ti ti-check"
  },
  hP = {
    key: 1,
    class: "ti ti-x"
  },
  pP = n("span", {
    class: "pc-mtext f-12"
  }, "Minimum 8 characters", -1),
  mP = n("br", null, null, -1),
  gP = {
    class: "pc-micon me-2"
  },
  _P = {
    key: 0,
    class: "ti ti-check"
  },
  bP = {
    key: 1,
    class: "ti ti-x"
  },
  vP = n("span", {
    class: "pc-mtext f-12"
  }, "At least 1 uppercase letter", -1),
  yP = n("br", null, null, -1),
  wP = {
    class: "pc-micon me-2"
  },
  $P = {
    key: 0,
    class: "ti ti-check"
  },
  CP = {
    key: 1,
    class: "ti ti-x"
  },
  kP = n("span", {
    class: "pc-mtext f-12"
  }, "At least 1 lowercase letter", -1),
  AP = {
    class: "col-6"
  },
  xP = {
    class: "pc-micon me-2"
  },
  SP = {
    key: 0,
    class: "ti ti-check"
  },
  EP = {
    key: 1,
    class: "ti ti-x"
  },
  PP = n("span", {
    class: "pc-mtext f-12"
  }, "At least 1 special character", -1),
  TP = n("br", null, null, -1),
  LP = {
    class: "pc-micon me-2"
  },
  OP = {
    key: 0,
    class: "ti ti-check"
  },
  IP = {
    key: 1,
    class: "ti ti-x"
  },
  BP = n("span", {
    class: "pc-mtext f-12"
  }, "At least 1 digit", -1),
  DP = {
    class: "form-group mb-2"
  },
  MP = n("label", {
    class: "form-label",
    for: "exampleInputPassword1"
  }, "Confirm Password", -1),
  RP = {
    class: "row"
  },
  NP = {
    class: "col-6"
  },
  FP = {
    class: "pc-micon me-2"
  },
  UP = {
    key: 0,
    class: "ti ti-check"
  },
  VP = {
    key: 1,
    class: "ti ti-x"
  },
  jP = n("span", {
    class: "pc-mtext f-12"
  }, "Passwords do not match", -1),
  HP = {
    class: "modal-footer"
  },
  qP = n("button", {
    type: "button",
    class: "btn btn-secondary",
    "data-bs-dismiss": "modal"
  }, "Close", -1),
  WP = ["disabled"],
  zP = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  };

function KP(e, t, s, o, i, r) {
  return _(), w("div", VE, [n("div", jE, [n("div", HE, [qE, n("div", WE, [n("div", zE, [KE, n("div", GE, [n("h5", YE, L(s.mt_id), 1)])]), JE, n("div", ZE, [n("div", XE, [n("div", QE, [n("div", eP, [be(n("input", {
    type: "radio",
    name: "radio3",
    class: "form-check-input input-primary",
    id: "customCheckdefhor1",
    value: "investor",
    "onUpdate:modelValue": t[0] || (t[0] = a => i.selectedOption = a)
  }, null, 512), [
    [kt, i.selectedOption]
  ]), tP])])]), n("div", sP, [n("div", nP, [n("div", oP, [be(n("input", {
    type: "radio",
    name: "radio3",
    class: "form-check-input input-primary",
    id: "customCheckdefhor2",
    value: "main",
    "onUpdate:modelValue": t[1] || (t[1] = a => i.selectedOption = a)
  }, null, 512), [
    [kt, i.selectedOption]
  ]), iP])])])]), n("div", rP, [n("div", aP, [lP, be(n("input", {
    "onUpdate:modelValue": t[2] || (t[2] = a => i.password = a),
    onInput: t[3] || (t[3] = (...a) => r.validatePassword && r.validatePassword(...a)),
    type: "password",
    class: "form-control",
    id: "password",
    placeholder: "Password"
  }, null, 544), [
    [Re, i.password]
  ])]), n("div", cP, [n("div", dP, [n("span", uP, [i.validations.minLength ? (_(), w("i", fP)) : (_(), w("i", hP))]), pP, mP, n("span", gP, [i.validations.uppercase ? (_(), w("i", _P)) : (_(), w("i", bP))]), vP, yP, n("span", wP, [i.validations.lowercase ? (_(), w("i", $P)) : (_(), w("i", CP))]), kP]), n("div", AP, [n("span", xP, [i.validations.specialCharacter ? (_(), w("i", SP)) : (_(), w("i", EP))]), PP, TP, n("span", LP, [i.validations.digit ? (_(), w("i", OP)) : (_(), w("i", IP))]), BP])]), n("div", DP, [MP, be(n("input", {
    "onUpdate:modelValue": t[4] || (t[4] = a => i.confirmPassword = a),
    onInput: t[5] || (t[5] = (...a) => r.validateConfirmPassword && r.validateConfirmPassword(...a)),
    type: "password",
    class: "form-control",
    id: "confirm_password",
    placeholder: "Password"
  }, null, 544), [
    [Re, i.confirmPassword]
  ])]), n("div", RP, [n("div", NP, [n("span", FP, [i.validations.match ? (_(), w("i", UP)) : (_(), w("i", VP))]), jP])])])]), n("div", HP, [qP, n("button", {
    class: "btn btn-primary",
    type: "button",
    disabled: i.isLoading,
    onClick: t[6] || (t[6] = (...a) => r.handleSubmitClick && r.handleSubmitClick(...a))
  }, [i.isLoading ? (_(), w("span", zP)) : D("", !0), J(" " + L(i.isLoading ? "Updating..." : "Update Password"), 1)], 8, WP)])])])])
}
const U1 = je(UE, [
    ["render", KP]
  ]),
  GP = Dt({
    components: {
      LeverageUpdateView: F1,
      PasswordUpdateView: U1
    },
    setup() {
      const e = he([]),
        t = he(!0),
        s = he(!0),
        o = he({}),
        r = Wi().currentRoute.value.params.mt_id,
        a = async () => {
          try {
            const $ = Ge("API_BASE_URL"),
              C = $e.get("authToken");
            if ($ && C) {
              const A = await ye.get(`${$}/live-accounts/data-list`, {
                headers: {
                  Authorization: `Bearer ${C}`
                }
              });
              e.value = A.data.result.mt_data, me.setDataWithTimestamp("liveAccounts", A.data.result.mt_data, 30);
              const O = A.data.result.mt_data.filter(V => V.mt_id === Number(r));
              o.value = O[0]
            } else throw new Error("Missing API_BASE_URL or authToken")
          } catch ($) {
            console.error("Error fetching data:", $), nt.fire({
              icon: "error",
              title: "Error fetching data",
              text: $.message || "An error occurred while fetching data"
            })
          }
        }, l = $ => {
          const C = me.getDataWithExpiration("liveAccounts");
          if (C) {
            const A = C.filter(O => O.mt_id === Number($));
            o.value = A[0]
          }
        };
      At(() => {
        l(r), a()
      });
      const d = () => {
          s.value = !0;
          var $ = new bootstrap.Modal(document.getElementById("updateLeverageModal"));
          $.show()
        },
        p = () => {
          p.value = !0;
          var $ = new bootstrap.Modal(document.getElementById("passwordupdatemodal"));
          $.show()
        };
      return {
        account_details: o,
        fetchAccountDetails: a,
        showUpdateLeverageModal: d,
        showPasswordUpdateModal: p,
        closeModalHandler: () => {
          console.log("dasfs"), t.value = !1;
          var $ = new bootstrap.Modal(document.getElementById("updateLeverageModal"));
          $.hide()
        },
        closePassModalHandler: () => {
          console.log("dasfspass"), s.value = !1;
          var $ = new bootstrap.Modal(document.getElementById("passwordupdatemodal"));
          $.hide()
        },
        showModal: t,
        showPassModel: s
      }
    }
  }),
  YP = {
    class: "pc-container"
  },
  JP = {
    class: "pc-content"
  },
  ZP = Ae('<div class="page-header"><div class="page-block"><div class="row align-items-center"><div class="col-md-12"><div class="page-header-title h5"><h2 class="mb-0">MT5 Details</h2></div></div></div></div></div>', 1),
  XP = {
    class: "row"
  },
  QP = {
    class: "col-sm-12"
  },
  eT = {
    class: "card"
  },
  tT = {
    class: "card-body"
  },
  sT = {
    class: "row g-3"
  },
  nT = {
    class: "col-12"
  },
  oT = {
    class: "alert alert-secondary mb-3 d-print-none"
  },
  iT = {
    class: "row align-items-center g-3"
  },
  rT = {
    class: "col-sm-6"
  },
  aT = {
    class: "row align-items-center"
  },
  lT = n("div", {
    class: "col-auto pe-0"
  }, [n("img", {
    src: Mt,
    alt: "user-image",
    class: "wid-60 hei-60 rounded"
  })], -1),
  cT = {
    class: "col"
  },
  dT = {
    class: "mb-0 f-w-500"
  },
  uT = {
    class: "text-truncate"
  },
  fT = {
    class: "text-muted f-12 mb-0"
  },
  hT = {
    class: "text-truncate w-100"
  },
  pT = Ae('<div class="col-sm-6 text-sm-end"><ul class="list-inline ms-auto mb-0 d-flex justify-content-end flex-wrap"><li class="list-inline-item"><div class="card mb-0"><div class="card-body p-2 mb-0"><div class="d-flex justify-content-between"><div class="me-2"><img src="' + Mt + '" alt="user-image" class="wid-20"></div><div><p class="mb-0 text-opacity-75">Launch Web Trader </p></div></div></div></div></li></ul></div>', 1),
  mT = {
    class: "row"
  },
  gT = {
    class: "col-sm-6"
  },
  _T = {
    class: "card bg-gray-800 dropbox-card"
  },
  bT = {
    class: "card-body"
  },
  vT = n("div", {
    class: "d-flex align-items-center justify-content-between"
  }, [n("p", {
    class: "text-white"
  }, "Balance")], -1),
  yT = {
    class: "d-flex align-items-center"
  },
  wT = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s"
  }, [n("i", {
    class: "ph-duotone ph-briefcase f-20"
  })])], -1),
  $T = {
    class: "flex-grow-1 ms-3"
  },
  CT = {
    class: "row g-1"
  },
  kT = {
    class: "col-6"
  },
  AT = {
    key: 0,
    class: "text-white mb-0 f-w-500"
  },
  xT = n("div", {
    class: "col-6 text-end"
  }, [n("button", {
    class: "btn btn-outline-light btn-print-invoice"
  }, "Quick Deposit")], -1),
  ST = {
    class: "row g-3"
  },
  ET = {
    class: "col-sm-6"
  },
  PT = {
    class: "bg-body p-3 rounded"
  },
  TT = Ae('<div class="d-flex align-items-center mb-2"><div class="flex-shrink-0"><i class="ph-duotone ph-file-cloud f-20"></i></div><div class="flex-grow-1 ms-2"><p class="mb-0">Server</p></div></div>', 1),
  LT = {
    key: 0,
    class: "mb-0 f-w-400"
  },
  OT = {
    class: "col-sm-6"
  },
  IT = {
    class: "bg-body p-3 rounded"
  },
  BT = Ae('<div class="d-flex align-items-center mb-2"><div class="flex-shrink-0"><i class="ph-duotone ph-cactus f-20"></i></div><div class="flex-grow-1 ms-2"><p class="mb-0">Credit</p></div></div>', 1),
  DT = {
    key: 0,
    class: "mb-0 f-w-400"
  },
  MT = {
    class: "col-sm-6"
  },
  RT = {
    class: "bg-body p-3 rounded"
  },
  NT = Ae('<div class="d-flex align-items-center mb-2"><div class="flex-shrink-0"><i class="ph-duotone ph-hand-coins f-20"></i></div><div class="flex-grow-1 ms-2"><p class="mb-0">Leverage</p></div></div>', 1),
  FT = {
    class: "row g-1"
  },
  UT = {
    class: "col-4"
  },
  VT = {
    key: 0,
    class: "mb-0 f-w-400"
  },
  jT = {
    class: "col-8 text-end f-12"
  },
  HT = {
    class: "col-sm-6"
  },
  qT = {
    class: "bg-body p-3 rounded"
  },
  WT = Ae('<div class="d-flex align-items-center mb-2"><div class="flex-shrink-0"><i class="ph-duotone ph-swap f-20"></i></div><div class="flex-grow-1 ms-2"><p class="mb-0">Swap</p></div></div>', 1),
  zT = {
    key: 0,
    class: "mb-0 f-w-400"
  },
  KT = {
    class: "col-sm-6"
  },
  GT = {
    class: "list-group list-group-flush"
  },
  YT = {
    class: "list-group-item"
  },
  JT = {
    class: "d-flex align-items-center"
  },
  ZT = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s border"
  }, [n("i", {
    class: "ph-duotone ph-chart-line-up f-20"
  })])], -1),
  XT = {
    class: "flex-grow-1 ms-3"
  },
  QT = {
    class: "row g-1"
  },
  eL = n("div", {
    class: "col-6"
  }, [n("p", {
    class: "text-muted mb-0 f-20"
  }, [n("small", null, "Equity")])], -1),
  tL = {
    class: "col-6 text-end"
  },
  sL = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  nL = {
    class: "list-group-item"
  },
  oL = {
    class: "d-flex align-items-center"
  },
  iL = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s border"
  }, [n("i", {
    class: "ph-duotone ph-butterfly f-20"
  })])], -1),
  rL = {
    class: "flex-grow-1 ms-3"
  },
  aL = {
    class: "row g-1"
  },
  lL = n("div", {
    class: "col-6"
  }, [n("p", {
    class: "text-muted mb-0 f-20"
  }, [n("small", null, "Free Margin")])], -1),
  cL = {
    class: "col-6 text-end"
  },
  dL = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  uL = {
    class: "list-group-item"
  },
  fL = {
    class: "d-flex align-items-center"
  },
  hL = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s border"
  }, [n("i", {
    class: "ph-duotone ph-chart-pie f-20"
  })])], -1),
  pL = {
    class: "flex-grow-1 ms-3"
  },
  mL = {
    class: "row g-1"
  },
  gL = n("div", {
    class: "col-6"
  }, [n("p", {
    class: "text-muted mb-0 f-20"
  }, [n("small", null, "Margin")])], -1),
  _L = {
    class: "col-6 text-end"
  },
  bL = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  vL = {
    class: "list-group-item"
  },
  yL = {
    class: "d-flex align-items-center"
  },
  wL = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s border"
  }, [n("i", {
    class: "ph-duotone ph-chart-pie-slice f-20"
  })])], -1),
  $L = {
    class: "flex-grow-1 ms-3"
  },
  CL = {
    class: "row g-1"
  },
  kL = n("div", {
    class: "col-6"
  }, [n("p", {
    class: "text-muted mb-0 f-20"
  }, [n("small", null, "Margin Level")])], -1),
  AL = {
    class: "col-6 text-end"
  },
  xL = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  SL = {
    class: "list-group-item"
  },
  EL = {
    class: "d-flex align-items-center"
  },
  PL = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s border"
  }, [n("i", {
    class: "ph-duotone ph-line-segments f-20"
  })])], -1),
  TL = {
    class: "flex-grow-1 ms-3"
  },
  LL = {
    class: "row g-1"
  },
  OL = n("div", {
    class: "col-6"
  }, [n("p", {
    class: "text-muted mb-0 f-20"
  }, [n("small", null, "Floating PL")])], -1),
  IL = {
    class: "col-6 text-end"
  },
  BL = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  DL = {
    class: "row"
  },
  ML = {
    class: "col-sm-6"
  },
  RL = {
    class: "row mt-3"
  },
  NL = Ae('<div class="col-sm-6"><div class="card bg-white"><div class="card-body p-3"><div class="d-flex align-items-center justify-content-between"><div><h4 class="mb-0">Internal </h4><p class="mb-0 text-opacity-75">Transfer </p></div><div class="avtar avtar-s border"><i class="ph-duotone ph-shuffle f-24"></i></div></div></div></div></div>', 1),
  FL = {
    class: "col-sm-6"
  },
  UL = Ae('<div class="card bg-white"><div class="card-body p-3"><div class="d-flex align-items-center justify-content-between"><div><h4 class="mb-0">Password </h4><p class="mb-0 text-opacity-75">Update </p></div><div class="avtar avtar-s border"><i class="ph-duotone ph-password f-24"></i></div></div></div></div>', 1),
  VL = [UL],
  jL = Ae('<div class="col-sm-6"><div class="row mt-3"><div class="col-sm-6"><div class="card bg-primary available-balance-card"><div class="card-body p-3"><div class="d-flex align-items-center justify-content-between"><div><h4 class="mb-0 text-white">Deposit</h4><p class="mb-0 text-white text-opacity-75">Fund your account </p></div><div class="avtar"><i class="ph-duotone ph-credit-card f-24"></i></div></div></div></div></div><div class="col-sm-6"><div class="card bg-secondary available-balance-card"><div class="card-body p-3"><div class="d-flex align-items-center justify-content-between"><div><h4 class="mb-0 text-white">Withdraw</h4><p class="mb-0 text-white text-opacity-75">Transfer your profits </p></div><div class="avtar"><i class="ph-duotone ph-bank f-24"></i></div></div></div></div></div></div></div>', 1),
  HL = {
    key: 0,
    class: ""
  },
  qL = {
    key: 1,
    class: ""
  };

function WL(e, t, s, o, i, r) {
  const a = ke("SmallLoadingSpinner"),
    l = ke("LeverageUpdateView"),
    d = ke("PasswordUpdateView");
  return _(), w("div", YP, [n("div", JP, [ZP, n("div", XP, [n("div", QP, [n("div", eT, [n("div", tT, [n("div", sT, [n("div", nT, [n("div", oT, [n("div", iT, [n("div", rT, [n("div", aT, [lT, n("div", cT, [n("h2", dT, [n("span", uT, L(e.$route.params.mt_id), 1)]), n("p", fT, [n("span", hT, L(e.account_details && e.account_details.groupname ? e.account_details.groupname : ""), 1)])])])]), pT])])])]), n("div", mT, [n("div", gT, [n("div", _T, [n("div", bT, [vT, n("div", yT, [wT, n("div", $T, [n("div", CT, [n("div", kT, [e.account_details ? (_(), w("h3", AT, L(e.account_details && e.account_details.balance ? e.account_details.balance : "0") + " $ ", 1)) : (_(), ve(a, {
    key: 1
  }))]), xT])])])])]), n("div", ST, [n("div", ET, [n("div", PT, [TT, e.account_details ? (_(), w("h5", LT, L(e.account_details && e.account_details.server ? e.account_details.server : ""), 1)) : (_(), ve(a, {
    key: 1
  }))])]), n("div", OT, [n("div", IT, [BT, e.account_details ? (_(), w("h5", DT, "$ " + L(e.account_details && e.account_details.credit ? e.account_details.credit : 0), 1)) : (_(), ve(a, {
    key: 1
  }))])]), n("div", MT, [n("div", RT, [NT, n("div", FT, [n("div", UT, [e.account_details ? (_(), w("h5", VT, "1:" + L(e.account_details && e.account_details.leverage ? e.account_details.leverage : 0), 1)) : (_(), ve(a, {
    key: 1
  }))]), n("div", jT, [n("a", {
    href: "#!",
    onClick: t[0] || (t[0] = (...p) => e.showUpdateLeverageModal && e.showUpdateLeverageModal(...p)),
    class: "card-link"
  }, "Update Leverage")])])])]), n("div", HT, [n("div", qT, [WT, e.account_details ? (_(), w("h5", zT, L(e.account_details && e.account_details.swap && e.account_details.swap === 1 ? "Yes" : "FREE"), 1)) : (_(), ve(a, {
    key: 1
  }))])])])]), n("div", KT, [n("ul", GT, [n("li", YT, [n("div", JT, [ZT, n("div", XT, [n("div", QT, [eL, n("div", tL, [e.account_details ? (_(), w("h4", sL, "$ " + L(e.account_details && e.account_details.equity ? e.account_details.equity : 0), 1)) : (_(), ve(a, {
    key: 1
  }))])])])])]), n("li", nL, [n("div", oL, [iL, n("div", rL, [n("div", aL, [lL, n("div", cL, [e.account_details ? (_(), w("h4", dL, "$ " + L(e.account_details && e.account_details.margin ? e.account_details.margin : 0), 1)) : (_(), ve(a, {
    key: 1
  }))])])])])]), n("li", uL, [n("div", fL, [hL, n("div", pL, [n("div", mL, [gL, n("div", _L, [e.account_details ? (_(), w("h4", bL, "$ " + L(e.account_details && e.account_details.margin ? e.account_details.margin : 0), 1)) : (_(), ve(a, {
    key: 1
  }))])])])])]), n("li", vL, [n("div", yL, [wL, n("div", $L, [n("div", CL, [kL, n("div", AL, [e.account_details ? (_(), w("h4", xL, L(e.account_details && e.account_details.margin_level ? e.account_details.margin_level : 0) + " %", 1)) : (_(), ve(a, {
    key: 1
  }))])])])])]), n("li", SL, [n("div", EL, [PL, n("div", TL, [n("div", LL, [OL, n("div", IL, [e.account_details ? (_(), w("h4", BL, L(e.account_details && e.account_details.floating ? e.account_details.floating : 0), 1)) : (_(), ve(a, {
    key: 1
  }))])])])])])])])]), n("div", DL, [n("div", ML, [n("div", RL, [NL, n("div", FL, [n("a", {
    href: "#!",
    onClick: t[1] || (t[1] = (...p) => e.showPasswordUpdateModal && e.showPasswordUpdateModal(...p))
  }, VL)])])]), jL])])])])])]), e.showModal ? (_(), w("div", HL, [e.account_details ? (_(), ve(l, {
    key: 0,
    leverage: e.account_details.leverage,
    mt_id: e.account_details.mt_id,
    onCloseModal: e.closeModalHandler
  }, null, 8, ["leverage", "mt_id", "onCloseModal"])) : D("", !0)])) : D("", !0), e.showPassModel ? (_(), w("div", qL, [e.account_details ? (_(), ve(d, {
    key: 0,
    leverage: e.account_details.leverage,
    mt_id: e.account_details.mt_id,
    onCloseModal: e.closePassModalHandler
  }, null, 8, ["leverage", "mt_id", "onCloseModal"])) : D("", !0)])) : D("", !0)])
}
const zL = je(GP, [
    ["render", WL]
  ]),
  KL = "/assets/wallet_ico-B_mEIhX6.webp",
  GL = Dt({
    setup(e, {
      emit: t
    }) {
      Wi();
      const s = he(!1),
        o = he(!1),
        i = he(null),
        r = he(null);
      At(() => {
        a()
      });
      const a = () => {
          const g = me.getDataWithExpiration("dashboard_data");
          g ? (i.value = g, s.value = !0) : l();
          const $ = me.getDataWithExpiration("recent_wallet_trans");
          $ && (r.value = $), d()
        },
        l = async () => {
          try {
            const g = Ge("API_BASE_URL"),
              $ = $e.get("authToken");
            if (g && $) {
              const C = await ye.get(`${g}/dashboard`, {
                headers: {
                  Authorization: `Bearer ${$}`
                }
              });
              i.value = C.data.result, me.setDataWithTimestamp("dashboard_data", C.data.result, 30), s.value = !0
            } else throw new Error("Missing API_BASE_URL or authToken")
          } catch (g) {
            console.error("Error fetching data:", g), nt.fire({
              icon: "error",
              title: "Error fetching data",
              text: g.message || "An error occurred while fetching data"
            })
          }
        }, d = async () => {
          try {
            const g = Ge("API_BASE_URL"),
              $ = $e.get("authToken");
            if (g && $) {
              const C = await ye.get(`${g}/transaction/wallet-transactions`, {
                headers: {
                  Authorization: `Bearer ${$}`
                }
              });
              r.value = C.data.result, me.setDataWithTimestamp("recent_wallet_trans", C.data.result, 30)
            } else throw new Error("Missing API_BASE_URL or authToken")
          } catch (g) {
            console.error("Error fetching data:", g), nt.fire({
              icon: "error",
              title: "Error fetching data",
              text: g.message || "An error occurred while fetching data"
            })
          }
        };
      return {
        dashboard_data: i,
        isLoading: o,
        activateWallet: async () => {
          try {
            const g = $e.get("authToken"),
              $ = localStorage.getItem("auth_id");
            o.value = !0;
            const C = await ye.post(`${API_BASE_URL}/live-account/activate-wallet`, {
              client_id: $
            }, {
              headers: {
                Authorization: `Bearer ${g}`
              }
            });
            if (C) {
              o.value = !1;
              const A = me.getDataWithExpiration("dashboard_data");
              A && (A.wallet_created = 1, me.setDataWithTimestamp("dashboard_data", A, 30)), nt.fire({
                icon: "success",
                title: C == null ? void 0 : C.data.message
              }).then(O => {
                O.isConfirmed && window.location.reload()
              })
            }
          } catch (g) {
            console.error("Error posting data:", g), nt.fire({
              icon: "error",
              title: g.response.message
            }).then($ => {
              $.isConfirmed && window.location.reload()
            })
          }
        },
        recent_wallet_trans: r,
        formattedDate: g => {
          const $ = new Date(g),
            C = {
              year: "numeric",
              month: "long",
              day: "numeric"
            };
          return $.toLocaleDateString("en-US", C)
        }
      }
    }
  }),
  YL = {
    class: "pc-container"
  },
  JL = {
    class: "pc-content"
  },
  ZL = Ae('<div class="page-header mb-0 pb-0"><div class="page-block"><div class="row align-items-center"><div class="col-md-12"><div class="page-header-title h2"><h4 class="mb-0">Wallet</h4></div></div></div></div></div>', 1),
  XL = {
    class: "row"
  },
  QL = {
    class: "col-md-6 col-lg-6"
  },
  eO = {
    class: "card"
  },
  tO = {
    class: "card-body"
  },
  sO = Ae('<div class="d-flex align-items-center justify-content-between mb-4 mt-2"><div><div class="avtar avtar-s bg-gray-300"><svg class="pc-icon"><use xlink:href="#custom-security-safe"></use></svg></div></div><div class=""><h5 class="text-black">My Wallet</h5></div></div>', 1),
  nO = {
    class: "text-center mb-5"
  },
  oO = n("img", {
    src: KL,
    class: "pt-4 mt-3",
    style: {
      width: "20%"
    },
    alt: "logo"
  }, null, -1),
  iO = {
    key: 0,
    class: "mb-3 pt-4"
  },
  rO = {
    key: 1
  },
  aO = {
    key: 2,
    class: "list-inline my-3"
  },
  lO = n("li", {
    class: "list-inline-item"
  }, [n("span", {
    class: "badge border bg-light-primary"
  }, "#456100")], -1),
  cO = [lO],
  dO = ["disabled"],
  uO = n("i", {
    class: "ti ti-plus me-2"
  }, null, -1),
  fO = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  hO = n("div", {
    class: "card bg-primary available-balance-card mt-3"
  }, [n("div", {
    class: "card-body p-3"
  }, [n("div", {
    class: "d-flex align-items-center justify-content-between"
  }, [n("div", null, [n("h4", {
    class: "mb-0 text-white"
  }, "Add Funds"), n("p", {
    class: "mb-0 text-white text-opacity-75"
  }, "to my wallet")]), n("div", {
    class: "avtar"
  }, [n("i", {
    class: "ti ti-database-import f-18"
  })])])])], -1),
  pO = n("div", {
    class: "border rounded p-3 my-3"
  }, [n("div", {
    class: "d-flex align-items-center justify-content-between"
  }, [n("div", null, [n("h4", {
    class: "mb-0"
  }, "Withdraw"), n("p", {
    class: "mb-0 text-opacity-75"
  }, "from my wallet")]), n("div", {
    class: "avtar avtar-s bg-gray-300"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-direct-inbox"
  })])])])], -1),
  mO = n("div", {
    class: "border rounded p-3 my-3"
  }, [n("div", {
    class: "d-flex align-items-center justify-content-between"
  }, [n("div", null, [n("h4", {
    class: "mb-0"
  }, "Transfer"), n("p", {
    class: "mb-0 text-opacity-75"
  }, "from my wallet")]), n("div", {
    class: "avtar avtar-s bg-gray-300"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-refresh-2"
  })])])])], -1),
  gO = {
    class: "col-md-6 col-lg-6"
  },
  _O = {
    class: "card"
  },
  bO = Ae('<div class="card-body border-bottom pb-0"><div class="d-flex align-items-center justify-content-between"><h5 class="mb-0">Recent Wallet Transactions</h5><div class="dropdown"><a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a></div></div></div>', 1),
  vO = {
    class: "tab-content",
    id: "myTabContent"
  },
  yO = {
    key: 0,
    class: "table"
  },
  wO = n("thead", null, [n("tr", null, [n("th", {
    scope: "col"
  }, "FROM"), n("th", {
    scope: "col"
  }, "TO"), n("th", {
    scope: "col"
  }, "DATE"), n("th", {
    scope: "col"
  }, "AMOUNT"), n("th", {
    scope: "col"
  }, "STATUS")])], -1),
  $O = {
    class: "d-flex align-items-center"
  },
  CO = {
    class: "avtar avtar-s border"
  },
  kO = {
    key: 0,
    class: "wid-30",
    src: ps
  },
  AO = {
    key: 1,
    src: Mt,
    class: "wid-30",
    alt: "logo"
  },
  xO = {
    class: "ms-2"
  },
  SO = {
    class: "mb-0 f-w-400"
  },
  EO = {
    class: "d-flex align-items-center"
  },
  PO = {
    class: "avtar avtar-s border"
  },
  TO = {
    key: 0,
    class: "wid-30",
    src: ps
  },
  LO = {
    key: 1,
    src: Mt,
    class: "wid-30",
    alt: "logo"
  },
  OO = {
    class: "ms-2"
  },
  IO = {
    class: "mb-0 f-w-400"
  },
  BO = {
    class: "f-w-500"
  },
  DO = {
    class: "f-w-500 f-16"
  },
  MO = n("td", {
    class: "text-warning"
  }, [n("div", {
    class: "avtar avtar-s bg-light-success"
  }, [n("i", {
    class: "ti ti-check f-20"
  })])], -1),
  RO = Ae('<div class="card-footer"><div class="row g-2"><div class="col-md-6"><div class="d-grid"><button class="btn btn-outline-secondary d-grid"><span class="text-truncate w-100">View all Transaction History</span></button></div></div><div class="col-md-6"><div class="d-grid"><button class="btn btn-primary d-grid"><span class="text-truncate w-100">Create new Transaction</span></button></div></div></div></div>', 1);

function NO(e, t, s, o, i, r) {
  var d, p, m, g, $;
  const a = ke("RouterLink"),
    l = ke("LoadingSpinner");
  return _(), w("div", YL, [n("div", JL, [ZL, n("div", XL, [n("div", QL, [n("div", eO, [n("div", tO, [sO, n("div", nO, [oO, ((d = e.dashboard_data) == null ? void 0 : d.wallet_created) == 1 ? (_(), w("p", iO, "Wallet Balance")) : D("", !0), ((p = e.dashboard_data) == null ? void 0 : p.wallet_created) == 1 ? (_(), w("h2", rO, L(this.$formatCurrency((m = e.dashboard_data) == null ? void 0 : m.wallet_balance)), 1)) : D("", !0), ((g = e.dashboard_data) == null ? void 0 : g.wallet_created) == 1 ? (_(), w("ul", aO, cO)) : D("", !0), (($ = e.dashboard_data) == null ? void 0 : $.wallet_created) != 1 ? (_(), w("button", {
    key: 3,
    class: "btn btn-outline-secondary",
    disabled: e.isLoading,
    type: "button"
  }, [uO, e.isLoading ? (_(), w("span", fO)) : D("", !0), J(" " + L(e.isLoading ? "Creating..." : "Create Account"), 1)], 8, dO)) : D("", !0)]), ie(a, {
    to: "/fund/deposit/deposit"
  }, {
    default: Ue(() => [hO]),
    _: 1
  }), ie(a, {
    to: "/fund/withdraw"
  }, {
    default: Ue(() => [pO]),
    _: 1
  }), ie(a, {
    to: "/fund/deposit/deposit"
  }, {
    default: Ue(() => [mO]),
    _: 1
  })])])]), n("div", gO, [n("div", _O, [bO, n("div", vO, [e.recent_wallet_trans ? (_(), w("table", yO, [wO, n("tbody", null, [(_(!0), w(Te, null, Je(e.recent_wallet_trans.wallet_trans, C => (_(), w("tr", null, [n("td", null, [n("div", $O, [n("div", CO, [C.from_mt_wallet === 1 ? (_(), w("img", kO)) : (_(), w("img", AO))]), n("div", xO, [n("h6", SO, L(C.from_mt_wallet ? "WALLET" : C.from_mt_id), 1)])])]), n("td", null, [n("div", EO, [n("div", PO, [C.to_mt_wallet === 1 ? (_(), w("img", TO)) : (_(), w("img", LO))]), n("div", OO, [n("h6", IO, L(C.to_mt_wallet ? "WALLET" : C.to_mt_id), 1)])])]), n("td", null, [n("h6", BO, L(e.formattedDate(C.created_at)), 1)]), n("td", null, [n("h6", DO, L(this.$formatCurrency(C.trans_amount)), 1)]), MO]))), 256))])])) : (_(), ve(l, {
    key: 1
  }))]), RO])])])])])
}
const FO = je(GL, [
    ["render", NO]
  ]),
  UO = {
    name: "AddBankView",
    data() {
      return {
        isLoading: !1,
        benificiary_name: "",
        iban: "",
        swift: "",
        hide: 0,
        bank_name: "",
        bank_address: "",
        errors: {
          any: ""
        }
      }
    },
    methods: {
      validateFields() {
        this.errors.any = "", !this.benificiary_name || !this.iban || !this.swift || !this.bank_name || !this.bank_address ? this.errors.any = "All fields are mandatory" : (this.errors.any = "", this.addBankDetails())
      },
      async addBankDetails() {
        var e;
        this.isLoading = !0;
        try {
          const t = localStorage.getItem("auth_id"),
            s = $e.get("authToken"),
            o = await ye.post(this.API_BASE_URL + "/profile/bank-add", {
              holder_name: this.benificiary_name,
              account_no: this.iban,
              ifsc_code: this.swift,
              bank_name: this.bank_name,
              bank_address: this.bank_address,
              country: "India"
            }, {
              headers: {
                Authorization: `Bearer ${s}`
              }
            });
          o && (this.hide = 2, new bootstrap.Modal(document.getElementById("add_bank_modal")).hide(), this.isLoading = !1, Swal.fire({
            icon: "success",
            title: o == null ? void 0 : o.data.message
          }).then(r => {
            r.isConfirmed && window.location.reload()
          }))
        } catch (t) {
          this.hide = 2, new bootstrap.Modal(document.getElementById("add_bank_modal")).hide(), Swal.fire({
            icon: "error",
            title: (e = t == null ? void 0 : t.response) == null ? void 0 : e.data.message
          }).then(o => {
            o.isConfirmed && window.location.reload()
          })
        }
      }
    }
  },
  VO = {
    key: 0,
    class: "modal fade",
    id: "add_bank_modal",
    tabindex: "-1",
    role: "dialog",
    "aria-labelledby": "exampleModalCenterTitle",
    "aria-hidden": "true"
  },
  jO = {
    class: "modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable"
  },
  HO = {
    class: "modal-content"
  },
  qO = n("div", {
    class: "modal-header"
  }, [n("h5", {
    class: "modal-title",
    id: "exampleModalLiveLabel"
  }, "Add Bank Details"), n("button", {
    type: "button",
    class: "btn-close",
    "data-bs-dismiss": "modal",
    "aria-label": "Close"
  })], -1),
  WO = {
    class: "modal-body"
  },
  zO = {
    class: "row"
  },
  KO = {
    class: "col-sm-12"
  },
  GO = {
    class: "form-group"
  },
  YO = n("label", {
    class: "form-label"
  }, "Beneficiary Name", -1),
  JO = {
    class: "form-group"
  },
  ZO = n("label", {
    class: "form-label"
  }, "Account Number/IBAN", -1),
  XO = {
    class: "form-group"
  },
  QO = n("label", {
    class: "form-label"
  }, "IFSC Code/SWIFT", -1),
  eI = {
    class: "form-group"
  },
  tI = n("label", {
    class: "form-label"
  }, "Bank Name", -1),
  sI = {
    class: "form-group"
  },
  nI = n("label", {
    class: "form-label"
  }, "Bank Address", -1),
  oI = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  iI = n("hr", {
    class: "my-3 border border-secondary-subtle"
  }, null, -1),
  rI = {
    class: "modal-footer justify-content-between"
  },
  aI = n("ul", {
    class: "list-inline me-auto mb-0"
  }, null, -1),
  lI = {
    class: "flex-grow-1 text-end"
  },
  cI = n("button", {
    type: "button",
    class: "btn btn-link-danger btn-pc-default",
    "data-bs-dismiss": "modal"
  }, "Cancel", -1),
  dI = ["disabled"],
  uI = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  };

function fI(e, t, s, o, i, r) {
  return i.hide != 2 ? (_(), w("div", VO, [n("div", jO, [n("div", HO, [qO, n("div", WO, [n("div", zO, [n("div", KO, [n("div", GO, [YO, be(n("input", {
    "onUpdate:modelValue": t[0] || (t[0] = a => i.benificiary_name = a),
    type: "text",
    class: "form-control",
    placeholder: "Beneficiary Name"
  }, null, 512), [
    [Re, i.benificiary_name]
  ])]), n("div", JO, [ZO, be(n("input", {
    "onUpdate:modelValue": t[1] || (t[1] = a => i.iban = a),
    type: "text",
    class: "form-control",
    placeholder: "Account Number/IBAN"
  }, null, 512), [
    [Re, i.iban]
  ])]), n("div", XO, [QO, be(n("input", {
    "onUpdate:modelValue": t[2] || (t[2] = a => i.swift = a),
    type: "text",
    class: "form-control",
    placeholder: "SWIFT/BIC"
  }, null, 512), [
    [Re, i.swift]
  ])]), n("div", eI, [tI, be(n("input", {
    "onUpdate:modelValue": t[3] || (t[3] = a => i.bank_name = a),
    type: "text",
    class: "form-control",
    placeholder: "Bank Name"
  }, null, 512), [
    [Re, i.bank_name]
  ])]), n("div", sI, [nI, be(n("input", {
    "onUpdate:modelValue": t[4] || (t[4] = a => i.bank_address = a),
    type: "text",
    class: "form-control",
    placeholder: "Bank Address"
  }, null, 512), [
    [Re, i.bank_address]
  ])]), i.errors.any ? (_(), w("div", oI, L(i.errors.any), 1)) : D("", !0), iI])])]), n("div", rI, [aI, n("div", lI, [cI, n("button", {
    class: "btn btn-primary",
    type: "button",
    disabled: i.isLoading,
    onClick: t[5] || (t[5] = (...a) => r.validateFields && r.validateFields(...a))
  }, [i.isLoading ? (_(), w("span", uI)) : D("", !0), J(" " + L(i.isLoading ? "Updating..." : "Add Bank Details"), 1)], 8, dI)])])])])])) : D("", !0)
}
const V1 = je(UO, [
    ["render", fI]
  ]),
  j1 = "/assets/images/bank.png",
  H1 = "/assets/images/trc.png",
  q1 = "/assets/fund/deposit_now_0-DZU7hRP7.webp",
  hI = {
    props: ["liveAccounts"],
    components: {
      AddBankView: V1
    },
    data() {
      return {
        bank_list: null,
        selectedAccount: null,
        selectedBankAccount: null,
        amount: 0,
        withdrawMethod: 1,
        liveMTAccounts: this.liveAccounts,
        isLoading: !1,
        isWithdrawLoading: !1,
        benificiary_name: "",
        iban: "",
        pending_with_count: 0,
        swift: "",
        hide: 0,
        bank_name: "",
        bank_address: "",
        wallet_id: "",
        preview: [null],
        fileData: [null],
        errorMessage: [""],
        doc_verified: localStorage.getItem("doc_verified"),
        errors: {
          any: "",
          selectedAccount: "",
          selectedBankAccount: "",
          amount: ""
        }
      }
    },
    watch: {
      selectedAccount(e, t) {
        this.validateSelectedAccount(e)
      },
      selectedBankAccount(e, t) {
        this.validateSelectedBankAccount(e)
      },
      amount(e, t) {
        this.validateAmount(e)
      }
    },
    mounted() {
      this.loadDataFromLocalStorage(), this.fetchBankList()
    },
    methods: {
      previewFile(e, t) {
        const s = e.target.files[0];
        if (!s) return;
        if (this.preview[t] = null, this.errorMessage[t] = "", !["application/pdf", "image/png", "image/jpeg"].includes(s.type)) {
          this.errorMessage[t] = "Invalid file type. Only PDF, PNG, and JPEG are allowed.";
          return
        }
        if (s.size > 2 * 1024 * 1024) {
          this.errorMessage[t] = "File is too large. The size limit is 2MB.";
          return
        }
        this.fileData[t] = s, this.preview[t] = URL.createObjectURL(s)
      },
      loadDataFromLocalStorage() {
        const e = me.getDataWithExpiration("bank_details");
        e && (this.bank_list = e);
        const t = me.getDataWithExpiration("pending_wd_count");
        t && (this.pending_with_count = t, console.log(this.pending_with_count));
        const s = me.getDataWithExpiration("liveAccounts");
        s && (this.liveMTAccounts = s, this.selectedAccount = s[0])
      },
      async fetchBankList() {
        ye.get(`${Ge("API_BASE_URL")}/profile/bank-list`, {
          headers: {
            Authorization: `Bearer ${$e.get("authToken")}`
          }
        }).then(e => {
          this.bank_list = e.data.result.bank_details, me.setDataWithTimestamp("bank_details", e.data.result.bank_details, 30)
        }).catch(e => {
          var t;
          Swal.fire({
            icon: "error",
            title: (t = e.response) == null ? void 0 : t.data.message
          }).then(s => {
            s.isConfirmed && window.location.reload()
          })
        })
      },
      validateFields() {
        this.errors.any = "", !this.benificiary_name || !this.iban || !this.swift || !this.bank_name || !this.bank_address ? this.errors.any = "All fields are mandatory" : (this.errors.any = "", this.addBankDetails())
      },
      async addBankDetails() {
        var e;
        this.isLoading = !0;
        try {
          const t = localStorage.getItem("auth_id"),
            s = $e.get("authToken"),
            o = await ye.post(this.API_BASE_URL + "/profile/bank-add", {
              holder_name: this.benificiary_name,
              account_no: this.iban,
              ifsc_code: this.swift,
              bank_name: this.bank_name,
              bank_address: this.bank_address,
              country: "India"
            }, {
              headers: {
                Authorization: `Bearer ${s}`
              }
            });
          o && (this.bank_list = o.data.result.bank_details, me.setDataWithTimestamp("bank_details", o.data.result.bank_details, 30), this.hide = 2, new bootstrap.Modal(document.getElementById("addBankModal2")).hide(), this.isLoading = !1, Swal.fire({
            icon: "success",
            title: o == null ? void 0 : o.data.message
          }).then(r => {
            r.isConfirmed && window.location.reload()
          }))
        } catch (t) {
          this.hide = 2, new bootstrap.Modal(document.getElementById("addBankModal2")).hide(), Swal.fire({
            icon: "error",
            title: (e = t == null ? void 0 : t.response) == null ? void 0 : e.data.message
          }).then(o => {
            o.isConfirmed && window.location.reload()
          })
        }
      },
      validateWithdrawTicketFields() {
        this.pending_with_count > 0 ? Swal.fire({
          icon: "error",
          title: "You already have a pending withdrawal request. Please wait for its resolution before submitting a new one."
        }).then(e => {
          e.isConfirmed && os.push("/dashboard")
        }) : this.validateSelectedAccount(this.selectedAccount) && this.validateSelectedBankAccount(this.selectedBankAccount) && this.validateAmount(this.amount) && (this.isWithdrawLoading = !0, this.createWithdrawTicket())
      },
      async createWithdrawTicket() {
        try {
          const e = $e.get("authToken"),
            t = new FormData;
          t.append("mt5id", this.selectedAccount.mt_id), t.append("trans_amount", this.amount), t.append("withdraw_method", this.withdrawMethod), t.append("bank_account", this.selectedBankAccount.id), t.append("is_wallet", !1), t.append("attachment", this.fileData[0]), t.append("client_note", this.wallet_id ? this.wallet_id : "");
          const s = await ye.post(this.API_BASE_URL + "/fund/withdraw/create", t, {
            headers: {
              "Content-Type": "multipart/form-data",
              Authorization: `Bearer ${e}`
            }
          });
          s && (this.isWithdrawLoading = !1, me.setDataWithTimestamp("pending_wd_count", this.pending_with_count + 1, 30), Swal.fire({
            icon: "success",
            title: s == null ? void 0 : s.data.message
          }).then(o => {
            o.isConfirmed && window.location.reload()
          }))
        } catch (e) {
          this.isWithdrawLoading = !1, console.log(e), Swal.fire({
            icon: "error",
            title: e.response.message
          }).then(t => {
            t.isConfirmed && window.location.reload()
          })
        }
      },
      validateSelectedAccount(e) {
        return e ? (this.errors.selectedAccount = "", !0) : (this.errors.selectedAccount = "Please select FROM account", !1)
      },
      validateSelectedBankAccount(e) {
        return e ? (this.errors.selectedBankAccount = "", !0) : (this.errors.selectedBankAccount = "Please select Bank Account", !1)
      },
      validateAmount(e) {
        return this.validateSelectedAccount(this.selectedAccount) ? e < 10 ? (this.errors.amount = "Minimum Withdrawable amount is 10 $", !1) : e > this.selectedAccount.margin_free - this.selectedAccount.credit ? (this.errors.amount = "You dont have enough balance in your account", !1) : (this.errors.amount = "", !0) : (this.errors.amount = "", !1)
      }
    },
    computed: {
      isPDF() {
        return e => this.fileData[e] && this.fileData[e].type === "application/pdf"
      },
      isImage() {
        return e => this.fileData[e] && this.fileData[e].type.includes("image")
      }
    }
  },
  pI = {
    class: "row"
  },
  mI = {
    class: "col-12"
  },
  gI = {
    class: "row"
  },
  _I = {
    class: "col-xl-8"
  },
  bI = {
    class: "card"
  },
  vI = n("div", {
    class: "card-body border-bottom"
  }, [n("h6", null, "CREATE WITHDRAW TICKET")], -1),
  yI = {
    class: "card-body"
  },
  wI = n("div", {
    class: "divider my-4"
  }, [n("span", null, "SELECT MT5 ACCOUNT")], -1),
  $I = {
    class: "row g-1"
  },
  CI = {
    class: "address-check border rounded"
  },
  kI = {
    class: "form-check paycard"
  },
  AI = ["id", "value", "name"],
  xI = ["for"],
  SI = {
    class: "p-1 my-1"
  },
  EI = {
    class: "row"
  },
  PI = {
    class: "col-6 mt-1"
  },
  TI = {
    key: 0,
    class: "h5 mb-0 d-block f-w-500 pb-0"
  },
  LI = n("img", {
    src: Mt,
    alt: "user-image",
    class: "wid-25 me-1 ms-1"
  }, null, -1),
  OI = {
    key: 1,
    class: "h4 mb-0 d-block pb-0"
  },
  II = n("img", {
    src: ps,
    alt: "user-image",
    class: "user-avtar wid-40"
  }, null, -1),
  BI = {
    class: "col-6 text-end mb-0 pb-0 pe-3"
  },
  DI = {
    class: "h5 mb-0 d-block f-w-500"
  },
  MI = n("span", {
    class: "text-muted mb-0 f-10"
  }, "Current Balance", -1),
  RI = n("div", {
    class: "divider my-4"
  }, [n("span", null, "SELECT WITHDRAW METHOD")], -1),
  NI = {
    class: "row g-1"
  },
  FI = {
    class: "col-md-3 col-lg-4 col-xl-4"
  },
  UI = {
    class: "address-check border rounded"
  },
  VI = {
    class: "form-check"
  },
  jI = Ae('<label class="form-check-label d-block" for="payopn-check-1"><span class="card-body p-2 d-block"><span class="h6 f-w-500 mb-1 d-block">BANK WITHDRAWAL</span><span class="d-flex align-items-center"><span class="f-10 badge bg-light-success me-1">WITHDRAW TO BANK ACCOUNT</span><img src="' + j1 + '" alt="img" class="img-fluid ms-1 wid-25"></span></span></label>', 1),
  HI = {
    class: "col-md-3 col-lg-4 col-xl-4"
  },
  qI = {
    class: "address-check border rounded"
  },
  WI = {
    class: "form-check"
  },
  zI = Ae('<label class="form-check-label d-block" for="payopn-check-2"><span class="card-body p-2 d-block"><span class="h6 f-w-500 mb-1 d-block">USDT WITHDRAWAL</span><span class="d-flex align-items-center"><span class="f-12 badge bg-light-success me-3">USDT-TRC20</span><img src="' + H1 + '" alt="img" class="img-fluid ms-1 wid-25"></span></span></label>', 1),
  KI = {
    class: "col-md-3 col-lg-4 col-xl-4"
  },
  GI = {
    class: "address-check border rounded"
  },
  YI = {
    class: "form-check"
  },
  JI = Ae('<label class="form-check-label d-block" for="payopn-check-3"><span class="card-body p-2 d-block"><span class="d-flex align-items-center"><span><span class="h6 f-w-500 mb-1 d-block">OTHERS</span><span class="f-12 text-muted">Other Withdrawal Options</span></span></span></span></label>', 1),
  ZI = n("div", {
    class: "divider my-4"
  }, [n("span", null, "WITHDRAW DETAILS")], -1),
  XI = {
    class: "row"
  },
  QI = {
    class: "col-12 mt-2"
  },
  eB = {
    key: 0,
    class: "form-group row"
  },
  tB = n("label", {
    class: "col-lg-4 col-form-label"
  }, [J("SELECT BANK ACCOUNT :"), n("small", {
    class: "text-muted d-block"
  }, " Please select the bank account to which you wish to transfer your funds ")], -1),
  sB = {
    class: "col-lg-8"
  },
  nB = {
    key: 0,
    class: "form-group"
  },
  oB = ["value"],
  iB = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  rB = {
    key: 1,
    class: "form-group"
  },
  aB = n("button", {
    type: "button",
    class: "btn btn-primary",
    "data-bs-toggle": "modal",
    "data-bs-target": "#addBankModal2"
  }, [n("i", {
    class: "ti ti-plus f-18"
  }), J("Add Bank Information")], -1),
  lB = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  cB = {
    class: "form-group row"
  },
  dB = n("label", {
    class: "col-lg-4 col-form-label"
  }, [J("ENTER AMOUNT :"), n("small", {
    class: "text-muted d-block"
  }, " Please enter the amount that you need to transfer")], -1),
  uB = {
    class: "col-lg-8"
  },
  fB = {
    class: "input-group mb-3"
  },
  hB = n("span", {
    class: "input-group-text"
  }, "$", -1),
  pB = n("span", {
    class: "input-group-text"
  }, ".00", -1),
  mB = {
    key: 1,
    class: "form-group row"
  },
  gB = n("label", {
    class: "col-lg-4 col-form-label"
  }, [J("ENTER WALLET ID :"), n("small", {
    class: "text-muted d-block"
  }, " Please enter your Wallet ID")], -1),
  _B = {
    class: "col-lg-8"
  },
  bB = {
    class: "input-group mb-3"
  },
  vB = {
    key: 2,
    class: "form-group row"
  },
  yB = n("label", {
    class: "col-lg-4 col-form-label"
  }, [J("CLIENT NOTE :"), n("small", {
    class: "text-muted d-block"
  })], -1),
  wB = {
    class: "col-lg-8"
  },
  $B = {
    class: "input-group mb-3"
  },
  CB = {
    key: 3,
    class: "form-group row"
  },
  kB = n("label", {
    class: "col-lg-4 col-form-label"
  }, [J("UPLOAD USDT WALLET QR CODE :"), n("small", {
    class: "text-muted d-block"
  }, " Upload your Wallet QR CODE ")], -1),
  AB = {
    class: "col-lg-8"
  },
  xB = {
    key: 0,
    class: "preview mt-2"
  },
  SB = ["src"],
  EB = ["src"],
  PB = {
    key: 1,
    class: "alert alert-danger mt-2"
  },
  TB = {
    class: ""
  },
  LB = {
    class: "row"
  },
  OB = n("div", {
    class: "col-lg-4"
  }, null, -1),
  IB = {
    class: "col-lg-8"
  },
  BB = {
    class: "row g-1"
  },
  DB = ["disabled"],
  MB = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  RB = {
    class: "form-group"
  },
  NB = {
    class: "d-grid gap-2 mt-4"
  },
  FB = {
    key: 0,
    class: "alert alert-warning d-flex align-items-center",
    role: "alert"
  },
  UB = n("i", {
    class: "ti ti-info-circle me-2 fs-3"
  }, null, -1),
  VB = {
    key: 1,
    class: "alert alert-info d-flex align-items-center",
    role: "alert"
  },
  jB = n("i", {
    class: "ti ti-info-circle me-2 fs-3"
  }, null, -1),
  HB = n("div", null, " Your identity verification documents have been received but are not yet verified. This process can take some time as we ensure the highest security standards. If you need immediate assistance or have any concerns, please do not hesitate to connect with our support team. We appreciate your patience and cooperation. ", -1),
  qB = [jB, HB],
  WB = {
    class: "col-xl-4"
  },
  zB = Ae('<div class="card coupon-card bg-primary"><div class="card-body"><div class="row"><div class="col-8 d-flex flex-column align-items-start justify-content-center"><h3 class="text-white f-w-500">Fuel Your Trading Journey</h3><span class="f-16 py-2 text-white">Deposit now and unlock the gateway to global markets.</span></div><div class="col-4 text-end"><img src="' + q1 + '" alt="img" class="img-fluid wid-110"></div></div></div></div>', 1),
  KB = {
    class: "card"
  },
  GB = n("div", {
    class: "card-header"
  }, [n("h5", null, "MT5 ACCOUNTS SUMMARY")], -1),
  YB = {
    class: "card-body p-0"
  },
  JB = {
    class: "list-group list-group-flush"
  },
  ZB = {
    class: "media align-items-start"
  },
  XB = {
    key: 0,
    class: "h4 mb-0 d-block f-w-500 pb-0"
  },
  QB = n("img", {
    src: Mt,
    alt: "user-image",
    class: "wid-25 me-1 ms-1"
  }, null, -1),
  eD = [QB],
  tD = {
    key: 1,
    class: "h4 mb-0 d-block pb-0"
  },
  sD = n("img", {
    src: ps,
    alt: "user-image",
    class: "user-avtar wid-40"
  }, null, -1),
  nD = [sD],
  oD = {
    class: "media-body mx-2"
  },
  iD = {
    class: "mb-1"
  },
  rD = {
    key: 0,
    class: "h4 mb-0 d-block f-w-500 pb-0"
  },
  aD = {
    key: 1,
    class: "h4 mb-0 d-block pb-0"
  },
  lD = {
    class: "text-sm mb-2"
  },
  cD = n("span", {
    class: "text-muted"
  }, "ACCOUNT CATEGORY :", -1),
  dD = {
    class: "border-top border-dashed"
  },
  uD = {
    class: "mb-1 mt-2"
  },
  fD = n("span", {
    class: "text-muted"
  }, "LEVERAGE :", -1),
  hD = n("span", {
    class: "text-muted"
  }, "| CREDIT :", -1),
  pD = n("span", {
    class: "text-muted"
  }, "| EQUITY :", -1),
  mD = {
    href: "#",
    class: "flex-shrink-0"
  },
  gD = {
    class: "f-w-500"
  },
  _D = n("p", {
    class: "text-muted text-sm mb-2 text-end"
  }, "Balance", -1),
  bD = {
    class: "list-group-item"
  },
  vD = {
    class: "float-end"
  },
  yD = {
    class: "mb-0 fw-medium"
  },
  wD = n("span", {
    class: "text-muted"
  }, "TOTAL CREDIT", -1),
  $D = {
    class: "list-group-item"
  },
  CD = {
    class: "float-end"
  },
  kD = {
    class: "mb-0 fw-medium"
  },
  AD = n("span", {
    class: "text-muted"
  }, "TOTAL EQUITY", -1),
  xD = {
    key: 0,
    id: "addBankModal2",
    class: "modal fade",
    tabindex: "-1",
    role: "dialog",
    "aria-labelledby": "exampleModalCenterTitle",
    "aria-hidden": "true"
  },
  SD = {
    class: "modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable"
  },
  ED = {
    class: "modal-content"
  },
  PD = n("div", {
    class: "modal-header"
  }, [n("h5", {
    class: "modal-title",
    id: "exampleModalLiveLabel"
  }, "Add Bank Details"), n("button", {
    type: "button",
    class: "btn-close",
    "data-bs-dismiss": "modal",
    "aria-label": "Close"
  })], -1),
  TD = {
    class: "modal-body"
  },
  LD = {
    class: "row"
  },
  OD = {
    class: "col-sm-12"
  },
  ID = {
    class: "form-group"
  },
  BD = n("label", {
    class: "form-label"
  }, "Beneficiary Name", -1),
  DD = {
    class: "form-group"
  },
  MD = n("label", {
    class: "form-label"
  }, "Account Number/IBAN", -1),
  RD = {
    class: "form-group"
  },
  ND = n("label", {
    class: "form-label"
  }, "IFSC Code/SWIFT", -1),
  FD = {
    class: "form-group"
  },
  UD = n("label", {
    class: "form-label"
  }, "Bank Name", -1),
  VD = {
    class: "form-group"
  },
  jD = n("label", {
    class: "form-label"
  }, "Bank Address", -1),
  HD = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  qD = n("hr", {
    class: "my-3 border border-secondary-subtle"
  }, null, -1),
  WD = {
    class: "modal-footer justify-content-between"
  },
  zD = n("ul", {
    class: "list-inline me-auto mb-0"
  }, null, -1),
  KD = {
    class: "flex-grow-1 text-end"
  },
  GD = n("button", {
    type: "button",
    class: "btn btn-link-danger btn-pc-default",
    "data-bs-dismiss": "modal"
  }, "Cancel", -1),
  YD = ["disabled"],
  JD = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  };

function ZD(e, t, s, o, i, r) {
  var l;
  const a = ke("RouterLink");
  return _(), w("div", null, [n("div", pI, [n("div", mI, [n("div", gI, [n("div", _I, [n("div", bI, [vI, n("div", yI, [wI, n("div", $I, [s.liveAccounts ? (_(!0), w(Te, {
    key: 0
  }, Je(i.liveMTAccounts, d => (_(), w("div", {
    class: "col-md-3 col-lg-4 col-xl-4",
    key: d.mt_id
  }, [n("div", CI, [n("div", kI, [be(n("input", {
    id: d.mt_id,
    "onUpdate:modelValue": t[0] || (t[0] = p => i.selectedAccount = p),
    value: d,
    type: "radio",
    name: d.mt_id,
    class: "form-check-input input-primary"
  }, null, 8, AI), [
    [kt, i.selectedAccount]
  ]), n("label", {
    class: "form-check-label d-block",
    for: d.mt_id
  }, [n("div", SI, [n("span", EI, [n("span", PI, [d.is_wallet != 1 ? (_(), w("span", TI, [LI, J(" " + L(d.mt_id), 1)])) : (_(), w("span", OI, [II, J(" Wallet ")]))]), n("span", BI, [n("span", DI, L(this.$formatCurrency(d.balance)), 1), MI])])])], 8, xI)])])]))), 128)) : D("", !0)]), RI, n("div", NI, [n("div", FI, [n("div", UI, [n("div", VI, [be(n("input", {
    type: "radio",
    "onUpdate:modelValue": t[1] || (t[1] = d => i.withdrawMethod = d),
    value: 1,
    name: "payoptradio1",
    class: "form-check-input input-primary",
    id: "payopn-check-1",
    checked: ""
  }, null, 512), [
    [kt, i.withdrawMethod]
  ]), jI])])]), n("div", HI, [n("div", qI, [n("div", WI, [be(n("input", {
    type: "radio",
    "onUpdate:modelValue": t[2] || (t[2] = d => i.withdrawMethod = d),
    value: 2,
    name: "payoptradio1",
    class: "form-check-input input-primary",
    id: "payopn-check-2"
  }, null, 512), [
    [kt, i.withdrawMethod]
  ]), zI])])]), n("div", KI, [n("div", GI, [n("div", YI, [be(n("input", {
    type: "radio",
    "onUpdate:modelValue": t[3] || (t[3] = d => i.withdrawMethod = d),
    value: 3,
    name: "payoptradio1",
    class: "form-check-input input-primary",
    id: "payopn-check-3"
  }, null, 512), [
    [kt, i.withdrawMethod]
  ]), JI])])])]), ZI, n("div", XI, [n("div", QI, [i.withdrawMethod === 1 ? (_(), w("div", eB, [tB, n("div", sB, [((l = i.bank_list) == null ? void 0 : l.length) > 0 ? (_(), w("div", nB, [be(n("select", {
    "onUpdate:modelValue": t[4] || (t[4] = d => i.selectedBankAccount = d),
    class: "form-select",
    id: "exampleFormControlSelect1"
  }, [(_(!0), w(Te, null, Je(i.bank_list, d => (_(), w("option", {
    key: d.id,
    value: d
  }, L(d.account_no), 9, oB))), 128))], 512), [
    [Xn, i.selectedBankAccount]
  ]), i.errors.selectedBankAccount ? (_(), w("div", iB, L(i.errors.selectedBankAccount), 1)) : D("", !0)])) : (_(), w("div", rB, [aB, i.errors.selectedBankAccount ? (_(), w("div", lB, L(i.errors.selectedBankAccount), 1)) : D("", !0)]))])])) : D("", !0), n("div", cB, [dB, n("div", uB, [n("div", fB, [hB, be(n("input", {
    "onUpdate:modelValue": t[5] || (t[5] = d => i.amount = d),
    type: "text",
    class: "form-control",
    "aria-label": "Amount (to the nearest dollar)"
  }, null, 512), [
    [Re, i.amount]
  ]), pB])])]), i.withdrawMethod === 2 ? (_(), w("div", mB, [gB, n("div", _B, [n("div", bB, [be(n("input", {
    "onUpdate:modelValue": t[6] || (t[6] = d => i.wallet_id = d),
    type: "text",
    class: "form-control",
    "aria-label": "Enter your Wallet ID"
  }, null, 512), [
    [Re, i.wallet_id]
  ])])])])) : D("", !0), i.withdrawMethod === 3 ? (_(), w("div", vB, [yB, n("div", wB, [n("div", $B, [be(n("input", {
    "onUpdate:modelValue": t[7] || (t[7] = d => i.wallet_id = d),
    type: "text",
    class: "form-control",
    "aria-label": "Enter your Wallet ID"
  }, null, 512), [
    [Re, i.wallet_id]
  ])])])])) : D("", !0), i.withdrawMethod === 2 ? (_(), w("div", CB, [kB, n("div", AB, [n("input", {
    type: "file",
    onChange: t[8] || (t[8] = d => r.previewFile(d, 0)),
    accept: "application/pdf,image/png,image/jpeg",
    class: "form-control"
  }, null, 32), i.preview[0] ? (_(), w("div", xB, [r.isImage(0) ? (_(), w("img", {
    key: 0,
    src: i.preview[0],
    alt: "Preview Image",
    class: "img-fluid",
    style: {
      "max-height": "220px",
      width: "auto",
      "max-width": "100%"
    }
  }, null, 8, SB)) : D("", !0), r.isPDF(0) ? (_(), w("iframe", {
    key: 1,
    src: i.preview[0],
    class: "file-preview w-100",
    style: {
      height: "30vh"
    }
  }, null, 8, EB)) : D("", !0)])) : D("", !0), i.errorMessage[0] ? (_(), w("div", PB, L(i.errorMessage[0]), 1)) : D("", !0)])])) : D("", !0)])]), n("div", TB, [n("div", LB, [OB, n("div", IB, [n("div", BB, [i.doc_verified === "3" ? (_(), w("button", {
    key: 0,
    class: "btn btn-primary",
    type: "button",
    disabled: i.isWithdrawLoading,
    onClick: t[9] || (t[9] = (...d) => r.validateWithdrawTicketFields && r.validateWithdrawTicketFields(...d))
  }, [i.isWithdrawLoading ? (_(), w("span", MB)) : D("", !0), J(" " + L(i.isLoading ? "Processing..." : "PROCESS WITHDRAWAL"), 1)], 8, DB)) : D("", !0)])])])]), n("form", null, [n("div", RB, [n("div", NB, [i.doc_verified === "0" ? (_(), w("div", FB, [UB, n("div", null, [J(" To proceed with withdrawals, you need to upload the required identity verification documents. Verification of your identity is essential for securing your transactions and complying with regulatory requirements. "), ie(a, {
    to: "/user/profile"
  }, {
    default: Ue(() => [J("Please upload your documents")]),
    _: 1
  }), J(" at your earliest convenience through your profile settings. ")])])) : D("", !0), i.doc_verified === "1" ? (_(), w("div", VB, qB)) : D("", !0)])])])])])]), n("div", WB, [zB, n("div", KB, [GB, n("div", YB, [n("ul", JB, [s.liveAccounts ? (_(!0), w(Te, {
    key: 0
  }, Je(i.liveMTAccounts, d => (_(), w("li", {
    key: d.mt_id,
    class: "list-group-item"
  }, [n("div", ZB, [d.is_wallet != 1 ? (_(), w("span", XB, eD)) : (_(), w("span", tD, nD)), n("div", oD, [n("h5", iD, [d.is_wallet != 1 ? (_(), w("span", rD, L(d.mt_id), 1)) : (_(), w("span", aD, " Wallet "))]), n("p", lD, [cD, J(" " + L(d.mt_category), 1)]), n("div", dD, [n("p", uD, [fD, J(" " + L(d.leverage) + " ", 1), hD, J(" " + L(this.$formatCurrency(d.credit)) + " ", 1), pD, J(" " + L(this.$formatCurrency(d.equity)), 1)])])]), n("div", mD, [n("h4", gD, L(this.$formatCurrency(d.balance)), 1), _D])])]))), 128)) : D("", !0), n("li", bD, [n("div", vD, [n("h4", yD, L(this.$formatCurrency(i.liveMTAccounts.reduce((d, p) => d + p.credit, 0))), 1)]), wD]), n("li", $D, [n("div", CD, [n("h4", kD, L(this.$formatCurrency(i.liveMTAccounts.reduce((d, p) => d + p.equity, 0))), 1)]), AD])])])])])])])]), i.hide != 2 ? (_(), w("div", xD, [n("div", SD, [n("div", ED, [PD, n("div", TD, [n("div", LD, [n("div", OD, [n("div", ID, [BD, be(n("input", {
    "onUpdate:modelValue": t[10] || (t[10] = d => i.benificiary_name = d),
    type: "text",
    class: "form-control",
    placeholder: "Beneficiary Name"
  }, null, 512), [
    [Re, i.benificiary_name]
  ])]), n("div", DD, [MD, be(n("input", {
    "onUpdate:modelValue": t[11] || (t[11] = d => i.iban = d),
    type: "text",
    class: "form-control",
    placeholder: "Account Number/IBAN"
  }, null, 512), [
    [Re, i.iban]
  ])]), n("div", RD, [ND, be(n("input", {
    "onUpdate:modelValue": t[12] || (t[12] = d => i.swift = d),
    type: "text",
    class: "form-control",
    placeholder: "SWIFT/BIC"
  }, null, 512), [
    [Re, i.swift]
  ])]), n("div", FD, [UD, be(n("input", {
    "onUpdate:modelValue": t[13] || (t[13] = d => i.bank_name = d),
    type: "text",
    class: "form-control",
    placeholder: "Bank Name"
  }, null, 512), [
    [Re, i.bank_name]
  ])]), n("div", VD, [jD, be(n("input", {
    "onUpdate:modelValue": t[14] || (t[14] = d => i.bank_address = d),
    type: "text",
    class: "form-control",
    placeholder: "Bank Address"
  }, null, 512), [
    [Re, i.bank_address]
  ])]), i.errors.any ? (_(), w("div", HD, L(i.errors.any), 1)) : D("", !0), qD])])]), n("div", WD, [zD, n("div", KD, [GD, n("button", {
    class: "btn btn-primary",
    type: "button",
    disabled: i.isLoading,
    onClick: t[15] || (t[15] = (...d) => r.validateFields && r.validateFields(...d))
  }, [i.isLoading ? (_(), w("span", JD)) : D("", !0), J(" " + L(i.isLoading ? "Updating..." : "Add Bank Details"), 1)], 8, YD)])])])])])) : D("", !0)])
}
const XD = je(hI, [
  ["render", ZD]
]);
var Yi = {},
  QD = function () {
    return typeof Promise == "function" && Promise.prototype && Promise.prototype.then
  },
  W1 = {},
  rs = {};
let qu;
const eM = [0, 26, 44, 70, 100, 134, 172, 196, 242, 292, 346, 404, 466, 532, 581, 655, 733, 815, 901, 991, 1085, 1156, 1258, 1364, 1474, 1588, 1706, 1828, 1921, 2051, 2185, 2323, 2465, 2611, 2761, 2876, 3034, 3196, 3362, 3532, 3706];
rs.getSymbolSize = function (t) {
  if (!t) throw new Error('"version" cannot be null or undefined');
  if (t < 1 || t > 40) throw new Error('"version" should be in range from 1 to 40');
  return t * 4 + 17
};
rs.getSymbolTotalCodewords = function (t) {
  return eM[t]
};
rs.getBCHDigit = function (e) {
  let t = 0;
  for (; e !== 0;) t++, e >>>= 1;
  return t
};
rs.setToSJISFunction = function (t) {
  if (typeof t != "function") throw new Error('"toSJISFunc" is not a valid function.');
  qu = t
};
rs.isKanjiModeEnabled = function () {
  return typeof qu < "u"
};
rs.toSJIS = function (t) {
  return qu(t)
};
var _l = {};
(function (e) {
  e.L = {
    bit: 1
  }, e.M = {
    bit: 0
  }, e.Q = {
    bit: 3
  }, e.H = {
    bit: 2
  };

  function t(s) {
    if (typeof s != "string") throw new Error("Param is not a string");
    switch (s.toLowerCase()) {
      case "l":
      case "low":
        return e.L;
      case "m":
      case "medium":
        return e.M;
      case "q":
      case "quartile":
        return e.Q;
      case "h":
      case "high":
        return e.H;
      default:
        throw new Error("Unknown EC Level: " + s)
    }
  }
  e.isValid = function (o) {
    return o && typeof o.bit < "u" && o.bit >= 0 && o.bit < 4
  }, e.from = function (o, i) {
    if (e.isValid(o)) return o;
    try {
      return t(o)
    } catch {
      return i
    }
  }
})(_l);

function z1() {
  this.buffer = [], this.length = 0
}
z1.prototype = {
  get: function (e) {
    const t = Math.floor(e / 8);
    return (this.buffer[t] >>> 7 - e % 8 & 1) === 1
  },
  put: function (e, t) {
    for (let s = 0; s < t; s++) this.putBit((e >>> t - s - 1 & 1) === 1)
  },
  getLengthInBits: function () {
    return this.length
  },
  putBit: function (e) {
    const t = Math.floor(this.length / 8);
    this.buffer.length <= t && this.buffer.push(0), e && (this.buffer[t] |= 128 >>> this.length % 8), this.length++
  }
};
var tM = z1;

function Ji(e) {
  if (!e || e < 1) throw new Error("BitMatrix size must be defined and greater than 0");
  this.size = e, this.data = new Uint8Array(e * e), this.reservedBit = new Uint8Array(e * e)
}
Ji.prototype.set = function (e, t, s, o) {
  const i = e * this.size + t;
  this.data[i] = s, o && (this.reservedBit[i] = !0)
};
Ji.prototype.get = function (e, t) {
  return this.data[e * this.size + t]
};
Ji.prototype.xor = function (e, t, s) {
  this.data[e * this.size + t] ^= s
};
Ji.prototype.isReserved = function (e, t) {
  return this.reservedBit[e * this.size + t]
};
var sM = Ji,
  K1 = {};
(function (e) {
  const t = rs.getSymbolSize;
  e.getRowColCoords = function (o) {
    if (o === 1) return [];
    const i = Math.floor(o / 7) + 2,
      r = t(o),
      a = r === 145 ? 26 : Math.ceil((r - 13) / (2 * i - 2)) * 2,
      l = [r - 7];
    for (let d = 1; d < i - 1; d++) l[d] = l[d - 1] - a;
    return l.push(6), l.reverse()
  }, e.getPositions = function (o) {
    const i = [],
      r = e.getRowColCoords(o),
      a = r.length;
    for (let l = 0; l < a; l++)
      for (let d = 0; d < a; d++) l === 0 && d === 0 || l === 0 && d === a - 1 || l === a - 1 && d === 0 || i.push([r[l], r[d]]);
    return i
  }
})(K1);
var G1 = {};
const nM = rs.getSymbolSize,
  zh = 7;
G1.getPositions = function (t) {
  const s = nM(t);
  return [
    [0, 0],
    [s - zh, 0],
    [0, s - zh]
  ]
};
var Y1 = {};
(function (e) {
  e.Patterns = {
    PATTERN000: 0,
    PATTERN001: 1,
    PATTERN010: 2,
    PATTERN011: 3,
    PATTERN100: 4,
    PATTERN101: 5,
    PATTERN110: 6,
    PATTERN111: 7
  };
  const t = {
    N1: 3,
    N2: 3,
    N3: 40,
    N4: 10
  };
  e.isValid = function (i) {
    return i != null && i !== "" && !isNaN(i) && i >= 0 && i <= 7
  }, e.from = function (i) {
    return e.isValid(i) ? parseInt(i, 10) : void 0
  }, e.getPenaltyN1 = function (i) {
    const r = i.size;
    let a = 0,
      l = 0,
      d = 0,
      p = null,
      m = null;
    for (let g = 0; g < r; g++) {
      l = d = 0, p = m = null;
      for (let $ = 0; $ < r; $++) {
        let C = i.get(g, $);
        C === p ? l++ : (l >= 5 && (a += t.N1 + (l - 5)), p = C, l = 1), C = i.get($, g), C === m ? d++ : (d >= 5 && (a += t.N1 + (d - 5)), m = C, d = 1)
      }
      l >= 5 && (a += t.N1 + (l - 5)), d >= 5 && (a += t.N1 + (d - 5))
    }
    return a
  }, e.getPenaltyN2 = function (i) {
    const r = i.size;
    let a = 0;
    for (let l = 0; l < r - 1; l++)
      for (let d = 0; d < r - 1; d++) {
        const p = i.get(l, d) + i.get(l, d + 1) + i.get(l + 1, d) + i.get(l + 1, d + 1);
        (p === 4 || p === 0) && a++
      }
    return a * t.N2
  }, e.getPenaltyN3 = function (i) {
    const r = i.size;
    let a = 0,
      l = 0,
      d = 0;
    for (let p = 0; p < r; p++) {
      l = d = 0;
      for (let m = 0; m < r; m++) l = l << 1 & 2047 | i.get(p, m), m >= 10 && (l === 1488 || l === 93) && a++, d = d << 1 & 2047 | i.get(m, p), m >= 10 && (d === 1488 || d === 93) && a++
    }
    return a * t.N3
  }, e.getPenaltyN4 = function (i) {
    let r = 0;
    const a = i.data.length;
    for (let d = 0; d < a; d++) r += i.data[d];
    return Math.abs(Math.ceil(r * 100 / a / 5) - 10) * t.N4
  };

  function s(o, i, r) {
    switch (o) {
      case e.Patterns.PATTERN000:
        return (i + r) % 2 === 0;
      case e.Patterns.PATTERN001:
        return i % 2 === 0;
      case e.Patterns.PATTERN010:
        return r % 3 === 0;
      case e.Patterns.PATTERN011:
        return (i + r) % 3 === 0;
      case e.Patterns.PATTERN100:
        return (Math.floor(i / 2) + Math.floor(r / 3)) % 2 === 0;
      case e.Patterns.PATTERN101:
        return i * r % 2 + i * r % 3 === 0;
      case e.Patterns.PATTERN110:
        return (i * r % 2 + i * r % 3) % 2 === 0;
      case e.Patterns.PATTERN111:
        return (i * r % 3 + (i + r) % 2) % 2 === 0;
      default:
        throw new Error("bad maskPattern:" + o)
    }
  }
  e.applyMask = function (i, r) {
    const a = r.size;
    for (let l = 0; l < a; l++)
      for (let d = 0; d < a; d++) r.isReserved(d, l) || r.xor(d, l, s(i, d, l))
  }, e.getBestMask = function (i, r) {
    const a = Object.keys(e.Patterns).length;
    let l = 0,
      d = 1 / 0;
    for (let p = 0; p < a; p++) {
      r(p), e.applyMask(p, i);
      const m = e.getPenaltyN1(i) + e.getPenaltyN2(i) + e.getPenaltyN3(i) + e.getPenaltyN4(i);
      e.applyMask(p, i), m < d && (d = m, l = p)
    }
    return l
  }
})(Y1);
var bl = {};
const yn = _l,
  ca = [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 1, 2, 2, 4, 1, 2, 4, 4, 2, 4, 4, 4, 2, 4, 6, 5, 2, 4, 6, 6, 2, 5, 8, 8, 4, 5, 8, 8, 4, 5, 8, 11, 4, 8, 10, 11, 4, 9, 12, 16, 4, 9, 16, 16, 6, 10, 12, 18, 6, 10, 17, 16, 6, 11, 16, 19, 6, 13, 18, 21, 7, 14, 21, 25, 8, 16, 20, 25, 8, 17, 23, 25, 9, 17, 23, 34, 9, 18, 25, 30, 10, 20, 27, 32, 12, 21, 29, 35, 12, 23, 34, 37, 12, 25, 34, 40, 13, 26, 35, 42, 14, 28, 38, 45, 15, 29, 40, 48, 16, 31, 43, 51, 17, 33, 45, 54, 18, 35, 48, 57, 19, 37, 51, 60, 19, 38, 53, 63, 20, 40, 56, 66, 21, 43, 59, 70, 22, 45, 62, 74, 24, 47, 65, 77, 25, 49, 68, 81],
  da = [7, 10, 13, 17, 10, 16, 22, 28, 15, 26, 36, 44, 20, 36, 52, 64, 26, 48, 72, 88, 36, 64, 96, 112, 40, 72, 108, 130, 48, 88, 132, 156, 60, 110, 160, 192, 72, 130, 192, 224, 80, 150, 224, 264, 96, 176, 260, 308, 104, 198, 288, 352, 120, 216, 320, 384, 132, 240, 360, 432, 144, 280, 408, 480, 168, 308, 448, 532, 180, 338, 504, 588, 196, 364, 546, 650, 224, 416, 600, 700, 224, 442, 644, 750, 252, 476, 690, 816, 270, 504, 750, 900, 300, 560, 810, 960, 312, 588, 870, 1050, 336, 644, 952, 1110, 360, 700, 1020, 1200, 390, 728, 1050, 1260, 420, 784, 1140, 1350, 450, 812, 1200, 1440, 480, 868, 1290, 1530, 510, 924, 1350, 1620, 540, 980, 1440, 1710, 570, 1036, 1530, 1800, 570, 1064, 1590, 1890, 600, 1120, 1680, 1980, 630, 1204, 1770, 2100, 660, 1260, 1860, 2220, 720, 1316, 1950, 2310, 750, 1372, 2040, 2430];
bl.getBlocksCount = function (t, s) {
  switch (s) {
    case yn.L:
      return ca[(t - 1) * 4 + 0];
    case yn.M:
      return ca[(t - 1) * 4 + 1];
    case yn.Q:
      return ca[(t - 1) * 4 + 2];
    case yn.H:
      return ca[(t - 1) * 4 + 3];
    default:
      return
  }
};
bl.getTotalCodewordsCount = function (t, s) {
  switch (s) {
    case yn.L:
      return da[(t - 1) * 4 + 0];
    case yn.M:
      return da[(t - 1) * 4 + 1];
    case yn.Q:
      return da[(t - 1) * 4 + 2];
    case yn.H:
      return da[(t - 1) * 4 + 3];
    default:
      return
  }
};
var J1 = {},
  vl = {};
const yi = new Uint8Array(512),
  Ra = new Uint8Array(256);
(function () {
  let t = 1;
  for (let s = 0; s < 255; s++) yi[s] = t, Ra[t] = s, t <<= 1, t & 256 && (t ^= 285);
  for (let s = 255; s < 512; s++) yi[s] = yi[s - 255]
})();
vl.log = function (t) {
  if (t < 1) throw new Error("log(" + t + ")");
  return Ra[t]
};
vl.exp = function (t) {
  return yi[t]
};
vl.mul = function (t, s) {
  return t === 0 || s === 0 ? 0 : yi[Ra[t] + Ra[s]]
};
(function (e) {
  const t = vl;
  e.mul = function (o, i) {
    const r = new Uint8Array(o.length + i.length - 1);
    for (let a = 0; a < o.length; a++)
      for (let l = 0; l < i.length; l++) r[a + l] ^= t.mul(o[a], i[l]);
    return r
  }, e.mod = function (o, i) {
    let r = new Uint8Array(o);
    for (; r.length - i.length >= 0;) {
      const a = r[0];
      for (let d = 0; d < i.length; d++) r[d] ^= t.mul(i[d], a);
      let l = 0;
      for (; l < r.length && r[l] === 0;) l++;
      r = r.slice(l)
    }
    return r
  }, e.generateECPolynomial = function (o) {
    let i = new Uint8Array([1]);
    for (let r = 0; r < o; r++) i = e.mul(i, new Uint8Array([1, t.exp(r)]));
    return i
  }
})(J1);
const Z1 = J1;

function Wu(e) {
  this.genPoly = void 0, this.degree = e, this.degree && this.initialize(this.degree)
}
Wu.prototype.initialize = function (t) {
  this.degree = t, this.genPoly = Z1.generateECPolynomial(this.degree)
};
Wu.prototype.encode = function (t) {
  if (!this.genPoly) throw new Error("Encoder not initialized");
  const s = new Uint8Array(t.length + this.degree);
  s.set(t);
  const o = Z1.mod(s, this.genPoly),
    i = this.degree - o.length;
  if (i > 0) {
    const r = new Uint8Array(this.degree);
    return r.set(o, i), r
  }
  return o
};
var oM = Wu,
  X1 = {},
  xn = {},
  zu = {};
zu.isValid = function (t) {
  return !isNaN(t) && t >= 1 && t <= 40
};
var Is = {};
const Q1 = "[0-9]+",
  iM = "[A-Z $%*+\\-./:]+";
let Ri = "(?:[u3000-u303F]|[u3040-u309F]|[u30A0-u30FF]|[uFF00-uFFEF]|[u4E00-u9FAF]|[u2605-u2606]|[u2190-u2195]|u203B|[u2010u2015u2018u2019u2025u2026u201Cu201Du2225u2260]|[u0391-u0451]|[u00A7u00A8u00B1u00B4u00D7u00F7])+";
Ri = Ri.replace(/u/g, "\\u");
const rM = "(?:(?![A-Z0-9 $%*+\\-./:]|" + Ri + `)(?:.|[\r
]))+`;
Is.KANJI = new RegExp(Ri, "g");
Is.BYTE_KANJI = new RegExp("[^A-Z0-9 $%*+\\-./:]+", "g");
Is.BYTE = new RegExp(rM, "g");
Is.NUMERIC = new RegExp(Q1, "g");
Is.ALPHANUMERIC = new RegExp(iM, "g");
const aM = new RegExp("^" + Ri + "$"),
  lM = new RegExp("^" + Q1 + "$"),
  cM = new RegExp("^[A-Z0-9 $%*+\\-./:]+$");
Is.testKanji = function (t) {
  return aM.test(t)
};
Is.testNumeric = function (t) {
  return lM.test(t)
};
Is.testAlphanumeric = function (t) {
  return cM.test(t)
};
(function (e) {
  const t = zu,
    s = Is;
  e.NUMERIC = {
    id: "Numeric",
    bit: 1,
    ccBits: [10, 12, 14]
  }, e.ALPHANUMERIC = {
    id: "Alphanumeric",
    bit: 2,
    ccBits: [9, 11, 13]
  }, e.BYTE = {
    id: "Byte",
    bit: 4,
    ccBits: [8, 16, 16]
  }, e.KANJI = {
    id: "Kanji",
    bit: 8,
    ccBits: [8, 10, 12]
  }, e.MIXED = {
    bit: -1
  }, e.getCharCountIndicator = function (r, a) {
    if (!r.ccBits) throw new Error("Invalid mode: " + r);
    if (!t.isValid(a)) throw new Error("Invalid version: " + a);
    return a >= 1 && a < 10 ? r.ccBits[0] : a < 27 ? r.ccBits[1] : r.ccBits[2]
  }, e.getBestModeForData = function (r) {
    return s.testNumeric(r) ? e.NUMERIC : s.testAlphanumeric(r) ? e.ALPHANUMERIC : s.testKanji(r) ? e.KANJI : e.BYTE
  }, e.toString = function (r) {
    if (r && r.id) return r.id;
    throw new Error("Invalid mode")
  }, e.isValid = function (r) {
    return r && r.bit && r.ccBits
  };

  function o(i) {
    if (typeof i != "string") throw new Error("Param is not a string");
    switch (i.toLowerCase()) {
      case "numeric":
        return e.NUMERIC;
      case "alphanumeric":
        return e.ALPHANUMERIC;
      case "kanji":
        return e.KANJI;
      case "byte":
        return e.BYTE;
      default:
        throw new Error("Unknown mode: " + i)
    }
  }
  e.from = function (r, a) {
    if (e.isValid(r)) return r;
    try {
      return o(r)
    } catch {
      return a
    }
  }
})(xn);
(function (e) {
  const t = rs,
    s = bl,
    o = _l,
    i = xn,
    r = zu,
    a = 7973,
    l = t.getBCHDigit(a);

  function d($, C, A) {
    for (let O = 1; O <= 40; O++)
      if (C <= e.getCapacity(O, A, $)) return O
  }

  function p($, C) {
    return i.getCharCountIndicator($, C) + 4
  }

  function m($, C) {
    let A = 0;
    return $.forEach(function (O) {
      const V = p(O.mode, C);
      A += V + O.getBitsLength()
    }), A
  }

  function g($, C) {
    for (let A = 1; A <= 40; A++)
      if (m($, A) <= e.getCapacity(A, C, i.MIXED)) return A
  }
  e.from = function (C, A) {
    return r.isValid(C) ? parseInt(C, 10) : A
  }, e.getCapacity = function (C, A, O) {
    if (!r.isValid(C)) throw new Error("Invalid QR Code version");
    typeof O > "u" && (O = i.BYTE);
    const V = t.getSymbolTotalCodewords(C),
      M = s.getTotalCodewordsCount(C, A),
      B = (V - M) * 8;
    if (O === i.MIXED) return B;
    const P = B - p(O, C);
    switch (O) {
      case i.NUMERIC:
        return Math.floor(P / 10 * 3);
      case i.ALPHANUMERIC:
        return Math.floor(P / 11 * 2);
      case i.KANJI:
        return Math.floor(P / 13);
      case i.BYTE:
      default:
        return Math.floor(P / 8)
    }
  }, e.getBestVersionForData = function (C, A) {
    let O;
    const V = o.from(A, o.M);
    if (Array.isArray(C)) {
      if (C.length > 1) return g(C, V);
      if (C.length === 0) return 1;
      O = C[0]
    } else O = C;
    return d(O.mode, O.getLength(), V)
  }, e.getEncodedBits = function (C) {
    if (!r.isValid(C) || C < 7) throw new Error("Invalid QR Code version");
    let A = C << 12;
    for (; t.getBCHDigit(A) - l >= 0;) A ^= a << t.getBCHDigit(A) - l;
    return C << 12 | A
  }
})(X1);
var e0 = {};
const Jd = rs,
  t0 = 1335,
  dM = 21522,
  Kh = Jd.getBCHDigit(t0);
e0.getEncodedBits = function (t, s) {
  const o = t.bit << 3 | s;
  let i = o << 10;
  for (; Jd.getBCHDigit(i) - Kh >= 0;) i ^= t0 << Jd.getBCHDigit(i) - Kh;
  return (o << 10 | i) ^ dM
};
var s0 = {};
const uM = xn;

function Oo(e) {
  this.mode = uM.NUMERIC, this.data = e.toString()
}
Oo.getBitsLength = function (t) {
  return 10 * Math.floor(t / 3) + (t % 3 ? t % 3 * 3 + 1 : 0)
};
Oo.prototype.getLength = function () {
  return this.data.length
};
Oo.prototype.getBitsLength = function () {
  return Oo.getBitsLength(this.data.length)
};
Oo.prototype.write = function (t) {
  let s, o, i;
  for (s = 0; s + 3 <= this.data.length; s += 3) o = this.data.substr(s, 3), i = parseInt(o, 10), t.put(i, 10);
  const r = this.data.length - s;
  r > 0 && (o = this.data.substr(s), i = parseInt(o, 10), t.put(i, r * 3 + 1))
};
var fM = Oo;
const hM = xn,
  cd = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", " ", "$", "%", "*", "+", "-", ".", "/", ":"];

function Io(e) {
  this.mode = hM.ALPHANUMERIC, this.data = e
}
Io.getBitsLength = function (t) {
  return 11 * Math.floor(t / 2) + 6 * (t % 2)
};
Io.prototype.getLength = function () {
  return this.data.length
};
Io.prototype.getBitsLength = function () {
  return Io.getBitsLength(this.data.length)
};
Io.prototype.write = function (t) {
  let s;
  for (s = 0; s + 2 <= this.data.length; s += 2) {
    let o = cd.indexOf(this.data[s]) * 45;
    o += cd.indexOf(this.data[s + 1]), t.put(o, 11)
  }
  this.data.length % 2 && t.put(cd.indexOf(this.data[s]), 6)
};
var pM = Io,
  mM = function (t) {
    for (var s = [], o = t.length, i = 0; i < o; i++) {
      var r = t.charCodeAt(i);
      if (r >= 55296 && r <= 56319 && o > i + 1) {
        var a = t.charCodeAt(i + 1);
        a >= 56320 && a <= 57343 && (r = (r - 55296) * 1024 + a - 56320 + 65536, i += 1)
      }
      if (r < 128) {
        s.push(r);
        continue
      }
      if (r < 2048) {
        s.push(r >> 6 | 192), s.push(r & 63 | 128);
        continue
      }
      if (r < 55296 || r >= 57344 && r < 65536) {
        s.push(r >> 12 | 224), s.push(r >> 6 & 63 | 128), s.push(r & 63 | 128);
        continue
      }
      if (r >= 65536 && r <= 1114111) {
        s.push(r >> 18 | 240), s.push(r >> 12 & 63 | 128), s.push(r >> 6 & 63 | 128), s.push(r & 63 | 128);
        continue
      }
      s.push(239, 191, 189)
    }
    return new Uint8Array(s).buffer
  };
const gM = mM,
  _M = xn;

function Bo(e) {
  this.mode = _M.BYTE, typeof e == "string" && (e = gM(e)), this.data = new Uint8Array(e)
}
Bo.getBitsLength = function (t) {
  return t * 8
};
Bo.prototype.getLength = function () {
  return this.data.length
};
Bo.prototype.getBitsLength = function () {
  return Bo.getBitsLength(this.data.length)
};
Bo.prototype.write = function (e) {
  for (let t = 0, s = this.data.length; t < s; t++) e.put(this.data[t], 8)
};
var bM = Bo;
const vM = xn,
  yM = rs;

function Do(e) {
  this.mode = vM.KANJI, this.data = e
}
Do.getBitsLength = function (t) {
  return t * 13
};
Do.prototype.getLength = function () {
  return this.data.length
};
Do.prototype.getBitsLength = function () {
  return Do.getBitsLength(this.data.length)
};
Do.prototype.write = function (e) {
  let t;
  for (t = 0; t < this.data.length; t++) {
    let s = yM.toSJIS(this.data[t]);
    if (s >= 33088 && s <= 40956) s -= 33088;
    else if (s >= 57408 && s <= 60351) s -= 49472;
    else throw new Error("Invalid SJIS character: " + this.data[t] + `
Make sure your charset is UTF-8`);
    s = (s >>> 8 & 255) * 192 + (s & 255), e.put(s, 13)
  }
};
var wM = Do,
  n0 = {
    exports: {}
  };
(function (e) {
  var t = {
    single_source_shortest_paths: function (s, o, i) {
      var r = {},
        a = {};
      a[o] = 0;
      var l = t.PriorityQueue.make();
      l.push(o, 0);
      for (var d, p, m, g, $, C, A, O, V; !l.empty();) {
        d = l.pop(), p = d.value, g = d.cost, $ = s[p] || {};
        for (m in $) $.hasOwnProperty(m) && (C = $[m], A = g + C, O = a[m], V = typeof a[m] > "u", (V || O > A) && (a[m] = A, l.push(m, A), r[m] = p))
      }
      if (typeof i < "u" && typeof a[i] > "u") {
        var M = ["Could not find a path from ", o, " to ", i, "."].join("");
        throw new Error(M)
      }
      return r
    },
    extract_shortest_path_from_predecessor_list: function (s, o) {
      for (var i = [], r = o; r;) i.push(r), s[r], r = s[r];
      return i.reverse(), i
    },
    find_path: function (s, o, i) {
      var r = t.single_source_shortest_paths(s, o, i);
      return t.extract_shortest_path_from_predecessor_list(r, i)
    },
    PriorityQueue: {
      make: function (s) {
        var o = t.PriorityQueue,
          i = {},
          r;
        s = s || {};
        for (r in o) o.hasOwnProperty(r) && (i[r] = o[r]);
        return i.queue = [], i.sorter = s.sorter || o.default_sorter, i
      },
      default_sorter: function (s, o) {
        return s.cost - o.cost
      },
      push: function (s, o) {
        var i = {
          value: s,
          cost: o
        };
        this.queue.push(i), this.queue.sort(this.sorter)
      },
      pop: function () {
        return this.queue.shift()
      },
      empty: function () {
        return this.queue.length === 0
      }
    }
  };
  e.exports = t
})(n0);
var $M = n0.exports;
(function (e) {
  const t = xn,
    s = fM,
    o = pM,
    i = bM,
    r = wM,
    a = Is,
    l = rs,
    d = $M;

  function p(M) {
    return unescape(encodeURIComponent(M)).length
  }

  function m(M, B, P) {
    const I = [];
    let R;
    for (;
      (R = M.exec(P)) !== null;) I.push({
      data: R[0],
      index: R.index,
      mode: B,
      length: R[0].length
    });
    return I
  }

  function g(M) {
    const B = m(a.NUMERIC, t.NUMERIC, M),
      P = m(a.ALPHANUMERIC, t.ALPHANUMERIC, M);
    let I, R;
    return l.isKanjiModeEnabled() ? (I = m(a.BYTE, t.BYTE, M), R = m(a.KANJI, t.KANJI, M)) : (I = m(a.BYTE_KANJI, t.BYTE, M), R = []), B.concat(P, I, R).sort(function (j, q) {
      return j.index - q.index
    }).map(function (j) {
      return {
        data: j.data,
        mode: j.mode,
        length: j.length
      }
    })
  }

  function $(M, B) {
    switch (B) {
      case t.NUMERIC:
        return s.getBitsLength(M);
      case t.ALPHANUMERIC:
        return o.getBitsLength(M);
      case t.KANJI:
        return r.getBitsLength(M);
      case t.BYTE:
        return i.getBitsLength(M)
    }
  }

  function C(M) {
    return M.reduce(function (B, P) {
      const I = B.length - 1 >= 0 ? B[B.length - 1] : null;
      return I && I.mode === P.mode ? (B[B.length - 1].data += P.data, B) : (B.push(P), B)
    }, [])
  }

  function A(M) {
    const B = [];
    for (let P = 0; P < M.length; P++) {
      const I = M[P];
      switch (I.mode) {
        case t.NUMERIC:
          B.push([I, {
            data: I.data,
            mode: t.ALPHANUMERIC,
            length: I.length
          }, {
            data: I.data,
            mode: t.BYTE,
            length: I.length
          }]);
          break;
        case t.ALPHANUMERIC:
          B.push([I, {
            data: I.data,
            mode: t.BYTE,
            length: I.length
          }]);
          break;
        case t.KANJI:
          B.push([I, {
            data: I.data,
            mode: t.BYTE,
            length: p(I.data)
          }]);
          break;
        case t.BYTE:
          B.push([{
            data: I.data,
            mode: t.BYTE,
            length: p(I.data)
          }])
      }
    }
    return B
  }

  function O(M, B) {
    const P = {},
      I = {
        start: {}
      };
    let R = ["start"];
    for (let F = 0; F < M.length; F++) {
      const j = M[F],
        q = [];
      for (let ne = 0; ne < j.length; ne++) {
        const ee = j[ne],
          ge = "" + F + ne;
        q.push(ge), P[ge] = {
          node: ee,
          lastCount: 0
        }, I[ge] = {};
        for (let T = 0; T < R.length; T++) {
          const re = R[T];
          P[re] && P[re].node.mode === ee.mode ? (I[re][ge] = $(P[re].lastCount + ee.length, ee.mode) - $(P[re].lastCount, ee.mode), P[re].lastCount += ee.length) : (P[re] && (P[re].lastCount = ee.length), I[re][ge] = $(ee.length, ee.mode) + 4 + t.getCharCountIndicator(ee.mode, B))
        }
      }
      R = q
    }
    for (let F = 0; F < R.length; F++) I[R[F]].end = 0;
    return {
      map: I,
      table: P
    }
  }

  function V(M, B) {
    let P;
    const I = t.getBestModeForData(M);
    if (P = t.from(B, I), P !== t.BYTE && P.bit < I.bit) throw new Error('"' + M + '" cannot be encoded with mode ' + t.toString(P) + `.
 Suggested mode is: ` + t.toString(I));
    switch (P === t.KANJI && !l.isKanjiModeEnabled() && (P = t.BYTE), P) {
      case t.NUMERIC:
        return new s(M);
      case t.ALPHANUMERIC:
        return new o(M);
      case t.KANJI:
        return new r(M);
      case t.BYTE:
        return new i(M)
    }
  }
  e.fromArray = function (B) {
    return B.reduce(function (P, I) {
      return typeof I == "string" ? P.push(V(I, null)) : I.data && P.push(V(I.data, I.mode)), P
    }, [])
  }, e.fromString = function (B, P) {
    const I = g(B, l.isKanjiModeEnabled()),
      R = A(I),
      F = O(R, P),
      j = d.find_path(F.map, "start", "end"),
      q = [];
    for (let ne = 1; ne < j.length - 1; ne++) q.push(F.table[j[ne]].node);
    return e.fromArray(C(q))
  }, e.rawSplit = function (B) {
    return e.fromArray(g(B, l.isKanjiModeEnabled()))
  }
})(s0);
const yl = rs,
  dd = _l,
  CM = tM,
  kM = sM,
  AM = K1,
  xM = G1,
  Zd = Y1,
  Xd = bl,
  SM = oM,
  Na = X1,
  EM = e0,
  PM = xn,
  ud = s0;

function TM(e, t) {
  const s = e.size,
    o = xM.getPositions(t);
  for (let i = 0; i < o.length; i++) {
    const r = o[i][0],
      a = o[i][1];
    for (let l = -1; l <= 7; l++)
      if (!(r + l <= -1 || s <= r + l))
        for (let d = -1; d <= 7; d++) a + d <= -1 || s <= a + d || (l >= 0 && l <= 6 && (d === 0 || d === 6) || d >= 0 && d <= 6 && (l === 0 || l === 6) || l >= 2 && l <= 4 && d >= 2 && d <= 4 ? e.set(r + l, a + d, !0, !0) : e.set(r + l, a + d, !1, !0))
  }
}

function LM(e) {
  const t = e.size;
  for (let s = 8; s < t - 8; s++) {
    const o = s % 2 === 0;
    e.set(s, 6, o, !0), e.set(6, s, o, !0)
  }
}

function OM(e, t) {
  const s = AM.getPositions(t);
  for (let o = 0; o < s.length; o++) {
    const i = s[o][0],
      r = s[o][1];
    for (let a = -2; a <= 2; a++)
      for (let l = -2; l <= 2; l++) a === -2 || a === 2 || l === -2 || l === 2 || a === 0 && l === 0 ? e.set(i + a, r + l, !0, !0) : e.set(i + a, r + l, !1, !0)
  }
}

function IM(e, t) {
  const s = e.size,
    o = Na.getEncodedBits(t);
  let i, r, a;
  for (let l = 0; l < 18; l++) i = Math.floor(l / 3), r = l % 3 + s - 8 - 3, a = (o >> l & 1) === 1, e.set(i, r, a, !0), e.set(r, i, a, !0)
}

function fd(e, t, s) {
  const o = e.size,
    i = EM.getEncodedBits(t, s);
  let r, a;
  for (r = 0; r < 15; r++) a = (i >> r & 1) === 1, r < 6 ? e.set(r, 8, a, !0) : r < 8 ? e.set(r + 1, 8, a, !0) : e.set(o - 15 + r, 8, a, !0), r < 8 ? e.set(8, o - r - 1, a, !0) : r < 9 ? e.set(8, 15 - r - 1 + 1, a, !0) : e.set(8, 15 - r - 1, a, !0);
  e.set(o - 8, 8, 1, !0)
}

function BM(e, t) {
  const s = e.size;
  let o = -1,
    i = s - 1,
    r = 7,
    a = 0;
  for (let l = s - 1; l > 0; l -= 2)
    for (l === 6 && l--;;) {
      for (let d = 0; d < 2; d++)
        if (!e.isReserved(i, l - d)) {
          let p = !1;
          a < t.length && (p = (t[a] >>> r & 1) === 1), e.set(i, l - d, p), r--, r === -1 && (a++, r = 7)
        } if (i += o, i < 0 || s <= i) {
        i -= o, o = -o;
        break
      }
    }
}

function DM(e, t, s) {
  const o = new CM;
  s.forEach(function (d) {
    o.put(d.mode.bit, 4), o.put(d.getLength(), PM.getCharCountIndicator(d.mode, e)), d.write(o)
  });
  const i = yl.getSymbolTotalCodewords(e),
    r = Xd.getTotalCodewordsCount(e, t),
    a = (i - r) * 8;
  for (o.getLengthInBits() + 4 <= a && o.put(0, 4); o.getLengthInBits() % 8 !== 0;) o.putBit(0);
  const l = (a - o.getLengthInBits()) / 8;
  for (let d = 0; d < l; d++) o.put(d % 2 ? 17 : 236, 8);
  return MM(o, e, t)
}

function MM(e, t, s) {
  const o = yl.getSymbolTotalCodewords(t),
    i = Xd.getTotalCodewordsCount(t, s),
    r = o - i,
    a = Xd.getBlocksCount(t, s),
    l = o % a,
    d = a - l,
    p = Math.floor(o / a),
    m = Math.floor(r / a),
    g = m + 1,
    $ = p - m,
    C = new SM($);
  let A = 0;
  const O = new Array(a),
    V = new Array(a);
  let M = 0;
  const B = new Uint8Array(e.buffer);
  for (let j = 0; j < a; j++) {
    const q = j < d ? m : g;
    O[j] = B.slice(A, A + q), V[j] = C.encode(O[j]), A += q, M = Math.max(M, q)
  }
  const P = new Uint8Array(o);
  let I = 0,
    R, F;
  for (R = 0; R < M; R++)
    for (F = 0; F < a; F++) R < O[F].length && (P[I++] = O[F][R]);
  for (R = 0; R < $; R++)
    for (F = 0; F < a; F++) P[I++] = V[F][R];
  return P
}

function RM(e, t, s, o) {
  let i;
  if (Array.isArray(e)) i = ud.fromArray(e);
  else if (typeof e == "string") {
    let p = t;
    if (!p) {
      const m = ud.rawSplit(e);
      p = Na.getBestVersionForData(m, s)
    }
    i = ud.fromString(e, p || 40)
  } else throw new Error("Invalid data");
  const r = Na.getBestVersionForData(i, s);
  if (!r) throw new Error("The amount of data is too big to be stored in a QR Code");
  if (!t) t = r;
  else if (t < r) throw new Error(`
The chosen QR Code version cannot contain this amount of data.
Minimum version required to store current data is: ` + r + `.
`);
  const a = DM(t, s, i),
    l = yl.getSymbolSize(t),
    d = new kM(l);
  return TM(d, t), LM(d), OM(d, t), fd(d, s, 0), t >= 7 && IM(d, t), BM(d, a), isNaN(o) && (o = Zd.getBestMask(d, fd.bind(null, d, s))), Zd.applyMask(o, d), fd(d, s, o), {
    modules: d,
    version: t,
    errorCorrectionLevel: s,
    maskPattern: o,
    segments: i
  }
}
W1.create = function (t, s) {
  if (typeof t > "u" || t === "") throw new Error("No input text");
  let o = dd.M,
    i, r;
  return typeof s < "u" && (o = dd.from(s.errorCorrectionLevel, dd.M), i = Na.from(s.version), r = Zd.from(s.maskPattern), s.toSJISFunc && yl.setToSJISFunction(s.toSJISFunc)), RM(t, i, o, r)
};
var o0 = {},
  Ku = {};
(function (e) {
  function t(s) {
    if (typeof s == "number" && (s = s.toString()), typeof s != "string") throw new Error("Color should be defined as hex string");
    let o = s.slice().replace("#", "").split("");
    if (o.length < 3 || o.length === 5 || o.length > 8) throw new Error("Invalid hex color: " + s);
    (o.length === 3 || o.length === 4) && (o = Array.prototype.concat.apply([], o.map(function (r) {
      return [r, r]
    }))), o.length === 6 && o.push("F", "F");
    const i = parseInt(o.join(""), 16);
    return {
      r: i >> 24 & 255,
      g: i >> 16 & 255,
      b: i >> 8 & 255,
      a: i & 255,
      hex: "#" + o.slice(0, 6).join("")
    }
  }
  e.getOptions = function (o) {
    o || (o = {}), o.color || (o.color = {});
    const i = typeof o.margin > "u" || o.margin === null || o.margin < 0 ? 4 : o.margin,
      r = o.width && o.width >= 21 ? o.width : void 0,
      a = o.scale || 4;
    return {
      width: r,
      scale: r ? 4 : a,
      margin: i,
      color: {
        dark: t(o.color.dark || "#000000ff"),
        light: t(o.color.light || "#ffffffff")
      },
      type: o.type,
      rendererOpts: o.rendererOpts || {}
    }
  }, e.getScale = function (o, i) {
    return i.width && i.width >= o + i.margin * 2 ? i.width / (o + i.margin * 2) : i.scale
  }, e.getImageWidth = function (o, i) {
    const r = e.getScale(o, i);
    return Math.floor((o + i.margin * 2) * r)
  }, e.qrToImageData = function (o, i, r) {
    const a = i.modules.size,
      l = i.modules.data,
      d = e.getScale(a, r),
      p = Math.floor((a + r.margin * 2) * d),
      m = r.margin * d,
      g = [r.color.light, r.color.dark];
    for (let $ = 0; $ < p; $++)
      for (let C = 0; C < p; C++) {
        let A = ($ * p + C) * 4,
          O = r.color.light;
        if ($ >= m && C >= m && $ < p - m && C < p - m) {
          const V = Math.floor(($ - m) / d),
            M = Math.floor((C - m) / d);
          O = g[l[V * a + M] ? 1 : 0]
        }
        o[A++] = O.r, o[A++] = O.g, o[A++] = O.b, o[A] = O.a
      }
  }
})(Ku);
(function (e) {
  const t = Ku;

  function s(i, r, a) {
    i.clearRect(0, 0, r.width, r.height), r.style || (r.style = {}), r.height = a, r.width = a, r.style.height = a + "px", r.style.width = a + "px"
  }

  function o() {
    try {
      return document.createElement("canvas")
    } catch {
      throw new Error("You need to specify a canvas element")
    }
  }
  e.render = function (r, a, l) {
    let d = l,
      p = a;
    typeof d > "u" && (!a || !a.getContext) && (d = a, a = void 0), a || (p = o()), d = t.getOptions(d);
    const m = t.getImageWidth(r.modules.size, d),
      g = p.getContext("2d"),
      $ = g.createImageData(m, m);
    return t.qrToImageData($.data, r, d), s(g, p, m), g.putImageData($, 0, 0), p
  }, e.renderToDataURL = function (r, a, l) {
    let d = l;
    typeof d > "u" && (!a || !a.getContext) && (d = a, a = void 0), d || (d = {});
    const p = e.render(r, a, d),
      m = d.type || "image/png",
      g = d.rendererOpts || {};
    return p.toDataURL(m, g.quality)
  }
})(o0);
var i0 = {};
const NM = Ku;

function Gh(e, t) {
  const s = e.a / 255,
    o = t + '="' + e.hex + '"';
  return s < 1 ? o + " " + t + '-opacity="' + s.toFixed(2).slice(1) + '"' : o
}

function hd(e, t, s) {
  let o = e + t;
  return typeof s < "u" && (o += " " + s), o
}

function FM(e, t, s) {
  let o = "",
    i = 0,
    r = !1,
    a = 0;
  for (let l = 0; l < e.length; l++) {
    const d = Math.floor(l % t),
      p = Math.floor(l / t);
    !d && !r && (r = !0), e[l] ? (a++, l > 0 && d > 0 && e[l - 1] || (o += r ? hd("M", d + s, .5 + p + s) : hd("m", i, 0), i = 0, r = !1), d + 1 < t && e[l + 1] || (o += hd("h", a), a = 0)) : i++
  }
  return o
}
i0.render = function (t, s, o) {
  const i = NM.getOptions(s),
    r = t.modules.size,
    a = t.modules.data,
    l = r + i.margin * 2,
    d = i.color.light.a ? "<path " + Gh(i.color.light, "fill") + ' d="M0 0h' + l + "v" + l + 'H0z"/>' : "",
    p = "<path " + Gh(i.color.dark, "stroke") + ' d="' + FM(a, r, i.margin) + '"/>',
    m = 'viewBox="0 0 ' + l + " " + l + '"',
    $ = '<svg xmlns="http://www.w3.org/2000/svg" ' + (i.width ? 'width="' + i.width + '" height="' + i.width + '" ' : "") + m + ' shape-rendering="crispEdges">' + d + p + `</svg>
`;
  return typeof o == "function" && o(null, $), $
};
const UM = QD,
  Qd = W1,
  r0 = o0,
  VM = i0;

function Gu(e, t, s, o, i) {
  const r = [].slice.call(arguments, 1),
    a = r.length,
    l = typeof r[a - 1] == "function";
  if (!l && !UM()) throw new Error("Callback required as last argument");
  if (l) {
    if (a < 2) throw new Error("Too few arguments provided");
    a === 2 ? (i = s, s = t, t = o = void 0) : a === 3 && (t.getContext && typeof i > "u" ? (i = o, o = void 0) : (i = o, o = s, s = t, t = void 0))
  } else {
    if (a < 1) throw new Error("Too few arguments provided");
    return a === 1 ? (s = t, t = o = void 0) : a === 2 && !t.getContext && (o = s, s = t, t = void 0), new Promise(function (d, p) {
      try {
        const m = Qd.create(s, o);
        d(e(m, t, o))
      } catch (m) {
        p(m)
      }
    })
  }
  try {
    const d = Qd.create(s, o);
    i(null, e(d, t, o))
  } catch (d) {
    i(d)
  }
}
Yi.create = Qd.create;
Yi.toCanvas = Gu.bind(null, r0.render);
Yi.toDataURL = Gu.bind(null, r0.renderToDataURL);
Yi.toString = Gu.bind(null, function (e, t, s) {
  return VM.render(e, s)
});
var jM = Object.defineProperty,
  HM = Object.defineProperties,
  qM = Object.getOwnPropertyDescriptors,
  Fa = Object.getOwnPropertySymbols,
  a0 = Object.prototype.hasOwnProperty,
  l0 = Object.prototype.propertyIsEnumerable,
  Yh = (e, t, s) => t in e ? jM(e, t, {
    enumerable: !0,
    configurable: !0,
    writable: !0,
    value: s
  }) : e[t] = s,
  WM = (e, t) => {
    for (var s in t || (t = {})) a0.call(t, s) && Yh(e, s, t[s]);
    if (Fa)
      for (var s of Fa(t)) l0.call(t, s) && Yh(e, s, t[s]);
    return e
  },
  zM = (e, t) => HM(e, qM(t)),
  KM = (e, t) => {
    var s = {};
    for (var o in e) a0.call(e, o) && t.indexOf(o) < 0 && (s[o] = e[o]);
    if (e != null && Fa)
      for (var o of Fa(e)) t.indexOf(o) < 0 && l0.call(e, o) && (s[o] = e[o]);
    return s
  };
const GM = ["low", "medium", "quartile", "high", "L", "M", "Q", "H"],
  YM = [0, 1, 2, 3, 4, 5, 6, 7],
  JM = ["alphanumeric", "numeric", "kanji", "byte"],
  ZM = ["image/png", "image/jpeg", "image/webp"],
  XM = 40;
var QM = Dt({
  props: {
    version: {
      type: Number,
      validator: e => e === Number.parseInt(String(e), 10) && e >= 1 && e <= XM
    },
    errorCorrectionLevel: {
      type: String,
      validator: e => GM.includes(e)
    },
    maskPattern: {
      type: Number,
      validator: e => YM.includes(e)
    },
    toSJISFunc: Function,
    margin: Number,
    scale: Number,
    width: Number,
    color: {
      type: Object,
      validator: e => ["dark", "light"].every(t => ["string", "undefined"].includes(typeof e[t])),
      required: !0
    },
    type: {
      type: String,
      validator: e => ZM.includes(e),
      required: !0
    },
    quality: {
      type: Number,
      validator: e => e === Number.parseFloat(String(e)) && e >= 0 && e <= 1,
      required: !1
    },
    value: {
      type: [String, Array],
      required: !0,
      validator(e) {
        return typeof e == "string" ? !0 : e.every(t => typeof t.data == "string" && "mode" in t && t.mode && JM.includes(t.mode))
      }
    }
  },
  setup(e, {
    attrs: t,
    emit: s
  }) {
    const o = he();
    return Ks(e, () => {
      const r = e,
        {
          quality: a,
          value: l
        } = r,
        d = KM(r, ["quality", "value"]);
      Yi.toDataURL(l, Object.assign(d, a == null || {
        renderOptions: {
          quality: a
        }
      })).then(p => {
        o.value = p, s("change", p)
      }).catch(p => s("error", p))
    }, {
      immediate: !0
    }), () => qi("img", zM(WM({}, t), {
      src: o.value
    }))
  }
});
const eR = {
    props: ["liveAccounts"],
    data() {
      return {
        isLoading: !1,
        isSubmitDisabled: !1,
        depositMethod: 2,
        selectedAccount: null,
        currency_list: null,
        b_currency_list: null,
        liveMTAccounts: this.liveAccounts,
        selectedCurrency: null,
        hasUSDTResponse: !1,
        responseData: null,
        toUSDValue: 0,
        currencyAmount: 0,
        qrValue: null,
        preview: [null],
        fileData: [null],
        errorMessage: [""],
        errors: {
          selectedAccount: "",
          toUSDValue: ""
        }
      }
    },
    watch: {
      currencyAmount(e, t) {
        this.handleAmountChange(e)
      },
      selectedAccount(e, t) {
        this.handleSelectedAccountChange()
      },
      depositMethod(e, t) {
        this.handleDepositMethodChange(e)
      }
    },
    components: {
      VueQrcode: QM
    },
    mounted() {
      this.loadDataFromLocalStorage(), this.fetchCurrencies()
    },
    methods: {
      previewFile(e, t) {
        const s = e.target.files[0];
        if (!s) return;
        if (this.preview[t] = null, this.errorMessage[t] = "", !["application/pdf", "image/png", "image/jpeg"].includes(s.type)) {
          this.errorMessage[t] = "Invalid file type. Only PDF, PNG, and JPEG are allowed.";
          return
        }
        if (s.size > 2 * 1024 * 1024) {
          this.errorMessage[t] = "File is too large. The size limit is 2MB.";
          return
        }
        this.fileData[t] = s, this.preview[t] = URL.createObjectURL(s)
      },
      loadDataFromLocalStorage() {
        const e = me.getDataWithExpiration("currency_list");
        e && (this.currency_list = e);
        const t = me.getDataWithExpiration("liveAccounts");
        t && (this.liveMTAccounts = t, this.selectedAccount = t[0])
      },
      async fetchCurrencies() {
        ye.get(`${Ge("API_BASE_URL")}/fund/deposit/currency-list`, {
          headers: {
            Authorization: `Bearer ${$e.get("authToken")}`
          }
        }).then(e => {
          this.currency_list = e.data.result.currency, this.b_currency_list = e.data.result.currency, me.setDataWithTimestamp("currency_list", e.data.result.currency, 30), this.selectedCurrency = e.data.result.currency[0], this.handleAmountChange()
        }).catch(e => {
          var t;
          Swal.fire({
            icon: "error",
            title: (t = e.response) == null ? void 0 : t.data.message
          }).then(s => {
            s.isConfirmed && window.location.reload()
          })
        })
      },
      handleCurrencyChange() {
        this.currencyAmount = 0, this.handleAmountChange()
      },
      handleAmountChange(e) {
        this.errors.toUSDValue = "", e ? this.toUSDValue = e * this.selectedCurrency.to_usd : this.toUSDValue = 0
      },
      handleDepositMethodChange(e) {
        this.currency_list.value = this.b_currency_list, e === 3 && (this.selectedCurrency = this.currency_list[8], this.handleCurrencyChange())
      },
      handleSelectedAccountChange() {
        this.errors.selectedAccount = ""
      },
      handleSubmitClick() {
        this.selectedAccount || (this.errors.selectedAccount = "Please Select an Account !"), this.toUSDValue < 10 && (this.errors.toUSDValue = "Minimum deposit amount is 10 USD !"), !this.errors.selectedAccount && !this.errors.toUSDValue && (this.createDepositTicket(), this.isLoading = !0)
      },
      async createDepositTicket() {
        try {
          const e = localStorage.getItem("auth_id"),
            t = $e.get("authToken"),
            s = new FormData;
          s.append("mt_id", this.selectedAccount.mt_id), s.append("trans_amount", this.currencyAmount), s.append("trans_usd", this.toUSDValue), s.append("payment_method", this.depositMethod), s.append("payment_currency", this.selectedCurrency.id), s.append("image", this.fileData[0]);
          const o = await ye.post(this.API_BASE_URL + "/fund/deposit/deposit/create", s, {
            headers: {
              "Content-Type": "multipart/form-data",
              Authorization: `Bearer ${t}`
            }
          });
          o && (this.isLoading = !1, this.depositMethod == 3 ? (console.log("entered"), this.responseData = o.data.result.payment_data, this.qrValue = o.data.result.payment_data.pay_address, this.hasUSDTResponse = !0, console.log("alldome")) : Swal.fire({
            icon: "success",
            title: o == null ? void 0 : o.data.message
          }).then(i => {
            i.isConfirmed && window.location.reload()
          }))
        } catch (e) {
          console.log(e), Swal.fire({
            icon: "error",
            title: e.response
          }).then(t => {
            t.isConfirmed && window.location.reload()
          })
        }
      }
    },
    computed: {
      isPDF() {
        return e => this.fileData[e] && this.fileData[e].type === "application/pdf"
      },
      isImage() {
        return e => this.fileData[e] && this.fileData[e].type.includes("image")
      }
    }
  },
  tR = {
    class: "row"
  },
  sR = {
    class: "col-12"
  },
  nR = {
    class: "row"
  },
  oR = {
    class: "col-xl-8"
  },
  iR = {
    class: "card"
  },
  rR = n("div", {
    class: "card-body border-bottom"
  }, [n("h6", null, "CREATE DEPOSIT TICKET")], -1),
  aR = {
    class: "card-body"
  },
  lR = n("div", {
    class: "divider my-4"
  }, [n("span", null, "SELECT MT5 ACCOUNT")], -1),
  cR = {
    class: "row g-1"
  },
  dR = {
    class: "address-check border rounded"
  },
  uR = {
    class: "form-check paycard"
  },
  fR = ["id", "value", "name"],
  hR = ["for"],
  pR = {
    class: "p-1 my-1"
  },
  mR = {
    class: "row"
  },
  gR = {
    class: "col-6 mt-1"
  },
  _R = {
    key: 0,
    class: "h5 mb-0 d-block f-w-500 pb-0"
  },
  bR = n("img", {
    src: Mt,
    alt: "user-image",
    class: "wid-25 me-1 ms-1"
  }, null, -1),
  vR = {
    key: 1,
    class: "h4 mb-0 d-block pb-0"
  },
  yR = n("img", {
    src: ps,
    alt: "user-image",
    class: "user-avtar wid-40"
  }, null, -1),
  wR = {
    class: "col-6 text-end mb-0 pb-0 pe-3"
  },
  $R = {
    class: "h5 mb-0 d-block f-w-500"
  },
  CR = n("span", {
    class: "text-muted mb-0 f-10"
  }, "Current Balance", -1),
  kR = n("div", {
    class: "divider my-4"
  }, [n("span", null, "SELECT PAYMENT METHOD")], -1),
  AR = {
    class: "row g-1"
  },
  xR = {
    class: "col-md-3 col-lg-4 col-xl-4"
  },
  SR = {
    class: "address-check border rounded"
  },
  ER = {
    class: "form-check"
  },
  PR = Ae('<label class="form-check-label d-block" for="payopn-check-1"><span class="card-body p-2 d-block"><span class="h6 f-w-500 mb-1 d-block">BANK DEPOSIT</span><span class="d-flex align-items-center"><span class="f-10 badge bg-light-success me-3">DEPOSIT TO BANK ACCOUNT</span><img src="' + j1 + '" alt="img" class="img-fluid ms-1 wid-25"></span></span></label>', 1),
  TR = {
    class: "col-md-3 col-lg-4 col-xl-4"
  },
  LR = {
    class: "address-check border rounded"
  },
  OR = {
    class: "form-check"
  },
  IR = Ae('<label class="form-check-label d-block" for="option_usdt"><span class="card-body p-2 d-block"><span class="h6 f-w-500 mb-1 d-block">USDT DEPOSIT</span><span class="d-flex align-items-center"><span class="f-10 badge bg-light-success me-3">USDT DEPOSIT</span><img src="' + H1 + '" alt="img" class="img-fluid ms-1 wid-25"></span></span></label>', 1),
  BR = {
    class: "col-md-3 col-lg-4 col-xl-4"
  },
  DR = {
    class: "address-check border rounded"
  },
  MR = {
    class: "form-check"
  },
  RR = Ae('<label class="form-check-label d-block" for="payopn-check-3"><span class="card-body p-2 d-block"><span class="d-flex align-items-center"><span><span class="h6 f-w-500 mb-1 d-block">OTHERS</span><span class="f-10 text-muted">Other Payment Options</span></span></span></span></label>', 1),
  NR = n("div", {
    class: "divider my-4"
  }, [n("span", null, "DEPOSIT DETAILS")], -1),
  FR = {
    class: "row"
  },
  UR = {
    class: "col-12 mt-2"
  },
  VR = {
    class: "form-group row"
  },
  jR = n("label", {
    class: "col-lg-4 col-form-label"
  }, [J("DEPOSIT CURRENCY :"), n("small", {
    class: "text-muted d-block"
  }, " Please select the currency you wish to use for the payment ")], -1),
  HR = {
    class: "col-lg-8"
  },
  qR = ["disabled"],
  WR = ["value", "selected"],
  zR = {
    key: 1,
    class: "py-3"
  },
  KR = {
    class: "form-group row"
  },
  GR = n("label", {
    class: "col-lg-4 col-form-label"
  }, [J("ENTER AMOUNT :"), n("small", {
    class: "text-muted d-block"
  }, " Please enter the amount to be deposited in selected currency")], -1),
  YR = {
    class: "col-lg-8"
  },
  JR = {
    class: "input-group mb-3"
  },
  ZR = {
    class: "input-group-text"
  },
  XR = {
    class: "form-group row"
  },
  QR = n("label", {
    class: "col-lg-4 col-form-label"
  }, [J("AMOUNT IN USD :"), n("small", {
    class: "text-muted d-block"
  }, " Deposit amount in USD ")], -1),
  eN = {
    class: "col-lg-8"
  },
  tN = {
    class: "input-group mb-3"
  },
  sN = n("span", {
    class: "input-group-text"
  }, "USD", -1),
  nN = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  oN = {
    key: 0,
    class: "form-group row"
  },
  iN = n("label", {
    class: "col-lg-4 col-form-label"
  }, [J("DEPOSIT PROOF :"), n("small", {
    class: "text-muted d-block"
  }, " Upload proof of your transaction ")], -1),
  rN = {
    class: "col-lg-8"
  },
  aN = {
    key: 0,
    class: "preview mt-2"
  },
  lN = ["src"],
  cN = ["src"],
  dN = {
    key: 1,
    class: "alert alert-danger mt-2"
  },
  uN = {
    class: ""
  },
  fN = {
    class: "row"
  },
  hN = n("div", {
    class: "col-lg-4"
  }, null, -1),
  pN = {
    class: "col-lg-8"
  },
  mN = {
    key: 0,
    class: "row"
  },
  gN = {
    class: "col-lg-4"
  },
  _N = {
    class: "card"
  },
  bN = {
    class: "card-body position-relative"
  },
  vN = n("div", {
    class: "position-absolute top-0 pt-3"
  }, [n("span", {
    class: "badge bg-primary"
  }, "USDT TRC20 DEPOSIT")], -1),
  yN = {
    class: "text-center mt-3"
  },
  wN = n("hr", {
    class: "my-3 border border-secondary-subtle"
  }, null, -1),
  $N = {
    class: "d-inline-flex align-items-center justify-content-between w-100 mb-3"
  },
  CN = n("p", {
    class: "mb-0"
  }, "PY.ID.", -1),
  kN = {
    key: 0,
    class: "mb-0"
  },
  AN = {
    class: "d-inline-flex align-items-center justify-content-between w-100 mb-3"
  },
  xN = n("p", {
    class: "mb-0"
  }, "AMOUNT ", -1),
  SN = {
    key: 0,
    class: "mb-0"
  },
  EN = n("div", {
    class: "d-inline-flex align-items-center justify-content-between w-100 mb-3"
  }, [n("p", {
    class: "mb-0"
  }, "CURRENCY"), n("p", {
    class: "mb-0"
  }, "USDT TRC20")], -1),
  PN = {
    class: "col-lg-8"
  },
  TN = {
    class: "card"
  },
  LN = n("div", {
    class: "card-header"
  }, [n("h5", null, "SCAN TO PAY")], -1),
  ON = {
    key: 0,
    class: "card-body"
  },
  IN = {
    class: "card"
  },
  BN = n("div", {
    class: "card-header"
  }, [n("h5", null, "Pay Address")], -1),
  DN = {
    class: "card-body"
  },
  MN = {
    key: 0,
    class: "mb-0"
  },
  RN = {
    key: 1,
    class: "row g-1"
  },
  NN = ["disabled"],
  FN = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  UN = Ae('<div class="d-flex justify-content-end"><div class="d-flex align-items-center text-muted my-4"><div class="avtar avtar-s bg-light-primary flex-shrink-0 me-2"><i class="material-icons-two-tone text-primary f-20">security</i></div><span class="text-muted text-sm w-100"> Safe &amp; Secure Payment</span></div></div>', 1),
  VN = {
    class: "col-xl-4"
  },
  jN = Ae('<div class="card coupon-card bg-primary"><div class="card-body"><div class="row"><div class="col-8 d-flex flex-column align-items-start justify-content-center"><h3 class="text-white f-w-500">Fuel Your Trading Journey</h3><span class="f-16 py-2 text-white">Deposit now and unlock the gateway to global markets.</span></div><div class="col-4 text-end"><img src="' + q1 + '" alt="img" class="img-fluid wid-110"></div></div></div></div>', 1),
  HN = {
    class: "card"
  },
  qN = n("div", {
    class: "card-header"
  }, [n("h5", null, "MT5 ACCOUNTS SUMMARY")], -1),
  WN = {
    class: "card-body p-0"
  },
  zN = {
    class: "list-group list-group-flush"
  },
  KN = {
    class: "media align-items-start"
  },
  GN = {
    key: 0,
    class: "h4 mb-0 d-block f-w-500 pb-0"
  },
  YN = n("img", {
    src: Mt,
    alt: "user-image",
    class: "wid-25 me-1 ms-1"
  }, null, -1),
  JN = [YN],
  ZN = {
    key: 1,
    class: "h4 mb-0 d-block pb-0"
  },
  XN = n("img", {
    src: ps,
    alt: "user-image",
    class: "user-avtar wid-40"
  }, null, -1),
  QN = [XN],
  eF = {
    class: "media-body mx-2"
  },
  tF = {
    class: "mb-1"
  },
  sF = {
    key: 0,
    class: "h4 mb-0 d-block f-w-500 pb-0"
  },
  nF = {
    key: 1,
    class: "h4 mb-0 d-block pb-0"
  },
  oF = {
    class: "text-sm mb-2"
  },
  iF = n("span", {
    class: "text-muted"
  }, "ACCOUNT CATEGORY :", -1),
  rF = {
    class: "border-top border-dashed"
  },
  aF = {
    class: "mb-1 mt-2"
  },
  lF = n("span", {
    class: "text-muted"
  }, "LEVERAGE :", -1),
  cF = n("span", {
    class: "text-muted"
  }, "| CREDIT :", -1),
  dF = n("span", {
    class: "text-muted"
  }, "| EQUITY :", -1),
  uF = {
    href: "#",
    class: "flex-shrink-0"
  },
  fF = {
    class: "f-w-500"
  },
  hF = n("p", {
    class: "text-muted text-sm mb-2 text-end"
  }, "Balance", -1),
  pF = {
    class: "list-group-item"
  },
  mF = {
    class: "float-end"
  },
  gF = {
    class: "mb-0 fw-medium"
  },
  _F = n("span", {
    class: "text-muted"
  }, "TOTAL CREDIT", -1),
  bF = {
    class: "list-group-item"
  },
  vF = {
    class: "float-end"
  },
  yF = {
    class: "mb-0 fw-medium"
  },
  wF = n("span", {
    class: "text-muted"
  }, "TOTAL EQUITY", -1),
  $F = {
    class: "card"
  },
  CF = {
    class: "card-body py-2"
  },
  kF = {
    class: "list-group list-group-flush"
  },
  AF = {
    class: "list-group-item px-0"
  },
  xF = {
    class: "float-end"
  },
  SF = {
    class: "mb-0 fw-medium"
  },
  EF = n("h5", {
    class: "mb-0 d-inline-block"
  }, "TOTAL BALANCE", -1);

function PF(e, t, s, o, i, r) {
  var d, p, m;
  const a = ke("SmallLoadingSpinner"),
    l = ke("vue-qrcode");
  return _(), w("div", tR, [n("div", sR, [n("div", nR, [n("div", oR, [n("div", iR, [rR, n("div", aR, [lR, n("div", cR, [s.liveAccounts ? (_(!0), w(Te, {
    key: 0
  }, Je(i.liveMTAccounts, g => (_(), w("div", {
    class: "col-md-3 col-lg-4 col-xl-4",
    key: g.mt_id
  }, [n("div", dR, [n("div", uR, [be(n("input", {
    id: g.mt_id,
    "onUpdate:modelValue": t[0] || (t[0] = $ => i.selectedAccount = $),
    value: g,
    type: "radio",
    name: g.mt_id,
    class: "form-check-input input-primary"
  }, null, 8, fR), [
    [kt, i.selectedAccount]
  ]), n("label", {
    class: "form-check-label d-block",
    for: g.mt_id
  }, [n("div", pR, [n("span", mR, [n("span", gR, [g.is_wallet != 1 ? (_(), w("span", _R, [bR, J(" " + L(g.mt_id), 1)])) : (_(), w("span", vR, [yR, J(" Wallet ")]))]), n("span", wR, [n("span", $R, L(this.$formatCurrency(g.balance)), 1), CR])])])], 8, hR)])])]))), 128)) : D("", !0)]), kR, n("div", AR, [n("div", xR, [n("div", SR, [n("div", ER, [be(n("input", {
    type: "radio",
    "onUpdate:modelValue": t[1] || (t[1] = g => i.depositMethod = g),
    value: 2,
    name: "payoptradio1",
    class: "form-check-input input-primary",
    id: "payopn-check-1"
  }, null, 512), [
    [kt, i.depositMethod]
  ]), PR])])]), n("div", TR, [n("div", LR, [n("div", OR, [be(n("input", {
    type: "radio",
    "onUpdate:modelValue": t[2] || (t[2] = g => i.depositMethod = g),
    value: 3,
    id: "option_usdt",
    class: "form-check-input input-primary"
  }, null, 512), [
    [kt, i.depositMethod]
  ]), IR])])]), n("div", BR, [n("div", DR, [n("div", MR, [be(n("input", {
    type: "radio",
    "onUpdate:modelValue": t[3] || (t[3] = g => i.depositMethod = g),
    value: 1,
    name: "payoptradio3",
    class: "form-check-input input-primary",
    id: "payopn-check-3"
  }, null, 512), [
    [kt, i.depositMethod]
  ]), RR])])])]), NR, n("div", FR, [n("div", UR, [n("div", VR, [jR, n("div", HR, [i.currency_list ? be((_(), w("select", {
    key: 0,
    class: "form-select",
    id: "exampleFormControlSelect1",
    "onUpdate:modelValue": t[4] || (t[4] = g => i.selectedCurrency = g),
    onChange: t[5] || (t[5] = (...g) => r.handleCurrencyChange && r.handleCurrencyChange(...g)),
    disabled: i.depositMethod === 3 || i.depositMethod === 4 || i.depositMethod === 5 || i.depositMethod === 7
  }, [(_(!0), w(Te, null, Je(i.currency_list, (g, $) => (_(), w("option", {
    key: g.id,
    value: g,
    selected: $ === 0
  }, L(g.currency), 9, WR))), 128))], 40, qR)), [
    [Xn, i.selectedCurrency]
  ]) : (_(), w("div", zR, [ie(a)]))])]), n("div", KR, [GR, n("div", YR, [n("div", JR, [n("span", ZR, L(i.selectedCurrency ? i.selectedCurrency.currency : " "), 1), be(n("input", {
    type: "number",
    class: "form-control",
    "aria-label": "Amount",
    "onUpdate:modelValue": t[6] || (t[6] = g => i.currencyAmount = g)
  }, null, 512), [
    [Re, i.currencyAmount]
  ])])])]), n("div", XR, [QR, n("div", eN, [n("div", tN, [sN, be(n("input", {
    type: "text",
    class: "form-control",
    "aria-label": "Amount",
    "onUpdate:modelValue": t[7] || (t[7] = g => i.toUSDValue = g),
    disabled: ""
  }, null, 512), [
    [Re, i.toUSDValue]
  ]), i.errors.toUSDValue ? (_(), w("div", nN, L(i.errors.toUSDValue), 1)) : D("", !0)])])]), i.depositMethod === 2 || i.depositMethod === 1 ? (_(), w("div", oN, [iN, n("div", rN, [n("input", {
    type: "file",
    onChange: t[8] || (t[8] = g => r.previewFile(g, 0)),
    accept: "application/pdf,image/png,image/jpeg",
    class: "form-control"
  }, null, 32), i.preview[0] ? (_(), w("div", aN, [r.isImage(0) ? (_(), w("img", {
    key: 0,
    src: i.preview[0],
    alt: "Preview Image",
    class: "img-fluid",
    style: {
      "max-height": "220px",
      width: "auto",
      "max-width": "100%"
    }
  }, null, 8, lN)) : D("", !0), r.isPDF(0) ? (_(), w("iframe", {
    key: 1,
    src: i.preview[0],
    class: "file-preview w-100",
    style: {
      height: "30vh"
    }
  }, null, 8, cN)) : D("", !0)])) : D("", !0), i.errorMessage[0] ? (_(), w("div", dN, L(i.errorMessage[0]), 1)) : D("", !0)])])) : D("", !0), n("div", uN, [n("div", fN, [hN, n("div", pN, [i.hasUSDTResponse ? (_(), w("div", mN, [n("div", gN, [n("div", _N, [n("div", bN, [vN, n("div", yN, [wN, n("div", $N, [CN, i.responseData ? (_(), w("p", kN, L(i.responseData.payment_id), 1)) : D("", !0)]), n("div", AN, [xN, i.responseData ? (_(), w("p", SN, L(this.$formatCurrency(i.responseData.pay_amount)), 1)) : D("", !0)]), EN])])])]), n("div", PN, [n("div", TN, [LN, i.responseData ? (_(), w("div", ON, [ie(l, {
    value: i.qrValue
  }, null, 8, ["value"])])) : D("", !0)]), n("div", IN, [BN, n("div", DN, [i.responseData ? (_(), w("p", MN, L(i.responseData.pay_address), 1)) : D("", !0)])])])])) : (_(), w("div", RN, [n("button", {
    class: "btn btn-primary col-12",
    disabled: i.isLoading,
    type: "button",
    onClick: t[9] || (t[9] = (...g) => r.handleSubmitClick && r.handleSubmitClick(...g))
  }, [i.isLoading ? (_(), w("span", FN)) : D("", !0), J(" " + L(i.isLoading ? "Submitting..." : "PROCESS PAYMENT"), 1)], 8, NN)]))])])]), UN])])])])]), n("div", VN, [jN, n("div", HN, [qN, n("div", WN, [n("ul", zN, [s.liveAccounts ? (_(!0), w(Te, {
    key: 0
  }, Je(i.liveMTAccounts, g => (_(), w("li", {
    key: g.mt_id,
    class: "list-group-item"
  }, [n("div", KN, [g.is_wallet != 1 ? (_(), w("span", GN, JN)) : (_(), w("span", ZN, QN)), n("div", eF, [n("h5", tF, [g.is_wallet != 1 ? (_(), w("span", sF, L(g.mt_id), 1)) : (_(), w("span", nF, " Wallet "))]), n("p", oF, [iF, J(" " + L(g.mt_category), 1)]), n("div", rF, [n("p", aF, [lF, J(" " + L(g.leverage) + " ", 1), cF, J(" " + L(this.$formatCurrency(g.credit)) + " ", 1), dF, J(" " + L(this.$formatCurrency(g.equity)), 1)])])]), n("div", uF, [n("h4", fF, L(this.$formatCurrency(g.balance)), 1), hF])])]))), 128)) : D("", !0), n("li", pF, [n("div", mF, [n("h4", gF, L(this.$formatCurrency((d = i.liveMTAccounts) == null ? void 0 : d.reduce((g, $) => g + $.equity, 0))), 1)]), _F]), n("li", bF, [n("div", vF, [n("h4", yF, L(this.$formatCurrency((p = i.liveMTAccounts) == null ? void 0 : p.reduce((g, $) => g + $.equity, 0))), 1)]), wF])])])]), n("div", $F, [n("div", CF, [n("ul", kF, [n("li", AF, [n("div", xF, [n("h3", SF, L(this.$formatCurrency((m = i.liveMTAccounts) == null ? void 0 : m.reduce((g, $) => g + $.balance, 0))), 1)]), EF])])])])])])])])
}
const TF = je(eR, [
    ["render", PF]
  ]),
  LF = {
    props: ["liveAccounts"],
    data() {
      return {
        isLoading: !1,
        selectedFromAccount: null,
        selectedToAccount: null,
        fromLiveAccounts: this.liveAccounts,
        toLiveAccounts: this.liveAccounts,
        transferAmount: 0,
        errors: {
          transferAmount: "",
          selectedFromAccount: "",
          selectedToAccount: ""
        }
      }
    },
    watch: {
      selectedFromAccount(e, t) {
        this.updateToMT5List(e), this.valiDateSelectedFromAccount(e)
      },
      selectedToAccount(e, t) {
        this.valiDateSelectedToAccount(e)
      },
      transferAmount(e, t) {
        this.valiDateTransferAmount(e)
      }
    },
    computed: {},
    components: {},
    mounted() {},
    methods: {
      updateToMT5List(e) {
        this.selectedToAccount = null, this.toLiveAccounts = this.liveAccounts.filter(t => t !== e)
      },
      handleSubmitClick() {
        this.valiDateSelectedFromAccount(this.selectedFromAccount) && this.valiDateSelectedToAccount(this.selectedToAccount) && this.valiDateTransferAmount(this.transferAmount) && (this.processInternalTransfer(), this.isLoading = !0)
      },
      valiDateSelectedFromAccount(e) {
        return e ? e.margin_free - e.credit <= 0 ? (this.errors.selectedFromAccount = "Please select an account with Balance greater than Zero", !1) : (this.valiDateTransferAmount(this.transferAmount), this.errors.selectedFromAccount = "", !0) : (this.errors.selectedFromAccount = "Please select FROM account", !1)
      },
      valiDateSelectedToAccount(e) {
        return e ? (this.errors.selectedToAccount = "", !0) : (this.errors.selectedToAccount = "Please select To account", !1)
      },
      valiDateTransferAmount(e) {
        return e < 1 ? (this.errors.transferAmount = "Please enter an amount greater than Zero", !1) : e > this.selectedFromAccount.margin_free - this.selectedFromAccount.credit ? (this.errors.transferAmount = "You dont have enough balance in selected account", !1) : (this.errors.transferAmount = "", !0)
      },
      async processInternalTransfer() {
        if (this.selectedFromAccount.margin_free - this.selectedFromAccount.credit <= 0) this.errors.transferAmount = "Please select an account with Balance greater than Zero";
        else if (this.transferAmount > this.selectedFromAccount.margin_free - this.selectedFromAccount.credit) this.errors.transferAmount = "Please enter an amount less than or equal to Transferable Amount", Swal.fire({
          icon: "error",
          title: "Please enter an amount less than or equal to Transferable Amount"
        }).then(e => {
          e.isConfirmed && window.location.reload()
        });
        else try {
          const e = localStorage.getItem("auth_id"),
            t = $e.get("authToken"),
            s = await ye.post(this.API_BASE_URL + "/fund/deposit/transfer/create", {
              from_account: this.selectedFromAccount.mt_id,
              to_account: this.selectedToAccount.mt_id,
              amount: Number(this.transferAmount)
            }, {
              headers: {
                Authorization: `Bearer ${t}`
              }
            });
          if (s) {
            this.isLoading = !1;
            const i = me.getDataWithExpiration("liveAccounts").map(r => r.mt_id === this.selectedFromAccount.mt_id ? {
              ...r,
              balance: Number(this.selectedFromAccount.balance) - Number(this.transferAmount),
              equity: Number(this.selectedFromAccount.equity) - Number(this.transferAmount),
              margin_free: Number(this.selectedFromAccount.margin_free) - Number(this.transferAmount)
            } : r.mt_id === this.selectedToAccount.mt_id ? {
              ...r,
              balance: Number(this.selectedToAccount.balance) + Number(this.transferAmount),
              equity: Number(this.selectedToAccount.equity) + Number(this.transferAmount),
              margin_free: Number(this.selectedToAccount.margin_free) + Number(this.transferAmount)
            } : r);
            this.fromLiveAccounts = i, this.toLiveAccounts = i, me.setDataWithTimestamp("liveAccounts", i, 30), Swal.fire({
              icon: "success",
              title: s == null ? void 0 : s.data.message
            }), os.push("/dashboard")
          }
        } catch (e) {
          console.log(e), Swal.fire({
            icon: "error",
            title: e.response.message
          }).then(t => {
            t.isConfirmed && window.location.reload()
          })
        }
      }
    }
  },
  OF = {
    class: "row"
  },
  IF = {
    class: "col-12"
  },
  BF = {
    class: "card"
  },
  DF = n("div", {
    class: "card-header"
  }, [n("h4", null, "Process Internal Transfer")], -1),
  MF = {
    key: 0,
    class: "card-body"
  },
  RF = {
    key: 0,
    class: "row align-items-center"
  },
  NF = {
    class: "col-md-5"
  },
  FF = n("label", {
    class: "form-label"
  }, "Select From Account", -1),
  UF = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  VF = ["v-if"],
  jF = {
    class: "form-check"
  },
  HF = ["id", "value"],
  qF = ["for"],
  WF = {
    class: "row"
  },
  zF = {
    class: "col-6"
  },
  KF = {
    key: 0,
    class: "h4 mb-0 d-block"
  },
  GF = n("img", {
    src: Mt,
    alt: "user-image",
    class: "user-avtar wid-40"
  }, null, -1),
  YF = {
    key: 1,
    class: "h4 mb-0 d-block"
  },
  JF = n("img", {
    src: ps,
    alt: "user-image",
    class: "user-avtar wid-40"
  }, null, -1),
  ZF = {
    class: "col-6 text-end"
  },
  XF = {
    class: "h4 mb-0 d-block f-w-500"
  },
  QF = n("span", {
    class: "text-muted mb-0"
  }, "Transferable Balance", -1),
  eU = n("div", {
    class: "col-md-2 d-flex justify-content-center"
  }, [n("div", {
    class: "avtar center"
  }, [n("i", {
    class: "ti ti-arrows-left-right f-40"
  })])], -1),
  tU = {
    class: "col-md-5"
  },
  sU = n("label", {
    class: "form-label"
  }, "Select To Account", -1),
  nU = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  oU = {
    class: "form-check"
  },
  iU = ["id", "value"],
  rU = ["for"],
  aU = {
    class: "row"
  },
  lU = {
    class: "col-6"
  },
  cU = {
    key: 0,
    class: "h4 mb-0 d-block"
  },
  dU = n("img", {
    src: Mt,
    alt: "user-image",
    class: "user-avtar wid-40"
  }, null, -1),
  uU = {
    key: 1,
    class: "h4 mb-0 d-block"
  },
  fU = n("img", {
    src: ps,
    alt: "user-image",
    class: "user-avtar wid-40"
  }, null, -1),
  hU = {
    class: "col-6 text-end"
  },
  pU = {
    class: "h4 mb-0 d-block f-w-500"
  },
  mU = n("span", {
    class: "text-muted mb-0"
  }, "Current Balance", -1),
  gU = {
    class: "row align-items-center mt-5"
  },
  _U = n("div", {
    class: "col-md-6"
  }, null, -1),
  bU = {
    class: "col-md-6"
  },
  vU = n("label", {
    class: "form-label",
    for: "exampleFormControlSelect1"
  }, "Enter Amount", -1),
  yU = {
    class: "input-group mb-3"
  },
  wU = n("span", {
    class: "input-group-text"
  }, "USD", -1),
  $U = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  CU = {
    class: "form-group text-end"
  },
  kU = {
    class: "d-grid gap-2 mt-4"
  },
  AU = ["disabled"],
  xU = n("i", {
    class: "ti ti-archive me-2"
  }, null, -1),
  SU = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  EU = {
    key: 1,
    class: "card-body"
  },
  PU = {
    class: "p-5 m-3"
  },
  TU = n("button", {
    class: "btn btn-outline-primary"
  }, [n("span", {
    class: "text-truncate w-100"
  }, "Create new Account")], -1);

function LU(e, t, s, o, i, r) {
  var p;
  const a = ke("LoadingSpinner"),
    l = ke("EmptyStateView"),
    d = ke("RouterLink");
  return _(), w("div", OF, [n("div", IF, [n("div", BF, [DF, ((p = this.fromLiveAccounts) == null ? void 0 : p.length) > 0 ? (_(), w("div", MF, [s.liveAccounts ? (_(), w("div", RF, [n("div", NF, [FF, i.errors.selectedFromAccount ? (_(), w("div", UF, L(i.errors.selectedFromAccount), 1)) : D("", !0), (_(!0), w(Te, null, Je(i.fromLiveAccounts, m => (_(), w("div", {
    "v-if": m.i_enabled2 != 0,
    key: m.mt_id,
    class: "price-check border rounded p-3 my-3"
  }, [n("div", jF, [be(n("input", {
    type: "radio",
    name: "fromAccount",
    class: "form-check-input input-primary",
    id: m.mt_id,
    "onUpdate:modelValue": t[0] || (t[0] = g => i.selectedFromAccount = g),
    value: m
  }, null, 8, HF), [
    [kt, i.selectedFromAccount]
  ]), n("label", {
    class: "form-check-label d-block",
    for: m.mt_id
  }, [n("span", WF, [n("span", zF, [m.is_wallet != 1 ? (_(), w("span", KF, [GF, J(" " + L(m.mt_id), 1)])) : (_(), w("span", YF, [JF, J(" Wallet ")]))]), n("span", ZF, [n("span", XF, L(this.$formatCurrency(m.margin_free - m.credit)), 1), QF])])], 8, qF)])], 8, VF))), 128))]), eU, n("div", tU, [sU, i.errors.selectedToAccount ? (_(), w("div", nU, L(i.errors.selectedToAccount), 1)) : D("", !0), (_(!0), w(Te, null, Je(i.toLiveAccounts, m => (_(), w("div", {
    key: m.mt_id,
    class: "price-check border rounded p-3 my-3"
  }, [n("div", oU, [be(n("input", {
    type: "radio",
    name: "toAccount",
    class: "form-check-input input-primary",
    id: "to_" + m.mt_id,
    "onUpdate:modelValue": t[1] || (t[1] = g => i.selectedToAccount = g),
    value: m
  }, null, 8, iU), [
    [kt, i.selectedToAccount]
  ]), n("label", {
    class: "form-check-label d-block",
    for: "to_" + m.mt_id
  }, [n("span", aU, [n("span", lU, [m.is_wallet != 1 ? (_(), w("span", cU, [dU, J(" " + L(m.mt_id), 1)])) : (_(), w("span", uU, [fU, J(" Wallet ")]))]), n("span", hU, [n("span", pU, L(this.$formatCurrency(m.balance)), 1), mU])])], 8, rU)])]))), 128))])])) : (_(), ve(a, {
    key: 1
  })), n("div", gU, [_U, n("div", bU, [n("form", null, [vU, n("div", yU, [wU, be(n("input", {
    "onUpdate:modelValue": t[2] || (t[2] = m => i.transferAmount = m),
    type: "text",
    class: "form-control",
    "aria-label": "Amount "
  }, null, 512), [
    [Re, i.transferAmount]
  ]), i.errors.transferAmount ? (_(), w("div", $U, L(i.errors.transferAmount), 1)) : D("", !0)]), n("div", CU, [n("div", kU, [n("button", {
    class: "btn btn-primary",
    type: "button",
    disabled: i.isLoading,
    onClick: t[3] || (t[3] = (...m) => r.handleSubmitClick && r.handleSubmitClick(...m))
  }, [xU, i.isLoading ? (_(), w("span", SU)) : D("", !0), J(" " + L(i.isLoading ? "Processing..." : "Process Transfer"), 1)], 8, AU)])])])])])])) : (_(), w("div", EU, [n("div", PU, [ie(l, {
    msg: "No Live Accounts Found"
  }), ie(d, {
    to: "/createLiveAccount",
    class: "d-grid"
  }, {
    default: Ue(() => [TU]),
    _: 1
  })])]))])])])
}
const OU = je(LF, [
    ["render", LU]
  ]),
  IU = {
    props: {
      option: String
    },
    components: {
      WithdrawView: XD,
      DepositView: TF,
      TransferView: OU
    },
    setup(e) {
      const t = he(e.option || "deposit"),
        s = he(null);

      function o(a) {
        t.value = a
      }
      const i = async () => {
        try {
          const a = Ge("API_BASE_URL"),
            l = $e.get("authToken");
          if (a && l) {
            const d = await ye.get(`${a}/live-accounts/data-list`, {
              headers: {
                Authorization: `Bearer ${l}`
              }
            });
            s.value = d.data.result.mt_data, me.setDataWithTimestamp("liveAccounts", d.data.result.mt_data, 30)
          } else throw new Error("Missing API_BASE_URL or authToken")
        } catch (a) {
          console.error("Error fetching data:", a), Swal.fire({
            icon: "error",
            title: "Error fetching data",
            text: a.message || "An error occurred while fetching data"
          })
        }
      }, r = () => {
        const a = me.getDataWithExpiration("liveAccounts");
        a && (s.value = a)
      };
      return At(() => {
        r(), i()
      }), {
        selectedOption: t,
        liveAccounts: s,
        fetchLiveAccountDataList: i,
        selectTab: o
      }
    }
  },
  BU = {
    class: "pc-container"
  },
  DU = {
    class: "pc-content"
  },
  MU = Ae('<div class="page-header mb-0 pb-0"><div class="page-block"><div class="row align-items-center"><div class="col-md-12"><div class="page-header-title h2"><h4 class="mb-0">Fund</h4></div></div></div></div></div>', 1),
  RU = {
    class: "row"
  },
  NU = {
    class: "col-sm-12"
  },
  FU = {
    class: "card"
  },
  UU = {
    class: "card-body p-0"
  },
  VU = {
    class: "nav nav-tabs checkout-tabs mb-0",
    id: "myTab",
    role: "tablist"
  },
  jU = {
    class: "nav-item",
    role: "presentation"
  },
  HU = Ae('<div class="media align-items-center"><div class="avtar avtar-s"><i class="feather icon-credit-card"></i></div><div class="media-body ms-2"><h6 class="mb-0">DEPOSIT</h6></div></div>', 1),
  qU = [HU],
  WU = {
    class: "nav-item",
    role: "presentation"
  },
  zU = Ae('<div class="media align-items-center"><div class="avtar avtar-s"><i class="feather icon-dollar-sign"></i></div><div class="media-body ms-2"><h6 class="mb-0">WITHDRAW</h6></div></div>', 1),
  KU = [zU],
  GU = {
    class: "nav-item",
    role: "presentation"
  },
  YU = Ae('<div class="media align-items-center"><div class="avtar avtar-s"><i class="feather icon-repeat"></i></div><div class="media-body ms-2"><h6 class="mb-0">INTERNAL TRANSFER</h6></div></div>', 1),
  JU = [YU],
  ZU = {
    class: "tab-content"
  },
  XU = {
    key: 0
  },
  QU = {
    key: 1
  },
  eV = {
    key: 2
  };

function tV(e, t, s, o, i, r) {
  const a = ke("DepositView"),
    l = ke("WithdrawView"),
    d = ke("TransferView");
  return _(), w("div", BU, [n("div", DU, [MU, n("div", RU, [n("div", NU, [n("div", FU, [n("div", UU, [n("ul", VU, [n("li", jU, [n("a", {
    class: "nav-link active",
    onClick: t[0] || (t[0] = p => o.selectTab("deposit")),
    id: "ecomtab-tab-1",
    "data-bs-toggle": "tab",
    href: "#ecomtab-1",
    role: "tab",
    "aria-controls": "ecomtab-1",
    "aria-selected": "false",
    tabindex: "-1"
  }, qU)]), n("li", WU, [n("a", {
    class: "nav-link",
    onClick: t[1] || (t[1] = p => o.selectTab("withdraw")),
    id: "ecomtab-tab-2",
    "data-bs-toggle": "tab",
    href: "#ecomtab-2",
    role: "tab",
    "aria-controls": "ecomtab-2",
    "aria-selected": "false",
    tabindex: "-1"
  }, KU)]), n("li", GU, [n("a", {
    class: "nav-link",
    onClick: t[2] || (t[2] = p => o.selectTab("transfer")),
    id: "ecomtab-tab-3",
    "data-bs-toggle": "tab",
    href: "#ecomtab-3",
    role: "tab",
    "aria-controls": "ecomtab-3",
    "aria-selected": "true"
  }, JU)])])])]), n("div", ZU, [o.selectedOption === "deposit" ? (_(), w("div", XU, [ie(a, {
    liveAccounts: o.liveAccounts
  }, null, 8, ["liveAccounts"])])) : D("", !0), o.selectedOption === "withdraw" ? (_(), w("div", QU, [ie(l, {
    liveAccounts: o.liveAccounts
  }, null, 8, ["liveAccounts"])])) : D("", !0), o.selectedOption === "transfer" ? (_(), w("div", eV, [ie(d, {
    liveAccounts: o.liveAccounts
  }, null, 8, ["liveAccounts"])])) : D("", !0)])])])])])
}
const sV = je(IU, [
    ["render", tV]
  ]),
  ua = "authToken",
  nV = {
    login(e) {
      this.setAuthToken("your_generated_token")
    },
    logout() {
      $e.remove(ua)
    },
    isAuthenticated() {
      return !!this.getAuthToken()
    },
    getAuthToken() {
      return $e.get(ua)
    },
    setAuthToken(e) {
      $e.set(ua, e, {
        expires: 3
      })
    },
    removeAuthToken() {
      $e.remove(ua)
    }
  },
  oV = {
    name: "PasswordUpdateView",
    data() {
      return {
        isLoading: !1,
        current_password: "",
        password: "",
        confirmPassword: "",
        validations: {
          minLength: !1,
          uppercase: !1,
          lowercase: !1,
          specialCharacter: !1,
          digit: !1,
          match: !1
        },
        errors: {
          current_password: ""
        }
      }
    },
    mounted() {
      $e.get("authToken")
    },
    methods: {
      validatePassword() {
        const e = this.password;
        this.validations.minLength = e.length >= 8, this.validations.uppercase = /[A-Z]/.test(e), this.validations.lowercase = /[a-z]/.test(e), this.validations.specialCharacter = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(e), this.validations.digit = /\d/.test(e)
      },
      validateConfirmPassword() {
        this.validations.match = this.password === this.confirmPassword
      },
      handleSubmitClick() {
        this.errors.current_password = "", this.current_password.length < 8 && (this.errors.current_password = "Current password should have at least 8 characters"), Object.values(this.validations).every(t => t) && !this.errors.current_password ? this.UpdatePassword() : console.log("Please fix validation errors.")
      },
      async UpdatePassword() {
        this.isLoading = !0;
        try {
          const e = localStorage.getItem("auth_id"),
            t = $e.get("authToken"),
            s = await ye.post(this.API_BASE_URL + "/profile/password-reset", {
              old_password: this.current_password,
              password: this.password,
              confirmPassword: this.confirmPassword
            }, {
              headers: {
                Authorization: `Bearer ${t}`
              }
            });
          s && (this.isLoading = !1, Swal.fire({
            icon: "success",
            title: s == null ? void 0 : s.data.message
          }).then(o => {
            o.isConfirmed && window.location.reload()
          }))
        } catch (e) {
          Swal.fire({
            icon: "error",
            title: e.response.data.message
          }).then(t => {
            t.isConfirmed && window.location.reload()
          })
        }
      }
    }
  },
  iV = {
    class: "card"
  },
  rV = n("div", {
    class: "card-header"
  }, [n("h5", null, "Change Password")], -1),
  aV = {
    class: "card-body"
  },
  lV = {
    class: "row"
  },
  cV = {
    class: "col-sm-6"
  },
  dV = {
    class: "form-group"
  },
  uV = n("label", {
    class: "form-label"
  }, "Current Password", -1),
  fV = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  hV = {
    class: "form-group"
  },
  pV = n("label", {
    class: "form-label"
  }, "New Password", -1),
  mV = {
    class: "form-group"
  },
  gV = n("label", {
    class: "form-label"
  }, "Confirm Password", -1),
  _V = {
    class: "col-sm-6"
  },
  bV = n("h5", null, "New password must contain:", -1),
  vV = {
    class: "list-group list-group-flush"
  },
  yV = {
    class: "list-group-item"
  },
  wV = {
    key: 0,
    class: "ti ti-circle-check text-success f-16 me-2"
  },
  $V = {
    key: 1,
    class: "ti ti-circle-minus text-danger f-16 me-2"
  },
  CV = {
    class: "list-group-item mb-0"
  },
  kV = {
    key: 0,
    class: "ti ti-circle-check text-success f-16 me-2"
  },
  AV = {
    key: 1,
    class: "ti ti-circle-minus text-danger f-16 me-2"
  },
  xV = {
    class: "list-group-item"
  },
  SV = {
    key: 0,
    class: "ti ti-circle-check text-success f-16 me-2"
  },
  EV = {
    key: 1,
    class: "ti ti-circle-minus text-danger f-16 me-2"
  },
  PV = {
    class: "list-group-item"
  },
  TV = {
    key: 0,
    class: "ti ti-circle-check text-success f-16 me-2"
  },
  LV = {
    key: 1,
    class: "ti ti-circle-minus text-danger f-16 me-2"
  },
  OV = {
    class: "list-group-item"
  },
  IV = {
    key: 0,
    class: "ti ti-circle-check text-success f-16 me-2"
  },
  BV = {
    key: 1,
    class: "ti ti-circle-minus text-danger f-16 me-2"
  },
  DV = {
    key: 0,
    class: "list-group-item"
  },
  MV = n("i", {
    class: "ti ti-circle-check text-success f-16 me-2"
  }, null, -1),
  RV = {
    key: 1,
    class: "list-group-item"
  },
  NV = n("i", {
    class: "ti ti-circle-minus text-danger f-16 me-2"
  }, null, -1),
  FV = {
    class: "card-footer text-end btn-page"
  },
  UV = n("div", {
    class: "btn btn-outline-secondary"
  }, "Cancel", -1),
  VV = ["disabled"],
  jV = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  };

function HV(e, t, s, o, i, r) {
  return _(), w("div", iV, [rV, n("div", aV, [n("div", lV, [n("div", cV, [n("div", dV, [uV, be(n("input", {
    "onUpdate:modelValue": t[0] || (t[0] = a => i.current_password = a),
    type: "password",
    class: "form-control"
  }, null, 512), [
    [Re, i.current_password]
  ]), i.errors.current_password ? (_(), w("div", fV, L(i.errors.current_password), 1)) : D("", !0)]), n("div", hV, [pV, be(n("input", {
    "onUpdate:modelValue": t[1] || (t[1] = a => i.password = a),
    onInput: t[2] || (t[2] = (...a) => r.validatePassword && r.validatePassword(...a)),
    type: "password",
    class: "form-control"
  }, null, 544), [
    [Re, i.password]
  ])]), n("div", mV, [gV, be(n("input", {
    "onUpdate:modelValue": t[3] || (t[3] = a => i.confirmPassword = a),
    onInput: t[4] || (t[4] = (...a) => r.validateConfirmPassword && r.validateConfirmPassword(...a)),
    type: "password",
    class: "form-control"
  }, null, 544), [
    [Re, i.confirmPassword]
  ])])]), n("div", _V, [bV, n("ul", vV, [n("li", yV, [i.validations.minLength ? (_(), w("i", wV)) : (_(), w("i", $V)), J(" At least 8 characters ")]), n("li", CV, [i.validations.lowercase ? (_(), w("i", kV)) : (_(), w("i", AV)), J(" At least 1 lower letter (a-z) ")]), n("li", xV, [i.validations.uppercase ? (_(), w("i", SV)) : (_(), w("i", EV)), J(" At least 1 uppercase letter(A-Z) ")]), n("li", PV, [i.validations.digit ? (_(), w("i", TV)) : (_(), w("i", LV)), J(" At least 1 number (0-9) ")]), n("li", OV, [i.validations.specialCharacter ? (_(), w("i", IV)) : (_(), w("i", BV)), J(" At least 1 special characters ")]), i.validations.match && i.validations.minLength ? (_(), w("li", DV, [MV, J(" Passwords match ")])) : (_(), w("li", RV, [NV, J(" Passwords do not match ")]))])])])]), n("div", FV, [UV, n("button", {
    class: "btn btn-primary",
    type: "button",
    disabled: i.isLoading,
    onClick: t[5] || (t[5] = (...a) => r.handleSubmitClick && r.handleSubmitClick(...a))
  }, [i.isLoading ? (_(), w("span", jV)) : D("", !0), J(" " + L(i.isLoading ? "Updating..." : "Update Password"), 1)], 8, VV)])])
}
const qV = je(oV, [
    ["render", HV]
  ]),
  WV = Dt({
    components: {
      AddBankView: V1
    },
    props: {
      bank_details: Array
    },
    setup(e) {
      Ge("API_BASE_URL");
      const t = he(e.clientDocs),
        s = he(!1);
      return At(() => {
        const i = me.getDataWithExpiration("bank_details");
        i && (t.value = i)
      }), {
        bankDetails: t,
        isLoading: s
      }
    }
  }),
  zV = {
    class: "card"
  },
  KV = {
    class: "card-header"
  },
  GV = {
    class: "row"
  },
  YV = {
    class: "col-6"
  },
  JV = {
    class: "col-6 text-end"
  },
  ZV = {
    key: 0,
    href: "#",
    class: "btn btn-primary d-inline-flex align-item-center",
    "data-bs-toggle": "modal",
    "data-bs-target": "#add_bank_modal"
  },
  XV = n("i", {
    class: "ti ti-plus f-18"
  }, null, -1),
  QV = {
    class: "card-body table-card"
  },
  ej = {
    key: 0,
    class: "table-responsive"
  },
  tj = {
    class: "table mb-0"
  },
  sj = n("thead", null, [n("tr", null, [n("th", null, "Account Number"), n("th", null, "Holder Name"), n("th", {
    class: "text-end"
  }, "Bank Name"), n("th", {
    class: "text-end"
  }, "IFSC Code"), n("th", {
    class: "text-end"
  }, "BANK ADDRESS")])], -1),
  nj = {
    class: "text-end"
  },
  oj = {
    class: "text-end"
  },
  ij = {
    class: "text-end"
  };

function rj(e, t, s, o, i, r) {
  var d, p, m;
  const a = ke("EmptyStateView"),
    l = ke("AddBankView");
  return _(), w("div", null, [n("div", zV, [n("div", KV, [n("div", GV, [n("div", YV, [n("h5", null, "Bank Details" + L((d = e.bankDetails) == null ? void 0 : d.length), 1)]), n("div", JV, [((p = e.bankDetails) == null ? void 0 : p.length) < 3 || !e.bankDetails ? (_(), w("a", ZV, [XV, J(" Add Bank Information ")])) : D("", !0)])])]), n("div", QV, [((m = e.bankDetails) == null ? void 0 : m.length) >= 1 ? (_(), w("div", ej, [n("table", tj, [sj, n("tbody", null, [(_(!0), w(Te, null, Je(e.bankDetails, g => (_(), w("tr", null, [n("td", null, L(g.account_no), 1), n("td", null, L(g.holder_name), 1), n("td", nj, L(g.bank_name), 1), n("td", oj, L(g.ifsc_code), 1), n("td", ij, L(g.bank_address), 1)]))), 256))])])])) : (_(), ve(a, {
    key: 1,
    msg: "Please add your Bank Details"
  }))])]), ie(l)])
}
const aj = je(WV, [
    ["render", rj]
  ]),
  lj = {
    props: {
      clientDocs: Array,
      docStatus: String
    },
    setup(e, {
      emit: t
    }) {
      const s = he(!1),
        o = he({
          identityFront: null,
          identityBack: null,
          proofAddress: null
        }),
        i = he(e.clientDocs),
        r = Ge("S3_BASE_URL"),
        a = g => new Date(g).toLocaleDateString("en-US", {
          day: "2-digit",
          month: "short",
          year: "numeric"
        }),
        l = (g, $, C) => {
          const A = `${r}/images/userdocs/${g}/${$}/${C}`;
          window.open(A, "_blank")
        },
        d = (g, $, C) => `${r}/images/userdocs/${g}/${$}/${C}`,
        p = async (g, $, C) => {
          const A = `${r}/images/userdocs/${g}/${$}/${C}`,
            V = await (await fetch(A)).blob(),
            M = document.createElement("a");
          M.href = URL.createObjectURL(V), M.download = `${C.split("/").pop()}`, document.body.appendChild(M), M.click(), document.body.removeChild(M)
        };
      return At(() => {
        const g = me.getDataWithExpiration("client_docs");
        g && (i.value = g)
      }), {
        isLoading: s,
        clientDoc: i,
        files: o,
        formatDate: a,
        openImage: l,
        getImageSrc: d,
        downloadImage: p
      }
    }
  },
  cj = {
    class: "card"
  },
  dj = {
    class: "card-header"
  },
  uj = {
    class: "row"
  },
  fj = n("div", {
    class: "col-6"
  }, [n("h5", null, "Uploaded Documents")], -1),
  hj = {
    class: "col-6 text-end"
  },
  pj = n("i", {
    class: "ti ti-plus f-18"
  }, null, -1),
  mj = {
    class: "card-body table-card"
  },
  gj = {
    key: 0,
    class: "table-responsive"
  },
  _j = {
    class: "table mb-0"
  },
  bj = n("thead", null, [n("tr", null, [n("th", null, "DOCUMENT"), n("th", null, "UPLOADED ON"), n("th", null, "DOC"), n("th", {
    class: "text-end"
  }, "STATUS"), n("th", {
    class: "text-end"
  }, "ACTIONS")])], -1),
  vj = {
    "data-index": "0"
  },
  yj = {
    class: "row"
  },
  wj = n("div", {
    class: "col-auto pe-2"
  }, [n("svg", {
    class: "pc-icon h-10"
  }, [n("use", {
    "xlink:href": "#custom-document-text"
  })])], -1),
  $j = {
    class: "col"
  },
  Cj = {
    class: "mb-1"
  },
  kj = {
    key: 0,
    class: "text-muted f-12 mb-0"
  },
  Aj = {
    key: 1,
    class: "text-muted f-12 mb-0"
  },
  xj = ["onClick"],
  Sj = ["src"],
  Ej = {
    key: 1,
    src: "https://i.ibb.co/MB6vHrM/docs.png",
    alt: "img",
    class: "card-img",
    style: {
      width: "60px"
    }
  },
  Pj = {
    class: "text-end"
  },
  Tj = {
    key: 0,
    class: "badge bg-light-success f-12"
  },
  Lj = {
    key: 1,
    class: "badge bg-light-warning f-12"
  },
  Oj = {
    key: 2,
    class: "badge bg-light-rejected f-12"
  },
  Ij = {
    class: "text-end"
  },
  Bj = {
    class: "list-inline me-auto mb-0"
  },
  Dj = {
    class: "list-inline-item align-bottom",
    "data-bs-toggle": "tooltip",
    title: "View"
  },
  Mj = ["onClick"],
  Rj = n("i", {
    class: "ti ti-eye f-18"
  }, null, -1),
  Nj = [Rj],
  Fj = {
    class: "list-inline-item align-bottom",
    "data-bs-toggle": "tooltip",
    title: "Download"
  },
  Uj = ["onClick"],
  Vj = n("i", {
    class: "ti ti-download f-18"
  }, null, -1),
  jj = [Vj];

function Hj(e, t, s, o, i, r) {
  var d;
  const a = ke("RouterLink"),
    l = ke("EmptyStateView");
  return _(), w("div", cj, [n("div", dj, [n("div", uj, [fj, n("div", hj, [ie(a, {
    to: "/user/documentUpload",
    class: "btn btn-primary text-end btn-page"
  }, {
    default: Ue(() => [pj, J(" Upload Documents ")]),
    _: 1
  })])])]), n("div", mj, [((d = o.clientDoc) == null ? void 0 : d.length) > 0 ? (_(), w("div", gj, [n("table", _j, [bj, n("tbody", null, [(_(!0), w(Te, null, Je(o.clientDoc, p => (_(), w("tr", vj, [n("td", null, [n("div", yj, [wj, n("div", $j, [n("h6", Cj, L(p.doc_path), 1), p.doc_type == "poa" ? (_(), w("p", kj, "Proof of Address ")) : D("", !0), p.doc_type == "poi" ? (_(), w("p", Aj, "Proof of Identity ")) : D("", !0)])])]), n("td", null, L(o.formatDate(p.created_at)), 1), n("td", null, [n("a", {
    href: "#",
    class: "",
    onClick: m => o.openImage(p.client_id, p.doc_type, p.doc_path)
  }, [p.extension !== "pdf" ? (_(), w("img", {
    key: 0,
    src: o.getImageSrc(p.client_id, p.doc_type, p.doc_path),
    alt: "img",
    class: "card-img",
    style: {
      width: "100px"
    }
  }, null, 8, Sj)) : (_(), w("img", Ej))], 8, xj)]), n("td", Pj, [p.status == 3 ? (_(), w("span", Tj, "Approved")) : D("", !0), p.status == 0 ? (_(), w("span", Lj, "Pending")) : D("", !0), p.status == 2 ? (_(), w("span", Oj, "Rejected")) : D("", !0)]), n("td", Ij, [n("ul", Bj, [n("li", Dj, [n("a", {
    href: "#",
    class: "avtar avtar-xs btn-link-secondary btn-pc-default",
    onClick: m => o.openImage(p.client_id, p.doc_type, p.doc_path)
  }, Nj, 8, Mj)]), n("li", Fj, [n("a", {
    ref_for: !0,
    ref: "downloadLink",
    style: {
      display: "none"
    }
  }, null, 512), n("a", {
    href: "#",
    class: "avtar avtar-xs btn-link-secondary btn-pc-default",
    onClick: m => o.downloadImage(p.client_id, p.doc_type, p.doc_path)
  }, jj, 8, Uj)])])])]))), 256))])])])) : (_(), ve(l, {
    key: 1,
    msg: "No documents added"
  }))])])
}
const qj = je(lj, [
    ["render", Hj]
  ]),
  Wj = {
    props: ["client"],
    setup(e) {
      const t = localStorage.getItem("client_name"),
        s = localStorage.getItem("email"),
        o = localStorage.getItem("contact_no");
      return {
        firstName: t,
        email: s,
        phone: o
      }
    }
  },
  zj = {
    class: "row"
  },
  Kj = {
    class: "col-12"
  },
  Gj = {
    class: "card"
  },
  Yj = n("div", {
    class: "card-header"
  }, [n("h5", null, "Personal Information")], -1),
  Jj = {
    class: "card-body"
  },
  Zj = {
    class: "row"
  },
  Xj = {
    class: "col-sm-6"
  },
  Qj = {
    class: "form-group"
  },
  eH = n("label", {
    class: "form-label"
  }, "Full Name ", -1),
  tH = {
    class: "col-sm-6"
  },
  sH = {
    class: "form-group"
  },
  nH = n("label", {
    class: "form-label"
  }, "Account Email ", -1),
  oH = {
    class: "col-sm-6"
  },
  iH = {
    class: "form-group"
  },
  rH = n("label", {
    class: "form-label"
  }, "Contact Number ", -1);

function aH(e, t, s, o, i, r) {
  return _(), w("div", zj, [n("div", Kj, [n("div", Gj, [Yj, n("div", Jj, [n("div", Zj, [n("div", Xj, [n("div", Qj, [eH, be(n("input", {
    type: "text",
    class: "form-control",
    "onUpdate:modelValue": t[0] || (t[0] = a => o.firstName = a),
    disabled: ""
  }, null, 512), [
    [Re, o.firstName]
  ])])]), n("div", tH, [n("div", sH, [nH, be(n("input", {
    type: "text",
    class: "form-control",
    "onUpdate:modelValue": t[1] || (t[1] = a => o.email = a),
    disabled: ""
  }, null, 512), [
    [Re, o.email]
  ])])]), n("div", oH, [n("div", iH, [rH, be(n("input", {
    type: "text",
    class: "form-control",
    "onUpdate:modelValue": t[2] || (t[2] = a => o.phone = a),
    disabled: ""
  }, null, 512), [
    [Re, o.phone]
  ])])])])])])])])
}
const lH = je(Wj, [
    ["render", aH]
  ]),
  cH = "/assets/img-profile-cover-DJAPkiCO.png",
  wl = "/assets/avatar-wUX9a-ps.webp",
  dH = {
    data() {
      return {
        clientName: "",
        clientEmail: "",
        companySlug: "succedo",
        profile_data: "",
        imageSrc: ""
      }
    },
    components: {
      PasswordUpdateView: qV,
      BankDetailsView: aj,
      DocumentsView: qj,
      PersonalDetailsView: lH
    },
    async mounted() {
      this.loadDataFromLocalStorage(), this.profile_data = await D1();
      const e = document.getElementById("logout-link"),
        t = document.getElementById("logout-link-2");
      e.addEventListener("click", this.confirmLogout), t.addEventListener("click", this.confirmLogout);
      const s = localStorage.getItem("client_name"),
        o = localStorage.getItem("email");
      s && (this.clientName = s), o && (this.clientEmail = o)
    },
    methods: {
      loadDataFromLocalStorage() {
        const e = me.getDataWithExpiration("profile_data");
        e && (this.profile_data = e)
      },
      generateNumber() {
        let t = 1e4 - localStorage.getItem("auth_id") + 1e3;
        return String(t).padStart(5, "0")
      }
    }
  },
  uH = {
    class: "pc-container"
  },
  fH = {
    class: "pc-content"
  },
  hH = Ae('<div class="page-header"><div class="page-block"><div class="row align-items-center"><div class="col-md-12"><div class="page-header-title h2"><h4 class="mb-0">My Profile</h4></div></div></div></div></div>', 1),
  pH = {
    class: "row"
  },
  mH = {
    class: "col-sm-12"
  },
  gH = {
    class: "card social-profile"
  },
  _H = n("img", {
    src: cH,
    alt: "",
    class: "w-100 card-img-top"
  }, null, -1),
  bH = {
    class: "card-body pt-0"
  },
  vH = {
    class: "row align-items-end"
  },
  yH = n("div", {
    class: "col-md-auto text-md-start"
  }, [n("img", {
    class: "img-fluid img-profile-avtar",
    src: wl,
    alt: "User image"
  })], -1),
  wH = {
    class: "col"
  },
  $H = {
    class: "row justify-content-between align-items-end"
  },
  CH = {
    class: "col-md-auto soc-profile-data"
  },
  kH = {
    class: "mb-1"
  },
  AH = {
    class: "mb-0"
  },
  xH = n("div", {
    class: "col-md-auto"
  }, null, -1),
  SH = {
    class: "row"
  },
  EH = {
    class: "col-sm-12"
  },
  PH = n("div", {
    class: "card"
  }, [n("div", {
    class: "card-body py-0"
  }, [n("ul", {
    class: "nav nav-tabs profile-tabs",
    id: "myTab",
    role: "tablist"
  }, [n("li", {
    class: "nav-item"
  }, [n("a", {
    class: "nav-link active",
    id: "profile-tab-3",
    "data-bs-toggle": "tab",
    href: "#profile-3",
    role: "tab",
    "aria-selected": "true"
  }, [n("i", {
    class: "ti ti-id me-2"
  }), J("Personal Details ")])]), n("li", {
    class: "nav-item"
  }, [n("a", {
    class: "nav-link",
    id: "profile-tab-2",
    "data-bs-toggle": "tab",
    href: "#profile-2",
    role: "tab",
    "aria-selected": "true"
  }, [n("i", {
    class: "ti ti-file-text me-2"
  }), J("Verification Docs ")])]), n("li", {
    class: "nav-item"
  }, [n("a", {
    class: "nav-link",
    id: "profile-tab-4",
    "data-bs-toggle": "tab",
    href: "#profile-4",
    role: "tab",
    "aria-selected": "true"
  }, [n("i", {
    class: "ti ti-lock me-2"
  }), J("Change Password ")])]), n("li", {
    class: "nav-item"
  }, [n("a", {
    class: "nav-link",
    id: "profile-tab-5",
    "data-bs-toggle": "tab",
    href: "#profile-5",
    role: "tab",
    "aria-selected": "true"
  }, [n("i", {
    class: "ti ti-file-text me-2"
  }), J("Bank Details ")])])])])], -1),
  TH = {
    class: "tab-content"
  },
  LH = {
    class: "tab-pane show active",
    id: "profile-3",
    role: "tabpanel",
    "aria-labelledby": "profile-tab-3"
  },
  OH = {
    class: "tab-pane",
    id: "profile-2",
    role: "tabpanel",
    "aria-labelledby": "profile-tab-2"
  },
  IH = {
    class: "tab-pane",
    id: "profile-4",
    role: "tabpanel",
    "aria-labelledby": "profile-tab-4"
  },
  BH = {
    class: "tab-pane",
    id: "profile-5",
    role: "tabpanel",
    "aria-labelledby": "profile-tab-5"
  },
  DH = n("div", {
    class: "tab-pane",
    id: "profile-6",
    role: "tabpanel",
    "aria-labelledby": "profile-tab-6"
  }, null, -1);

function MH(e, t, s, o, i, r) {
  var m, g, $, C, A;
  const a = ke("PersonalDetailsView"),
    l = ke("DocumentsView"),
    d = ke("PasswordUpdateView"),
    p = ke("BankDetailsView");
  return _(), w("div", uH, [n("div", fH, [hH, n("div", pH, [n("div", mH, [n("div", gH, [_H, n("div", bH, [n("div", vH, [yH, n("div", wH, [n("div", $H, [n("div", CH, [n("h5", kH, L(i.clientName), 1), n("p", AH, "#" + L(r.generateNumber()), 1)]), xH])])])])]), n("div", SH, [n("div", EH, [PH, n("div", TH, [n("div", LH, [ie(a, {
    client: (m = i.profile_data) == null ? void 0 : m.client_details
  }, null, 8, ["client"])]), n("div", OH, [ie(l, {
    client_docs: (g = i.profile_data) == null ? void 0 : g.client_docs,
    doc_status: (C = ($ = i.profile_data) == null ? void 0 : $.client_details) == null ? void 0 : C.doc_verified
  }, null, 8, ["client_docs", "doc_status"])]), n("div", IH, [ie(d)]), n("div", BH, [ie(p, {
    bank_details: (A = i.profile_data) == null ? void 0 : A.bank_details
  }, null, 8, ["bank_details"])]), DH])])])])])])])
}
const RH = je(dH, [
    ["render", MH]
  ]),
  NH = "/assets/ib_banner-BzBUwwPx.png",
  FH = {
    setup() {
      const e = he(!1),
        t = Ge("API_BASE_URL"),
        s = localStorage.getItem("is_ib"),
        o = he(!1);
      return o.value = s != 0, {
        isLoading: e,
        requestIBStatus: async () => {
          try {
            const r = localStorage.getItem("auth_id"),
              a = $e.get("authToken");
            e.value = !0, await ye.post(`${t}/profile/request-ib`, {
              client_id: r
            }, {
              headers: {
                Authorization: `Bearer ${a}`
              }
            }).then(l => {
              var d;
              console.log(l), e.value = !1, o.value = !0, localStorage.setItem("is_ib", 1), Swal.fire({
                icon: "success",
                title: (d = l == null ? void 0 : l.data) == null ? void 0 : d.message
              }).then(p => {
                p.isConfirmed && window.location.reload()
              })
            }).catch(l => {
              var d;
              Swal.fire({
                icon: "error",
                title: (d = l.response) == null ? void 0 : d.data.message
              }).then(p => {
                p.isConfirmed && window.location.reload()
              })
            })
          } catch (r) {
            console.error("Error posting data:", r)
          }
        },
        isRequested: o
      }
    }
  },
  UH = {
    class: "pc-container"
  },
  VH = {
    class: "pc-content"
  },
  jH = {
    class: "row"
  },
  HH = {
    class: "card"
  },
  qH = n("div", {
    class: "card-header"
  }, [n("h4", {
    class: "mb-0"
  }, "Introducing Broker Program")], -1),
  WH = {
    class: "card-body"
  },
  zH = {
    class: "row"
  },
  KH = n("div", {
    class: "col-md-6 col-lg-6"
  }, [n("div", {
    class: "text-center mb-5"
  }, [n("img", {
    src: NH,
    alt: "Introducing Broker",
    class: "pt-4 mt-3",
    style: {
      width: "80%"
    }
  })])], -1),
  GH = {
    class: "col-md-6 col-lg-6"
  },
  YH = Ae('<h5 class="mb-4">Become an Introducing Broker with Succedo Markets</h5><p>Join our Introducing Broker (IB) program and unlock the potential to grow your business and increase your revenue. As a valued partner, you’ll gain access to our world-class trading technology, dedicated support, and competitive compensation structures.</p><h6>Benefits of Being an Introducing Broker:</h6><ul><li><strong>Attractive Rebates:</strong> Earn competitive rebates and commissions on the trading activity of clients you introduce.</li><li><strong>Marketing Support:</strong> Access a wide range of marketing materials and tools designed to help you attract and retain clients.</li><li><strong>Dedicated Manager:</strong> Receive personal support from a dedicated account manager who understands your business.</li><li><strong>Transparent Reporting:</strong> Use our robust reporting tools to track your success and optimize your strategies.</li></ul><h6>How to Become an Introducing Broker?</h6><p>Starting as an Introducing Broker with <?=SITE_TITLE ?>is simple. Follow these steps: </p><ol><li>Place a request</li><li>Receive confirmation and your unique IB link.</li><li>Start promoting <?=SITE_TITLE ?>and earn as your referrals trade!</li></ol>', 7),
  JH = {
    key: 0,
    class: "mt-4 mb-5"
  },
  ZH = ["disabled"],
  XH = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  QH = {
    key: 1,
    class: "badge bg-light-warning mt-4 mb-5"
  };

function eq(e, t, s, o, i, r) {
  return _(), w("div", UH, [n("div", VH, [n("div", jH, [n("div", HH, [qH, n("div", WH, [n("div", zH, [KH, n("div", GH, [YH, o.isRequested ? (_(), w("span", QH, "Pending Approval")) : (_(), w("div", JH, [n("button", {
    class: "btn btn-primary",
    disabled: o.isLoading,
    type: "button",
    onClick: t[0] || (t[0] = (...a) => o.requestIBStatus && o.requestIBStatus(...a))
  }, [o.isLoading ? (_(), w("span", XH)) : D("", !0), J(" " + L(o.isLoading ? "Requesting..." : "Enroll to Become Introducing Broker"), 1)], 8, ZH)]))])])])])])])])
}
const tq = je(FH, [
    ["render", eq]
  ]),
  sq = "/assets/ib_avatar-B4rFuWeE.webp",
  nq = {
    class: "row"
  },
  oq = {
    class: "col-12"
  },
  iq = {
    class: "card"
  },
  rq = {
    class: "card-body p-3"
  },
  aq = {
    class: "nav nav-pills nav-justified",
    role: "tablist"
  },
  lq = {
    class: "nav-item",
    "data-target-form": "#LEVEL1FORM",
    role: "presentation"
  },
  cq = ["onClick"],
  dq = n("i", {
    class: "ti ti-chart-bar me-2"
  }, null, -1),
  uq = {
    class: "d-none d-sm-inline"
  },
  fq = {
    class: "card"
  },
  hq = {
    class: "card-body"
  },
  pq = {
    class: "tab-content"
  },
  mq = {
    class: "tab-pane active",
    id: "#LEVEL1",
    role: "tabpanel"
  },
  gq = {
    class: "datatable-container"
  },
  _q = {
    class: "table table-hover datatable-table",
    id: "pc-dt-simple"
  },
  bq = n("thead", null, [n("tr", null, [n("th", {
    style: {
      width: "30%"
    }
  }, "CLIENT"), n("th", {
    class: "text-end",
    style: {
      width: "10%"
    }
  }, "TOTAL ACCOUNTS"), n("th", {
    class: "text-end",
    style: {
      width: "10%"
    }
  }, "TOTAL DEPOSIT"), n("th", {
    class: "text-end",
    style: {
      width: "10%"
    }
  }, "TOTAL LOTS"), n("th", {
    class: "text-end",
    style: {
      width: "10%"
    }
  }, "COMMISSION"), n("th", {
    class: "text-end",
    style: {
      width: "15%"
    }
  }, "PROFILE STATUS")])], -1),
  vq = {
    key: 0
  },
  yq = {
    "data-index": "0"
  },
  wq = {
    class: "row align-items-center"
  },
  $q = n("div", {
    class: "col-auto pe-0"
  }, [n("img", {
    src: sq,
    alt: "user-image",
    class: "wid-55 hei-55 rounded"
  })], -1),
  Cq = {
    class: "col"
  },
  kq = {
    class: "mb-2"
  },
  Aq = {
    class: "text-truncate w-100"
  },
  xq = {
    class: "text-muted f-12 mb-0"
  },
  Sq = {
    class: "text-truncate w-100"
  },
  Eq = {
    class: "text-end f-w-400"
  },
  Pq = {
    class: "f-w-400 text-end"
  },
  Tq = {
    class: "f-w-400 text-end"
  },
  Lq = n("i", {
    class: "fas fa-star text-warning ms-2"
  }, null, -1),
  Oq = {
    class: "f-w-400 text-end f-18"
  },
  Iq = n("td", {
    class: "text-end"
  }, [n("span", {
    class: "badge bg-success"
  }, "Active")], -1),
  Bq = {
    __name: "NewIBListingPage",
    props: {
      dashboardData: {
        type: Object,
        required: !0
      }
    },
    setup(e) {
      he([]);
      const t = he(0);
      he(null), he(0), he(0), he(0);
      const s = he(null),
        o = he(null),
        i = e;
      s.value = i.dashboardData.levels, o.value = i.dashboardData.level1clientsData, t.value = 0;
      const r = Ge("API_BASE_URL"),
        a = async l => {
          t.value = l, o.value = null;
          try {
            console.log("hgdshfh");
            const d = localStorage.getItem("auth_id"),
              p = $e.get("authToken"),
              m = await ye.post(`${r}/getIbLevelInfo`, {
                parent_id: i.dashboardData.levels[l],
                ib_id: d
              }, {
                headers: {
                  Authorization: `Bearer ${p}`
                }
              });
            console.log(m), o.value = m.data.levelclientsData
          } catch (d) {
            d.response && console.log(d)
          }
        };
      return At(() => {}), (l, d) => (_(), w("div", null, [n("div", nq, [n("div", oq, [n("div", iq, [n("div", rq, [n("ul", aq, [(_(!0), w(Te, null, Je(s.value, (p, m) => (_(), w("li", lq, [n("a", {
        href: "#LEVEL1",
        "data-bs-toggle": "tab",
        "data-toggle": "tab",
        class: Ie(["nav-link", {
          active: t.value === m
        }]),
        onClick: g => a(m),
        "aria-selected": "false",
        role: "tab",
        tabindex: "-1"
      }, [dq, n("span", uq, L(p.key.toString().toUpperCase()), 1)], 10, cq)]))), 256))])])]), n("div", fq, [n("div", hq, [n("div", pq, [n("div", mq, [n("div", gq, [n("table", _q, [bq, o.value ? (_(), w("tbody", vq, [(_(!0), w(Te, null, Je(o.value, p => (_(), w("tr", yq, [n("td", null, [n("div", wq, [$q, n("div", Cq, [n("h6", kq, [n("span", Aq, L(p.client_name), 1)]), n("p", xq, [n("span", Sq, L(p.email), 1)])])])]), n("td", Eq, L(p.total_mt_accounts), 1), n("td", Pq, L(Pt(Hs)(p.deposit)), 1), n("td", Tq, [J(L(p.total_lots), 1), Lq]), n("td", Oq, L(Pt(Hs)(p.total_commission)), 1), Iq]))), 256))])) : (_(), ve(Vs, {
        key: 1
      }))])])])])])])])])]))
    }
  },
  Dq = {
    class: "pc-container"
  },
  Mq = {
    class: "pc-content"
  },
  Rq = Ae('<div class="page-header mb-0 pb-0 mt-0 pt-0"><div class="page-block mb-0 pb-0 mt-0 pt-0"><div class="row align-items-center mb-0 pb-0 mt-0 pt-0"><div class="col-md-12 mb-0 pb-0 mt-0 pt-0"><div class="page-header-title h2"><h4 class="mb-0">MY IB PROFILE</h4></div></div></div></div></div>', 1),
  Nq = n("div", {
    class: "row mb-0 pb-0 mt-0 pt-0"
  }, [n("div", {
    class: "col-12 mb-0 pb-0 mt-0 pt-0"
  }, [n("div", {
    class: "card mb-0 pb-0 mt-0 pt-0"
  }, [n("div", {
    class: "card-body mb-0 pb-0 mt-0 pt-0"
  }, [n("div", {
    class: "row mb-0 pb-0 mt-0 pt-0"
  }, [n("ul", {
    class: "nav nav-tabs profile-tabs",
    id: "myTab",
    role: "tablist"
  }, [n("li", {
    class: "nav-item",
    role: "presentation"
  }, [n("a", {
    class: "nav-link active",
    id: "profile-tab-1",
    "data-bs-toggle": "tab",
    href: "#ib-home",
    role: "tab",
    "aria-selected": "true"
  }, [n("i", {
    class: "ti ti-smart-home me-2"
  }), J("IB Home ")])]), n("li", {
    class: "nav-item",
    role: "presentation"
  }, [n("a", {
    class: "nav-link",
    id: "profile-tab-2",
    "data-bs-toggle": "tab",
    href: "#ib-connect",
    role: "tab",
    "aria-selected": "false",
    tabindex: "-1"
  }, [n("i", {
    class: "ti ti-affiliate me-2"
  }), J("My Connections ")])])])])])])])], -1),
  Fq = {
    class: "tab-content mb-0 pb-0 mt-0 pt-0"
  },
  Uq = {
    class: "tab-pane pt-3 active show mb-0 pb-0 mt-0 pt-0",
    id: "ib-home",
    role: "tabpanel",
    "aria-labelledby": "profile-tab-1"
  },
  Vq = {
    class: "row mb-0 pb-0 mt-0 pt-0"
  },
  jq = {
    class: "col-lg-9 mb-0 pb-0 mt-0 pt-0"
  },
  Hq = {
    class: "card mb-0 pb-0 mt-0 pt-0"
  },
  qq = {
    class: "card-body mb-0 pb-3 mt-0 pt-3"
  },
  Wq = {
    class: "row g-3"
  },
  zq = {
    class: "col-md-6 col-xxl-3"
  },
  Kq = {
    class: "card mb-0"
  },
  Gq = {
    class: "card-body p-3"
  },
  Yq = {
    class: "d-flex align-items-center justify-content-between gap-1"
  },
  Jq = {
    class: "d-flex align-items-center gap-1"
  },
  Zq = {
    key: 0,
    class: "mb-0 f-w-500"
  },
  Xq = n("div", {
    class: "avtar avtar-s bg-light-primary"
  }, [n("i", {
    class: "ti ti-mood-kid f-18"
  })], -1),
  Qq = n("p", {
    class: "mb-0 text-muted d-flex align-items-center gap-2 f-12 mt-3"
  }, " Total Clients ", -1),
  eW = {
    class: "col-md-6 col-xxl-3"
  },
  tW = {
    class: "card mb-0"
  },
  sW = {
    class: "card-body p-3"
  },
  nW = {
    class: "d-flex align-items-center justify-content-between gap-1"
  },
  oW = {
    class: "d-flex align-items-center gap-1"
  },
  iW = {
    key: 0,
    class: "mb-0 f-w-500"
  },
  rW = n("div", {
    class: "avtar avtar-s bg-light-primary"
  }, [n("i", {
    class: "ti ti-database f-18"
  })], -1),
  aW = n("p", {
    class: "mb-0 text-muted d-flex align-items-center gap-2 f-12 mt-3"
  }, " Total Volume ", -1),
  lW = {
    class: "col-md-6 col-xxl-3"
  },
  cW = {
    class: "card mb-0"
  },
  dW = {
    class: "card-body p-3"
  },
  uW = {
    class: "d-flex align-items-center justify-content-between gap-1"
  },
  fW = {
    class: "d-flex align-items-center gap-1"
  },
  hW = {
    key: 0,
    class: "mb-0 f-w-500"
  },
  pW = n("div", {
    class: "avtar avtar-s bg-light-primary"
  }, [n("i", {
    class: "ti ti-report-money f-18"
  })], -1),
  mW = n("p", {
    class: "mb-0 text-muted d-flex align-items-center gap-2 f-12 mt-3"
  }, " Generated Commission ", -1),
  gW = {
    class: "col-md-6 col-xxl-3"
  },
  _W = {
    class: "card mb-0"
  },
  bW = {
    class: "card-body p-3"
  },
  vW = {
    class: "d-flex align-items-center justify-content-between gap-1"
  },
  yW = {
    class: "d-flex align-items-center gap-1"
  },
  wW = {
    key: 0,
    class: "mb-0 f-w-500"
  },
  $W = n("div", {
    class: "avtar avtar-s bg-light-primary"
  }, [n("i", {
    class: "ti ti-shield-check f-18"
  })], -1),
  CW = n("p", {
    class: "mb-0 text-muted d-flex align-items-center gap-2 f-12 mt-3"
  }, " Commission Transferred ", -1),
  kW = {
    class: "col-lg-3"
  },
  AW = {
    class: "card"
  },
  xW = {
    class: "card-body"
  },
  SW = {
    class: "row p-1"
  },
  EW = {
    class: "col-12"
  },
  PW = {
    class: "bg-body p-2 rounded"
  },
  TW = {
    class: "d-flex align-items-center justify-content-between"
  },
  LW = Ae('<div class="d-flex align-items-center"><div class="flex-shrink-0"><span class="p-1 d-block bg-primary rounded-circle"><span class="visually-hidden">New alerts</span></span></div><div class="flex-grow-1 ms-2"><p class="mb-0">Deposits</p></div></div>', 1),
  OW = {
    key: 0,
    class: "mb-0 f-w-500"
  },
  IW = {
    class: "row p-1"
  },
  BW = {
    class: "col-12"
  },
  DW = {
    class: "bg-body p-2 rounded"
  },
  MW = {
    class: "d-flex align-items-center justify-content-between"
  },
  RW = Ae('<div class="d-flex align-items-center"><div class="flex-shrink-0"><span class="p-1 d-block bg-primary rounded-circle"><span class="visually-hidden">New alerts</span></span></div><div class="flex-grow-1 ms-2"><p class="mb-0">Withdraw</p></div></div>', 1),
  NW = {
    key: 0,
    class: "mb-0 f-w-500"
  },
  FW = {
    class: "row mb-0 pb-0 mt-0 pt-0"
  },
  UW = {
    class: "col-xl-6 col-md-6 mb-0 pb-0 mt-0 pt-0"
  },
  VW = {
    class: "card mb-0 pb-0 mt-0 pt-0"
  },
  jW = {
    class: "card-body"
  },
  HW = {
    class: "d-flex align-items-center justify-content-between"
  },
  qW = n("h5", {
    class: "mb-0 f-w-500"
  }, "TRANSFER MY COMMISSION", -1),
  WW = {
    class: "bg-body p-1 mt-1 rounded"
  },
  zW = {
    class: "mt-1 row align-items-center"
  },
  KW = {
    class: "col-12 text-end"
  },
  GW = {
    key: 0,
    class: "mb-1 me-2 ms-2 f-w-500"
  },
  YW = n("p", {
    class: "text-warning mb-0 me-2 ms-2"
  }, " Transferrable Balance", -1),
  JW = n("hr", {
    style: {
      opacity: ".1"
    }
  }, null, -1),
  ZW = {
    class: "form"
  },
  XW = n("label", {
    class: "form-label mt-3",
    for: "exampleFormControlSelect1"
  }, "Select Account", -1),
  QW = {
    key: 0,
    class: "row"
  },
  ez = {
    class: "border card p-2"
  },
  tz = {
    class: "form-check mb-0"
  },
  sz = ["id", "value"],
  nz = ["for"],
  oz = {
    class: "h5 d-block"
  },
  iz = {
    class: "float-end badge bg-light-primary f-14 fw-medium"
  },
  rz = {
    key: 0
  },
  az = {
    key: 1
  },
  lz = n("span", {
    class: "text-muted mt-2 mb-0"
  }, [n("span", {
    class: "float-end text-muted mt-2 f-12"
  }, " Current Balance ")], -1),
  cz = {
    key: 2,
    class: "invalid-feedback mb-3",
    style: {
      display: "block !important"
    }
  },
  dz = {
    key: 1,
    class: "p-5 m-3"
  },
  uz = n("button", {
    class: "btn btn-outline-primary"
  }, [n("span", {
    class: "text-truncate w-100"
  }, "Create new Account")], -1),
  fz = n("label", {
    class: "form-label",
    for: "exampleFormControlSelect1"
  }, "Enter Amount", -1),
  hz = {
    class: "input-group mb-3"
  },
  pz = n("span", {
    class: "input-group-text"
  }, "$", -1),
  mz = n("span", {
    class: "input-group-text"
  }, ".00", -1),
  gz = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  _z = {
    class: "d-grid mb-5 mt-4"
  },
  bz = ["disabled"],
  vz = n("i", {
    class: "ti ti-shield-check me-2"
  }, null, -1),
  yz = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  wz = {
    class: "col-xl-6 col-md-6 mt-0 pt-0"
  },
  $z = {
    class: "card mt-0 pt-0"
  },
  Cz = {
    class: "card-body"
  },
  kz = Ae('<div class="d-flex align-items-center justify-content-between"><h5 class="mb-0 f-w-500">MY REFERRAL LINK</h5><div class="avtar avtar-s bg-light-primary"><i class="ti ti-list f-18"></i></div></div><hr style="opacity:.1;"><label class="col-form-label col-12 text-lg-start">Your exclusive referral link is ready! Share this link to invite potential clients to register under your supervision and start their trading journey.</label>', 3),
  Az = {
    class: "col-12 mb-4"
  },
  xz = {
    class: "input-group mb-2"
  },
  Sz = n("i", {
    class: "feather icon-copy"
  }, null, -1),
  Ez = [Sz],
  Pz = {
    key: 0,
    class: "popup"
  },
  Tz = {
    class: "card"
  },
  Lz = {
    class: "card-body pb-0"
  },
  Oz = Ae('<div class="d-flex align-items-center justify-content-between"><h4 class="mb-0">Transfer History</h4><div class="avtar avtar-s bg-light-primary"><i class="ti ti-list f-18"></i></div></div><hr style="opacity:.1;">', 2),
  Iz = {
    key: 0,
    class: "table-responsive"
  },
  Bz = {
    class: "table table-hover"
  },
  Dz = n("thead", null, [n("tr", null, [n("th", null, "TRANSFERRED TO"), n("th", null, "PROCESSED ON"), n("th", null, "AMOUNT"), n("th", null, "STATUS")])], -1),
  Mz = {
    key: 0
  },
  Rz = n("strong", null, "MT ", -1),
  Nz = {
    key: 1
  },
  Fz = n("strong", null, "WALLET", -1),
  Uz = [Fz],
  Vz = {
    key: 0
  },
  jz = n("span", {
    class: "badge bg-light-success"
  }, "Success", -1),
  Hz = [jz],
  qz = {
    key: 1
  },
  Wz = n("span", {
    class: "badge bg-light-danger"
  }, "Rejected", -1),
  zz = [Wz],
  Kz = {
    key: 0,
    class: "tab-pane pt-3",
    id: "ib-connect",
    role: "tabpanel",
    "aria-labelledby": "profile-tab-2"
  },
  Gz = {
    __name: "ibProfileView",
    setup(e) {
      const t = he(null),
        s = he(!1),
        o = he(null),
        i = he(null),
        r = he(null),
        a = he(null),
        l = he(0),
        d = he("https://succedo.paperboatsolutions.com/api/client"),
        p = he(!1),
        m = he({
          transferAmount: "",
          selectedAccount: ""
        });
      Ks(r, j => {
        R(j)
      }), Ks(o, j => {
        I(j)
      });
      const g = he(null),
        $ = he(""),
        C = async () => {
          try {
            const j = $e.get("authToken");
            if (d.value && j) {
              const q = await ye.get(`${d.value}/ib/level-data-list`, {
                headers: {
                  Authorization: `Bearer ${j}`
                }
              });
              g.value = q.data.result, $.value = "https://my.succedomarkets.com/register?promo=" + q.data.result.ib_key[0].ib_key
            } else throw new Error("Missing API_BASE_URL or authToken")
          } catch (j) {
            console.error("Error fetching live account data:", j), nt.fire({
              icon: "error",
              title: "Error fetching data",
              text: j.message || "An error occurred while fetching data"
            })
          }
        }, A = async () => {
          const j = me.getDataWithExpiration("liveAccounts");
          j ? i.value = j : await O();
          const q = me.getDataWithExpiration("commission_transfers");
          q ? a.value = q : await V()
        }, O = async () => {
          try {
            const j = $e.get("authToken");
            if (d.value && j) {
              const q = await ye.get(`${d.value}/live-accounts/data-list`, {
                headers: {
                  Authorization: `Bearer ${j}`
                }
              });
              i.value = q.data.result.mt_data, me.setDataWithTimestamp("liveAccounts", q.data.result.mt_data, 30), me.setDataWithTimestamp("total_balance", q.data.result.total_balance, 30)
            } else throw new Error("Missing API_BASE_URL or authToken")
          } catch (j) {
            console.error("Error fetching live account data:", j), nt.fire({
              icon: "error",
              title: "Error fetching data",
              text: j.message || "An error occurred while fetching data"
            })
          }
        }, V = async () => {
          try {
            const j = $e.get("authToken");
            if (d.value && j) {
              const q = await ye.get(`${d.value}/ib/commission-transfers`, {
                headers: {
                  Authorization: `Bearer ${j}`
                }
              });
              a.value = q.data.result.commission_transfers, me.setDataWithTimestamp("commission_transfers", q.data.result.commission_transfers, 30)
            } else throw new Error("Missing API_BASE_URL or authToken")
          } catch (j) {
            console.error("Error fetching live account data:", j), nt.fire({
              icon: "error",
              title: "Error fetching data",
              text: j.message || "An error occurred while fetching data"
            })
          }
        }, M = () => {
          navigator.clipboard.writeText($.value).then(() => {
            p.value = !0, setTimeout(() => p.value = !1, 2e3)
          }).catch(j => {
            console.error("Failed to copy: ", j)
          })
        }, B = () => {
          R(r.value) && I(o.value) && (P(), s.value = !0)
        }, P = async () => {
          var j;
          try {
            const q = localStorage.getItem("auth_id"),
              ne = $e.get("authToken"),
              ee = await ye.post(`${d.value}/ib/transfer-commission`, {
                to_account: r.value.mt_id,
                amount: Number(o.value)
              }, {
                headers: {
                  Authorization: `Bearer ${ne}`
                }
              });
            if (ee) {
              s.value = !1;
              const T = me.getDataWithExpiration("liveAccounts").map(re => re.mt_id === r.value.mt_id ? {
                ...re,
                balance: Number(r.value.balance) + Number(o.value),
                equity: Number(r.value.equity) + Number(o.value),
                margin_free: Number(r.value.margin_free) + Number(o.value)
              } : re);
              i.value = T, l.value += Number(o.value), me.setDataWithTimestamp("liveAccounts", T, 30), nt.fire({
                icon: "success",
                title: ee == null ? void 0 : ee.data.message
              })
            }
          } catch (q) {
            console.log(q), nt.fire({
              icon: "error",
              title: (j = q.response) == null ? void 0 : j.data.message
            }).then(ne => {
              ne.isConfirmed && window.location.reload()
            })
          }
        }, I = j => {
          var q;
          return j ? j > ((q = t.value) == null ? void 0 : q.balanceCommision) ? (m.value.transferAmount = "You dont have enough balance", !1) : (m.value.transferAmount = "", !0) : (m.value.transferAmount = "Please enter amount", !1)
        }, R = j => j ? (m.value.selectedAccount = "", !0) : (m.value.selectedAccount = "Please select an account", !1), F = j => {
          const q = {
              year: "numeric",
              month: "long",
              day: "numeric"
            },
            ne = new Date(j.replace(" ", "T"));
          return new Intl.DateTimeFormat("en-US", q).format(ne)
        };
      return At(() => {
        C(), A(), V()
      }), (j, q) => {
        var T;
        const ne = ke("LoadingSpinner"),
          ee = ke("EmptyStateView"),
          ge = ke("RouterLink");
        return _(), w("div", Dq, [n("div", Mq, [Rq, Nq, n("div", Fq, [n("div", Uq, [n("div", Vq, [n("div", jq, [n("div", Hq, [n("div", qq, [n("div", Wq, [n("div", zq, [n("div", Kq, [n("div", Gq, [n("div", Yq, [n("div", Jq, [g.value ? (_(), w("h3", Zq, L(g.value.total_clients), 1)) : (_(), ve(Vs, {
          key: 1
        }))]), Xq]), Qq])])]), n("div", eW, [n("div", tW, [n("div", sW, [n("div", nW, [n("div", oW, [g.value ? (_(), w("h5", iW, L(g.value.totalLots), 1)) : (_(), ve(Vs, {
          key: 1
        }))]), rW]), aW])])]), n("div", lW, [n("div", cW, [n("div", dW, [n("div", uW, [n("div", fW, [g.value ? (_(), w("h5", hW, L(Pt(Hs)(g.value.totalCommision)), 1)) : (_(), ve(Vs, {
          key: 1
        }))]), pW]), mW])])]), n("div", gW, [n("div", _W, [n("div", bW, [n("div", vW, [n("div", yW, [g.value ? (_(), w("h5", wW, L(Pt(Hs)(g.value.commisionWithdraw)), 1)) : (_(), ve(Vs, {
          key: 1
        }))]), $W]), CW])])])])])])]), n("div", kW, [n("div", AW, [n("div", xW, [n("div", SW, [n("div", EW, [n("div", PW, [n("div", TW, [LW, g.value ? (_(), w("h5", OW, L(Pt(Hs)(g.value.total_team_deposit)), 1)) : (_(), ve(Vs, {
          key: 1
        }))])])])]), n("div", IW, [n("div", BW, [n("div", DW, [n("div", MW, [RW, g.value ? (_(), w("h5", NW, L(Pt(Hs)(g.value.total_team_withdraw)), 1)) : (_(), ve(Vs, {
          key: 1
        }))])])])])])])])]), n("div", FW, [n("div", UW, [n("div", VW, [n("div", jW, [n("div", HW, [qW, n("div", WW, [n("div", zW, [n("div", KW, [g.value ? (_(), w("h3", GW, L(Pt(Hs)(g.value.balanceCommision)), 1)) : (_(), ve(Vs, {
          key: 1
        })), YW])])])]), JW, n("div", ZW, [XW, ((T = i.value) == null ? void 0 : T.length) > 0 ? (_(), w("div", QW, [i.value ? (_(!0), w(Te, {
          key: 0
        }, Je(i.value, re => (_(), w("div", {
          key: re.mt_id,
          class: "col-lg-6"
        }, [n("div", ez, [n("div", tz, [be(n("input", {
          type: "radio",
          name: "radio3",
          class: "form-check-input input-primary",
          id: re.mt_id,
          "onUpdate:modelValue": q[0] || (q[0] = Le => r.value = Le),
          value: re
        }, null, 8, sz), [
          [kt, r.value]
        ]), n("label", {
          class: "form-check-label d-block mb-0",
          for: re.mt_id
        }, [n("span", null, [n("span", oz, [n("span", iz, L(Pt(Hs)(re.balance)), 1), re.is_wallet != 1 ? (_(), w("span", rz, "MT " + L(re.mt_id), 1)) : (_(), w("span", az, "WALLET"))]), lz])], 8, nz)])])]))), 128)) : (_(), ve(ne, {
          key: 1
        })), m.value.selectedAccount ? (_(), w("div", cz, L(m.value.selectedAccount), 1)) : D("", !0)])) : (_(), w("div", dz, [ie(ee, {
          msg: "No Live Accounts Found"
        }), ie(ge, {
          to: "/createLiveAccount",
          class: "d-grid"
        }, {
          default: Ue(() => [uz]),
          _: 1
        })])), fz, n("div", hz, [pz, be(n("input", {
          "onUpdate:modelValue": q[1] || (q[1] = re => o.value = re),
          type: "text",
          class: "form-control",
          "aria-label": "Amount (to the nearest dollar)"
        }, null, 512), [
          [Re, o.value]
        ]), mz, m.value.transferAmount ? (_(), w("div", gz, L(m.value.transferAmount), 1)) : D("", !0)]), n("div", _z, [n("button", {
          class: "btn btn-outline-secondary",
          type: "button",
          disabled: s.value,
          onClick: B
        }, [vz, s.value ? (_(), w("span", yz)) : D("", !0), J(" " + L(s.value ? "Processing..." : "Process Transfer"), 1)], 8, bz)])])])])]), n("div", wz, [n("div", $z, [n("div", Cz, [kz, n("div", Az, [n("div", xz, [be(n("input", {
          type: "text",
          class: "form-control",
          id: "pc-clipboard-1",
          "onUpdate:modelValue": q[2] || (q[2] = re => $.value = re),
          placeholder: "Type some value to copy",
          disabled: ""
        }, null, 512), [
          [Re, $.value]
        ]), n("button", {
          onClick: M,
          class: "btn btn-lg btn-primary"
        }, Ez)]), p.value ? (_(), w("div", Pz, "Copied!")) : D("", !0)])])]), n("div", Tz, [n("div", Lz, [Oz, a.value ? (_(), w("div", Iz, [n("table", Bz, [Dz, n("tbody", null, [(_(!0), w(Te, null, Je(a.value, re => (_(), w("tr", null, [n("td", null, [re.is_wallet != 1 ? (_(), w("span", Mz, [Rz, J(L(re.mt_id), 1)])) : (_(), w("span", Nz, Uz))]), n("td", null, L(F(re.created_at)), 1), n("td", null, "$ " + L(re.amount), 1), re.status == 3 ? (_(), w("td", Vz, Hz)) : D("", !0), re.status == 2 ? (_(), w("td", qz, zz)) : D("", !0)]))), 256))])])])) : (_(), ve(ne, {
          key: 1
        }))])])])])]), g.value ? (_(), w("div", Kz, [ie(Bq, {
          "dashboard-data": g.value
        }, null, 8, ["dashboard-data"])])) : D("", !0)])])])
      }
    }
  },
  Yz = {
    setup() {
      const e = he(null),
        t = he([]),
        s = he([]),
        o = he(null),
        i = he(null),
        r = he(!1),
        a = he(0),
        l = he({
          selectedGroup: null,
          selectedLeverage: null,
          depositAmount: null
        }),
        d = Ge("API_BASE_URL"),
        p = ye.create({
          baseURL: d,
          headers: {
            Authorization: `Bearer ${$e.get("authToken")}`
          }
        }),
        m = async () => {
          r.value = !0;
          try {
            const B = await p.get("/demo-account/form-data");
            t.value = B.data.result.demo_mt_groups, s.value = B.data.result.leverage, me.setDataWithTimestamp("demo_mt_groups", t.value, 30), me.setDataWithTimestamp("leverages_data", s.value, 30)
          } catch (B) {
            M(B)
          } finally {
            r.value = !1
          }
        }, g = async () => {
          try {
            const B = await p.get("/dashboard");
            a.value = B.data.result.total_demo_account, me.setDataWithTimestamp("dashboard_data", B.data.result, 30)
          } catch (B) {
            M(B)
          }
        }, $ = () => {
          const B = me.getDataWithExpiration("demo_mt_groups"),
            P = me.getDataWithExpiration("leverages_data"),
            I = me.getDataWithExpiration("dashboard_data");
          B && (t.value = B), P && (s.value = P), I && (a.value = I.total_demo_account)
        };
      At(() => {
        $(), m(), g()
      });
      const C = () => {
          O(), A.value && V()
        },
        A = ss(() => a.value === 3 ? (nt.fire({
          icon: "error",
          title: "Failed to Create Account",
          text: "Only 3 Demo Accounts can be created "
        }), !1) : (l.value.selectedGroup = e.value ? "" : "Please select a Group", l.value.selectedLeverage = o.value ? "" : "Please select Leverage", l.value.depositAmount = i.value && i.value >= 10 && i.value <= 5e3 ? "" : "Please enter an Amount between $10 - $5000", !l.value.selectedGroup && !l.value.selectedLeverage && !l.value.depositAmount)),
        O = () => {
          A.value
        },
        V = async () => {
          r.value = !0;
          try {
            const B = await p.post("/demo-account/create", {
              group_id: e.value,
              amount: i.value,
              leverage: o.value
            });
            nt.fire({
              icon: "success",
              title: "Account Created Successfully",
              text: B.data.message
            }).then(P => {
              P.isConfirmed && window.location.reload()
            })
          } catch (B) {
            nt.fire({
              icon: "error",
              title: "Failed to Create Account",
              text: B.response.data.message || "An unexpected error occurred"
            })
          } finally {
            r.value = !1
          }
        }, M = B => {
          console.error("API Error:", B), nt.fire({
            icon: "error",
            title: "Error fetching data",
            text: B.message || "An unexpected error occurred"
          })
        };
      return {
        selectedGroup: e,
        groups: t,
        leverages: s,
        selectedLeverage: o,
        depositAmount: i,
        errors: l,
        total_mt_account: a,
        isLoading: r,
        submitForm: C
      }
    }
  },
  Jz = {
    class: "pc-container"
  },
  Zz = {
    class: "pc-content"
  },
  Xz = Ae('<div class="page-header mb-0 pb-0"><div class="page-block"><div class="row align-items-center"><div class="col-md-12"><div class="page-header-title h2"><h4 class="mb-0">Create DEMO MT5 Account</h4></div></div></div></div></div>', 1),
  Qz = {
    class: "row"
  },
  eK = {
    class: "col-sm-11"
  },
  tK = {
    class: "card"
  },
  sK = n("div", {
    class: "card-header"
  }, [n("h5", null, "SET UP YOUR ACCOUNT")], -1),
  nK = {
    class: "card-body"
  },
  oK = {
    xmlns: "http://www.w3.org/2000/svg",
    style: {
      display: "none"
    }
  },
  iK = n("symbol", {
    id: "exclamation-triangle-fill",
    fill: "currentColor",
    viewBox: "0 0 16 16"
  }, [n("path", {
    d: "M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
  })], -1),
  rK = [iK],
  aK = {
    class: "form-group mb-0"
  },
  lK = {
    class: "row"
  },
  cK = n("div", {
    class: "col-3"
  }, [n("label", {
    class: "form-label"
  }, "Choose Account Type")], -1),
  dK = {
    class: "col-9"
  },
  uK = {
    key: 0,
    class: "row"
  },
  fK = {
    class: "auth-option"
  },
  hK = ["id", "value"],
  pK = ["for"],
  mK = {
    class: "d-block m-4"
  },
  gK = {
    class: "h5 d-block"
  },
  _K = n("strong", {
    class: "float-end"
  }, [n("span", {
    class: "badge bg-light-primary"
  }, "DEMO")], -1),
  bK = n("span", {
    class: "h6 d-block mt-4 f-w-400 f-12"
  }, " Provides direct access to market participants, featuring tight spreads and faster execution, suitable for advanced traders. ", -1),
  vK = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  yK = {
    key: 1,
    class: "d-flex justify-content-center py-5"
  },
  wK = n("div", {
    class: "spinner-border",
    role: "status"
  }, [n("span", {
    class: "sr-only"
  }, "Loading...")], -1),
  $K = [wK],
  CK = {
    class: "row mt-3"
  },
  kK = n("div", {
    class: "col-3"
  }, [n("label", {
    class: "form-label"
  }, "Select Leverage")], -1),
  AK = {
    class: "col-9"
  },
  xK = ["value"],
  SK = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  EK = {
    class: "row mt-3"
  },
  PK = n("div", {
    class: "col-3"
  }, " Amount to be deposited in Demo Account ", -1),
  TK = {
    class: "col-9"
  },
  LK = {
    class: "input-group mb-3"
  },
  OK = n("span", {
    class: "input-group-text"
  }, "$", -1),
  IK = n("span", {
    class: "input-group-text"
  }, ".00", -1),
  BK = {
    key: 0,
    class: "invalid-feedback",
    style: {
      display: "block !important"
    }
  },
  DK = {
    class: "row mt-3"
  },
  MK = n("div", {
    class: "col-3"
  }, null, -1),
  RK = {
    class: "col-9"
  },
  NK = {
    class: "d-grid gap-2 mt-2"
  },
  FK = ["disabled"],
  UK = n("i", {
    class: "ti ti-plus me-2"
  }, null, -1),
  VK = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  };

function jK(e, t, s, o, i, r) {
  var a, l, d, p;
  return _(), w("div", Jz, [n("div", Zz, [Xz, n("div", Qz, [n("div", eK, [n("div", tK, [sK, n("div", nK, [(_(), w("svg", oK, rK)), n("form", null, [n("div", aK, [n("div", lK, [cK, n("div", dK, [o.groups ? (_(), w("div", uK, [(_(!0), w(Te, null, Je(o.groups, m => (_(), w("div", {
    class: "col-sm-6",
    key: m.id
  }, [n("div", fK, [be(n("input", {
    type: "radio",
    class: "btn-check",
    name: "options",
    id: "option" + m.id,
    "onUpdate:modelValue": t[0] || (t[0] = g => o.selectedGroup = g),
    value: m.id
  }, null, 8, hK), [
    [kt, o.selectedGroup]
  ]), n("label", {
    class: "auth-megaoption",
    for: "option" + m.id,
    style: {
      height: "140px !important"
    }
  }, [n("div", mK, [n("span", null, [n("span", gK, [_K, J(" " + L(m.group_category), 1)]), bK])])], 8, pK)])]))), 128)), o.errors.selectedGroup ? (_(), w("div", vK, L((l = (a = o.errors.selectedGroup) == null ? void 0 : a.value) == null ? void 0 : l.replace(/^"(.*)"$/, "$1")), 1)) : D("", !0)])) : (_(), w("div", yK, $K))])]), n("div", CK, [kK, n("div", AK, [be(n("select", {
    class: "form-select",
    "onUpdate:modelValue": t[1] || (t[1] = m => o.selectedLeverage = m)
  }, [(_(!0), w(Te, null, Je(o.leverages, m => (_(), w("option", {
    key: m.id,
    value: m.leverage
  }, L(`1:${m.leverage}`), 9, xK))), 128))], 512), [
    [Xn, o.selectedLeverage]
  ]), o.errors.selectedLeverage ? (_(), w("div", SK, L((p = (d = o.errors.selectedLeverage) == null ? void 0 : d.value) == null ? void 0 : p.replace(/^"(.*)"$/, "$1")), 1)) : D("", !0)])]), n("div", EK, [PK, n("div", TK, [n("div", LK, [OK, be(n("input", {
    "onUpdate:modelValue": t[2] || (t[2] = m => o.depositAmount = m),
    type: "text",
    class: "form-control",
    "aria-label": "Amount (to the nearest dollar)"
  }, null, 512), [
    [Re, o.depositAmount]
  ]), IK, o.errors.depositAmount ? (_(), w("div", BK, L(o.errors.depositAmount), 1)) : D("", !0)])])]), n("div", DK, [MK, n("div", RK, [n("div", NK, [n("button", {
    class: "btn btn-lg btn-primary",
    disabled: o.isLoading,
    type: "button",
    onClick: t[3] || (t[3] = (...m) => o.submitForm && o.submitForm(...m))
  }, [UK, o.isLoading ? (_(), w("span", VK)) : D("", !0), J(" " + L(o.isLoading ? "Creating..." : "Create Account"), 1)], 8, FK)])])])])])])])])])])])
}
const HK = je(Yz, [
    ["render", jK]
  ]),
  qK = Dt({
    components: {
      LeverageUpdateView: F1,
      PasswordUpdateView: U1
    },
    setup() {
      const e = he([]),
        t = he(!0),
        s = he(!0),
        o = he({}),
        r = Wi().currentRoute.value.params.mt_id,
        a = async () => {
          try {
            const $ = Ge("API_BASE_URL"),
              C = $e.get("authToken");
            if ($ && C) {
              const A = await ye.get(`${$}/demo-accounts/data-list`, {
                headers: {
                  Authorization: `Bearer ${C}`
                }
              });
              e.value = A.data.result.mt_data, me.setDataWithTimestamp("demoAccounts", A.data.result.mt_data, 30);
              const O = A.data.result.mt_data.filter(V => V.mt_id === Number(r));
              o.value = O[0], console.log(o.value)
            } else throw new Error("Missing API_BASE_URL or authToken")
          } catch ($) {
            console.error("Error fetching data:", $), nt.fire({
              icon: "error",
              title: "Error fetching data",
              text: $.message || "An error occurred while fetching data"
            })
          }
        }, l = $ => {
          const C = me.getDataWithExpiration("demoAccounts");
          if (console.log(C), C) {
            const A = C.filter(O => O.mt_id === Number($));
            o.value = A[0]
          }
        };
      At(() => {
        l(r), a()
      });
      const d = () => {
          s.value = !0;
          var $ = new bootstrap.Modal(document.getElementById("updateLeverageModal"));
          $.show()
        },
        p = () => {
          p.value = !0;
          var $ = new bootstrap.Modal(document.getElementById("passwordupdatemodal"));
          $.show()
        };
      return {
        account_details: o,
        fetchAccountDetails: a,
        showUpdateLeverageModal: d,
        showPasswordUpdateModal: p,
        closeModalHandler: () => {
          console.log("dasfs"), t.value = !1;
          var $ = new bootstrap.Modal(document.getElementById("updateLeverageModal"));
          $.hide()
        },
        closePassModalHandler: () => {
          console.log("dasfspass"), s.value = !1;
          var $ = new bootstrap.Modal(document.getElementById("passwordupdatemodal"));
          $.hide()
        },
        showModal: t,
        showPassModel: s
      }
    }
  }),
  WK = {
    class: "pc-container"
  },
  zK = {
    class: "pc-content"
  },
  KK = Ae('<div class="page-header"><div class="page-block"><div class="row align-items-center"><div class="col-md-12"><div class="page-header-title h5"><h2 class="mb-0">MT5 Details</h2></div></div></div></div></div>', 1),
  GK = {
    class: "row"
  },
  YK = {
    class: "col-sm-12"
  },
  JK = {
    class: "card"
  },
  ZK = {
    class: "card-body"
  },
  XK = {
    class: "row g-3"
  },
  QK = {
    class: "col-12"
  },
  eG = {
    class: "alert alert-secondary mb-3 d-print-none"
  },
  tG = {
    class: "row align-items-center g-3"
  },
  sG = {
    class: "col-sm-6"
  },
  nG = {
    class: "row align-items-center"
  },
  oG = n("div", {
    class: "col-auto pe-0"
  }, [n("img", {
    src: R1,
    alt: "user-image",
    class: "wid-60 hei-60 rounded"
  })], -1),
  iG = {
    class: "col"
  },
  rG = {
    class: "mb-0 f-w-500"
  },
  aG = {
    class: "text-truncate"
  },
  lG = {
    class: "text-muted f-12 mb-0"
  },
  cG = {
    class: "text-truncate w-100"
  },
  dG = Ae('<div class="col-sm-6 text-sm-end"><ul class="list-inline ms-auto mb-0 d-flex justify-content-end flex-wrap"><li class="list-inline-item"><div class="card mb-0"><div class="card-body p-2 mb-0"><div class="d-flex justify-content-between"><div class="me-2"><img src="' + Mt + '" alt="user-image" class="wid-20"></div><div><p class="mb-0 text-opacity-75">Launch Web Trader </p></div></div></div></div></li></ul></div>', 1),
  uG = {
    class: "row"
  },
  fG = {
    class: "col-sm-6"
  },
  hG = {
    class: "card bg-gray-800 dropbox-card"
  },
  pG = {
    class: "card-body"
  },
  mG = n("div", {
    class: "d-flex align-items-center justify-content-between"
  }, [n("p", {
    class: "text-white"
  }, "Balance")], -1),
  gG = {
    class: "d-flex align-items-center"
  },
  _G = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s"
  }, [n("i", {
    class: "ph-duotone ph-briefcase f-20"
  })])], -1),
  bG = {
    class: "flex-grow-1 ms-3"
  },
  vG = {
    class: "row g-1"
  },
  yG = {
    class: "col-6"
  },
  wG = {
    key: 0,
    class: "text-white mb-0 f-w-500"
  },
  $G = n("div", {
    class: "col-6 text-end"
  }, [n("button", {
    class: "btn btn-outline-light btn-print-invoice"
  }, "Quick Deposit")], -1),
  CG = {
    class: "row g-3"
  },
  kG = {
    class: "col-sm-6"
  },
  AG = {
    class: "bg-body p-3 rounded"
  },
  xG = Ae('<div class="d-flex align-items-center mb-2"><div class="flex-shrink-0"><i class="ph-duotone ph-file-cloud f-20"></i></div><div class="flex-grow-1 ms-2"><p class="mb-0">Server</p></div></div>', 1),
  SG = {
    key: 0,
    class: "mb-0 f-w-400"
  },
  EG = {
    class: "col-sm-6"
  },
  PG = {
    class: "bg-body p-3 rounded"
  },
  TG = Ae('<div class="d-flex align-items-center mb-2"><div class="flex-shrink-0"><i class="ph-duotone ph-cactus f-20"></i></div><div class="flex-grow-1 ms-2"><p class="mb-0">Credit</p></div></div>', 1),
  LG = {
    key: 0,
    class: "mb-0 f-w-400"
  },
  OG = {
    class: "col-sm-6"
  },
  IG = {
    class: "bg-body p-3 rounded"
  },
  BG = Ae('<div class="d-flex align-items-center mb-2"><div class="flex-shrink-0"><i class="ph-duotone ph-hand-coins f-20"></i></div><div class="flex-grow-1 ms-2"><p class="mb-0">Leverage</p></div></div>', 1),
  DG = {
    class: "row g-1"
  },
  MG = {
    class: "col-4"
  },
  RG = {
    key: 0,
    class: "mb-0 f-w-400"
  },
  NG = {
    class: "col-8 text-end f-12"
  },
  FG = {
    class: "col-sm-6"
  },
  UG = {
    class: "bg-body p-3 rounded"
  },
  VG = Ae('<div class="d-flex align-items-center mb-2"><div class="flex-shrink-0"><i class="ph-duotone ph-swap f-20"></i></div><div class="flex-grow-1 ms-2"><p class="mb-0">Swap</p></div></div>', 1),
  jG = {
    key: 0,
    class: "mb-0 f-w-400"
  },
  HG = {
    class: "col-sm-6"
  },
  qG = {
    class: "list-group list-group-flush"
  },
  WG = {
    class: "list-group-item"
  },
  zG = {
    class: "d-flex align-items-center"
  },
  KG = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s border"
  }, [n("i", {
    class: "ph-duotone ph-chart-line-up f-20"
  })])], -1),
  GG = {
    class: "flex-grow-1 ms-3"
  },
  YG = {
    class: "row g-1"
  },
  JG = n("div", {
    class: "col-6"
  }, [n("p", {
    class: "text-muted mb-0 f-20"
  }, [n("small", null, "Equity")])], -1),
  ZG = {
    class: "col-6 text-end"
  },
  XG = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  QG = {
    class: "list-group-item"
  },
  eY = {
    class: "d-flex align-items-center"
  },
  tY = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s border"
  }, [n("i", {
    class: "ph-duotone ph-butterfly f-20"
  })])], -1),
  sY = {
    class: "flex-grow-1 ms-3"
  },
  nY = {
    class: "row g-1"
  },
  oY = n("div", {
    class: "col-6"
  }, [n("p", {
    class: "text-muted mb-0 f-20"
  }, [n("small", null, "Free Margin")])], -1),
  iY = {
    class: "col-6 text-end"
  },
  rY = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  aY = {
    class: "list-group-item"
  },
  lY = {
    class: "d-flex align-items-center"
  },
  cY = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s border"
  }, [n("i", {
    class: "ph-duotone ph-chart-pie f-20"
  })])], -1),
  dY = {
    class: "flex-grow-1 ms-3"
  },
  uY = {
    class: "row g-1"
  },
  fY = n("div", {
    class: "col-6"
  }, [n("p", {
    class: "text-muted mb-0 f-20"
  }, [n("small", null, "Margin")])], -1),
  hY = {
    class: "col-6 text-end"
  },
  pY = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  mY = {
    class: "list-group-item"
  },
  gY = {
    class: "d-flex align-items-center"
  },
  _Y = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s border"
  }, [n("i", {
    class: "ph-duotone ph-chart-pie-slice f-20"
  })])], -1),
  bY = {
    class: "flex-grow-1 ms-3"
  },
  vY = {
    class: "row g-1"
  },
  yY = n("div", {
    class: "col-6"
  }, [n("p", {
    class: "text-muted mb-0 f-20"
  }, [n("small", null, "Margin Level")])], -1),
  wY = {
    class: "col-6 text-end"
  },
  $Y = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  CY = {
    class: "list-group-item"
  },
  kY = {
    class: "d-flex align-items-center"
  },
  AY = n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s border"
  }, [n("i", {
    class: "ph-duotone ph-line-segments f-20"
  })])], -1),
  xY = {
    class: "flex-grow-1 ms-3"
  },
  SY = {
    class: "row g-1"
  },
  EY = n("div", {
    class: "col-6"
  }, [n("p", {
    class: "text-muted mb-0 f-20"
  }, [n("small", null, "Floating PL")])], -1),
  PY = {
    class: "col-6 text-end"
  },
  TY = {
    key: 0,
    class: "mb-1 f-w-400"
  },
  LY = {
    class: "row"
  },
  OY = {
    class: "col-sm-6"
  },
  IY = {
    class: "row mt-3"
  },
  BY = Ae('<div class="col-sm-6"><div class="card bg-white"><div class="card-body p-3"><div class="d-flex align-items-center justify-content-between"><div><h4 class="mb-0">Internal </h4><p class="mb-0 text-opacity-75">Transfer </p></div><div class="avtar avtar-s border"><i class="ph-duotone ph-shuffle f-24"></i></div></div></div></div></div>', 1),
  DY = {
    class: "col-sm-6"
  },
  MY = Ae('<div class="card bg-white"><div class="card-body p-3"><div class="d-flex align-items-center justify-content-between"><div><h4 class="mb-0">Password </h4><p class="mb-0 text-opacity-75">Update </p></div><div class="avtar avtar-s border"><i class="ph-duotone ph-password f-24"></i></div></div></div></div>', 1),
  RY = [MY],
  NY = Ae('<div class="col-sm-6"><div class="row mt-3"><div class="col-sm-6"><div class="card bg-primary available-balance-card"><div class="card-body p-3"><div class="d-flex align-items-center justify-content-between"><div><h4 class="mb-0 text-white">Deposit</h4><p class="mb-0 text-white text-opacity-75">Fund your account </p></div><div class="avtar"><i class="ph-duotone ph-credit-card f-24"></i></div></div></div></div></div><div class="col-sm-6"><div class="card bg-secondary available-balance-card"><div class="card-body p-3"><div class="d-flex align-items-center justify-content-between"><div><h4 class="mb-0 text-white">Withdraw</h4><p class="mb-0 text-white text-opacity-75">Transfer your profits </p></div><div class="avtar"><i class="ph-duotone ph-bank f-24"></i></div></div></div></div></div></div></div>', 1),
  FY = {
    key: 0,
    class: ""
  },
  UY = {
    key: 1,
    class: ""
  };

function VY(e, t, s, o, i, r) {
  const a = ke("SmallLoadingSpinner"),
    l = ke("LeverageUpdateView"),
    d = ke("PasswordUpdateView");
  return _(), w("div", WK, [n("div", zK, [KK, n("div", GK, [n("div", YK, [n("div", JK, [n("div", ZK, [n("div", XK, [n("div", QK, [n("div", eG, [n("div", tG, [n("div", sG, [n("div", nG, [oG, n("div", iG, [n("h2", rG, [n("span", aG, L(e.$route.params.mt_id), 1)]), n("p", lG, [n("span", cG, L(e.account_details && e.account_details.groupname ? e.account_details.groupname : ""), 1)])])])]), dG])])])]), n("div", uG, [n("div", fG, [n("div", hG, [n("div", pG, [mG, n("div", gG, [_G, n("div", bG, [n("div", vG, [n("div", yG, [e.account_details ? (_(), w("h3", wG, L(e.account_details && e.account_details.balance ? e.account_details.balance : "0") + " $ ", 1)) : (_(), ve(a, {
    key: 1
  }))]), $G])])])])]), n("div", CG, [n("div", kG, [n("div", AG, [xG, e.account_details ? (_(), w("h5", SG, L(e.account_details && e.account_details.server ? e.account_details.server : ""), 1)) : (_(), ve(a, {
    key: 1
  }))])]), n("div", EG, [n("div", PG, [TG, e.account_details ? (_(), w("h5", LG, "$ " + L(e.account_details && e.account_details.credit ? e.account_details.credit : 0), 1)) : (_(), ve(a, {
    key: 1
  }))])]), n("div", OG, [n("div", IG, [BG, n("div", DG, [n("div", MG, [e.account_details ? (_(), w("h5", RG, "1:" + L(e.account_details && e.account_details.leverage ? e.account_details.leverage : 0), 1)) : (_(), ve(a, {
    key: 1
  }))]), n("div", NG, [n("a", {
    href: "#!",
    onClick: t[0] || (t[0] = (...p) => e.showUpdateLeverageModal && e.showUpdateLeverageModal(...p)),
    class: "card-link"
  }, "Update Leverage")])])])]), n("div", FG, [n("div", UG, [VG, e.account_details ? (_(), w("h5", jG, L(e.account_details && e.account_details.swap && e.account_details.swap === 1 ? "Yes" : "FREE"), 1)) : (_(), ve(a, {
    key: 1
  }))])])])]), n("div", HG, [n("ul", qG, [n("li", WG, [n("div", zG, [KG, n("div", GG, [n("div", YG, [JG, n("div", ZG, [e.account_details ? (_(), w("h4", XG, "$ " + L(e.account_details && e.account_details.equity ? e.account_details.equity : 0), 1)) : (_(), ve(a, {
    key: 1
  }))])])])])]), n("li", QG, [n("div", eY, [tY, n("div", sY, [n("div", nY, [oY, n("div", iY, [e.account_details ? (_(), w("h4", rY, "$ " + L(e.account_details && e.account_details.margin ? e.account_details.margin : 0), 1)) : (_(), ve(a, {
    key: 1
  }))])])])])]), n("li", aY, [n("div", lY, [cY, n("div", dY, [n("div", uY, [fY, n("div", hY, [e.account_details ? (_(), w("h4", pY, "$ " + L(e.account_details && e.account_details.margin ? e.account_details.margin : 0), 1)) : (_(), ve(a, {
    key: 1
  }))])])])])]), n("li", mY, [n("div", gY, [_Y, n("div", bY, [n("div", vY, [yY, n("div", wY, [e.account_details ? (_(), w("h4", $Y, L(e.account_details && e.account_details.margin_level ? e.account_details.margin_level : 0) + " %", 1)) : (_(), ve(a, {
    key: 1
  }))])])])])]), n("li", CY, [n("div", kY, [AY, n("div", xY, [n("div", SY, [EY, n("div", PY, [e.account_details ? (_(), w("h4", TY, L(e.account_details && e.account_details.floating ? e.account_details.floating : 0), 1)) : (_(), ve(a, {
    key: 1
  }))])])])])])])])]), n("div", LY, [n("div", OY, [n("div", IY, [BY, n("div", DY, [n("a", {
    href: "#!",
    onClick: t[1] || (t[1] = (...p) => e.showPasswordUpdateModal && e.showPasswordUpdateModal(...p))
  }, RY)])])]), NY])])])])])]), e.showModal ? (_(), w("div", FY, [e.account_details ? (_(), ve(l, {
    key: 0,
    leverage: e.account_details.leverage,
    mt_id: e.account_details.mt_id,
    onCloseModal: e.closeModalHandler
  }, null, 8, ["leverage", "mt_id", "onCloseModal"])) : D("", !0)])) : D("", !0), e.showPassModel ? (_(), w("div", UY, [e.account_details ? (_(), ve(d, {
    key: 0,
    leverage: e.account_details.leverage,
    mt_id: e.account_details.mt_id,
    onCloseModal: e.closePassModalHandler
  }, null, 8, ["leverage", "mt_id", "onCloseModal"])) : D("", !0)])) : D("", !0)])
}
const jY = je(qK, [
    ["render", VY]
  ]),
  HY = "/assets/doc_upload-tuKFdIoz.png",
  qY = {
    data() {
      return {
        preview: [null, null, null],
        currentTab: 1,
        isLoading: !1,
        fileData: [null, null, null],
        errorMessage: ["", "", ""]
      }
    },
    methods: {
      moveNext() {
        this.currentTab < 4 && (this.currentTab === 1 ? this.currentTab++ : this.currentTab === 2 ? (this.fileData[0] === null && (this.errorMessage[0] = "Mandatory Document"), this.fileData[1] === null && (this.errorMessage[1] = "Mandatory Document"), this.errorMessage[0] === "" && this.errorMessage[1] === "" && this.currentTab++) : this.currentTab === 3 && (this.fileData[2] === null && (this.errorMessage[2] = "Mandatory Document"), this.errorMessage[0] === "" && this.errorMessage[1] === "" && this.errorMessage[2] === "" && this.handleSubmitClick()))
      },
      previewFile(e, t) {
        const s = e.target.files[0];
        if (!s) return;
        if (this.preview[t] = null, this.errorMessage[t] = "", !["application/pdf", "image/png", "image/jpeg"].includes(s.type)) {
          this.errorMessage[t] = "Invalid file type. Only PDF, PNG, and JPEG are allowed.";
          return
        }
        if (s.size > 2 * 1024 * 1024) {
          this.errorMessage[t] = "File is too large. The size limit is 2MB.";
          return
        }
        this.fileData[t] = s, this.preview[t] = URL.createObjectURL(s)
      },
      handleSubmitClick() {
        const e = new FormData,
          t = $e.get("authToken");
        e.append("poi", this.fileData[0]), e.append("poi1", this.fileData[1]), e.append("poa", this.fileData[2]), this.isLoading = !0, ye.post(this.API_BASE_URL + "/profile/add-docs", e, {
          headers: {
            "Content-Type": "multipart/form-data",
            Authorization: `Bearer ${t}`
          }
        }).then(s => {
          this.isLoading = !1, this.$toast(s == null ? void 0 : s.data.message, {
            type: "success"
          }), os.push("/dashboard")
        }).catch(s => {
          this.isLoading = !1, Swal.fire({
            icon: "error",
            title: s.response.data.message
          }).then(o => {
            o.isConfirmed && window.location.reload()
          })
        })
      }
    },
    computed: {
      isPDF() {
        return e => this.fileData[e] && this.fileData[e].type === "application/pdf"
      },
      isImage() {
        return e => this.fileData[e] && this.fileData[e].type.includes("image")
      }
    }
  },
  Zt = e => (No("data-v-d6f2db71"), e = e(), Fo(), e),
  WY = {
    class: "pc-container mt-0 mb-0 pb-0 pt-0"
  },
  zY = {
    class: "pc-content mt-0 mb-0 pb-0 pt-0"
  },
  KY = Ae('<div class="page-header mb-0 pb-0 mt-0 pt-0" data-v-d6f2db71><div class="page-block" data-v-d6f2db71><div class="row align-items-center" data-v-d6f2db71><div class="col-md-12" data-v-d6f2db71><div class="page-header-title h2" data-v-d6f2db71><h4 class="mb-0" data-v-d6f2db71>Document Upload</h4></div></div></div></div></div>', 1),
  GY = {
    class: "row mb-0 pb-0 mt-0 pt-0"
  },
  YY = {
    class: "col-sm-12 mb-0 pb-0 mt-0 pt-0"
  },
  JY = {
    id: "basicwizard",
    class: "form-wizard row justify-content-center mb-0 pb-0 mt-0 pt-0"
  },
  ZY = {
    class: "col-12 mb-0 pb-0 mt-0 pt-0"
  },
  XY = {
    class: "card mb-2 mt-2 pt-0"
  },
  QY = {
    class: "card-body p-2"
  },
  eJ = {
    class: "nav nav-pills nav-justified"
  },
  tJ = {
    class: "nav-item"
  },
  sJ = Zt(() => n("i", {
    class: "ph-duotone ph-chat-teardrop-text"
  }, null, -1)),
  nJ = Zt(() => n("span", {
    class: "d-none d-sm-inline"
  }, "Instructions", -1)),
  oJ = [sJ, nJ],
  iJ = {
    class: "nav-item"
  },
  rJ = Zt(() => n("i", {
    class: "ph-duotone ph-user-list"
  }, null, -1)),
  aJ = Zt(() => n("span", {
    class: "d-none d-sm-inline"
  }, "Proof of Identity", -1)),
  lJ = [rJ, aJ],
  cJ = {
    class: "nav-item",
    "data-target-form": "#educationDetailForm"
  },
  dJ = Zt(() => n("i", {
    class: "ph-duotone ph-map-pin-line"
  }, null, -1)),
  uJ = Zt(() => n("span", {
    class: "d-none d-sm-inline"
  }, "Proof of Address", -1)),
  fJ = [dJ, uJ],
  hJ = {
    class: "card"
  },
  pJ = {
    class: "card-body"
  },
  mJ = {
    class: "tab-content"
  },
  gJ = Zt(() => n("form", {
    id: "contactForm",
    method: "post",
    action: "#"
  }, [n("div", {
    class: "row mt-3"
  }, [n("div", {
    class: "col-sm-auto text-center col-lg-4"
  }, [n("div", {
    class: "text-center me-3"
  }, [n("img", {
    src: HY,
    alt: "user-image",
    class: "rounded img-fluid ms-2 mt-3",
    style: {
      width: "80%"
    }
  })])]), n("div", {
    class: "col-sm-auto col-lg-8"
  }, [n("div", {
    class: "text-muted ms-4 mb-4"
  }, [n("h3", {
    class: "mb-2"
  }, "Instructions to Upload Your Documents"), n("small", {
    class: "text-muted"
  }, "Your document security is important to us. All files uploaded through this portal are securely stored.")]), n("div", {
    class: "row"
  }, [n("div", {
    class: "col-sm-6"
  }, [n("ul", {
    class: "list-group list-group-flush"
  }, [n("li", {
    class: "list-group-item"
  }, [n("div", {
    class: "d-flex align-items-center"
  }, [n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s bg-light-success"
  }, [n("i", {
    class: "ti ti-shield-check f-20"
  })])]), n("div", {
    class: "flex-grow-1 ms-3"
  }, [n("div", {
    class: "row g-1"
  }, [n("div", {
    class: "col-12"
  }, [n("p", {
    class: "text-muted mb-1"
  }, " Acceptable formats: PDF, JPG, JPEG, PNG. ")])])])])]), n("li", {
    class: "list-group-item"
  }, [n("div", {
    class: "d-flex align-items-center"
  }, [n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s bg-light-success"
  }, [n("i", {
    class: "ti ti-shield-check f-20"
  })])]), n("div", {
    class: "flex-grow-1 ms-3"
  }, [n("div", {
    class: "row g-1"
  }, [n("div", {
    class: "col-12"
  }, [n("p", {
    class: "text-muted mb-1"
  }, " Each file size should not exceed 5 MB. ")])])])])]), n("li", {
    class: "list-group-item"
  }, [n("div", {
    class: "d-flex align-items-center"
  }, [n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s bg-light-success"
  }, [n("i", {
    class: "ti ti-shield-check f-20"
  })])]), n("div", {
    class: "flex-grow-1 ms-3"
  }, [n("div", {
    class: "row g-1"
  }, [n("div", {
    class: "col-12"
  }, [n("p", {
    class: "text-muted mb-1"
  }, " Documents must be clear and legible. ")])])])])])])]), n("div", {
    class: "col-sm-6"
  }, [n("ul", {
    class: "list-group list-group-flush"
  }, [n("li", {
    class: "list-group-item"
  }, [n("div", {
    class: "d-flex align-items-center"
  }, [n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s bg-light-success"
  }, [n("i", {
    class: "ti ti-shield-check f-20"
  })])]), n("div", {
    class: "flex-grow-1 ms-3"
  }, [n("div", {
    class: "row g-1"
  }, [n("div", {
    class: "col-12"
  }, [n("p", {
    class: "text-muted mb-1"
  }, " Must be a government-issued identity card. ")])])])])]), n("li", {
    class: "list-group-item"
  }, [n("div", {
    class: "d-flex align-items-center"
  }, [n("div", {
    class: "flex-shrink-0"
  }, [n("div", {
    class: "avtar avtar-s bg-light-success"
  }, [n("i", {
    class: "ti ti-shield-check f-20"
  })])]), n("div", {
    class: "flex-grow-1 ms-3"
  }, [n("div", {
    class: "row g-1"
  }, [n("div", {
    class: "col-12"
  }, [n("p", {
    class: "text-muted mb-1"
  }, " Document must be NON-EXPIRED ")])])])])])])])])])])], -1)),
  _J = [gJ],
  bJ = {
    id: "jobForm",
    method: "post",
    action: "#"
  },
  vJ = {
    class: "row"
  },
  yJ = Zt(() => n("div", {
    class: "text-muted"
  }, [n("h3", {
    class: "mb-2"
  }, "Upload Your Proof of Identity"), n("small", {
    class: "text-danger"
  }, "BOTH FILES ARE MANDATORY. If your document includes both the front and back sides in a single file, please upload the same file in both fields.")], -1)),
  wJ = {
    class: "col-6"
  },
  $J = {
    class: "row mt-4"
  },
  CJ = {
    class: "col-12"
  },
  kJ = Zt(() => n("div", {
    class: "text-muted"
  }, [n("h5", {
    class: "mb-1"
  }, "Front Side"), n("small", {
    class: "text-muted mb-2"
  }, "Upload the front side of your ID")], -1)),
  AJ = {
    key: 0,
    class: "preview mt-2"
  },
  xJ = ["src"],
  SJ = ["src"],
  EJ = {
    key: 1,
    class: "alert alert-danger mt-2"
  },
  PJ = {
    class: "col-6"
  },
  TJ = {
    class: "row mt-4"
  },
  LJ = {
    class: "col-12"
  },
  OJ = Zt(() => n("div", {
    class: "text-muted"
  }, [n("h5", {
    class: "mb-1"
  }, "Back Side"), n("small", {
    class: "text-muted mb-2"
  }, "Upload the Back Side of your ID")], -1)),
  IJ = {
    key: 0,
    class: "preview mt-2"
  },
  BJ = ["src"],
  DJ = ["src"],
  MJ = {
    key: 1,
    class: "alert alert-danger mt-2"
  },
  RJ = {
    class: "row"
  },
  NJ = Zt(() => n("div", {
    class: "text-muted"
  }, [n("h3", {
    class: "mb-2"
  }, "Upload Your Proof of Address"), n("small", {
    class: "text-danger"
  }, "Please Upload a document to verify Your Proof of Address")], -1)),
  FJ = {
    class: "col-6"
  },
  UJ = {
    class: "row mt-4"
  },
  VJ = {
    class: "col-12"
  },
  jJ = Zt(() => n("div", {
    class: "text-muted"
  }, [n("h5", {
    class: "mb-1"
  }, "Front Side"), n("small", {
    class: "text-muted mb-2"
  }, "Upload the front side of your ID")], -1)),
  HJ = {
    key: 0,
    class: "preview mt-2"
  },
  qJ = ["src"],
  WJ = ["src"],
  zJ = {
    key: 1,
    class: "alert alert-danger mt-2"
  },
  KJ = {
    class: "d-flex wizard justify-content-between mt-4"
  },
  GJ = Zt(() => n("div", {
    class: "first"
  }, null, -1)),
  YJ = {
    class: "d-flex"
  },
  JJ = {
    class: "next"
  },
  ZJ = {
    key: 0
  },
  XJ = {
    key: 1
  },
  QJ = ["disabled"],
  eZ = {
    key: 0,
    class: "spinner-grow spinner-grow-sm",
    role: "status"
  },
  tZ = Zt(() => n("div", {
    class: "last"
  }, null, -1));

function sZ(e, t, s, o, i, r) {
  return _(), w("div", WY, [n("div", zY, [KY, n("div", GY, [n("div", YY, [n("div", JY, [n("div", ZY, [n("div", XY, [n("div", QY, [n("ul", eJ, [n("li", tJ, [n("a", {
    href: "#contactDetail",
    class: Ie(["nav-link", i.currentTab === 1 ? "active" : "icon-btn"])
  }, oJ, 2)]), n("li", iJ, [n("a", {
    href: "#jobDetail",
    class: Ie(["nav-link", i.currentTab === 2 ? "active" : "icon-btn"])
  }, lJ, 2)]), n("li", cJ, [n("a", {
    href: "#educationDetail",
    class: Ie(["nav-link", i.currentTab === 3 ? "active" : "icon-btn"])
  }, fJ, 2)])])])]), n("div", hJ, [n("div", pJ, [n("div", mJ, [n("div", {
    class: Ie([{
      "tab-pane show active": i.currentTab === 1
    }, "tab-pane"]),
    id: "contactDetail"
  }, _J, 2), n("div", {
    class: Ie([{
      "tab-pane show active": i.currentTab === 2
    }, "tab-pane"]),
    id: "jobDetail"
  }, [n("form", bJ, [n("div", vJ, [yJ, n("div", wJ, [n("div", $J, [n("div", CJ, [kJ, n("input", {
    type: "file",
    onChange: t[0] || (t[0] = a => r.previewFile(a, 0)),
    accept: "application/pdf,image/png,image/jpeg",
    class: "form-control"
  }, null, 32), i.preview[0] ? (_(), w("div", AJ, [r.isImage(0) ? (_(), w("img", {
    key: 0,
    src: i.preview[0],
    alt: "Preview Image",
    class: "img-fluid",
    style: {
      "max-height": "220px",
      width: "auto",
      "max-width": "100%"
    }
  }, null, 8, xJ)) : D("", !0), r.isPDF(0) ? (_(), w("iframe", {
    key: 1,
    src: i.preview[0],
    class: "file-preview w-100",
    style: {
      height: "30vh"
    }
  }, null, 8, SJ)) : D("", !0)])) : D("", !0), i.errorMessage[0] ? (_(), w("div", EJ, L(i.errorMessage[0]), 1)) : D("", !0)])])]), n("div", PJ, [n("div", TJ, [n("div", LJ, [OJ, n("input", {
    type: "file",
    onChange: t[1] || (t[1] = a => r.previewFile(a, 1)),
    accept: "application/pdf,image/png,image/jpeg",
    class: "form-control"
  }, null, 32), i.preview[1] ? (_(), w("div", IJ, [r.isImage(1) ? (_(), w("img", {
    key: 0,
    src: i.preview[1],
    alt: "Preview Image",
    class: "img-fluid",
    style: {
      "max-height": "220px",
      width: "auto",
      "max-width": "100%"
    }
  }, null, 8, BJ)) : D("", !0), r.isPDF(1) ? (_(), w("iframe", {
    key: 1,
    src: i.preview[1],
    class: "file-preview",
    style: {
      height: "30vh"
    }
  }, null, 8, DJ)) : D("", !0)])) : D("", !0), i.errorMessage[1] ? (_(), w("div", MJ, L(i.errorMessage[1]), 1)) : D("", !0)])])])])])], 2), n("div", {
    class: Ie([{
      "tab-pane show active": i.currentTab === 3
    }, "tab-pane"]),
    id: "educationDetail"
  }, [n("div", RJ, [NJ, n("div", FJ, [n("div", UJ, [n("div", VJ, [jJ, n("input", {
    type: "file",
    onChange: t[2] || (t[2] = a => r.previewFile(a, 2)),
    accept: "application/pdf,image/png,image/jpeg",
    class: "form-control"
  }, null, 32), i.preview[2] ? (_(), w("div", HJ, [r.isImage(2) ? (_(), w("img", {
    key: 0,
    src: i.preview[2],
    alt: "Preview Image",
    class: "img-fluid",
    style: {
      "max-height": "220px",
      width: "auto",
      "max-width": "100%"
    }
  }, null, 8, qJ)) : D("", !0), r.isPDF(2) ? (_(), w("iframe", {
    key: 1,
    src: i.preview[2],
    class: "file-preview w-100",
    style: {
      height: "30vh"
    }
  }, null, 8, WJ)) : D("", !0)])) : D("", !0), i.errorMessage[2] ? (_(), w("div", zJ, L(i.errorMessage[2]), 1)) : D("", !0)])])])])], 2), n("div", KJ, [GJ, n("div", YJ, [n("div", JJ, [i.currentTab != 3 ? (_(), w("button", {
    key: 0,
    onClick: t[3] || (t[3] = (...a) => r.moveNext && r.moveNext(...a)),
    href: "javascript:void(0);",
    class: "btn btn-primary mt-3 mt-md-0"
  }, [i.currentTab === 1 ? (_(), w("div", ZJ, "Upload Documents")) : D("", !0), i.currentTab === 2 ? (_(), w("div", XJ, "Next")) : D("", !0)])) : D("", !0), i.currentTab === 3 ? (_(), w("button", {
    key: 1,
    class: "btn btn-primary mt-3",
    type: "submit",
    disabled: i.isLoading,
    onClick: t[4] || (t[4] = (...a) => r.moveNext && r.moveNext(...a))
  }, [i.isLoading ? (_(), w("span", eZ)) : D("", !0), J(" " + L(i.isLoading ? "Uploading..." : "Upload Documents"), 1)], 8, QJ)) : D("", !0)])]), tZ])])])])])])])])])])
}
const nZ = je(qY, [
    ["render", sZ],
    ["__scopeId", "data-v-d6f2db71"]
  ]),
  os = S$({
    history: o$("/"),
    routes: [{
      path: "/",
      redirect: "/dashboard"
    }, {
      path: "/login",
      name: "Login",
      component: C5,
      meta: {
        public: !0
      }
    }, {
      path: "/register",
      name: "Register",
      component: U8,
      meta: {
        public: !0
      },
      beforeEnter: (e, t, s) => {
        e.query.promo && $e.set("promoCode", e.query.promo, {
          expires: 3
        }), s()
      }
    }, {
      path: "/app/auth/signup",
      redirect: e => {
        const t = e.query.promo;
        return console.log("Redirecting with promo:", t), $e.set("promoCode", t, {
          expires: 3
        }), {
          path: "/register",
          query: {
            promo: t
          }
        }
      }
    }, {
      path: "/verify",
      name: "Verify",
      component: P6,
      meta: {
        public: !0
      }
    }, {
      path: "/email-success",
      name: "EmailSuccess",
      component: a9,
      props: !0,
      meta: {
        public: !0
      }
    }, {
      path: "/dashboard",
      name: "Dashboard",
      component: zC,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/fund/deposit/:option",
      name: "Fund",
      component: sV,
      props: !0,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/transactions/deposit/:option",
      name: "Transactions",
      component: xx,
      props: !0,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/wallet",
      name: "Wallet",
      component: FO,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/liveAccounts",
      name: "LiveAccounts",
      component: eS,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/createLiveAccount",
      name: "CreateLiveMT5",
      component: mE,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/createDemoAccount",
      name: "CreateDemoMT5",
      component: HK,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/demoAccounts",
      name: "DemoAccounts",
      component: hS,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/mt5Details/:mt_id",
      name: "MT5Details",
      component: zL,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/mt5DemoDetails/:mt_id",
      name: "MT5DemoDetails",
      component: jY,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/user/profile",
      name: "ClientProfile",
      component: RH,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/user/documentUpload",
      name: "DocumentUploadView",
      component: nZ,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/ib",
      name: "IntroduceIbView",
      component: tq,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/ib-profile",
      name: "IbProfileView",
      component: Gz,
      meta: {
        requiresAuth: !0
      }
    }, {
      path: "/:pathMatch(.*)*",
      redirect: () => $e.get("authToken") ? "/dashboard" : "/register"
    }]
  });
os.beforeEach((e, t, s) => {
  const o = nV.isAuthenticated(),
    i = e.meta.requiresAuth;
  window.scrollTo(0, 0), i && !o ? s("/login") : s()
});
const oZ = "/assets/paperboat_light_logo-OGLNelFf.png",
  iZ = "/assets/bitrage_light_logo-CtV_c85h.png",
  rZ = "/assets/succedo_logo_light-BP0eFhae.svg",
  aZ = {
    data() {
      return {
        dark_flag: !1,
        fullPage: !0,
        clientName: "",
        clientEmail: "",
        loader: null,
        companySlug: "succedo"
      }
    },
    setup() {
      const e = P$();
      return {
        isCurrentRoute: s => e.name === s
      }
    },
    mounted() {
      const e = document.getElementById("logout-link"),
        t = document.getElementById("logout-link-2");
      e.addEventListener("click", this.confirmLogout), t.addEventListener("click", this.confirmLogout);
      const s = localStorage.getItem("client_name"),
        o = localStorage.getItem("email");
      s && (this.clientName = s), o && (this.clientEmail = o)
    },
    methods: {
      generateNumber() {
        let t = 1e4 - localStorage.getItem("auth_id") + 1e3;
        return String(t).padStart(5, "0")
      },
      async confirmLogout(e) {
        e.preventDefault(), (await nt.fire({
          icon: "question",
          text: "Are you sure you want to logout?",
          showCancelButton: !0,
          confirmButtonText: "Yes",
          cancelButtonText: "No"
        })).isConfirmed && this.logout()
      },
      async logout() {
        this.loader = this.$loading.show({
          container: this.fullPage ? null : this.$refs.formContainer,
          canCancel: !1,
          opacity: .7,
          color: "#F4A01E"
        });
        try {
          const e = localStorage.getItem("auth_id"),
            t = $e.get("authToken");
          (await ye.post(this.API_BASE_URL + "/logout", {
            user_id: e
          }, {
            headers: {
              Authorization: `Bearer ${t}`
            }
          })).data.status && ($e.remove("authToken"), localStorage.clear(), this.loader.hide(), os.push("/login"))
        } catch {
          $e.remove("authToken"), localStorage.clear(), this.loader.hide(), os.push("/login")
        }
      },
      layout_change(e) {
        var t = document.querySelector(".pct-offcanvas");
        document.getElementsByTagName("body")[0].setAttribute("data-pc-theme", e);
        var s = document.querySelector('.theme-layout .btn[data-value="default"]');
        if (s && s.classList.remove("active"), e == "dark") {
          this.dark_flag = !0, document.querySelector(".pc-sidebar .m-header .logo-lg") && document.querySelector(".pc-sidebar .m-header .logo-lg").setAttribute("src", "../assets/images/bitrage_logo_white.png"), document.querySelector(".navbar-brand .logo-lg") && document.querySelector(".navbar-brand .logo-lg").setAttribute("src", "../assets/images/logo-white.svg"), document.querySelector(".auth-main.v1 .auth-sidefooter") && document.querySelector(".auth-main.v1 .auth-sidefooter img").setAttribute("src", "../assets/images/logo-white.svg"), document.querySelector(".footer-top .footer-logo") && document.querySelector(".footer-top .footer-logo").setAttribute("src", "../assets/images/logo-white.svg");
          var t = document.querySelector(".theme-layout .btn.active");
          t && (document.querySelector(".theme-layout .btn.active").classList.remove("active"), document.querySelector(".theme-layout .btn[data-value='false']").classList.add("active"))
        } else {
          this.dark_flag = !1, document.querySelector(".pc-sidebar .m-header .logo-lg") && document.querySelector(".pc-sidebar .m-header .logo-lg").setAttribute("src", "../assets/images/logo-dark.svg"), document.querySelector(".navbar-brand .logo-lg") && document.querySelector(".navbar-brand .logo-lg").setAttribute("src", "../assets/images/logo-dark.svg"), document.querySelector(".auth-main.v1 .auth-sidefooter") && document.querySelector(".auth-main.v1 .auth-sidefooter img").setAttribute("src", "../assets/images/logo-dark.svg"), document.querySelector(".footer-top .footer-logo") && document.querySelector(".footer-top .footer-logo").setAttribute("src", "../assets/images/logo-dark.svg");
          var t = document.querySelector(".theme-layout .btn.active");
          t && (document.querySelector(".theme-layout .btn.active").classList.remove("active"), document.querySelector(".theme-layout .btn[data-value='true']").classList.add("active"))
        }
      }
    },
    components: {
      RouterLink: Mu
    }
  },
  lZ = {
    class: "pc-sidebar"
  },
  cZ = {
    class: "navbar-wrapper"
  },
  dZ = {
    class: "m-header"
  },
  uZ = {
    key: 0,
    href: " /dashboard",
    class: "b-brand text-primary"
  },
  fZ = {
    key: 0,
    src: gl,
    class: "img-fluid logo-lg",
    alt: "logo",
    style: {
      width: "70%"
    }
  },
  hZ = {
    key: 1,
    src: oZ,
    class: "img-fluid logo-lg",
    alt: "logo",
    style: {
      width: "70%"
    }
  },
  pZ = n("span", {
    class: "badge bg-light-success rounded-pill ms-2 theme-version"
  }, "v1.0", -1),
  mZ = {
    key: 1,
    href: " /dashboard",
    class: "b-brand text-primary"
  },
  gZ = {
    key: 0,
    src: Gi,
    class: "img-fluid logo-lg",
    alt: "logo",
    style: {
      width: "70%"
    }
  },
  _Z = {
    key: 1,
    src: iZ,
    class: "img-fluid logo-lg",
    alt: "logo",
    style: {
      width: "70%"
    }
  },
  bZ = n("span", {
    class: "badge bg-light-success rounded-pill ms-2 theme-version"
  }, "v1.0", -1),
  vZ = {
    key: 2,
    href: " /dashboard",
    class: "b-brand text-primary"
  },
  yZ = {
    key: 0,
    src: ml,
    class: "img-fluid logo-lg",
    alt: "logo",
    style: {
      width: "70%"
    }
  },
  wZ = {
    key: 1,
    src: rZ,
    class: "img-fluid logo-lg",
    alt: "logo",
    style: {
      width: "70%"
    }
  },
  $Z = n("span", {
    class: "badge bg-light-primary rounded-pill ms-2 theme-version"
  }, "v1.0", -1),
  CZ = {
    class: "navbar-content"
  },
  kZ = {
    class: "card pc-user-card"
  },
  AZ = {
    class: "card-body"
  },
  xZ = {
    class: "d-flex align-items-center"
  },
  SZ = n("div", {
    class: "flex-shrink-0"
  }, [n("img", {
    src: wl,
    alt: "user-image",
    class: "user-avtar wid-70 rounded-circle"
  })], -1),
  EZ = {
    class: "flex-grow-1 ms-3 me-2"
  },
  PZ = {
    class: "mb-0"
  },
  TZ = n("a", {
    class: "btn btn-icon btn-link-secondary avtar",
    "data-bs-toggle": "collapse",
    href: "#pc_sidebar_userlink"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-sort-outline"
  })])], -1),
  LZ = {
    class: "collapse pc-user-links",
    id: "pc_sidebar_userlink"
  },
  OZ = {
    class: "pt-3"
  },
  IZ = n("i", {
    class: "ti ti-user"
  }, null, -1),
  BZ = n("span", null, "My Account", -1),
  DZ = n("a", {
    href: "#!",
    id: "logout-link"
  }, [n("i", {
    class: "ti ti-power"
  }), n("span", null, "Logout")], -1),
  MZ = {
    class: "pc-navbar"
  },
  RZ = n("li", {
    class: "pc-item pc-caption"
  }, [n("label", null, "Navigation")], -1),
  NZ = n("span", {
    class: "pc-micon"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-status-up"
  })])], -1),
  FZ = n("span", {
    class: "pc-mtext"
  }, "Dashboard", -1),
  UZ = n("span", {
    class: "pc-micon"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-box-1"
  })])], -1),
  VZ = n("span", {
    class: "pc-mtext"
  }, "Fund", -1),
  jZ = n("span", {
    class: "pc-micon"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-keyboard"
  })])], -1),
  HZ = n("span", {
    class: "pc-mtext"
  }, "Transactions", -1),
  qZ = n("span", {
    class: "pc-micon"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-security-safe"
  })])], -1),
  WZ = n("span", {
    class: "pc-mtext"
  }, "My Wallet", -1),
  zZ = n("span", {
    class: "pc-micon"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-shield"
  })])], -1),
  KZ = n("span", {
    class: "pc-mtext"
  }, "Live MT5 Accounts", -1),
  GZ = n("span", {
    class: "pc-badge"
  }, [n("i", {
    class: "ti ti-chart-line"
  })], -1),
  YZ = n("span", {
    class: "pc-micon"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-setting-outline"
  })])], -1),
  JZ = n("span", {
    class: "pc-mtext"
  }, "Demo MT5 Accounts", -1),
  ZZ = n("span", {
    class: "pc-badge"
  }, [n("i", {
    class: "ti ti-circles"
  })], -1),
  XZ = {
    class: "pc-header"
  },
  QZ = {
    class: "header-wrapper"
  },
  eX = n("div", {
    class: "me-auto pc-mob-drp"
  }, [n("ul", {
    class: "list-unstyled"
  }, [n("li", {
    class: "pc-h-item pc-sidebar-collapse"
  }, [n("a", {
    href: "#",
    class: "pc-head-link ms-0",
    id: "sidebar-hide"
  }, [n("i", {
    class: "ti ti-menu-2"
  })])]), n("li", {
    class: "pc-h-item pc-sidebar-popup"
  }, [n("a", {
    href: "#",
    class: "pc-head-link ms-0",
    id: "mobile-collapse"
  }, [n("i", {
    class: "ti ti-menu-2"
  })])]), n("li", {
    class: "dropdown pc-h-item"
  }, [n("a", {
    class: "pc-head-link dropdown-toggle arrow-none m-0 trig-drp-search",
    "data-bs-toggle": "dropdown",
    href: "#",
    role: "button",
    "aria-haspopup": "false",
    "aria-expanded": "false"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-search-normal-1"
  })])]), n("div", {
    class: "dropdown-menu pc-h-dropdown drp-search"
  }, [n("form", {
    class: "px-3 py-2"
  }, [n("input", {
    type: "search",
    class: "form-control border-0 shadow-none",
    placeholder: "Search here. . ."
  })])])])])], -1),
  tX = {
    class: "ms-auto"
  },
  sX = {
    class: "list-unstyled"
  },
  nX = {
    class: "dropdown pc-h-item"
  },
  oX = n("a", {
    class: "pc-head-link dropdown-toggle arrow-none me-0",
    "data-bs-toggle": "dropdown",
    href: "#",
    role: "button",
    "aria-haspopup": "false",
    "aria-expanded": "false"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-sun-1"
  })])], -1),
  iX = {
    class: "dropdown-menu dropdown-menu-end pc-h-dropdown"
  },
  rX = n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-moon"
  })], -1),
  aX = n("span", null, "Dark", -1),
  lX = [rX, aX],
  cX = n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-sun-1"
  })], -1),
  dX = n("span", null, "Light", -1),
  uX = [cX, dX],
  fX = n("li", {
    class: "dropdown pc-h-item"
  }, [n("a", {
    class: "pc-head-link dropdown-toggle arrow-none me-0",
    "data-bs-toggle": "dropdown",
    href: "#",
    role: "button",
    "aria-haspopup": "false",
    "aria-expanded": "false"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-setting-2"
  })])]), n("div", {
    class: "dropdown-menu dropdown-menu-end pc-h-dropdown"
  }, [n("a", {
    href: "#!",
    class: "dropdown-item"
  }, [n("i", {
    class: "ti ti-user"
  }), n("span", null, "My Account")]), n("a", {
    href: "#!",
    class: "dropdown-item"
  }, [n("i", {
    class: "ti ti-settings"
  }), n("span", null, "Settings")]), n("a", {
    href: "#!",
    class: "dropdown-item"
  }, [n("i", {
    class: "ti ti-headset"
  }), n("span", null, "Support")]), n("a", {
    href: "#!",
    class: "dropdown-item",
    id: "logout-link-2"
  }, [n("i", {
    class: "ti ti-power"
  }), n("span", null, "Logout")])])], -1),
  hX = n("li", {
    class: "pc-h-item"
  }, [n("a", {
    href: "#",
    class: "pc-head-link me-0",
    "data-bs-toggle": "offcanvas",
    "data-bs-target": "#announcement",
    "aria-controls": "announcement"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-flash"
  })])])], -1),
  pX = n("li", {
    class: "dropdown pc-h-item"
  }, [n("a", {
    class: "pc-head-link dropdown-toggle arrow-none me-0",
    "data-bs-toggle": "dropdown",
    href: "#",
    role: "button",
    "aria-haspopup": "false",
    "aria-expanded": "false"
  }, [n("svg", {
    class: "pc-icon"
  }, [n("use", {
    "xlink:href": "#custom-notification"
  })]), n("span", {
    class: "badge bg-success pc-h-badge"
  }, "3")]), n("div", {
    class: "dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown"
  }, [n("div", {
    class: "dropdown-header d-flex align-items-center justify-content-between"
  }, [n("h5", {
    class: "m-0"
  }, "Notifications"), n("a", {
    href: "#!",
    class: "btn btn-link btn-sm"
  }, "Mark all read")]), n("div", {
    class: "dropdown-body text-wrap header-notification-scroll position-relative",
    style: {
      "max-height": "calc(100vh - 215px)"
    }
  }, [n("p", {
    class: "text-span"
  }, "Today"), n("div", {
    class: "card mb-2"
  }, [n("div", {
    class: "card-body"
  }, [n("div", {
    class: "d-flex"
  }, [n("div", {
    class: "flex-shrink-0"
  }, [n("svg", {
    class: "pc-icon text-primary"
  }, [n("use", {
    "xlink:href": "#custom-layer"
  })])]), n("div", {
    class: "flex-grow-1 ms-3"
  }, [n("span", {
    class: "float-end text-sm text-muted"
  }, "19 April. Friday"), n("h5", {
    class: "text-body mb-2"
  }, "We've Upgraded Our Client Portal!"), n("p", {
    class: "mb-0"
  }, "We're excited to announce that our client portal has been upgraded! We've introduced several enhancements to improve functionality and provide you with a more intuitive and streamlined experience.")])])])])]), n("div", {
    class: "text-center py-2"
  }, [n("a", {
    href: "#!",
    class: "link-danger"
  }, "Clear all Notifications")])])], -1),
  mX = {
    class: "dropdown pc-h-item header-user/profile"
  },
  gX = n("a", {
    class: "pc-head-link dropdown-toggle arrow-none me-0",
    "data-bs-toggle": "dropdown",
    href: "#",
    role: "button",
    "aria-haspopup": "false",
    "data-bs-auto-close": "outside",
    "aria-expanded": "false"
  }, [n("img", {
    src: wl,
    alt: "user-image",
    class: "user-avtar"
  })], -1),
  _X = {
    class: "dropdown-menu dropdown-user/profile dropdown-menu-end pc-h-dropdown"
  },
  bX = n("div", {
    class: "dropdown-header d-flex align-items-center justify-content-between"
  }, [n("h5", {
    class: "m-0"
  }, "Profile")], -1),
  vX = {
    class: "dropdown-body"
  },
  yX = {
    class: "profile-notification-scroll position-relative",
    style: {
      "max-height": "calc(100vh - 225px)"
    }
  },
  wX = {
    class: "d-flex mb-1"
  },
  $X = n("div", {
    class: "flex-shrink-0"
  }, [n("img", {
    src: wl,
    alt: "user-image",
    class: "user-avtar wid-35"
  })], -1),
  CX = {
    class: "flex-grow-1 ms-3"
  },
  kX = {
    class: "mb-1"
  },
  AX = n("hr", {
    class: "border-secondary border-opacity-50"
  }, null, -1),
  xX = {
    class: "card"
  },
  SX = {
    class: "card-body py-3"
  },
  EX = n("div", {
    class: "d-flex align-items-center justify-content-between"
  }, [n("h6", {
    class: "mb-0 d-inline-flex align-items-center"
  }, [n("svg", {
    class: "pc-icon text-muted me-2"
  }, [n("use", {
    "xlink:href": "#custom-user"
  })]), J("My Profile")])], -1),
  PX = n("hr", {
    class: "border-secondary border-opacity-50"
  }, null, -1),
  TX = {
    class: "d-grid mb-3"
  },
  LX = n("svg", {
    class: "pc-icon me-2"
  }, [n("use", {
    "xlink:href": "#custom-logout-1-outline"
  })], -1),
  OX = {
    class: "offcanvas pc-announcement-offcanvas offcanvas-end",
    tabindex: "-1",
    id: "announcement",
    "aria-labelledby": "announcementLabel"
  },
  IX = n("div", {
    class: "offcanvas-header"
  }, [n("h5", {
    class: "offcanvas-title",
    id: "announcementLabel"
  }, "What's new announcement?"), n("button", {
    type: "button",
    class: "btn-close",
    "data-bs-dismiss": "offcanvas",
    "aria-label": "Close"
  })], -1),
  BX = {
    class: "offcanvas-body"
  },
  DX = n("p", {
    class: "text-span"
  }, "Today", -1),
  MX = {
    class: "card mb-3"
  },
  RX = {
    class: "card-body"
  },
  NX = Ae('<div class="align-items-center d-flex flex-wrap gap-2 mb-3"><div class="badge bg-light-success f-12">Big News</div><p class="mb-0 text-muted">2 min ago</p><span class="badge dot bg-warning"></span></div><h5 class="mb-3"><?=SITE_TITLE ?>is Redesigned</h5><p class="text-muted">Please note that we are still in the process of renewing aspects of the user experience. You might encounter some areas under development, but rest assured, these improvements are being made to better serve your needs.</p>', 3),
  FX = {
    class: "row"
  },
  UX = {
    class: "col-12"
  },
  VX = {
    class: "d-grid"
  };

function jX(e, t, s, o, i, r) {
  const a = ke("RouterLink");
  return _(), w(Te, null, [n("nav", lZ, [n("div", cZ, [n("div", dZ, [this.companySlug == "paperboat" ? (_(), w("a", uZ, [this.dark_flag ? D("", !0) : (_(), w("img", fZ)), this.dark_flag ? (_(), w("img", hZ)) : D("", !0), pZ])) : D("", !0), this.companySlug == "bitrage" ? (_(), w("a", mZ, [this.dark_flag ? D("", !0) : (_(), w("img", gZ)), this.dark_flag ? (_(), w("img", _Z)) : D("", !0), bZ])) : D("", !0), this.companySlug == "succedo" ? (_(), w("a", vZ, [this.dark_flag ? D("", !0) : (_(), w("img", yZ)), this.dark_flag ? (_(), w("img", wZ)) : D("", !0), $Z])) : D("", !0)]), n("div", CZ, [n("div", kZ, [n("div", AZ, [n("div", xZ, [SZ, n("div", EZ, [n("h6", PZ, L(i.clientName), 1), n("small", null, "#" + L(r.generateNumber()), 1)]), TZ]), n("div", LZ, [n("div", OZ, [ie(a, {
    to: "/user/profile"
  }, {
    default: Ue(() => [IZ, BZ]),
    _: 1
  }), DZ])])])]), n("ul", MZ, [RZ, n("li", {
    class: Ie(["pc-item", {
      active: o.isCurrentRoute("Dashboard")
    }])
  }, [ie(a, {
    to: "/dashboard",
    class: "pc-link"
  }, {
    default: Ue(() => [NZ, FZ]),
    _: 1
  })], 2), n("li", {
    class: Ie(["pc-item", {
      active: o.isCurrentRoute("Fund")
    }])
  }, [ie(a, {
    to: {
      name: "Fund",
      params: {
        option: "deposit"
      }
    },
    class: "pc-link"
  }, {
    default: Ue(() => [UZ, VZ]),
    _: 1
  })], 2), n("li", {
    class: Ie(["pc-item", {
      active: o.isCurrentRoute("Transactions")
    }])
  }, [ie(a, {
    to: {
      name: "Transactions",
      params: {
        option: "deposit"
      }
    },
    class: "pc-link"
  }, {
    default: Ue(() => [jZ, HZ]),
    _: 1
  })], 2), n("li", {
    class: Ie(["pc-item", {
      active: o.isCurrentRoute("Wallet")
    }])
  }, [ie(a, {
    to: "/wallet",
    class: "pc-link"
  }, {
    default: Ue(() => [qZ, WZ]),
    _: 1
  })], 2), n("li", {
    class: Ie(["pc-item", {
      active: o.isCurrentRoute("LiveAccounts")
    }])
  }, [ie(a, {
    to: "/liveAccounts",
    class: "pc-link"
  }, {
    default: Ue(() => [zZ, KZ, GZ]),
    _: 1
  })], 2), n("li", {
    class: Ie(["pc-item", {
      active: o.isCurrentRoute("DemoAccounts")
    }])
  }, [ie(a, {
    to: "/demoAccounts",
    class: "pc-link"
  }, {
    default: Ue(() => [YZ, JZ, ZZ]),
    _: 1
  })], 2)])])])]), n("header", XZ, [n("div", QZ, [eX, n("div", tX, [n("ul", sX, [n("li", nX, [oX, n("div", iX, [n("a", {
    href: "#!",
    class: "dropdown-item",
    onClick: t[0] || (t[0] = l => r.layout_change("dark"))
  }, lX), n("a", {
    href: "#!",
    class: "dropdown-item",
    onClick: t[1] || (t[1] = l => r.layout_change("light"))
  }, uX)])]), fX, hX, pX, n("li", mX, [gX, n("div", _X, [bX, n("div", vX, [n("div", yX, [n("div", wX, [$X, n("div", CX, [n("h6", kX, L(i.clientName) + " 🖖", 1), n("span", null, L(i.clientEmail), 1)])]), AX, n("div", xX, [n("div", SX, [ie(a, {
    to: "/user/profile"
  }, {
    default: Ue(() => [EX]),
    _: 1
  })])]), PX, n("div", TX, [n("button", {
    class: "btn btn-primary",
    onClick: t[2] || (t[2] = (...l) => r.confirmLogout && r.confirmLogout(...l))
  }, [LX, J("Logout ")])])])])])])])])])]), n("div", OX, [IX, n("div", BX, [DX, n("div", MX, [n("div", RX, [NX, n("div", FX, [n("div", UX, [n("div", VX, [ie(a, {
    class: "btn btn-outline-secondary",
    to: "/dashboard"
  }, {
    default: Ue(() => [J("Explore More")]),
    _: 1
  })])])])])])])])], 64)
}
const HX = je(aZ, [
    ["render", jX]
  ]),
  qX = {
    __name: "App",
    setup(e) {
      return (t, s) => (_(), w(Te, null, [be(n("div", null, [n("h1", null, L(), 1), ie(HX)], 512), [
        [Ti, !(t.$route.path === "/register" || t.$route.path === "/login" || t.$route.path === "/verify")]
      ]), ie(Pt(l1))], 64))
    }
  },
  ft = Dm(qX);
ft.use(S0);
ft.use(Qy.LoadingPlugin);
ft.use(Zm, {
  position: "top-right",
  timeout: 3e3,
  closeOnClick: !0,
  pauseOnFocusLoss: !0,
  pauseOnHover: !0,
  draggable: !0,
  draggablePercent: .6,
  showCloseButtonOnHover: !1,
  hideProgressBar: !1,
  closeButton: "button",
  icon: !0,
  rtl: !1
});
ft.component("v-select", Yy);
ft.component("LoadingSpinner", iw);
ft.component("SmallLoadingSpinner", Vs);
ft.component("EmptyStateView", bw);
window.Swal = ft.config.globalProperties.$swal;
ft.use(dy);
ft.config.globalProperties.companyName = "Succedo Markets";
ft.config.globalProperties.companySlug = "succedo";
ft.config.globalProperties.$formatCurrency = Hs;
ft.config.globalProperties.API_BASE_URL = "https://succedo.paperboatsolutions.com/api/client";
ft.config.globalProperties.COMPANY_NAME = "Succedo Markets";
ft.config.globalProperties.S3_BASE_URL = "https://s3.ap-south-1.amazonaws.com/crm.paperboat.demo/succedo";
ft.config.globalProperties.COMPANY_SLUG = "succedo";
ft.provide("API_BASE_URL", "https://succedo.paperboatsolutions.com/api/client");
ft.provide("S3_BASE_URL", "https://s3.ap-south-1.amazonaws.com/crm.paperboat.demo/succedo");
ft.provide("COMPANY_SLUG", "succedo");
ft.use(Sw());
ft.use(os);
ft.mount("#app");