(function () {
  "use strict";
  if (localStorage.getItem("Volghdarktheme")) {
    document.querySelector("html").setAttribute("data-theme-mode", "dark");
    document.querySelector("html").setAttribute("data-menu-styles", "dark");
    document.querySelector("html").setAttribute("data-header-styles", "dark");
  }
  if (localStorage.Volghrtl) {
    let html = document.querySelector("html");
    html.setAttribute("dir", "rtl");
    document
      .querySelector("#style")
      ?.setAttribute(
        "href",
        "../assets/libs/bootstrap/css/bootstrap.rtl.min.css"
      );
  }
  if (localStorage.Volghlayout) {
    let html = document.querySelector("html");
    html.setAttribute("data-nav-layout", "horizontal");
    document.querySelector("html").setAttribute("data-menu-styles", "light");
  }
  if (localStorage.getItem("Volghlayout") == "horizontal") {
    document
      .querySelector("html")
      .setAttribute("data-nav-layout", "horizontal");
  }
  if (localStorage.loaderEnable == "true") {
    document.querySelector("html").setAttribute("loader", "enable");
  } else {
    if (!document.querySelector("html").getAttribute("loader")) {
      document.querySelector("html").setAttribute("loader", "disable");
    }
  }

  function localStorageBackup() {
    // if there is a value stored, update color picker and background color
    // Used to retrive the data from local storage
    if (localStorage.primaryRGB) {
      if (document.querySelector(".theme-container-primary")) {
        document.querySelector(".theme-container-primary").value =
          localStorage.primaryRGB;
      }
      document
        .querySelector("html")
        .style.setProperty("--primary-rgb", localStorage.primaryRGB);
    }
    if (localStorage.bodyBgRGB && localStorage.bodylightRGB) {
      if (document.querySelector(".theme-container-background")) {
        document.querySelector(".theme-container-background").value =
          localStorage.bodyBgRGB;
      }
      document
        .querySelector("html")
        .style.setProperty("--body-bg-rgb", localStorage.bodyBgRGB);
      document
        .querySelector("html")
        .style.setProperty("--body-bg-rgb2", localStorage.bodylightRGB);
      document
        .querySelector("html")
        .style.setProperty("--light-rgb", localStorage.bodylightRGB);
      document
        .querySelector("html")
        .style.setProperty(
          "--form-control-bg",
          `rgb(${localStorage.bodylightRGB})`
        );
      document
        .querySelector("html")
        .style.setProperty("--gray-3", `rgb(${localStorage.bodylightRGB})`);
      document
        .querySelector("html")
        .style.setProperty("--input-border", "rgba(255,255,255,0.1)");
      let html = document.querySelector("html");
      html.setAttribute("data-theme-mode", "dark");
      html.setAttribute("data-menu-styles", "dark");
      html.setAttribute("data-header-styles", "dark");
    }
    if (localStorage.Volghdarktheme) {
      let html = document.querySelector("html");
      html.setAttribute("data-theme-mode", "dark");
    }
    if (localStorage.Volghlayout) {
      let html = document.querySelector("html");
      let layoutValue = localStorage.getItem("Volghlayout");
      html.setAttribute("data-nav-layout", "horizontal");
      setTimeout(() => {
        clearNavDropdown();
      }, 1000);
      html.setAttribute("data-nav-style", "menu-click");
      setTimeout(() => {
        checkHoriMenu();
      }, 5000);
    }
    if (localStorage.Volghverticalstyles) {
      let html = document.querySelector("html");
      let verticalStyles = localStorage.getItem("Volghverticalstyles");

      if (verticalStyles == "default") {
        html.setAttribute("data-vertical-style", "default");
        localStorage.removeItem("Volghnavstyles");
      }
      if (verticalStyles == "closed") {
        html.setAttribute("data-vertical-style", "closed");
        localStorage.removeItem("Volghnavstyles");
      }
      if (verticalStyles == "icontext") {
        html.setAttribute("data-vertical-style", "icontext");
        localStorage.removeItem("Volghnavstyles");
      }
      if (verticalStyles == "overlay") {
        html.setAttribute("data-vertical-style", "overlay");
        localStorage.removeItem("Volghnavstyles");
      }
      if (verticalStyles == "detached") {
        html.setAttribute("data-vertical-style", "detached");
        localStorage.removeItem("Volghnavstyles");
      }
      if (verticalStyles == "doublemenu") {
        html.setAttribute("data-vertical-style", "doublemenu");
        localStorage.removeItem("Volghnavstyles");
        setTimeout(() => {
          const menuSlideItem = document.querySelectorAll(
            ".main-menu > li > .side-menu__item"
          );

          // Create the tooltip element
          const tooltip = document.createElement("div");
          tooltip.className = "custome-tooltip";
          // Set the CSS properties of the tooltip element
          tooltip.style.setProperty("position", "fixed");
          tooltip.style.setProperty("display", "none");
          tooltip.style.setProperty("padding", "0.5rem");
          tooltip.style.setProperty("font-weight", "500");
          tooltip.style.setProperty("font-size", "0.75rem");
          tooltip.style.setProperty("background-color", "rgb(15, 23 ,42)");
          tooltip.style.setProperty("color", "rgb(255, 255 ,255)");
          tooltip.style.setProperty("margin-inline-start", "48px");
          tooltip.style.setProperty("border-radius", "0.25rem");
          tooltip.style.setProperty("z-index", "99");

          menuSlideItem.forEach((e) => {
            // Add an event listener to the menu slide item to show the tooltip
            e.addEventListener("mouseenter", () => {
              if (localStorage.Volghverticalstyles == "doublemenu") {
                tooltip.style.setProperty("display", "block");
                tooltip.textContent =
                  e.querySelector(".side-menu__label").textContent;
                if (
                  document
                    .querySelector("html")
                    .getAttribute("data-vertical-style") == "doublemenu"
                ) {
                  e.appendChild(tooltip);
                }
              }
            });

            // Add an event listener to hide the tooltip
            e.addEventListener("mouseleave", () => {
              tooltip.style.setProperty("display", "none");
              tooltip.textContent =
                e.querySelector(".side-menu__label").textContent;
            });
          });
        }, 1000);
      }
    }
    if (localStorage.Volghnavstyles) {
      let html = document.querySelector("html");
      let navStyles = localStorage.getItem("Volghnavstyles");
      if (navStyles == "menu-click") {
        html.setAttribute("data-nav-style", "menu-click");
        localStorage.removeItem("Volghverticalstyles");
        html.removeAttribute("data-vertical-style");
      }
      if (navStyles == "menu-hover") {
        html.setAttribute("data-nav-style", "menu-hover");
        localStorage.removeItem("Volghverticalstyles");
        html.removeAttribute("data-vertical-style");
      }
      if (navStyles == "icon-click") {
        html.setAttribute("data-nav-style", "icon-click");
        localStorage.removeItem("Volghverticalstyles");
        html.removeAttribute("data-vertical-style");
      }
      if (navStyles == "icon-hover") {
        html.setAttribute("data-nav-style", "icon-hover");
        localStorage.removeItem("Volghverticalstyles");
        html.removeAttribute("data-vertical-style");
      }
    }
    if (localStorage.Volghclassic) {
      let html = document.querySelector("html");
      html.setAttribute("data-page-style", "classic");
    }
    if (localStorage.Volghmodern) {
      let html = document.querySelector("html");
      html.setAttribute("data-page-style", "modern");
    }
    if (localStorage.Volghboxed) {
      let html = document.querySelector("html");
      html.setAttribute("data-width", "boxed");
    }
    if (localStorage.Volghfullwidth) {
      let html = document.querySelector("html");
      html.setAttribute("data-width", "fullwidth");
    }
    if (localStorage.Volghheaderfixed) {
      let html = document.querySelector("html");
      html.setAttribute("data-header-position", "fixed");
    }
    if (localStorage.Volghheaderscrollable) {
      let html = document.querySelector("html");
      html.setAttribute("data-header-position", "scrollable");
    }
    if (localStorage.Volghmenufixed) {
      let html = document.querySelector("html");
      html.setAttribute("data-menu-position", "fixed");
    }
    if (localStorage.Volghmenuscrollable) {
      let html = document.querySelector("html");
      html.setAttribute("data-menu-position", "scrollable");
    }
    if (localStorage.VolghMenu) {
      let html = document.querySelector("html");
      let menuValue = localStorage.getItem("VolghMenu");
      switch (menuValue) {
        case "light":
          html.setAttribute("data-menu-styles", "light");
          break;
        case "dark":
          html.setAttribute("data-menu-styles", "dark");
          break;
        case "color":
          html.setAttribute("data-menu-styles", "color");
          break;
        case "gradient":
          html.setAttribute("data-menu-styles", "gradient");
          break;
        case "transparent":
          html.setAttribute("data-menu-styles", "transparent");
          break;
        default:
          break;
      }
    }
    if (localStorage.VolghHeader) {
      let html = document.querySelector("html");
      let headerValue = localStorage.getItem("VolghHeader");
      html.setAttribute("data-header-styles", headerValue);
    }
    if (localStorage.bgimg) {
      let html = document.querySelector("html");
      let value = localStorage.getItem("bgimg");
      html.setAttribute("data-bg-img", value);
    }
  }
  localStorageBackup();
})();
