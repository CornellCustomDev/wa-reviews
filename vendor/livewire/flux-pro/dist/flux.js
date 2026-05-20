(() => {
  // node_modules/@oddbird/popover-polyfill/dist/popover.js
  var ToggleEvent = class extends Event {
    oldState;
    newState;
    constructor(type, { oldState = "", newState = "", ...init } = {}) {
      super(type, init);
      this.oldState = String(oldState || "");
      this.newState = String(newState || "");
    }
  };
  var popoverToggleTaskQueue = /* @__PURE__ */ new WeakMap();
  function queuePopoverToggleEventTask(element2, oldState, newState) {
    popoverToggleTaskQueue.set(
      element2,
      setTimeout(() => {
        if (!popoverToggleTaskQueue.has(element2)) return;
        element2.dispatchEvent(
          new ToggleEvent("toggle", {
            cancelable: false,
            oldState,
            newState
          })
        );
      }, 0)
    );
  }
  var ShadowRoot2 = globalThis.ShadowRoot || function() {
  };
  var HTMLDialogElement = globalThis.HTMLDialogElement || function() {
  };
  var topLayerElements = /* @__PURE__ */ new WeakMap();
  var autoPopoverList = /* @__PURE__ */ new WeakMap();
  var visibilityState = /* @__PURE__ */ new WeakMap();
  function getPopoverVisibilityState(popover) {
    return visibilityState.get(popover) || "hidden";
  }
  var popoverInvoker = /* @__PURE__ */ new WeakMap();
  function popoverTargetAttributeActivationBehavior(element2) {
    const popover = element2.popoverTargetElement;
    if (!(popover instanceof HTMLElement)) {
      return;
    }
    const visibility = getPopoverVisibilityState(popover);
    if (element2.popoverTargetAction === "show" && visibility === "showing") {
      return;
    }
    if (element2.popoverTargetAction === "hide" && visibility === "hidden") return;
    if (visibility === "showing") {
      hidePopover(popover, true, true);
    } else if (checkPopoverValidity(popover, false)) {
      popoverInvoker.set(popover, element2);
      showPopover(popover);
    }
  }
  function checkPopoverValidity(element2, expectedToBeShowing) {
    if (element2.popover !== "auto" && element2.popover !== "manual") {
      return false;
    }
    if (!element2.isConnected) return false;
    if (expectedToBeShowing && getPopoverVisibilityState(element2) !== "showing") {
      return false;
    }
    if (!expectedToBeShowing && getPopoverVisibilityState(element2) !== "hidden") {
      return false;
    }
    if (element2 instanceof HTMLDialogElement && element2.hasAttribute("open")) {
      return false;
    }
    if (document.fullscreenElement === element2) return false;
    return true;
  }
  function getStackPosition(popover) {
    if (!popover) return 0;
    return Array.from(autoPopoverList.get(popover.ownerDocument) || []).indexOf(
      popover
    ) + 1;
  }
  function topMostClickedPopover(target) {
    const clickedPopover = nearestInclusiveOpenPopover(target);
    const invokerPopover = nearestInclusiveTargetPopoverForInvoker(target);
    if (getStackPosition(clickedPopover) > getStackPosition(invokerPopover)) {
      return clickedPopover;
    }
    return invokerPopover;
  }
  function topMostAutoPopover(document2) {
    const documentPopovers = autoPopoverList.get(document2);
    for (const popover of documentPopovers || []) {
      if (!popover.isConnected) {
        documentPopovers.delete(popover);
      } else {
        return popover;
      }
    }
    return null;
  }
  function getRootNode(node) {
    if (typeof node.getRootNode === "function") {
      return node.getRootNode();
    }
    if (node.parentNode) return getRootNode(node.parentNode);
    return node;
  }
  function nearestInclusiveOpenPopover(node) {
    while (node) {
      if (node instanceof HTMLElement && node.popover === "auto" && visibilityState.get(node) === "showing") {
        return node;
      }
      node = node instanceof Element && node.assignedSlot || node.parentElement || getRootNode(node);
      if (node instanceof ShadowRoot2) node = node.host;
      if (node instanceof Document) return;
    }
  }
  function nearestInclusiveTargetPopoverForInvoker(node) {
    while (node) {
      const nodePopover = node.popoverTargetElement;
      if (nodePopover instanceof HTMLElement) return nodePopover;
      node = node.parentElement || getRootNode(node);
      if (node instanceof ShadowRoot2) node = node.host;
      if (node instanceof Document) return;
    }
  }
  function topMostPopoverAncestor(newPopover) {
    const popoverPositions = /* @__PURE__ */ new Map();
    let i = 0;
    for (const popover of autoPopoverList.get(newPopover.ownerDocument) || []) {
      popoverPositions.set(popover, i);
      i += 1;
    }
    popoverPositions.set(newPopover, i);
    i += 1;
    let topMostPopoverAncestor22 = null;
    function checkAncestor(candidate) {
      const candidateAncestor = nearestInclusiveOpenPopover(candidate);
      if (candidateAncestor === null) return null;
      const candidatePosition = popoverPositions.get(candidateAncestor);
      if (topMostPopoverAncestor22 === null || popoverPositions.get(topMostPopoverAncestor22) < candidatePosition) {
        topMostPopoverAncestor22 = candidateAncestor;
      }
    }
    checkAncestor(newPopover.parentElement || getRootNode(newPopover));
    return topMostPopoverAncestor22;
  }
  function isFocusable(focusTarget) {
    if (focusTarget.hidden || focusTarget instanceof ShadowRoot2) return false;
    if (focusTarget instanceof HTMLButtonElement || focusTarget instanceof HTMLInputElement || focusTarget instanceof HTMLSelectElement || focusTarget instanceof HTMLTextAreaElement || focusTarget instanceof HTMLOptGroupElement || focusTarget instanceof HTMLOptionElement || focusTarget instanceof HTMLFieldSetElement) {
      if (focusTarget.disabled) return false;
    }
    if (focusTarget instanceof HTMLInputElement && focusTarget.type === "hidden") {
      return false;
    }
    if (focusTarget instanceof HTMLAnchorElement && focusTarget.href === "") {
      return false;
    }
    return typeof focusTarget.tabIndex === "number" && focusTarget.tabIndex !== -1;
  }
  function focusDelegate(focusTarget) {
    if (focusTarget.shadowRoot && focusTarget.shadowRoot.delegatesFocus !== true) {
      return null;
    }
    let whereToLook = focusTarget;
    if (whereToLook.shadowRoot) {
      whereToLook = whereToLook.shadowRoot;
    }
    let autoFocusDelegate = whereToLook.querySelector("[autofocus]");
    if (autoFocusDelegate) {
      return autoFocusDelegate;
    } else {
      const slots = whereToLook.querySelectorAll("slot");
      for (const slot of slots) {
        const assignedElements = slot.assignedElements({ flatten: true });
        for (const el of assignedElements) {
          if (el.hasAttribute("autofocus")) {
            return el;
          } else {
            autoFocusDelegate = el.querySelector("[autofocus]");
            if (autoFocusDelegate) {
              return autoFocusDelegate;
            }
          }
        }
      }
    }
    const walker2 = focusTarget.ownerDocument.createTreeWalker(
      whereToLook,
      NodeFilter.SHOW_ELEMENT
    );
    let descendant = walker2.currentNode;
    while (descendant) {
      if (isFocusable(descendant)) {
        return descendant;
      }
      descendant = walker2.nextNode();
    }
  }
  function popoverFocusingSteps(subject) {
    focusDelegate(subject)?.focus();
  }
  var previouslyFocusedElements = /* @__PURE__ */ new WeakMap();
  function showPopover(element2) {
    if (!checkPopoverValidity(element2, false)) {
      return;
    }
    const document2 = element2.ownerDocument;
    if (!element2.dispatchEvent(
      new ToggleEvent("beforetoggle", {
        cancelable: true,
        oldState: "closed",
        newState: "open"
      })
    )) {
      return;
    }
    if (!checkPopoverValidity(element2, false)) {
      return;
    }
    let shouldRestoreFocus = false;
    if (element2.popover === "auto") {
      const originalType = element2.getAttribute("popover");
      const ancestor = topMostPopoverAncestor(element2) || document2;
      hideAllPopoversUntil(ancestor, false, true);
      if (originalType !== element2.getAttribute("popover") || !checkPopoverValidity(element2, false)) {
        return;
      }
    }
    if (!topMostAutoPopover(document2)) {
      shouldRestoreFocus = true;
    }
    previouslyFocusedElements.delete(element2);
    const originallyFocusedElement = document2.activeElement;
    element2.classList.add(":popover-open");
    visibilityState.set(element2, "showing");
    if (!topLayerElements.has(document2)) {
      topLayerElements.set(document2, /* @__PURE__ */ new Set());
    }
    topLayerElements.get(document2).add(element2);
    popoverFocusingSteps(element2);
    if (element2.popover === "auto") {
      if (!autoPopoverList.has(document2)) {
        autoPopoverList.set(document2, /* @__PURE__ */ new Set());
      }
      autoPopoverList.get(document2).add(element2);
      setInvokerAriaExpanded(popoverInvoker.get(element2), true);
    }
    if (shouldRestoreFocus && originallyFocusedElement && element2.popover === "auto") {
      previouslyFocusedElements.set(element2, originallyFocusedElement);
    }
    queuePopoverToggleEventTask(element2, "closed", "open");
  }
  function hidePopover(element2, focusPreviousElement = false, fireEvents = false) {
    if (!checkPopoverValidity(element2, true)) {
      return;
    }
    const document2 = element2.ownerDocument;
    if (element2.popover === "auto") {
      hideAllPopoversUntil(element2, focusPreviousElement, fireEvents);
      if (!checkPopoverValidity(element2, true)) {
        return;
      }
    }
    setInvokerAriaExpanded(popoverInvoker.get(element2), false);
    popoverInvoker.delete(element2);
    if (fireEvents) {
      element2.dispatchEvent(
        new ToggleEvent("beforetoggle", {
          oldState: "open",
          newState: "closed"
        })
      );
      if (!checkPopoverValidity(element2, true)) {
        return;
      }
    }
    topLayerElements.get(document2)?.delete(element2);
    autoPopoverList.get(document2)?.delete(element2);
    element2.classList.remove(":popover-open");
    visibilityState.set(element2, "hidden");
    if (fireEvents) {
      queuePopoverToggleEventTask(element2, "open", "closed");
    }
    const previouslyFocusedElement = previouslyFocusedElements.get(element2);
    if (previouslyFocusedElement) {
      previouslyFocusedElements.delete(element2);
      if (focusPreviousElement) {
        previouslyFocusedElement.focus();
      }
    }
  }
  function closeAllOpenPopovers(document2, focusPreviousElement = false, fireEvents = false) {
    let popover = topMostAutoPopover(document2);
    while (popover) {
      hidePopover(popover, focusPreviousElement, fireEvents);
      popover = topMostAutoPopover(document2);
    }
  }
  function hideAllPopoversUntil(endpoint, focusPreviousElement, fireEvents) {
    const document2 = endpoint.ownerDocument || endpoint;
    if (endpoint instanceof Document) {
      return closeAllOpenPopovers(document2, focusPreviousElement, fireEvents);
    }
    let lastToHide = null;
    let foundEndpoint = false;
    for (const popover of autoPopoverList.get(document2) || []) {
      if (popover === endpoint) {
        foundEndpoint = true;
      } else if (foundEndpoint) {
        lastToHide = popover;
        break;
      }
    }
    if (!foundEndpoint) {
      return closeAllOpenPopovers(document2, focusPreviousElement, fireEvents);
    }
    while (lastToHide && getPopoverVisibilityState(lastToHide) === "showing" && autoPopoverList.get(document2)?.size) {
      hidePopover(lastToHide, focusPreviousElement, fireEvents);
    }
  }
  var popoverPointerDownTargets = /* @__PURE__ */ new WeakMap();
  function lightDismissOpenPopovers(event) {
    if (!event.isTrusted) return;
    const target = event.composedPath()[0];
    if (!target) return;
    const document2 = target.ownerDocument;
    const topMostPopover = topMostAutoPopover(document2);
    if (!topMostPopover) return;
    const ancestor = topMostClickedPopover(target);
    if (ancestor && event.type === "pointerdown") {
      popoverPointerDownTargets.set(document2, ancestor);
    } else if (event.type === "pointerup") {
      const sameTarget = popoverPointerDownTargets.get(document2) === ancestor;
      popoverPointerDownTargets.delete(document2);
      if (sameTarget) {
        hideAllPopoversUntil(ancestor || document2, false, true);
      }
    }
  }
  var initialAriaExpandedValue = /* @__PURE__ */ new WeakMap();
  function setInvokerAriaExpanded(el, force = false) {
    if (!el) return;
    if (!initialAriaExpandedValue.has(el)) {
      initialAriaExpandedValue.set(el, el.getAttribute("aria-expanded"));
    }
    const popover = el.popoverTargetElement;
    if (popover instanceof HTMLElement && popover.popover === "auto") {
      el.setAttribute("aria-expanded", String(force));
    } else {
      const initialValue = initialAriaExpandedValue.get(el);
      if (!initialValue) {
        el.removeAttribute("aria-expanded");
      } else {
        el.setAttribute("aria-expanded", initialValue);
      }
    }
  }
  var ShadowRoot22 = globalThis.ShadowRoot || function() {
  };
  function isSupported() {
    return typeof HTMLElement !== "undefined" && typeof HTMLElement.prototype === "object" && "popover" in HTMLElement.prototype;
  }
  function patchSelectorFn(object, name, mapper) {
    const original = object[name];
    Object.defineProperty(object, name, {
      value(selector) {
        return original.call(this, mapper(selector));
      }
    });
  }
  var nonEscapedPopoverSelector = /(^|[^\\]):popover-open\b/g;
  function hasLayerSupport() {
    return typeof globalThis.CSSLayerBlockRule === "function";
  }
  function getStyles() {
    const useLayer = hasLayerSupport();
    return `
${useLayer ? "@layer popover-polyfill {" : ""}
  :where([popover]) {
    position: fixed;
    z-index: 2147483647;
    inset: 0;
    padding: 0.25em;
    width: fit-content;
    height: fit-content;
    border-width: initial;
    border-color: initial;
    border-image: initial;
    border-style: solid;
    background-color: canvas;
    color: canvastext;
    overflow: auto;
    margin: auto;
  }

  :where([popover]:not(.\\:popover-open)) {
    display: none;
  }

  :where(dialog[popover].\\:popover-open) {
    display: block;
  }

  :where(dialog[popover][open]) {
    display: revert;
  }

  :where([anchor].\\:popover-open) {
    inset: auto;
  }

  :where([anchor]:popover-open) {
    inset: auto;
  }

  @supports not (background-color: canvas) {
    :where([popover]) {
      background-color: white;
      color: black;
    }
  }

  @supports (width: -moz-fit-content) {
    :where([popover]) {
      width: -moz-fit-content;
      height: -moz-fit-content;
    }
  }

  @supports not (inset: 0) {
    :where([popover]) {
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
    }
  }
${useLayer ? "}" : ""}
`;
  }
  var popoverStyleSheet = null;
  function injectStyles(root) {
    const styles = getStyles();
    if (popoverStyleSheet === null) {
      try {
        popoverStyleSheet = new CSSStyleSheet();
        popoverStyleSheet.replaceSync(styles);
      } catch {
        popoverStyleSheet = false;
      }
    }
    if (popoverStyleSheet === false) {
      const sheet = document.createElement("style");
      sheet.textContent = styles;
      if (root instanceof Document) {
        root.head.prepend(sheet);
      } else {
        root.prepend(sheet);
      }
    } else {
      root.adoptedStyleSheets = [popoverStyleSheet, ...root.adoptedStyleSheets];
    }
  }
  function apply() {
    if (typeof window === "undefined") return;
    window.ToggleEvent = window.ToggleEvent || ToggleEvent;
    function rewriteSelector(selector) {
      if (selector?.includes(":popover-open")) {
        selector = selector.replace(
          nonEscapedPopoverSelector,
          "$1.\\:popover-open"
        );
      }
      return selector;
    }
    patchSelectorFn(Document.prototype, "querySelector", rewriteSelector);
    patchSelectorFn(Document.prototype, "querySelectorAll", rewriteSelector);
    patchSelectorFn(Element.prototype, "querySelector", rewriteSelector);
    patchSelectorFn(Element.prototype, "querySelectorAll", rewriteSelector);
    patchSelectorFn(Element.prototype, "matches", rewriteSelector);
    patchSelectorFn(Element.prototype, "closest", rewriteSelector);
    patchSelectorFn(
      DocumentFragment.prototype,
      "querySelectorAll",
      rewriteSelector
    );
    Object.defineProperties(HTMLElement.prototype, {
      popover: {
        enumerable: true,
        configurable: true,
        get() {
          if (!this.hasAttribute("popover")) return null;
          const value3 = (this.getAttribute("popover") || "").toLowerCase();
          if (value3 === "" || value3 == "auto") return "auto";
          return "manual";
        },
        set(value3) {
          if (value3 === null) {
            this.removeAttribute("popover");
          } else {
            this.setAttribute("popover", value3);
          }
        }
      },
      showPopover: {
        enumerable: true,
        configurable: true,
        value() {
          showPopover(this);
        }
      },
      hidePopover: {
        enumerable: true,
        configurable: true,
        value() {
          hidePopover(this, true, true);
        }
      },
      togglePopover: {
        enumerable: true,
        configurable: true,
        value(force) {
          if (visibilityState.get(this) === "showing" && force === void 0 || force === false) {
            hidePopover(this, true, true);
          } else if (force === void 0 || force === true) {
            showPopover(this);
          }
        }
      }
    });
    const originalAttachShadow = Element.prototype.attachShadow;
    if (originalAttachShadow) {
      Object.defineProperties(Element.prototype, {
        attachShadow: {
          enumerable: true,
          configurable: true,
          writable: true,
          value(options) {
            const shadowRoot = originalAttachShadow.call(this, options);
            injectStyles(shadowRoot);
            return shadowRoot;
          }
        }
      });
    }
    const originalAttachInternals = HTMLElement.prototype.attachInternals;
    if (originalAttachInternals) {
      Object.defineProperties(HTMLElement.prototype, {
        attachInternals: {
          enumerable: true,
          configurable: true,
          writable: true,
          value() {
            const internals = originalAttachInternals.call(this);
            if (internals.shadowRoot) {
              injectStyles(internals.shadowRoot);
            }
            return internals;
          }
        }
      });
    }
    const popoverTargetAssociatedElements = /* @__PURE__ */ new WeakMap();
    function applyPopoverInvokerElementMixin(ElementClass) {
      Object.defineProperties(ElementClass.prototype, {
        popoverTargetElement: {
          enumerable: true,
          configurable: true,
          set(targetElement) {
            if (targetElement === null) {
              this.removeAttribute("popovertarget");
              popoverTargetAssociatedElements.delete(this);
            } else if (!(targetElement instanceof Element)) {
              throw new TypeError(
                `popoverTargetElement must be an element or null`
              );
            } else {
              this.setAttribute("popovertarget", "");
              popoverTargetAssociatedElements.set(this, targetElement);
            }
          },
          get() {
            if (this.localName !== "button" && this.localName !== "input") {
              return null;
            }
            if (this.localName === "input" && this.type !== "reset" && this.type !== "image" && this.type !== "button") {
              return null;
            }
            if (this.disabled) {
              return null;
            }
            if (this.form && this.type === "submit") {
              return null;
            }
            const targetElement = popoverTargetAssociatedElements.get(this);
            if (targetElement && targetElement.isConnected) {
              return targetElement;
            } else if (targetElement && !targetElement.isConnected) {
              popoverTargetAssociatedElements.delete(this);
              return null;
            }
            const root = getRootNode(this);
            const idref = this.getAttribute("popovertarget");
            if ((root instanceof Document || root instanceof ShadowRoot22) && idref) {
              return root.getElementById(idref) || null;
            }
            return null;
          }
        },
        popoverTargetAction: {
          enumerable: true,
          configurable: true,
          get() {
            const value3 = (this.getAttribute("popovertargetaction") || "").toLowerCase();
            if (value3 === "show" || value3 === "hide") return value3;
            return "toggle";
          },
          set(value3) {
            this.setAttribute("popovertargetaction", value3);
          }
        }
      });
    }
    applyPopoverInvokerElementMixin(HTMLButtonElement);
    applyPopoverInvokerElementMixin(HTMLInputElement);
    const handleInvokerActivation = (event) => {
      const composedPath = event.composedPath();
      const target = composedPath[0];
      if (!(target instanceof Element) || target?.shadowRoot) {
        return;
      }
      const root = getRootNode(target);
      if (!(root instanceof ShadowRoot22 || root instanceof Document)) {
        return;
      }
      const invoker = composedPath.find(
        (el) => el.matches?.("[popovertargetaction],[popovertarget]")
      );
      if (invoker) {
        popoverTargetAttributeActivationBehavior(invoker);
        event.preventDefault();
        return;
      }
    };
    const onKeydown = (event) => {
      const key = event.key;
      const target = event.target;
      if (!event.defaultPrevented && target && (key === "Escape" || key === "Esc")) {
        hideAllPopoversUntil(target.ownerDocument, true, true);
      }
    };
    const addEventListeners = (root) => {
      root.addEventListener("click", handleInvokerActivation);
      root.addEventListener("keydown", onKeydown);
      root.addEventListener("pointerdown", lightDismissOpenPopovers);
      root.addEventListener("pointerup", lightDismissOpenPopovers);
    };
    addEventListeners(document);
    injectStyles(document);
  }
  if (!isSupported()) apply();

  // node_modules/@oddbird/popover-polyfill/dist/popover-fn.js
  var ToggleEvent2 = class extends Event {
    oldState;
    newState;
    constructor(type, { oldState = "", newState = "", ...init } = {}) {
      super(type, init);
      this.oldState = String(oldState || "");
      this.newState = String(newState || "");
    }
  };
  var popoverToggleTaskQueue2 = /* @__PURE__ */ new WeakMap();
  function queuePopoverToggleEventTask2(element2, oldState, newState) {
    popoverToggleTaskQueue2.set(
      element2,
      setTimeout(() => {
        if (!popoverToggleTaskQueue2.has(element2)) return;
        element2.dispatchEvent(
          new ToggleEvent2("toggle", {
            cancelable: false,
            oldState,
            newState
          })
        );
      }, 0)
    );
  }
  var ShadowRoot3 = globalThis.ShadowRoot || function() {
  };
  var HTMLDialogElement2 = globalThis.HTMLDialogElement || function() {
  };
  var topLayerElements2 = /* @__PURE__ */ new WeakMap();
  var autoPopoverList2 = /* @__PURE__ */ new WeakMap();
  var visibilityState2 = /* @__PURE__ */ new WeakMap();
  function getPopoverVisibilityState2(popover) {
    return visibilityState2.get(popover) || "hidden";
  }
  var popoverInvoker2 = /* @__PURE__ */ new WeakMap();
  function popoverTargetAttributeActivationBehavior2(element2) {
    const popover = element2.popoverTargetElement;
    if (!(popover instanceof HTMLElement)) {
      return;
    }
    const visibility = getPopoverVisibilityState2(popover);
    if (element2.popoverTargetAction === "show" && visibility === "showing") {
      return;
    }
    if (element2.popoverTargetAction === "hide" && visibility === "hidden") return;
    if (visibility === "showing") {
      hidePopover2(popover, true, true);
    } else if (checkPopoverValidity2(popover, false)) {
      popoverInvoker2.set(popover, element2);
      showPopover2(popover);
    }
  }
  function checkPopoverValidity2(element2, expectedToBeShowing) {
    if (element2.popover !== "auto" && element2.popover !== "manual") {
      return false;
    }
    if (!element2.isConnected) return false;
    if (expectedToBeShowing && getPopoverVisibilityState2(element2) !== "showing") {
      return false;
    }
    if (!expectedToBeShowing && getPopoverVisibilityState2(element2) !== "hidden") {
      return false;
    }
    if (element2 instanceof HTMLDialogElement2 && element2.hasAttribute("open")) {
      return false;
    }
    if (document.fullscreenElement === element2) return false;
    return true;
  }
  function getStackPosition2(popover) {
    if (!popover) return 0;
    return Array.from(autoPopoverList2.get(popover.ownerDocument) || []).indexOf(
      popover
    ) + 1;
  }
  function topMostClickedPopover2(target) {
    const clickedPopover = nearestInclusiveOpenPopover2(target);
    const invokerPopover = nearestInclusiveTargetPopoverForInvoker2(target);
    if (getStackPosition2(clickedPopover) > getStackPosition2(invokerPopover)) {
      return clickedPopover;
    }
    return invokerPopover;
  }
  function topMostAutoPopover2(document2) {
    const documentPopovers = autoPopoverList2.get(document2);
    for (const popover of documentPopovers || []) {
      if (!popover.isConnected) {
        documentPopovers.delete(popover);
      } else {
        return popover;
      }
    }
    return null;
  }
  function getRootNode2(node) {
    if (typeof node.getRootNode === "function") {
      return node.getRootNode();
    }
    if (node.parentNode) return getRootNode2(node.parentNode);
    return node;
  }
  function nearestInclusiveOpenPopover2(node) {
    while (node) {
      if (node instanceof HTMLElement && node.popover === "auto" && visibilityState2.get(node) === "showing") {
        return node;
      }
      node = node instanceof Element && node.assignedSlot || node.parentElement || getRootNode2(node);
      if (node instanceof ShadowRoot3) node = node.host;
      if (node instanceof Document) return;
    }
  }
  function nearestInclusiveTargetPopoverForInvoker2(node) {
    while (node) {
      const nodePopover = node.popoverTargetElement;
      if (nodePopover instanceof HTMLElement) return nodePopover;
      node = node.parentElement || getRootNode2(node);
      if (node instanceof ShadowRoot3) node = node.host;
      if (node instanceof Document) return;
    }
  }
  function topMostPopoverAncestor2(newPopover) {
    const popoverPositions = /* @__PURE__ */ new Map();
    let i = 0;
    for (const popover of autoPopoverList2.get(newPopover.ownerDocument) || []) {
      popoverPositions.set(popover, i);
      i += 1;
    }
    popoverPositions.set(newPopover, i);
    i += 1;
    let topMostPopoverAncestor22 = null;
    function checkAncestor(candidate) {
      const candidateAncestor = nearestInclusiveOpenPopover2(candidate);
      if (candidateAncestor === null) return null;
      const candidatePosition = popoverPositions.get(candidateAncestor);
      if (topMostPopoverAncestor22 === null || popoverPositions.get(topMostPopoverAncestor22) < candidatePosition) {
        topMostPopoverAncestor22 = candidateAncestor;
      }
    }
    checkAncestor(newPopover.parentElement || getRootNode2(newPopover));
    return topMostPopoverAncestor22;
  }
  function isFocusable2(focusTarget) {
    if (focusTarget.hidden || focusTarget instanceof ShadowRoot3) return false;
    if (focusTarget instanceof HTMLButtonElement || focusTarget instanceof HTMLInputElement || focusTarget instanceof HTMLSelectElement || focusTarget instanceof HTMLTextAreaElement || focusTarget instanceof HTMLOptGroupElement || focusTarget instanceof HTMLOptionElement || focusTarget instanceof HTMLFieldSetElement) {
      if (focusTarget.disabled) return false;
    }
    if (focusTarget instanceof HTMLInputElement && focusTarget.type === "hidden") {
      return false;
    }
    if (focusTarget instanceof HTMLAnchorElement && focusTarget.href === "") {
      return false;
    }
    return typeof focusTarget.tabIndex === "number" && focusTarget.tabIndex !== -1;
  }
  function focusDelegate2(focusTarget) {
    if (focusTarget.shadowRoot && focusTarget.shadowRoot.delegatesFocus !== true) {
      return null;
    }
    let whereToLook = focusTarget;
    if (whereToLook.shadowRoot) {
      whereToLook = whereToLook.shadowRoot;
    }
    let autoFocusDelegate = whereToLook.querySelector("[autofocus]");
    if (autoFocusDelegate) {
      return autoFocusDelegate;
    } else {
      const slots = whereToLook.querySelectorAll("slot");
      for (const slot of slots) {
        const assignedElements = slot.assignedElements({ flatten: true });
        for (const el of assignedElements) {
          if (el.hasAttribute("autofocus")) {
            return el;
          } else {
            autoFocusDelegate = el.querySelector("[autofocus]");
            if (autoFocusDelegate) {
              return autoFocusDelegate;
            }
          }
        }
      }
    }
    const walker2 = focusTarget.ownerDocument.createTreeWalker(
      whereToLook,
      NodeFilter.SHOW_ELEMENT
    );
    let descendant = walker2.currentNode;
    while (descendant) {
      if (isFocusable2(descendant)) {
        return descendant;
      }
      descendant = walker2.nextNode();
    }
  }
  function popoverFocusingSteps2(subject) {
    focusDelegate2(subject)?.focus();
  }
  var previouslyFocusedElements2 = /* @__PURE__ */ new WeakMap();
  function showPopover2(element2) {
    if (!checkPopoverValidity2(element2, false)) {
      return;
    }
    const document2 = element2.ownerDocument;
    if (!element2.dispatchEvent(
      new ToggleEvent2("beforetoggle", {
        cancelable: true,
        oldState: "closed",
        newState: "open"
      })
    )) {
      return;
    }
    if (!checkPopoverValidity2(element2, false)) {
      return;
    }
    let shouldRestoreFocus = false;
    if (element2.popover === "auto") {
      const originalType = element2.getAttribute("popover");
      const ancestor = topMostPopoverAncestor2(element2) || document2;
      hideAllPopoversUntil2(ancestor, false, true);
      if (originalType !== element2.getAttribute("popover") || !checkPopoverValidity2(element2, false)) {
        return;
      }
    }
    if (!topMostAutoPopover2(document2)) {
      shouldRestoreFocus = true;
    }
    previouslyFocusedElements2.delete(element2);
    const originallyFocusedElement = document2.activeElement;
    element2.classList.add(":popover-open");
    visibilityState2.set(element2, "showing");
    if (!topLayerElements2.has(document2)) {
      topLayerElements2.set(document2, /* @__PURE__ */ new Set());
    }
    topLayerElements2.get(document2).add(element2);
    popoverFocusingSteps2(element2);
    if (element2.popover === "auto") {
      if (!autoPopoverList2.has(document2)) {
        autoPopoverList2.set(document2, /* @__PURE__ */ new Set());
      }
      autoPopoverList2.get(document2).add(element2);
      setInvokerAriaExpanded2(popoverInvoker2.get(element2), true);
    }
    if (shouldRestoreFocus && originallyFocusedElement && element2.popover === "auto") {
      previouslyFocusedElements2.set(element2, originallyFocusedElement);
    }
    queuePopoverToggleEventTask2(element2, "closed", "open");
  }
  function hidePopover2(element2, focusPreviousElement = false, fireEvents = false) {
    if (!checkPopoverValidity2(element2, true)) {
      return;
    }
    const document2 = element2.ownerDocument;
    if (element2.popover === "auto") {
      hideAllPopoversUntil2(element2, focusPreviousElement, fireEvents);
      if (!checkPopoverValidity2(element2, true)) {
        return;
      }
    }
    setInvokerAriaExpanded2(popoverInvoker2.get(element2), false);
    popoverInvoker2.delete(element2);
    if (fireEvents) {
      element2.dispatchEvent(
        new ToggleEvent2("beforetoggle", {
          oldState: "open",
          newState: "closed"
        })
      );
      if (!checkPopoverValidity2(element2, true)) {
        return;
      }
    }
    topLayerElements2.get(document2)?.delete(element2);
    autoPopoverList2.get(document2)?.delete(element2);
    element2.classList.remove(":popover-open");
    visibilityState2.set(element2, "hidden");
    if (fireEvents) {
      queuePopoverToggleEventTask2(element2, "open", "closed");
    }
    const previouslyFocusedElement = previouslyFocusedElements2.get(element2);
    if (previouslyFocusedElement) {
      previouslyFocusedElements2.delete(element2);
      if (focusPreviousElement) {
        previouslyFocusedElement.focus();
      }
    }
  }
  function closeAllOpenPopovers2(document2, focusPreviousElement = false, fireEvents = false) {
    let popover = topMostAutoPopover2(document2);
    while (popover) {
      hidePopover2(popover, focusPreviousElement, fireEvents);
      popover = topMostAutoPopover2(document2);
    }
  }
  function hideAllPopoversUntil2(endpoint, focusPreviousElement, fireEvents) {
    const document2 = endpoint.ownerDocument || endpoint;
    if (endpoint instanceof Document) {
      return closeAllOpenPopovers2(document2, focusPreviousElement, fireEvents);
    }
    let lastToHide = null;
    let foundEndpoint = false;
    for (const popover of autoPopoverList2.get(document2) || []) {
      if (popover === endpoint) {
        foundEndpoint = true;
      } else if (foundEndpoint) {
        lastToHide = popover;
        break;
      }
    }
    if (!foundEndpoint) {
      return closeAllOpenPopovers2(document2, focusPreviousElement, fireEvents);
    }
    while (lastToHide && getPopoverVisibilityState2(lastToHide) === "showing" && autoPopoverList2.get(document2)?.size) {
      hidePopover2(lastToHide, focusPreviousElement, fireEvents);
    }
  }
  var popoverPointerDownTargets2 = /* @__PURE__ */ new WeakMap();
  function lightDismissOpenPopovers2(event) {
    if (!event.isTrusted) return;
    const target = event.composedPath()[0];
    if (!target) return;
    const document2 = target.ownerDocument;
    const topMostPopover = topMostAutoPopover2(document2);
    if (!topMostPopover) return;
    const ancestor = topMostClickedPopover2(target);
    if (ancestor && event.type === "pointerdown") {
      popoverPointerDownTargets2.set(document2, ancestor);
    } else if (event.type === "pointerup") {
      const sameTarget = popoverPointerDownTargets2.get(document2) === ancestor;
      popoverPointerDownTargets2.delete(document2);
      if (sameTarget) {
        hideAllPopoversUntil2(ancestor || document2, false, true);
      }
    }
  }
  var initialAriaExpandedValue2 = /* @__PURE__ */ new WeakMap();
  function setInvokerAriaExpanded2(el, force = false) {
    if (!el) return;
    if (!initialAriaExpandedValue2.has(el)) {
      initialAriaExpandedValue2.set(el, el.getAttribute("aria-expanded"));
    }
    const popover = el.popoverTargetElement;
    if (popover instanceof HTMLElement && popover.popover === "auto") {
      el.setAttribute("aria-expanded", String(force));
    } else {
      const initialValue = initialAriaExpandedValue2.get(el);
      if (!initialValue) {
        el.removeAttribute("aria-expanded");
      } else {
        el.setAttribute("aria-expanded", initialValue);
      }
    }
  }
  var ShadowRoot23 = globalThis.ShadowRoot || function() {
  };
  function isSupported2() {
    return typeof HTMLElement !== "undefined" && typeof HTMLElement.prototype === "object" && "popover" in HTMLElement.prototype;
  }
  function isPolyfilled() {
    return Boolean(
      document.body?.showPopover && !/native code/i.test(document.body.showPopover.toString())
    );
  }
  function patchSelectorFn2(object, name, mapper) {
    const original = object[name];
    Object.defineProperty(object, name, {
      value(selector) {
        return original.call(this, mapper(selector));
      }
    });
  }
  var nonEscapedPopoverSelector2 = /(^|[^\\]):popover-open\b/g;
  function hasLayerSupport2() {
    return typeof globalThis.CSSLayerBlockRule === "function";
  }
  function getStyles2() {
    const useLayer = hasLayerSupport2();
    return `
${useLayer ? "@layer popover-polyfill {" : ""}
  :where([popover]) {
    position: fixed;
    z-index: 2147483647;
    inset: 0;
    padding: 0.25em;
    width: fit-content;
    height: fit-content;
    border-width: initial;
    border-color: initial;
    border-image: initial;
    border-style: solid;
    background-color: canvas;
    color: canvastext;
    overflow: auto;
    margin: auto;
  }

  :where([popover]:not(.\\:popover-open)) {
    display: none;
  }

  :where(dialog[popover].\\:popover-open) {
    display: block;
  }

  :where(dialog[popover][open]) {
    display: revert;
  }

  :where([anchor].\\:popover-open) {
    inset: auto;
  }

  :where([anchor]:popover-open) {
    inset: auto;
  }

  @supports not (background-color: canvas) {
    :where([popover]) {
      background-color: white;
      color: black;
    }
  }

  @supports (width: -moz-fit-content) {
    :where([popover]) {
      width: -moz-fit-content;
      height: -moz-fit-content;
    }
  }

  @supports not (inset: 0) {
    :where([popover]) {
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
    }
  }
${useLayer ? "}" : ""}
`;
  }
  var popoverStyleSheet2 = null;
  function injectStyles2(root) {
    const styles = getStyles2();
    if (popoverStyleSheet2 === null) {
      try {
        popoverStyleSheet2 = new CSSStyleSheet();
        popoverStyleSheet2.replaceSync(styles);
      } catch {
        popoverStyleSheet2 = false;
      }
    }
    if (popoverStyleSheet2 === false) {
      const sheet = document.createElement("style");
      sheet.textContent = styles;
      if (root instanceof Document) {
        root.head.prepend(sheet);
      } else {
        root.prepend(sheet);
      }
    } else {
      root.adoptedStyleSheets = [popoverStyleSheet2, ...root.adoptedStyleSheets];
    }
  }
  function apply2() {
    if (typeof window === "undefined") return;
    window.ToggleEvent = window.ToggleEvent || ToggleEvent2;
    function rewriteSelector(selector) {
      if (selector?.includes(":popover-open")) {
        selector = selector.replace(
          nonEscapedPopoverSelector2,
          "$1.\\:popover-open"
        );
      }
      return selector;
    }
    patchSelectorFn2(Document.prototype, "querySelector", rewriteSelector);
    patchSelectorFn2(Document.prototype, "querySelectorAll", rewriteSelector);
    patchSelectorFn2(Element.prototype, "querySelector", rewriteSelector);
    patchSelectorFn2(Element.prototype, "querySelectorAll", rewriteSelector);
    patchSelectorFn2(Element.prototype, "matches", rewriteSelector);
    patchSelectorFn2(Element.prototype, "closest", rewriteSelector);
    patchSelectorFn2(
      DocumentFragment.prototype,
      "querySelectorAll",
      rewriteSelector
    );
    Object.defineProperties(HTMLElement.prototype, {
      popover: {
        enumerable: true,
        configurable: true,
        get() {
          if (!this.hasAttribute("popover")) return null;
          const value3 = (this.getAttribute("popover") || "").toLowerCase();
          if (value3 === "" || value3 == "auto") return "auto";
          return "manual";
        },
        set(value3) {
          if (value3 === null) {
            this.removeAttribute("popover");
          } else {
            this.setAttribute("popover", value3);
          }
        }
      },
      showPopover: {
        enumerable: true,
        configurable: true,
        value() {
          showPopover2(this);
        }
      },
      hidePopover: {
        enumerable: true,
        configurable: true,
        value() {
          hidePopover2(this, true, true);
        }
      },
      togglePopover: {
        enumerable: true,
        configurable: true,
        value(force) {
          if (visibilityState2.get(this) === "showing" && force === void 0 || force === false) {
            hidePopover2(this, true, true);
          } else if (force === void 0 || force === true) {
            showPopover2(this);
          }
        }
      }
    });
    const originalAttachShadow = Element.prototype.attachShadow;
    if (originalAttachShadow) {
      Object.defineProperties(Element.prototype, {
        attachShadow: {
          enumerable: true,
          configurable: true,
          writable: true,
          value(options) {
            const shadowRoot = originalAttachShadow.call(this, options);
            injectStyles2(shadowRoot);
            return shadowRoot;
          }
        }
      });
    }
    const originalAttachInternals = HTMLElement.prototype.attachInternals;
    if (originalAttachInternals) {
      Object.defineProperties(HTMLElement.prototype, {
        attachInternals: {
          enumerable: true,
          configurable: true,
          writable: true,
          value() {
            const internals = originalAttachInternals.call(this);
            if (internals.shadowRoot) {
              injectStyles2(internals.shadowRoot);
            }
            return internals;
          }
        }
      });
    }
    const popoverTargetAssociatedElements = /* @__PURE__ */ new WeakMap();
    function applyPopoverInvokerElementMixin(ElementClass) {
      Object.defineProperties(ElementClass.prototype, {
        popoverTargetElement: {
          enumerable: true,
          configurable: true,
          set(targetElement) {
            if (targetElement === null) {
              this.removeAttribute("popovertarget");
              popoverTargetAssociatedElements.delete(this);
            } else if (!(targetElement instanceof Element)) {
              throw new TypeError(
                `popoverTargetElement must be an element or null`
              );
            } else {
              this.setAttribute("popovertarget", "");
              popoverTargetAssociatedElements.set(this, targetElement);
            }
          },
          get() {
            if (this.localName !== "button" && this.localName !== "input") {
              return null;
            }
            if (this.localName === "input" && this.type !== "reset" && this.type !== "image" && this.type !== "button") {
              return null;
            }
            if (this.disabled) {
              return null;
            }
            if (this.form && this.type === "submit") {
              return null;
            }
            const targetElement = popoverTargetAssociatedElements.get(this);
            if (targetElement && targetElement.isConnected) {
              return targetElement;
            } else if (targetElement && !targetElement.isConnected) {
              popoverTargetAssociatedElements.delete(this);
              return null;
            }
            const root = getRootNode2(this);
            const idref = this.getAttribute("popovertarget");
            if ((root instanceof Document || root instanceof ShadowRoot23) && idref) {
              return root.getElementById(idref) || null;
            }
            return null;
          }
        },
        popoverTargetAction: {
          enumerable: true,
          configurable: true,
          get() {
            const value3 = (this.getAttribute("popovertargetaction") || "").toLowerCase();
            if (value3 === "show" || value3 === "hide") return value3;
            return "toggle";
          },
          set(value3) {
            this.setAttribute("popovertargetaction", value3);
          }
        }
      });
    }
    applyPopoverInvokerElementMixin(HTMLButtonElement);
    applyPopoverInvokerElementMixin(HTMLInputElement);
    const handleInvokerActivation = (event) => {
      const composedPath = event.composedPath();
      const target = composedPath[0];
      if (!(target instanceof Element) || target?.shadowRoot) {
        return;
      }
      const root = getRootNode2(target);
      if (!(root instanceof ShadowRoot23 || root instanceof Document)) {
        return;
      }
      const invoker = composedPath.find(
        (el) => el.matches?.("[popovertargetaction],[popovertarget]")
      );
      if (invoker) {
        popoverTargetAttributeActivationBehavior2(invoker);
        event.preventDefault();
        return;
      }
    };
    const onKeydown = (event) => {
      const key = event.key;
      const target = event.target;
      if (!event.defaultPrevented && target && (key === "Escape" || key === "Esc")) {
        hideAllPopoversUntil2(target.ownerDocument, true, true);
      }
    };
    const addEventListeners = (root) => {
      root.addEventListener("click", handleInvokerActivation);
      root.addEventListener("keydown", onKeydown);
      root.addEventListener("pointerdown", lightDismissOpenPopovers2);
      root.addEventListener("pointerup", lightDismissOpenPopovers2);
    };
    addEventListeners(document);
    injectStyles2(document);
  }

  // js/utils.js
  var styleSheet;
  function inject(callback) {
    let styles = callback({
      css: (strings, ...values) => `@layer base { ${strings.raw[0] + values.join("")} }`
    });
    if (document.adoptedStyleSheets === void 0) {
      let styleElement = document.createElement("style");
      styleElement.textContent = styles;
      document.head.appendChild(styleElement);
      return;
    }
    if (styleSheet === void 0) {
      styleSheet = new CSSStyleSheet();
      document.adoptedStyleSheets = [...document.adoptedStyleSheets, styleSheet];
    }
    styleSheet.insertRule(styles);
  }
  function closest(el, condition) {
    let current = el;
    while (current) {
      if (condition(current)) return current;
      current = current.parentElement;
    }
  }
  function walker(el, callback) {
    let walker2 = document.createTreeWalker(
      el,
      NodeFilter.SHOW_ELEMENT,
      callback ? {
        acceptNode: (el2) => {
          let skipped, rejected;
          callback(el2, {
            skip: () => skipped = true,
            reject: () => rejected = true
          });
          if (skipped) return NodeFilter.FILTER_SKIP;
          if (rejected) return NodeFilter.FILTER_REJECT;
          return NodeFilter.FILTER_ACCEPT;
        }
      } : {}
    );
    return new Traverse(walker2);
  }
  var Traverse = class {
    constructor(walker2) {
      this.walker = walker2;
    }
    from(el) {
      this.walker.currentNode = el;
      return this;
    }
    first() {
      return this.walker.firstChild();
    }
    last() {
      return this.walker.lastChild();
    }
    next(el) {
      this.walker.currentNode = el;
      return this.walker.nextSibling();
    }
    nextOrFirst(el) {
      let found = this.next(el);
      if (found) return found;
      this.walker.currentNode = this.walker.root;
      return this.first();
    }
    prev(el) {
      this.walker.currentNode = el;
      return this.walker.previousSibling();
    }
    prevOrLast(el) {
      let found = this.prev(el);
      if (found) return found;
      this.walker.currentNode = this.walker.root;
      return this.last();
    }
    closest(el, condition) {
      let walker2 = this.from(el).walker;
      while (walker2.currentNode) {
        if (condition(walker2.currentNode)) return walker2.currentNode;
        walker2.parentNode();
      }
    }
    contains(el) {
      return this.find((i) => i === el);
    }
    find(callback) {
      return this.walk((el, bail) => {
        callback(el) && bail(el);
      });
    }
    findOrFirst(callback) {
      let found = this.find(callback);
      if (!found) this.walker.currentNode = this.walker.root;
      return this.first();
    }
    each(callback) {
      this.walk((el) => callback(el));
    }
    some(callback) {
      return !!this.find(callback);
    }
    every(callback) {
      let every = true;
      this.walk((el) => {
        callback(el) || (every = false);
      });
      return every;
    }
    map(callback) {
      let els = [];
      this.walk((el) => els.push(callback(el)));
      return els;
    }
    filter(callback) {
      let els = [];
      this.walk((el) => callback(el) && els.push(el));
      return els;
    }
    walk(callback) {
      let current;
      let walker2 = this.walker;
      let bailed;
      while (walker2.nextNode()) {
        current = walker2.currentNode;
        callback(current, (bailValue) => bailed = bailValue);
        if (bailed !== void 0) {
          break;
        }
      }
      return bailed;
    }
  };
  function element(name, type) {
    customElements.define(`ui-${name}`, type);
  }
  function on(target, event, handler, options = {}) {
    target.addEventListener(event, handler, options);
    return {
      off: () => target.removeEventListener(event, handler),
      pause: (callback) => {
        target.removeEventListener(event, handler), callback();
        target.addEventListener(event, handler);
      }
    };
  }
  function isFocusable3(el) {
    let selectors = [
      "a[href]",
      "area[href]",
      "input:not([disabled])",
      "select:not([disabled])",
      "textarea:not([disabled])",
      "button:not([disabled])",
      "iframe",
      "object",
      "embed",
      "[tabindex]",
      "[contenteditable]"
    ];
    return selectors.some((selector) => el.matches(selector)) && el.tabIndex >= 0;
  }
  function throttle(func, limit) {
    let inThrottle;
    return function() {
      let context = this, args = arguments;
      if (!inThrottle) {
        func.apply(context, args);
        inThrottle = true;
        setTimeout(() => inThrottle = false, limit);
      }
    };
  }
  function timeout(callback, delay) {
    let timerId;
    let start;
    let remaining = delay;
    let active = false;
    let timeout2 = {
      pause: () => {
        if (!active) return;
        clearTimeout(timerId);
        remaining -= Date.now() - start;
        active = false;
      },
      resume: () => {
        if (active) return;
        start = Date.now();
        timerId = setTimeout(callback, remaining);
        active = true;
      },
      cancel: () => {
        clearTimeout(timerId);
        active = false;
        remaining = delay;
      }
    };
    timeout2.resume();
    return timeout2;
  }
  var using = "pointer";
  document.addEventListener("keydown", () => using = "keyboard", { capture: true });
  document.addEventListener("pointerdown", (e) => {
    using = e.pointerType === "mouse" ? "mouse" : "touch";
  }, { capture: true });
  document.addEventListener("pointermove", (e) => {
    using = e.pointerType === "mouse" ? "mouse" : "touch";
  }, { capture: true });
  function isUsingKeyboard() {
    return using === "keyboard";
  }
  function isUsingTouch() {
    return using === "touch";
  }
  function search(el, callback) {
    let runningQuery = "";
    let clearRunningQuery = debounce(() => {
      runningQuery = "";
    }, 300);
    el.addEventListener("keydown", (e) => {
      if (e.key.length === 1 && /[a-zA-Z]/.test(e.key)) {
        runningQuery += e.key;
        callback(runningQuery);
        e.stopPropagation();
      }
      clearRunningQuery();
    });
  }
  function dispenseId(el, prefix) {
    return "lofi-" + (prefix ? prefix + "-" : "") + Math.random().toString(16).slice(2);
  }
  function assignId(el, prefix) {
    let id = el.hasAttribute("id") ? el.getAttribute("id") : dispenseId(el, prefix);
    setAttribute2(el, "id", id);
    if (!el._x_bindings) el._x_bindings = {};
    if (!el._x_bindings.id) el._x_bindings.id = id;
    return id;
  }
  function detangle() {
    let blocked = false;
    return (callback) => (...args) => {
      if (blocked) return;
      blocked = true;
      try {
        callback(...args);
      } finally {
        blocked = false;
      }
    };
  }
  function interest(trigger, panel, { gain, lose, focusable, useSafeArea }) {
    let engaged = false;
    let focusInHander = (e) => {
      if (!isUsingKeyboard()) return;
      if (trigger.contains(e.target) || panel.contains(e.target)) {
        engaged = true;
        gain();
      } else {
        engaged = false;
        lose();
      }
    };
    focusable && document.addEventListener("focusin", focusInHander);
    let removeFocusInHandler = () => {
      document.removeEventListener("focusin", focusInHander);
    };
    let removeSafeArea = () => {
    };
    let removePointerMoveHandler = () => {
    };
    let disinterest = () => {
      engaged = false;
      lose();
      removeSafeArea();
      removePointerMoveHandler();
    };
    let clear = () => {
      engaged = false;
      removeSafeArea();
      removePointerMoveHandler();
    };
    let pointerEnterHandler = (e) => {
      if (isUsingTouch()) return;
      if (engaged) return;
      engaged = true;
      gain();
      setTimeout(() => {
        let { safeArea, redraw: redrawSafeArea, remove: remove2 } = useSafeArea ? createSafeArea(trigger, panel, e.clientX, e.clientY) : nullSafeArea();
        removeSafeArea = remove2;
        let pointerStoppedOverSafeAreaTimeout;
        let pointerMoveHandler = throttle((e2) => {
          let panelRect = panel.getBoundingClientRect();
          let triggerRect = trigger.getBoundingClientRect();
          let mouseState;
          if (safeArea.contains(e2.target) && mouseIsExclusivelyInsideSafeArea(triggerRect, panelRect, e2.clientX, e2.clientY)) mouseState = "safeArea";
          else if (panel.contains(e2.target)) mouseState = "panel";
          else if (trigger.contains(e2.target)) mouseState = "trigger";
          else mouseState = "outside";
          if (pointerStoppedOverSafeAreaTimeout) {
            clearTimeout(pointerStoppedOverSafeAreaTimeout);
          }
          switch (mouseState) {
            case "outside":
              disinterest();
              break;
            case "trigger":
              redrawSafeArea(e2.clientX, e2.clientY);
              break;
            case "panel":
              removeSafeArea();
              break;
            case "safeArea":
              redrawSafeArea(e2.clientX, e2.clientY);
              pointerStoppedOverSafeAreaTimeout = setTimeout(() => {
                disinterest();
              }, 300);
              break;
            default:
              break;
          }
        }, 100);
        document.addEventListener("pointermove", pointerMoveHandler);
        removePointerMoveHandler = () => document.removeEventListener("pointermove", pointerMoveHandler);
      });
    };
    trigger.addEventListener("pointerenter", pointerEnterHandler);
    let removePointerEnterHandler = () => {
      trigger.removeEventListener("pointerenter", pointerEnterHandler);
    };
    let remove = () => {
      clear();
      removePointerEnterHandler();
      removeFocusInHandler();
    };
    return { clear, remove };
  }
  function createSafeArea(trigger, panel, x, y) {
    let safeArea = document.createElement("div");
    let panelRect = panel.getBoundingClientRect();
    let triggerRect = trigger.getBoundingClientRect();
    safeArea.style.position = "fixed";
    setAttribute2(safeArea, "data-safe-area", "");
    let draw = (x2, y2) => {
      if (panelRect.top === 0 && panelRect.bottom === 0) return;
      let direction;
      if (panelRect.left < triggerRect.left) direction = "left";
      if (panelRect.right > triggerRect.right) direction = "right";
      if (panelRect.top < triggerRect.top && panelRect.bottom < y2) direction = "up";
      if (panelRect.bottom > triggerRect.bottom && panelRect.top > y2) direction = "down";
      if (direction === void 0) direction = "right";
      let left, right, width, top, bottom, height, offset3, shape;
      let padding = 10;
      switch (direction) {
        case "left":
          left = panelRect.right;
          right = Math.max(panelRect.right, x2) + 5;
          width = right - left;
          top = Math.min(triggerRect.top, panelRect.top) - padding;
          bottom = Math.max(triggerRect.bottom, panelRect.bottom) + padding;
          height = bottom - top;
          offset3 = y2 - top;
          shape = `polygon(0% 0%, 100% ${offset3}px, 0% 100%)`;
          break;
        case "right":
          left = Math.min(panelRect.left, x2) - 5;
          right = panelRect.left;
          width = right - left;
          top = Math.min(triggerRect.top, panelRect.top) - padding;
          bottom = Math.max(triggerRect.bottom, panelRect.bottom) + padding;
          height = bottom - top;
          offset3 = y2 - top;
          shape = `polygon(0% ${offset3}px, 100% 0%, 100% 100%)`;
          break;
        case "up":
          left = Math.min(x2, panelRect.left) - padding;
          right = Math.max(x2, panelRect.right) + padding;
          width = right - left;
          top = panelRect.bottom;
          bottom = Math.max(panelRect.bottom, y2) + 5;
          height = bottom - top;
          offset3 = x2 - left;
          shape = `polygon(0% 0%, 100% 0%, ${offset3}px 100%)`;
          break;
        case "down":
          left = Math.min(x2, panelRect.left) - padding;
          right = Math.max(x2, panelRect.right) + padding;
          width = right - left;
          top = Math.min(panelRect.top, y2) - 5;
          bottom = panelRect.top;
          height = bottom - top;
          offset3 = x2 - left;
          shape = `polygon(${offset3}px 0%, 100% 100%, 0% 100%)`;
          break;
      }
      safeArea.style.left = `${left}px`;
      safeArea.style.top = `${top}px`;
      safeArea.style.width = `${width}px`;
      safeArea.style.height = `${height}px`;
      safeArea.style.clipPath = shape;
    };
    return {
      safeArea,
      redraw: (x2, y2) => {
        if (!safeArea.isConnected) trigger.appendChild(safeArea);
        draw(x2, y2);
      },
      remove: () => {
        safeArea.remove();
      }
    };
  }
  function mouseIsExclusivelyInsideSafeArea(triggerRect, panelRect, x, y) {
    return !mouseIsOverTrigger(triggerRect, x, y) && !mouseIsOverPanel(panelRect, x, y);
  }
  function mouseIsOverTrigger(triggerRect, x, y) {
    if (triggerRect.left <= x && x <= triggerRect.right && (triggerRect.top <= y && y <= triggerRect.bottom)) return true;
    return false;
  }
  function mouseIsOverPanel(panelRect, x, y) {
    if (panelRect.left <= x && x <= panelRect.right && (panelRect.top <= y && y <= panelRect.bottom)) return true;
    return false;
  }
  function setAttribute2(el, name, value3) {
    if (el._durableAttributeObserver === void 0) {
      el._durableAttributeObserver = attributeObserver(el, [name]);
    }
    if (!el._durableAttributeObserver.hasAttribute(name)) {
      el._durableAttributeObserver.addAttribute(name);
    }
    el._durableAttributeObserver.pause(() => {
      el.setAttribute(name, value3);
    });
  }
  function removeAndReleaseAttribute(el, name) {
    removeAttribute(el, name);
    releaseAttribute(el, name);
  }
  function removeAttribute(el, name) {
    if (el._durableAttributeObserver === void 0) {
      el._durableAttributeObserver = attributeObserver(el, [name]);
    }
    if (!el._durableAttributeObserver.hasAttribute(name)) {
      el._durableAttributeObserver.addAttribute(name);
    }
    el._durableAttributeObserver.pause(() => {
      el.removeAttribute(name);
    });
  }
  function releaseAttribute(el, name) {
    if (!el?._durableAttributeObserver?.hasAttribute(name)) return;
    el._durableAttributeObserver.releaseAttribute(name);
  }
  function attributeObserver(el, initialAttributes) {
    let processMutations = (mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.oldValue === null) {
          el._durableAttributeObserver.pause(() => removeAttribute(el, mutation.attributeName));
        } else {
          el._durableAttributeObserver.pause(() => setAttribute2(el, mutation.attributeName, mutation.oldValue));
        }
      });
    };
    let observer = new MutationObserver((mutations) => processMutations(mutations));
    observer.observe(el, { attributeFilter: initialAttributes, attributeOldValue: true });
    return {
      attributes: initialAttributes,
      hasAttribute(name) {
        return this.attributes.includes(name);
      },
      addAttribute(name) {
        this.attributes.includes(name) || this.attributes.push(name);
        observer.observe(el, { attributeFilter: this.attributes, attributeOldValue: true });
      },
      releaseAttribute(name) {
        if (!this.hasAttribute(name)) return;
        observer.observe(el, { attributeFilter: this.attributes, attributeOldValue: true });
      },
      pause(callback) {
        processMutations(observer.takeRecords());
        observer.disconnect();
        callback();
        observer.observe(el, { attributeFilter: this.attributes, attributeOldValue: true });
      }
    };
  }
  function nullSafeArea() {
    return {
      safeArea: { contains: () => false },
      redraw: () => {
      },
      remove: () => {
      }
    };
  }
  function debounce(callback, delay) {
    let timeout2;
    return (...args) => {
      clearTimeout(timeout2);
      timeout2 = setTimeout(() => {
        callback(...args);
      }, delay);
    };
  }
  var lockCount = 0;
  var pointerEventsLocked = false;
  inject(({ css }) => css`[data-flux-allow-scroll] { pointer-events: auto; }`);
  function lockScroll(el = null, allowScroll = false, except = []) {
    if (allowScroll) return { lock: () => {
    }, unlock: () => {
    } };
    let applyDocumentLockStyles = (disablePointerEvents = false) => {
      undoLockStyles(document.documentElement);
      let lockStyles = {
        overflow: "hidden",
        ...disablePointerEvents ? { pointerEvents: "none" } : {}
      };
      if (window.CSS && CSS.supports && CSS.supports("scrollbar-gutter: stable")) {
        if (document.documentElement.scrollHeight > document.documentElement.clientHeight && window.getComputedStyle(document.documentElement).overflowY !== "hidden") {
          lockStyles.scrollbarGutter = "stable";
        }
      } else {
        lockStyles.paddingRight = `calc(${window.innerWidth - document.documentElement.clientWidth}px + ${window.getComputedStyle(document.documentElement).paddingRight})`;
      }
      setLockStyles(document.documentElement, lockStyles);
      if (disablePointerEvents) {
        setAttribute2(el, "data-flux-allow-scroll", "");
        except.forEach((el2) => {
          setAttribute2(el2, "data-flux-allow-scroll", "");
        });
        pointerEventsLocked = true;
      }
    };
    let removeDocumentLockStyles = (enablePointerEvents = false) => {
      undoLockStyles(document.documentElement);
      if (enablePointerEvents) {
        removeAndReleaseAttribute(el, "data-flux-allow-scroll");
        except.forEach((el2) => {
          removeAttribute(el2, "data-flux-allow-scroll");
        });
        pointerEventsLocked = false;
      }
    };
    return {
      lock() {
        lockCount++;
        if (lockCount > 1 && el !== null && pointerEventsLocked) return;
        applyDocumentLockStyles(el !== null && !pointerEventsLocked);
      },
      unlock() {
        lockCount = Math.max(0, lockCount - 1);
        if (lockCount > 0 && el !== null && !pointerEventsLocked) return;
        removeDocumentLockStyles(el !== null && pointerEventsLocked);
        if (lockCount > 0) {
          applyDocumentLockStyles(false);
        }
      }
    };
  }
  function setLockStyles(element2, styles) {
    let unlockedStyles = JSON.parse(element2.getAttribute(`data-flux-scroll-unlock`) || "{}");
    Object.entries(styles).forEach(([style, value3]) => {
      if (unlockedStyles[style] === void 0) {
        unlockedStyles[style] = element2.style[style];
        element2.style[style] = value3;
      }
    });
    element2.setAttribute(`data-flux-scroll-unlock`, JSON.stringify(unlockedStyles));
  }
  function undoLockStyles(element2) {
    let unlockedStyles = JSON.parse(element2.getAttribute(`data-flux-scroll-unlock`) || "{}");
    Object.entries(unlockedStyles).forEach(([style, value3]) => {
      element2.style[style] = value3;
    });
    element2.removeAttribute(`data-flux-scroll-unlock`);
  }
  function setStyle(element2, style, value3) {
    let currentValue = element2.style[style];
    element2.style[style] = value3;
    return () => {
      element2.style[style] = currentValue;
    };
  }
  function initFauxButton(el, isDisabled, action) {
    let ifKey = (key, callback) => (e) => {
      if (e.key === key && !isDisabled()) {
        callback();
        e.preventDefault();
        e.stopPropagation();
      }
    };
    setAttribute2(el, "role", "button");
    let syncDisabledAttributes = () => {
      if (el.hasAttribute("disabled")) {
        setAttribute2(el, "aria-disabled", "true");
        setAttribute2(el, "tabindex", "-1");
      } else {
        removeAttribute(el, "aria-disabled");
        setAttribute2(el, "tabindex", "0");
      }
    };
    let observer = new MutationObserver(() => syncDisabledAttributes());
    observer.observe(el, { attributes: true, attributeFilter: ["disabled"] });
    syncDisabledAttributes();
    on(el, "click", () => action());
    on(el, "keydown", ifKey("Enter", () => action()));
    on(el, "keydown", ifKey(" ", () => {
    }));
    on(el, "keyup", ifKey(" ", () => action()));
  }
  function getLocale() {
    return navigator?.language || document.documentElement.lang || "en-US";
  }
  function hydrateTemplate(template, slotsAndAttributes = { slots: {}, attrs: {} }) {
    let { slots = {}, attrs = {} } = slotsAndAttributes;
    let clone = template.content.cloneNode(true).firstElementChild;
    Object.entries(slots).forEach(([key, value3]) => {
      let slotNodes = key === "default" ? clone.querySelectorAll("slot:not([name])") : clone.querySelectorAll(`slot[name="${key}"]`);
      slotNodes.forEach((i) => i.replaceWith(
        typeof value3 === "string" ? document.createTextNode(value3) : value3
      ));
    });
    clone.querySelectorAll("slot").forEach((slot) => slot.remove());
    Object.entries(attrs).forEach(([key, value3]) => {
      clone.setAttribute(key, value3);
    });
    clone.setAttribute("data-appended", "");
    return clone;
  }
  function isRTL(el) {
    return getComputedStyle(el).direction === "rtl";
  }
  function isSafari() {
    return /^((?!chrome|android).)*safari/i.test(navigator.userAgent) && !navigator.userAgent.includes("CriOS") && !navigator.userAgent.includes("FxiOS");
  }
  function isIOS() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
  }
  var Observable = class {
    constructor() {
      this.subscribers = [];
    }
    subscribe(reason, callback) {
      this.subscribers.push({ reason, callback });
    }
    notify(reason, data) {
      this.subscribers.forEach(({ reason: subReason, callback }) => {
        if (reason === subReason) {
          callback(data);
        }
      });
    }
  };

  // js/element.js
  var UIElement = class extends HTMLElement {
    wasDisconnected = false;
    onUnmounts = [];
    constructor() {
      super();
      this.boot?.();
    }
    connectedCallback() {
      if (this.wasDisconnected) {
        this.wasDisconnected = false;
        return;
      }
      queueMicrotask(() => {
        this.mount?.();
      });
    }
    disconnectedCallback() {
      this.wasDisconnected = true;
      queueMicrotask(() => {
        if (this.wasDisconnected) {
          this.unmount?.();
          this.onUnmounts.forEach((i) => i());
          this.onUnmounts = [];
        }
        this.wasDisconnected = false;
      });
    }
    onUnmount(callback) {
      this.onUnmounts.push(callback);
    }
    mixin(func, options = {}) {
      return new func(this, options);
    }
    // @todo: this is redundant now...
    appendMixin(func, options = {}) {
      return new func(this, options);
    }
    use(func) {
      let found;
      this.mixins.forEach((mixin) => {
        if (mixin instanceof func) found = mixin;
      });
      return found;
    }
    uses(func) {
      let found;
      this.mixins.forEach((mixin) => {
        if (mixin instanceof func) found = true;
      });
      return !!found;
    }
    on(event, handler) {
      return on(this, event, handler);
    }
    root(name, attributes = {}) {
      if (name === void 0) return this.__root;
      let el = document.createElement(name);
      for (let name2 in attributes) {
        setAttribute(el, name2, attributes[name2]);
      }
      let shadow = this.attachShadow({ mode: "open" });
      el.appendChild(document.createElement("slot"));
      shadow.appendChild(el);
      this.__root = el;
      return this.__root;
    }
  };
  var UIControl = class extends UIElement {
    //
  };

  // js/mixins/mixin.js
  var Mixin = class {
    constructor(el, options = {}) {
      this.el = el;
      this.grouped = options.grouped === void 0 ? true : false;
      this.el.mixins = this.el.mixins ? this.el.mixins : /* @__PURE__ */ new Map();
      this.el.mixins.set(this.constructor.name, this);
      this.el[this.constructor.name] = true;
      if (!this.el.use) this.el.use = UIElement.prototype.use.bind(this.el);
      this.opts = options;
      this.boot?.({
        options: (defaults) => {
          let options2 = defaults;
          Object.entries(this.opts).forEach(([key, value3]) => {
            if (value3 !== void 0) {
              options2[key] = value3;
            }
          });
          this.opts = options2;
        }
      });
      queueMicrotask(() => {
        this.mount?.();
      });
    }
    options() {
      return this.opts;
    }
    hasGroup() {
      return !!this.group();
    }
    group() {
      if (this.grouped === false) return;
      return closest(this.el, (i) => i[this.groupedByType.name])?.use(this.groupedByType);
    }
    on(event, handler) {
      return on(this.el, event, handler);
    }
  };
  var MixinGroup = class extends Mixin {
    constructor(el, options = {}) {
      super(el, options);
    }
    walker() {
      return walker(this.el, (el, { skip, reject }) => {
        if (el[this.constructor.name] && el !== this.el) return reject();
        if (!el[this.groupOfType.name]) return skip();
        if (!el.mixins.get(this.groupOfType.name).grouped) return skip();
      });
    }
  };

  // js/mixins/controllable.js
  var Controllable = class extends Mixin {
    boot({ options }) {
      options({
        bubbles: false
      });
      this.initialState = this.el.value;
      this.getterFunc = () => {
      };
      this.setterFunc = (value3) => this.initialState = value3;
      Object.defineProperty(this.el, "value", {
        get: () => {
          return this.getterFunc();
        },
        set: (value3) => {
          this.setterFunc(value3);
        }
      });
    }
    initial(callback) {
      callback(this.initialState);
    }
    getter(func) {
      this.getterFunc = func;
    }
    setter(func) {
      this.setterFunc = func;
    }
    dispatch() {
      this.el.dispatchEvent(new Event("input", {
        bubbles: this.options().bubbles,
        cancelable: true
      }));
      this.el.dispatchEvent(new Event("change", {
        bubbles: this.options().bubbles,
        cancelable: true
      }));
    }
  };

  // js/mixins/dialogable.js
  var lastMouseDownEvent = null;
  document.addEventListener("mousedown", (event) => lastMouseDownEvent = event);
  var Dialogable = class extends Mixin {
    boot({ options }) {
      options({
        clickOutside: true,
        triggers: []
      });
      this.onChanges = [];
      this.state = false;
      this.stopDialogFromFocusingTheFirstElement();
      let triggers = this.options().triggers;
      let observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.attributeName !== "open") return;
          this.el.hasAttribute("open") ? this.state = true : this.state = false;
        });
        this.onChanges.forEach((i) => i());
      });
      observer.observe(this.el, { attributeFilter: ["open"] });
      if (this.options().clickOutside) {
        let contentEl = this.el.querySelector("[data-flux-modal-content]") || this.el;
        this.el.addEventListener("click", (e) => {
          if (e.target !== contentEl && contentEl?.contains(e.target)) return;
          if (lastMouseDownEvent && clickHappenedOutside(contentEl, lastMouseDownEvent) && clickHappenedOutside(contentEl, e)) {
            this.cancel();
            e.preventDefault();
            e.stopPropagation();
          }
        });
      }
      if (this.el.hasAttribute("open")) {
        this.state = true;
        this.hide();
        this.show();
      }
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    show() {
      if (!this.el.isConnected) return;
      this.el.showModal();
    }
    hide() {
      this.el.close();
    }
    toggle() {
      this.state ? this.hide() : this.show();
    }
    cancel() {
      let event = new Event("cancel", { bubbles: false, cancelable: true });
      this.el.dispatchEvent(event);
      if (!event.defaultPrevented) {
        this.hide();
      }
    }
    getState() {
      return this.state;
    }
    setState(value3) {
      value3 ? this.show() : this.hide();
    }
    // By default, browsers focus the first focusable element inside a dialog when it is opened. This is bad for screen readers because
    // the focus could potentially be at the end of the dialog skipping all of the content. This also causes issues for iOS devices
    // as when inputs are focused and the keyboard is shown, hiding half of the dialog content...
    stopDialogFromFocusingTheFirstElement() {
      let placeholder = document.createElement("div");
      placeholder.setAttribute("data-flux-focus-placeholder", "");
      placeholder.setAttribute("data-appended", "");
      placeholder.setAttribute("tabindex", "0");
      this.el.prepend(placeholder);
      this.onChange(() => {
        setAttribute2(placeholder, "style", this.state ? "display: none" : "display: block");
        if (this.state && isSafari() && !this.el.hasAttribute("autofocus") && this.el.querySelectorAll("[autofocus]").length === 0) {
          setTimeout(() => {
            this.el.setAttribute("tabindex", "-1");
            this.el.focus();
            this.el.blur();
          });
        }
      });
    }
  };
  function clickHappenedOutside(el, event) {
    let rect = el.getBoundingClientRect();
    let x = event.clientX;
    let y = event.clientY;
    let isInside = x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom;
    return !isInside;
  }

  // js/mixins/closeable.js
  var Closeable = class extends Mixin {
    boot() {
      this.onCloses = [];
    }
    onClose(callback) {
      this.onCloses.push(callback);
    }
    close() {
      this.onCloses.forEach((callback) => callback());
    }
  };

  // js/modal.js
  var UIModal = class extends UIElement {
    boot() {
      this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
      this._controllable = new Controllable(this, { disabled: this.hasAttribute("disabled") });
      let button = this.button();
      let dialog = this.dialog();
      if (!dialog) return;
      dialog._dialogable = new Dialogable(dialog, {
        clickOutside: !this.hasAttribute("disable-click-outside")
      });
      dialog._closeable = new Closeable(dialog);
      dialog._closeable.onClose(() => dialog._dialogable.hide());
      this._controllable.initial((initial) => initial && dialog._dialogable.show());
      this._controllable.getter(() => dialog._dialogable.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        dialog._dialogable.setState(value3);
      }));
      dialog._dialogable.onChange(detangled(() => {
        this._controllable.dispatch();
      }));
      let refresh = () => {
        if (dialog._dialogable.getState()) {
          setAttribute2(this, "data-open", "");
          button?.setAttribute("data-open", "");
          setAttribute2(dialog, "data-open", "");
        } else {
          removeAttribute(this, "data-open");
          button?.removeAttribute("data-open");
          removeAttribute(dialog, "data-open");
        }
      };
      dialog._dialogable.onChange(() => refresh());
      refresh();
      let { lock, unlock } = lockScroll();
      dialog._dialogable.onChange(() => {
        dialog._dialogable.getState() ? lock() : unlock();
      });
      button && on(button, "click", (e) => {
        dialog._dialogable.show();
      });
    }
    unmount() {
      if (this.dialog()?._dialogable?.getState()) {
        let { unlock } = lockScroll();
        unlock();
      }
    }
    button() {
      let button = this.querySelector("button,ui-button");
      let dialog = this.dialog();
      if (dialog?.contains(button)) return;
      return button;
    }
    dialog() {
      return this.querySelector("dialog");
    }
    showModal() {
      let dialog = this.dialog();
      if (!dialog) return;
      dialog.showModal();
    }
  };
  inject(({ css }) => css`dialog, ::backdrop { margin: auto; }`);
  element("modal", UIModal);

  // js/mixins/activatable.js
  var ActivatableGroup = class extends MixinGroup {
    groupOfType = Activatable;
    boot({ options }) {
      options({
        wrap: false,
        filter: false
      });
      this.onChanges = [];
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    activated(activeEl) {
      this.onChanges.forEach((i) => i());
    }
    activateFirst() {
      this.filterAwareWalker().first()?.use(Activatable).activate();
    }
    activateBySearch(query) {
      let found = this.filterAwareWalker().find((i) => i.textContent.toLowerCase().trim().startsWith(query.toLowerCase()));
      found?.use(Activatable).activate();
    }
    activateSelectedOrFirst(selectedEl) {
      let isHidden = (el) => el.matches("ui-option, ui-option-create") ? getComputedStyle(el).display === "none" : false;
      if (!selectedEl || isHidden(selectedEl)) {
        this.filterAwareWalker().first()?.use(Activatable).activate();
        return;
      }
      selectedEl?.use(Activatable).activate();
    }
    activateActiveOrFirst() {
      let active = this.getActive();
      if (!active) {
        this.filterAwareWalker().first()?.use(Activatable).activate();
        return;
      }
      active?.use(Activatable).activate();
    }
    activateActiveOrLast() {
      let active = this.getActive();
      if (!active) {
        this.filterAwareWalker().last()?.use(Activatable).activate();
        return;
      }
      active?.use(Activatable).activate();
    }
    activatePrev() {
      let active = this.getActive();
      if (!active) {
        this.filterAwareWalker().last()?.use(Activatable).activate();
        return;
      }
      let found;
      if (this.options.wrap) {
        found = this.filterAwareWalker().prevOrLast(active);
      } else {
        found = this.filterAwareWalker().prev(active);
      }
      found?.use(Activatable).activate();
    }
    activateNext() {
      let active = this.getActive();
      if (!active) {
        this.filterAwareWalker().first()?.use(Activatable).activate();
        return;
      }
      let found;
      if (this.options.wrap) {
        found = this.filterAwareWalker().nextOrFirst(active);
      } else {
        found = this.filterAwareWalker().next(active);
      }
      found?.use(Activatable).activate();
    }
    getActive() {
      return this.walker().find((i) => i.use(Activatable).isActive());
    }
    clearActive() {
      this.getActive()?.use(Activatable).deactivate();
    }
    filterAwareWalker() {
      let isHidden = (el) => el.matches("ui-option, ui-option-create") ? getComputedStyle(el).display === "none" : false;
      return walker(this.el, (el, { skip, reject }) => {
        if (el[this.constructor.name] && el !== this.el) return reject();
        if (!el[this.groupOfType.name]) return skip();
        if (el.hasAttribute("disabled")) return reject();
        if (isHidden(el)) return reject();
      });
    }
  };
  var Activatable = class _Activatable extends Mixin {
    groupedByType = ActivatableGroup;
    mount() {
      this.el.addEventListener("mouseenter", () => {
        this.activate();
      });
      this.el.addEventListener("mouseleave", () => {
        this.deactivate();
      });
    }
    activate(force = false) {
      if (this.group()) {
        this.group().walker().each((item) => item.use(_Activatable).deactivate(false));
      }
      if (this.el.hasAttribute("disabled") && !force) return;
      setAttribute2(this.el, "data-active", "");
      if (isUsingKeyboard()) {
        this.el.scrollIntoView({ block: "nearest" });
      }
      this.group() && this.group().activated(this.el);
    }
    deactivate(notify = true) {
      removeAttribute(this.el, "data-active");
      notify && this.group() && this.group().activated(this.el);
    }
    isActive() {
      return this.el.hasAttribute("data-active");
    }
  };

  // js/mixins/filterable.js
  var FilterableGroup = class extends MixinGroup {
    groupOfType = Filterable;
    boot({ options }) {
      options({});
      this.onChanges = [];
      this.lastSearch = "";
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    filter(search2) {
      if (search2 === "") {
        this.walker().each((i) => {
          i.use(Filterable).unfilter();
        });
      } else {
        this.walker().each((i) => {
          if (this.matches(i, search2)) {
            i.use(Filterable).unfilter();
          } else {
            i.use(Filterable).filter();
          }
        });
      }
      if (this.lastSearch !== search2) {
        this.onChanges.forEach((i) => i());
      }
      this.lastSearch = search2;
    }
    matches(el, search2) {
      return this.normalize(el.textContent).includes(this.normalize(search2));
    }
    // This function normalizes the value to remove diacritics (accents) and convert to lowercase
    // to ensure that the search is case-insensitive and diacritic-insensitive...
    normalize(value3) {
      if (value3 == null) return "";
      return value3.normalize("NFD").replace(/\p{Diacritic}/gu, "").toLowerCase().trim();
    }
    hasResults() {
      return this.walker().some((i) => !i.use(Filterable).isFiltered());
    }
  };
  var Filterable = class extends Mixin {
    groupedByType = FilterableGroup;
    boot({ options }) {
      options({ mirror: null, keep: false });
      this.onChanges = [];
    }
    filter() {
      if (this.options().keep) return;
      setAttribute2(this.el, "data-hidden", "");
      if (this.options().mirror) setAttribute2(this.options().mirror, "data-hidden", "");
    }
    unfilter() {
      if (this.options().keep) return;
      removeAttribute(this.el, "data-hidden");
      if (this.options().mirror) removeAttribute(this.options().mirror, "data-hidden", "");
    }
    isFiltered() {
      return this.el.hasAttribute("data-hidden");
    }
  };

  // js/mixins/popoverable.js
  var currentlyOpenPopoversByScope = /* @__PURE__ */ new Map();
  var Popoverable = class extends Mixin {
    boot({ options }) {
      options({ triggers: [], scope: null });
      let scope = this.options().scope || "global";
      setAttribute2(this.el, "popover", "manual");
      this.triggers = this.options().triggers;
      this.onChanges = [];
      this.state = false;
      on(this.el, "beforetoggle", (e) => {
        let oldState = this.state;
        this.state = e.newState === "open";
        if (this.state) {
          closeOtherOpenPopovers(this.el, scope);
          let controller = new AbortController();
          let activeElement = document.activeElement;
          let triggers = [...this.triggers, activeElement];
          setTimeout(() => {
            closeOnClickOutside(this.el, triggers, controller);
            closeOnFocusAway(this.el, triggers, controller);
            closeOnEscape(this.el, triggers, controller);
          });
          this.el.addEventListener("beforetoggle", (e2) => {
            if (e2.newState === "closed") {
              controller.abort();
              activeElement?.focus();
            }
          }, { signal: controller.signal });
        }
        if (oldState !== this.state) {
          this.onChanges.forEach((i) => i(this.state, oldState));
        }
      });
      on(this.el, "toggle", (e) => {
        if (e.newState === "open") {
          if (!currentlyOpenPopoversByScope.has(scope)) {
            currentlyOpenPopoversByScope.set(scope, /* @__PURE__ */ new Set());
          }
          currentlyOpenPopoversByScope.get(scope).add(this.el);
        } else if (e.newState === "closed") {
          if (!currentlyOpenPopoversByScope.has(scope)) return;
          currentlyOpenPopoversByScope.get(scope).delete(this.el);
          if (currentlyOpenPopoversByScope.get(scope).size === 0) {
            currentlyOpenPopoversByScope.delete(scope);
          }
        }
      });
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    setState(value3) {
      value3 ? this.show() : this.hide();
    }
    getState() {
      return this.state;
    }
    toggle() {
      this.el.isConnected && this.el.togglePopover();
    }
    show() {
      this.el.isConnected && this.el.showPopover();
    }
    hide() {
      this.el.isConnected && this.el.hidePopover();
    }
  };
  function closeOtherOpenPopovers(el, scope) {
    if (!currentlyOpenPopoversByScope.has(scope)) return;
    currentlyOpenPopoversByScope.get(scope).forEach((popoverEl) => {
      if (el.contains(popoverEl) || popoverEl.contains(el)) return;
      popoverEl.hidePopover();
    });
  }
  function closeOnClickOutside(el, except, controller) {
    document.addEventListener("click", (e) => {
      if (el.contains(e.target) || except.includes(e.target)) return;
      el.hidePopover();
    }, { signal: controller.signal });
  }
  function closeOnFocusAway(el, except, controller) {
    document.addEventListener("focusin", (e) => {
      if (el.contains(e.target) || except.includes(e.target)) return;
      controller.abort();
      el.hidePopover();
    }, {
      // Without "capture: true", when you focus away from the popover onto an element that triggers a popover
      // on focus (a tooltip), it will focus back this popover's trigger instead of keeping focus on the tooltip button.
      // It does this because only one popover can be open at a time, so focusing the tooltip, opens a popover, closing this one,
      // which will trigger the "focus back" behavior.
      capture: true,
      signal: controller.signal
    });
  }
  function closeOnEscape(el, except, controller) {
    document.addEventListener("keydown", (e) => {
      if (e.key !== "Escape") return;
      el.hidePopover();
    }, { signal: controller.signal });
  }

  // js/mixins/disableable.js
  var Disableable = class extends Mixin {
    boot({ options }) {
      options({
        disableWithParent: true
      });
      this.onChanges = [];
      Object.defineProperty(this.el, "disabled", {
        get: () => {
          return this.el.hasAttribute("disabled");
        },
        set: (value3) => {
          if (value3) {
            this.el.setAttribute("disabled", "");
          } else {
            this.el.removeAttribute("disabled");
          }
        }
      });
      if (this.el.hasAttribute("disabled")) {
        this.el.disabled = true;
      } else if (this.options().disableWithParent && this.el.parentElement?.closest("[disabled]")) {
        this.el.disabled = true;
      }
      let observer = new MutationObserver((mutations) => {
        this.onChanges.forEach((i) => i(this.el.disabled));
      });
      observer.observe(this.el, { attributeFilter: ["disabled"] });
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    onInitAndChange(callback) {
      callback(this.el.disabled);
      this.onChanges.push(callback);
    }
    enabled(callback) {
      return (...args) => {
        if (this.el.disabled) return;
        return callback(...args);
      };
    }
    disabled(callback) {
      return (...args) => {
        if (!this.el.disabled) return;
        return callback(...args);
      };
    }
    isDisabled() {
      return this.el.disabled;
    }
  };

  // node_modules/@floating-ui/utils/dist/floating-ui.utils.mjs
  var min = Math.min;
  var max = Math.max;
  var round = Math.round;
  var floor = Math.floor;
  var createCoords = (v) => ({
    x: v,
    y: v
  });
  var oppositeSideMap = {
    left: "right",
    right: "left",
    bottom: "top",
    top: "bottom"
  };
  var oppositeAlignmentMap = {
    start: "end",
    end: "start"
  };
  function clamp(start, value3, end) {
    return max(start, min(value3, end));
  }
  function evaluate(value3, param) {
    return typeof value3 === "function" ? value3(param) : value3;
  }
  function getSide(placement) {
    return placement.split("-")[0];
  }
  function getAlignment(placement) {
    return placement.split("-")[1];
  }
  function getOppositeAxis(axis) {
    return axis === "x" ? "y" : "x";
  }
  function getAxisLength(axis) {
    return axis === "y" ? "height" : "width";
  }
  function getSideAxis(placement) {
    return ["top", "bottom"].includes(getSide(placement)) ? "y" : "x";
  }
  function getAlignmentAxis(placement) {
    return getOppositeAxis(getSideAxis(placement));
  }
  function getAlignmentSides(placement, rects, rtl) {
    if (rtl === void 0) {
      rtl = false;
    }
    const alignment = getAlignment(placement);
    const alignmentAxis = getAlignmentAxis(placement);
    const length = getAxisLength(alignmentAxis);
    let mainAlignmentSide = alignmentAxis === "x" ? alignment === (rtl ? "end" : "start") ? "right" : "left" : alignment === "start" ? "bottom" : "top";
    if (rects.reference[length] > rects.floating[length]) {
      mainAlignmentSide = getOppositePlacement(mainAlignmentSide);
    }
    return [mainAlignmentSide, getOppositePlacement(mainAlignmentSide)];
  }
  function getExpandedPlacements(placement) {
    const oppositePlacement = getOppositePlacement(placement);
    return [getOppositeAlignmentPlacement(placement), oppositePlacement, getOppositeAlignmentPlacement(oppositePlacement)];
  }
  function getOppositeAlignmentPlacement(placement) {
    return placement.replace(/start|end/g, (alignment) => oppositeAlignmentMap[alignment]);
  }
  function getSideList(side, isStart, rtl) {
    const lr = ["left", "right"];
    const rl = ["right", "left"];
    const tb = ["top", "bottom"];
    const bt = ["bottom", "top"];
    switch (side) {
      case "top":
      case "bottom":
        if (rtl) return isStart ? rl : lr;
        return isStart ? lr : rl;
      case "left":
      case "right":
        return isStart ? tb : bt;
      default:
        return [];
    }
  }
  function getOppositeAxisPlacements(placement, flipAlignment, direction, rtl) {
    const alignment = getAlignment(placement);
    let list = getSideList(getSide(placement), direction === "start", rtl);
    if (alignment) {
      list = list.map((side) => side + "-" + alignment);
      if (flipAlignment) {
        list = list.concat(list.map(getOppositeAlignmentPlacement));
      }
    }
    return list;
  }
  function getOppositePlacement(placement) {
    return placement.replace(/left|right|bottom|top/g, (side) => oppositeSideMap[side]);
  }
  function expandPaddingObject(padding) {
    return {
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
      ...padding
    };
  }
  function getPaddingObject(padding) {
    return typeof padding !== "number" ? expandPaddingObject(padding) : {
      top: padding,
      right: padding,
      bottom: padding,
      left: padding
    };
  }
  function rectToClientRect(rect) {
    const {
      x,
      y,
      width,
      height
    } = rect;
    return {
      width,
      height,
      top: y,
      left: x,
      right: x + width,
      bottom: y + height,
      x,
      y
    };
  }

  // node_modules/@floating-ui/core/dist/floating-ui.core.mjs
  function computeCoordsFromPlacement(_ref, placement, rtl) {
    let {
      reference,
      floating
    } = _ref;
    const sideAxis = getSideAxis(placement);
    const alignmentAxis = getAlignmentAxis(placement);
    const alignLength = getAxisLength(alignmentAxis);
    const side = getSide(placement);
    const isVertical = sideAxis === "y";
    const commonX = reference.x + reference.width / 2 - floating.width / 2;
    const commonY = reference.y + reference.height / 2 - floating.height / 2;
    const commonAlign = reference[alignLength] / 2 - floating[alignLength] / 2;
    let coords;
    switch (side) {
      case "top":
        coords = {
          x: commonX,
          y: reference.y - floating.height
        };
        break;
      case "bottom":
        coords = {
          x: commonX,
          y: reference.y + reference.height
        };
        break;
      case "right":
        coords = {
          x: reference.x + reference.width,
          y: commonY
        };
        break;
      case "left":
        coords = {
          x: reference.x - floating.width,
          y: commonY
        };
        break;
      default:
        coords = {
          x: reference.x,
          y: reference.y
        };
    }
    switch (getAlignment(placement)) {
      case "start":
        coords[alignmentAxis] -= commonAlign * (rtl && isVertical ? -1 : 1);
        break;
      case "end":
        coords[alignmentAxis] += commonAlign * (rtl && isVertical ? -1 : 1);
        break;
    }
    return coords;
  }
  var computePosition = async (reference, floating, config) => {
    const {
      placement = "bottom",
      strategy = "absolute",
      middleware = [],
      platform: platform2
    } = config;
    const validMiddleware = middleware.filter(Boolean);
    const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(floating));
    let rects = await platform2.getElementRects({
      reference,
      floating,
      strategy
    });
    let {
      x,
      y
    } = computeCoordsFromPlacement(rects, placement, rtl);
    let statefulPlacement = placement;
    let middlewareData = {};
    let resetCount = 0;
    for (let i = 0; i < validMiddleware.length; i++) {
      const {
        name,
        fn
      } = validMiddleware[i];
      const {
        x: nextX,
        y: nextY,
        data,
        reset
      } = await fn({
        x,
        y,
        initialPlacement: placement,
        placement: statefulPlacement,
        strategy,
        middlewareData,
        rects,
        platform: platform2,
        elements: {
          reference,
          floating
        }
      });
      x = nextX != null ? nextX : x;
      y = nextY != null ? nextY : y;
      middlewareData = {
        ...middlewareData,
        [name]: {
          ...middlewareData[name],
          ...data
        }
      };
      if (reset && resetCount <= 50) {
        resetCount++;
        if (typeof reset === "object") {
          if (reset.placement) {
            statefulPlacement = reset.placement;
          }
          if (reset.rects) {
            rects = reset.rects === true ? await platform2.getElementRects({
              reference,
              floating,
              strategy
            }) : reset.rects;
          }
          ({
            x,
            y
          } = computeCoordsFromPlacement(rects, statefulPlacement, rtl));
        }
        i = -1;
      }
    }
    return {
      x,
      y,
      placement: statefulPlacement,
      strategy,
      middlewareData
    };
  };
  async function detectOverflow(state, options) {
    var _await$platform$isEle;
    if (options === void 0) {
      options = {};
    }
    const {
      x,
      y,
      platform: platform2,
      rects,
      elements,
      strategy
    } = state;
    const {
      boundary = "clippingAncestors",
      rootBoundary = "viewport",
      elementContext = "floating",
      altBoundary = false,
      padding = 0
    } = evaluate(options, state);
    const paddingObject = getPaddingObject(padding);
    const altContext = elementContext === "floating" ? "reference" : "floating";
    const element2 = elements[altBoundary ? altContext : elementContext];
    const clippingClientRect = rectToClientRect(await platform2.getClippingRect({
      element: ((_await$platform$isEle = await (platform2.isElement == null ? void 0 : platform2.isElement(element2))) != null ? _await$platform$isEle : true) ? element2 : element2.contextElement || await (platform2.getDocumentElement == null ? void 0 : platform2.getDocumentElement(elements.floating)),
      boundary,
      rootBoundary,
      strategy
    }));
    const rect = elementContext === "floating" ? {
      x,
      y,
      width: rects.floating.width,
      height: rects.floating.height
    } : rects.reference;
    const offsetParent = await (platform2.getOffsetParent == null ? void 0 : platform2.getOffsetParent(elements.floating));
    const offsetScale = await (platform2.isElement == null ? void 0 : platform2.isElement(offsetParent)) ? await (platform2.getScale == null ? void 0 : platform2.getScale(offsetParent)) || {
      x: 1,
      y: 1
    } : {
      x: 1,
      y: 1
    };
    const elementClientRect = rectToClientRect(platform2.convertOffsetParentRelativeRectToViewportRelativeRect ? await platform2.convertOffsetParentRelativeRectToViewportRelativeRect({
      elements,
      rect,
      offsetParent,
      strategy
    }) : rect);
    return {
      top: (clippingClientRect.top - elementClientRect.top + paddingObject.top) / offsetScale.y,
      bottom: (elementClientRect.bottom - clippingClientRect.bottom + paddingObject.bottom) / offsetScale.y,
      left: (clippingClientRect.left - elementClientRect.left + paddingObject.left) / offsetScale.x,
      right: (elementClientRect.right - clippingClientRect.right + paddingObject.right) / offsetScale.x
    };
  }
  var flip = function(options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: "flip",
      options,
      async fn(state) {
        var _middlewareData$arrow, _middlewareData$flip;
        const {
          placement,
          middlewareData,
          rects,
          initialPlacement,
          platform: platform2,
          elements
        } = state;
        const {
          mainAxis: checkMainAxis = true,
          crossAxis: checkCrossAxis = true,
          fallbackPlacements: specifiedFallbackPlacements,
          fallbackStrategy = "bestFit",
          fallbackAxisSideDirection = "none",
          flipAlignment = true,
          ...detectOverflowOptions
        } = evaluate(options, state);
        if ((_middlewareData$arrow = middlewareData.arrow) != null && _middlewareData$arrow.alignmentOffset) {
          return {};
        }
        const side = getSide(placement);
        const initialSideAxis = getSideAxis(initialPlacement);
        const isBasePlacement = getSide(initialPlacement) === initialPlacement;
        const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating));
        const fallbackPlacements = specifiedFallbackPlacements || (isBasePlacement || !flipAlignment ? [getOppositePlacement(initialPlacement)] : getExpandedPlacements(initialPlacement));
        const hasFallbackAxisSideDirection = fallbackAxisSideDirection !== "none";
        if (!specifiedFallbackPlacements && hasFallbackAxisSideDirection) {
          fallbackPlacements.push(...getOppositeAxisPlacements(initialPlacement, flipAlignment, fallbackAxisSideDirection, rtl));
        }
        const placements2 = [initialPlacement, ...fallbackPlacements];
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const overflows = [];
        let overflowsData = ((_middlewareData$flip = middlewareData.flip) == null ? void 0 : _middlewareData$flip.overflows) || [];
        if (checkMainAxis) {
          overflows.push(overflow[side]);
        }
        if (checkCrossAxis) {
          const sides2 = getAlignmentSides(placement, rects, rtl);
          overflows.push(overflow[sides2[0]], overflow[sides2[1]]);
        }
        overflowsData = [...overflowsData, {
          placement,
          overflows
        }];
        if (!overflows.every((side2) => side2 <= 0)) {
          var _middlewareData$flip2, _overflowsData$filter;
          const nextIndex = (((_middlewareData$flip2 = middlewareData.flip) == null ? void 0 : _middlewareData$flip2.index) || 0) + 1;
          const nextPlacement = placements2[nextIndex];
          if (nextPlacement) {
            return {
              data: {
                index: nextIndex,
                overflows: overflowsData
              },
              reset: {
                placement: nextPlacement
              }
            };
          }
          let resetPlacement = (_overflowsData$filter = overflowsData.filter((d) => d.overflows[0] <= 0).sort((a, b) => a.overflows[1] - b.overflows[1])[0]) == null ? void 0 : _overflowsData$filter.placement;
          if (!resetPlacement) {
            switch (fallbackStrategy) {
              case "bestFit": {
                var _overflowsData$filter2;
                const placement2 = (_overflowsData$filter2 = overflowsData.filter((d) => {
                  if (hasFallbackAxisSideDirection) {
                    const currentSideAxis = getSideAxis(d.placement);
                    return currentSideAxis === initialSideAxis || // Create a bias to the `y` side axis due to horizontal
                    // reading directions favoring greater width.
                    currentSideAxis === "y";
                  }
                  return true;
                }).map((d) => [d.placement, d.overflows.filter((overflow2) => overflow2 > 0).reduce((acc, overflow2) => acc + overflow2, 0)]).sort((a, b) => a[1] - b[1])[0]) == null ? void 0 : _overflowsData$filter2[0];
                if (placement2) {
                  resetPlacement = placement2;
                }
                break;
              }
              case "initialPlacement":
                resetPlacement = initialPlacement;
                break;
            }
          }
          if (placement !== resetPlacement) {
            return {
              reset: {
                placement: resetPlacement
              }
            };
          }
        }
        return {};
      }
    };
  };
  async function convertValueToCoords(state, options) {
    const {
      placement,
      platform: platform2,
      elements
    } = state;
    const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating));
    const side = getSide(placement);
    const alignment = getAlignment(placement);
    const isVertical = getSideAxis(placement) === "y";
    const mainAxisMulti = ["left", "top"].includes(side) ? -1 : 1;
    const crossAxisMulti = rtl && isVertical ? -1 : 1;
    const rawValue = evaluate(options, state);
    let {
      mainAxis,
      crossAxis,
      alignmentAxis
    } = typeof rawValue === "number" ? {
      mainAxis: rawValue,
      crossAxis: 0,
      alignmentAxis: null
    } : {
      mainAxis: rawValue.mainAxis || 0,
      crossAxis: rawValue.crossAxis || 0,
      alignmentAxis: rawValue.alignmentAxis
    };
    if (alignment && typeof alignmentAxis === "number") {
      crossAxis = alignment === "end" ? alignmentAxis * -1 : alignmentAxis;
    }
    return isVertical ? {
      x: crossAxis * crossAxisMulti,
      y: mainAxis * mainAxisMulti
    } : {
      x: mainAxis * mainAxisMulti,
      y: crossAxis * crossAxisMulti
    };
  }
  var offset = function(options) {
    if (options === void 0) {
      options = 0;
    }
    return {
      name: "offset",
      options,
      async fn(state) {
        var _middlewareData$offse, _middlewareData$arrow;
        const {
          x,
          y,
          placement,
          middlewareData
        } = state;
        const diffCoords = await convertValueToCoords(state, options);
        if (placement === ((_middlewareData$offse = middlewareData.offset) == null ? void 0 : _middlewareData$offse.placement) && (_middlewareData$arrow = middlewareData.arrow) != null && _middlewareData$arrow.alignmentOffset) {
          return {};
        }
        return {
          x: x + diffCoords.x,
          y: y + diffCoords.y,
          data: {
            ...diffCoords,
            placement
          }
        };
      }
    };
  };
  var shift = function(options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: "shift",
      options,
      async fn(state) {
        const {
          x,
          y,
          placement
        } = state;
        const {
          mainAxis: checkMainAxis = true,
          crossAxis: checkCrossAxis = false,
          limiter = {
            fn: (_ref) => {
              let {
                x: x2,
                y: y2
              } = _ref;
              return {
                x: x2,
                y: y2
              };
            }
          },
          ...detectOverflowOptions
        } = evaluate(options, state);
        const coords = {
          x,
          y
        };
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const crossAxis = getSideAxis(getSide(placement));
        const mainAxis = getOppositeAxis(crossAxis);
        let mainAxisCoord = coords[mainAxis];
        let crossAxisCoord = coords[crossAxis];
        if (checkMainAxis) {
          const minSide = mainAxis === "y" ? "top" : "left";
          const maxSide = mainAxis === "y" ? "bottom" : "right";
          const min2 = mainAxisCoord + overflow[minSide];
          const max2 = mainAxisCoord - overflow[maxSide];
          mainAxisCoord = clamp(min2, mainAxisCoord, max2);
        }
        if (checkCrossAxis) {
          const minSide = crossAxis === "y" ? "top" : "left";
          const maxSide = crossAxis === "y" ? "bottom" : "right";
          const min2 = crossAxisCoord + overflow[minSide];
          const max2 = crossAxisCoord - overflow[maxSide];
          crossAxisCoord = clamp(min2, crossAxisCoord, max2);
        }
        const limitedCoords = limiter.fn({
          ...state,
          [mainAxis]: mainAxisCoord,
          [crossAxis]: crossAxisCoord
        });
        return {
          ...limitedCoords,
          data: {
            x: limitedCoords.x - x,
            y: limitedCoords.y - y,
            enabled: {
              [mainAxis]: checkMainAxis,
              [crossAxis]: checkCrossAxis
            }
          }
        };
      }
    };
  };
  var size = function(options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: "size",
      options,
      async fn(state) {
        var _state$middlewareData, _state$middlewareData2;
        const {
          placement,
          rects,
          platform: platform2,
          elements
        } = state;
        const {
          apply: apply3 = () => {
          },
          ...detectOverflowOptions
        } = evaluate(options, state);
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const side = getSide(placement);
        const alignment = getAlignment(placement);
        const isYAxis = getSideAxis(placement) === "y";
        const {
          width,
          height
        } = rects.floating;
        let heightSide;
        let widthSide;
        if (side === "top" || side === "bottom") {
          heightSide = side;
          widthSide = alignment === (await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating)) ? "start" : "end") ? "left" : "right";
        } else {
          widthSide = side;
          heightSide = alignment === "end" ? "top" : "bottom";
        }
        const maximumClippingHeight = height - overflow.top - overflow.bottom;
        const maximumClippingWidth = width - overflow.left - overflow.right;
        const overflowAvailableHeight = min(height - overflow[heightSide], maximumClippingHeight);
        const overflowAvailableWidth = min(width - overflow[widthSide], maximumClippingWidth);
        const noShift = !state.middlewareData.shift;
        let availableHeight = overflowAvailableHeight;
        let availableWidth = overflowAvailableWidth;
        if ((_state$middlewareData = state.middlewareData.shift) != null && _state$middlewareData.enabled.x) {
          availableWidth = maximumClippingWidth;
        }
        if ((_state$middlewareData2 = state.middlewareData.shift) != null && _state$middlewareData2.enabled.y) {
          availableHeight = maximumClippingHeight;
        }
        if (noShift && !alignment) {
          const xMin = max(overflow.left, 0);
          const xMax = max(overflow.right, 0);
          const yMin = max(overflow.top, 0);
          const yMax = max(overflow.bottom, 0);
          if (isYAxis) {
            availableWidth = width - 2 * (xMin !== 0 || xMax !== 0 ? xMin + xMax : max(overflow.left, overflow.right));
          } else {
            availableHeight = height - 2 * (yMin !== 0 || yMax !== 0 ? yMin + yMax : max(overflow.top, overflow.bottom));
          }
        }
        await apply3({
          ...state,
          availableWidth,
          availableHeight
        });
        const nextDimensions = await platform2.getDimensions(elements.floating);
        if (width !== nextDimensions.width || height !== nextDimensions.height) {
          return {
            reset: {
              rects: true
            }
          };
        }
        return {};
      }
    };
  };

  // node_modules/@floating-ui/utils/dist/floating-ui.utils.dom.mjs
  function hasWindow() {
    return typeof window !== "undefined";
  }
  function getNodeName(node) {
    if (isNode(node)) {
      return (node.nodeName || "").toLowerCase();
    }
    return "#document";
  }
  function getWindow(node) {
    var _node$ownerDocument;
    return (node == null || (_node$ownerDocument = node.ownerDocument) == null ? void 0 : _node$ownerDocument.defaultView) || window;
  }
  function getDocumentElement(node) {
    var _ref;
    return (_ref = (isNode(node) ? node.ownerDocument : node.document) || window.document) == null ? void 0 : _ref.documentElement;
  }
  function isNode(value3) {
    if (!hasWindow()) {
      return false;
    }
    return value3 instanceof Node || value3 instanceof getWindow(value3).Node;
  }
  function isElement(value3) {
    if (!hasWindow()) {
      return false;
    }
    return value3 instanceof Element || value3 instanceof getWindow(value3).Element;
  }
  function isHTMLElement(value3) {
    if (!hasWindow()) {
      return false;
    }
    return value3 instanceof HTMLElement || value3 instanceof getWindow(value3).HTMLElement;
  }
  function isShadowRoot(value3) {
    if (!hasWindow() || typeof ShadowRoot === "undefined") {
      return false;
    }
    return value3 instanceof ShadowRoot || value3 instanceof getWindow(value3).ShadowRoot;
  }
  function isOverflowElement(element2) {
    const {
      overflow,
      overflowX,
      overflowY,
      display
    } = getComputedStyle2(element2);
    return /auto|scroll|overlay|hidden|clip/.test(overflow + overflowY + overflowX) && !["inline", "contents"].includes(display);
  }
  function isTableElement(element2) {
    return ["table", "td", "th"].includes(getNodeName(element2));
  }
  function isTopLayer(element2) {
    return [":popover-open", ":modal"].some((selector) => {
      try {
        return element2.matches(selector);
      } catch (e) {
        return false;
      }
    });
  }
  function isContainingBlock(elementOrCss) {
    const webkit = isWebKit();
    const css = isElement(elementOrCss) ? getComputedStyle2(elementOrCss) : elementOrCss;
    return ["transform", "translate", "scale", "rotate", "perspective"].some((value3) => css[value3] ? css[value3] !== "none" : false) || (css.containerType ? css.containerType !== "normal" : false) || !webkit && (css.backdropFilter ? css.backdropFilter !== "none" : false) || !webkit && (css.filter ? css.filter !== "none" : false) || ["transform", "translate", "scale", "rotate", "perspective", "filter"].some((value3) => (css.willChange || "").includes(value3)) || ["paint", "layout", "strict", "content"].some((value3) => (css.contain || "").includes(value3));
  }
  function getContainingBlock(element2) {
    let currentNode = getParentNode(element2);
    while (isHTMLElement(currentNode) && !isLastTraversableNode(currentNode)) {
      if (isContainingBlock(currentNode)) {
        return currentNode;
      } else if (isTopLayer(currentNode)) {
        return null;
      }
      currentNode = getParentNode(currentNode);
    }
    return null;
  }
  function isWebKit() {
    if (typeof CSS === "undefined" || !CSS.supports) return false;
    return CSS.supports("-webkit-backdrop-filter", "none");
  }
  function isLastTraversableNode(node) {
    return ["html", "body", "#document"].includes(getNodeName(node));
  }
  function getComputedStyle2(element2) {
    return getWindow(element2).getComputedStyle(element2);
  }
  function getNodeScroll(element2) {
    if (isElement(element2)) {
      return {
        scrollLeft: element2.scrollLeft,
        scrollTop: element2.scrollTop
      };
    }
    return {
      scrollLeft: element2.scrollX,
      scrollTop: element2.scrollY
    };
  }
  function getParentNode(node) {
    if (getNodeName(node) === "html") {
      return node;
    }
    const result = (
      // Step into the shadow DOM of the parent of a slotted node.
      node.assignedSlot || // DOM Element detected.
      node.parentNode || // ShadowRoot detected.
      isShadowRoot(node) && node.host || // Fallback.
      getDocumentElement(node)
    );
    return isShadowRoot(result) ? result.host : result;
  }
  function getNearestOverflowAncestor(node) {
    const parentNode = getParentNode(node);
    if (isLastTraversableNode(parentNode)) {
      return node.ownerDocument ? node.ownerDocument.body : node.body;
    }
    if (isHTMLElement(parentNode) && isOverflowElement(parentNode)) {
      return parentNode;
    }
    return getNearestOverflowAncestor(parentNode);
  }
  function getOverflowAncestors(node, list, traverseIframes) {
    var _node$ownerDocument2;
    if (list === void 0) {
      list = [];
    }
    if (traverseIframes === void 0) {
      traverseIframes = true;
    }
    const scrollableAncestor = getNearestOverflowAncestor(node);
    const isBody = scrollableAncestor === ((_node$ownerDocument2 = node.ownerDocument) == null ? void 0 : _node$ownerDocument2.body);
    const win = getWindow(scrollableAncestor);
    if (isBody) {
      const frameElement = getFrameElement(win);
      return list.concat(win, win.visualViewport || [], isOverflowElement(scrollableAncestor) ? scrollableAncestor : [], frameElement && traverseIframes ? getOverflowAncestors(frameElement) : []);
    }
    return list.concat(scrollableAncestor, getOverflowAncestors(scrollableAncestor, [], traverseIframes));
  }
  function getFrameElement(win) {
    return win.parent && Object.getPrototypeOf(win.parent) ? win.frameElement : null;
  }

  // node_modules/@floating-ui/dom/dist/floating-ui.dom.mjs
  function getCssDimensions(element2) {
    const css = getComputedStyle2(element2);
    let width = parseFloat(css.width) || 0;
    let height = parseFloat(css.height) || 0;
    const hasOffset = isHTMLElement(element2);
    const offsetWidth = hasOffset ? element2.offsetWidth : width;
    const offsetHeight = hasOffset ? element2.offsetHeight : height;
    const shouldFallback = round(width) !== offsetWidth || round(height) !== offsetHeight;
    if (shouldFallback) {
      width = offsetWidth;
      height = offsetHeight;
    }
    return {
      width,
      height,
      $: shouldFallback
    };
  }
  function unwrapElement(element2) {
    return !isElement(element2) ? element2.contextElement : element2;
  }
  function getScale(element2) {
    const domElement = unwrapElement(element2);
    if (!isHTMLElement(domElement)) {
      return createCoords(1);
    }
    const rect = domElement.getBoundingClientRect();
    const {
      width,
      height,
      $
    } = getCssDimensions(domElement);
    let x = ($ ? round(rect.width) : rect.width) / width;
    let y = ($ ? round(rect.height) : rect.height) / height;
    if (!x || !Number.isFinite(x)) {
      x = 1;
    }
    if (!y || !Number.isFinite(y)) {
      y = 1;
    }
    return {
      x,
      y
    };
  }
  var noOffsets = /* @__PURE__ */ createCoords(0);
  function getVisualOffsets(element2) {
    const win = getWindow(element2);
    if (!isWebKit() || !win.visualViewport) {
      return noOffsets;
    }
    return {
      x: win.visualViewport.offsetLeft,
      y: win.visualViewport.offsetTop
    };
  }
  function shouldAddVisualOffsets(element2, isFixed, floatingOffsetParent) {
    if (isFixed === void 0) {
      isFixed = false;
    }
    if (!floatingOffsetParent || isFixed && floatingOffsetParent !== getWindow(element2)) {
      return false;
    }
    return isFixed;
  }
  function getBoundingClientRect(element2, includeScale, isFixedStrategy, offsetParent) {
    if (includeScale === void 0) {
      includeScale = false;
    }
    if (isFixedStrategy === void 0) {
      isFixedStrategy = false;
    }
    const clientRect = element2.getBoundingClientRect();
    const domElement = unwrapElement(element2);
    let scale = createCoords(1);
    if (includeScale) {
      if (offsetParent) {
        if (isElement(offsetParent)) {
          scale = getScale(offsetParent);
        }
      } else {
        scale = getScale(element2);
      }
    }
    const visualOffsets = shouldAddVisualOffsets(domElement, isFixedStrategy, offsetParent) ? getVisualOffsets(domElement) : createCoords(0);
    let x = (clientRect.left + visualOffsets.x) / scale.x;
    let y = (clientRect.top + visualOffsets.y) / scale.y;
    let width = clientRect.width / scale.x;
    let height = clientRect.height / scale.y;
    if (domElement) {
      const win = getWindow(domElement);
      const offsetWin = offsetParent && isElement(offsetParent) ? getWindow(offsetParent) : offsetParent;
      let currentWin = win;
      let currentIFrame = getFrameElement(currentWin);
      while (currentIFrame && offsetParent && offsetWin !== currentWin) {
        const iframeScale = getScale(currentIFrame);
        const iframeRect = currentIFrame.getBoundingClientRect();
        const css = getComputedStyle2(currentIFrame);
        const left = iframeRect.left + (currentIFrame.clientLeft + parseFloat(css.paddingLeft)) * iframeScale.x;
        const top = iframeRect.top + (currentIFrame.clientTop + parseFloat(css.paddingTop)) * iframeScale.y;
        x *= iframeScale.x;
        y *= iframeScale.y;
        width *= iframeScale.x;
        height *= iframeScale.y;
        x += left;
        y += top;
        currentWin = getWindow(currentIFrame);
        currentIFrame = getFrameElement(currentWin);
      }
    }
    return rectToClientRect({
      width,
      height,
      x,
      y
    });
  }
  function getWindowScrollBarX(element2, rect) {
    const leftScroll = getNodeScroll(element2).scrollLeft;
    if (!rect) {
      return getBoundingClientRect(getDocumentElement(element2)).left + leftScroll;
    }
    return rect.left + leftScroll;
  }
  function getHTMLOffset(documentElement, scroll, ignoreScrollbarX) {
    if (ignoreScrollbarX === void 0) {
      ignoreScrollbarX = false;
    }
    const htmlRect = documentElement.getBoundingClientRect();
    const x = htmlRect.left + scroll.scrollLeft - (ignoreScrollbarX ? 0 : (
      // RTL <body> scrollbar.
      getWindowScrollBarX(documentElement, htmlRect)
    ));
    const y = htmlRect.top + scroll.scrollTop;
    return {
      x,
      y
    };
  }
  function convertOffsetParentRelativeRectToViewportRelativeRect(_ref) {
    let {
      elements,
      rect,
      offsetParent,
      strategy
    } = _ref;
    const isFixed = strategy === "fixed";
    const documentElement = getDocumentElement(offsetParent);
    const topLayer = elements ? isTopLayer(elements.floating) : false;
    if (offsetParent === documentElement || topLayer && isFixed) {
      return rect;
    }
    let scroll = {
      scrollLeft: 0,
      scrollTop: 0
    };
    let scale = createCoords(1);
    const offsets = createCoords(0);
    const isOffsetParentAnElement = isHTMLElement(offsetParent);
    if (isOffsetParentAnElement || !isOffsetParentAnElement && !isFixed) {
      if (getNodeName(offsetParent) !== "body" || isOverflowElement(documentElement)) {
        scroll = getNodeScroll(offsetParent);
      }
      if (isHTMLElement(offsetParent)) {
        const offsetRect = getBoundingClientRect(offsetParent);
        scale = getScale(offsetParent);
        offsets.x = offsetRect.x + offsetParent.clientLeft;
        offsets.y = offsetRect.y + offsetParent.clientTop;
      }
    }
    const htmlOffset = documentElement && !isOffsetParentAnElement && !isFixed ? getHTMLOffset(documentElement, scroll, true) : createCoords(0);
    return {
      width: rect.width * scale.x,
      height: rect.height * scale.y,
      x: rect.x * scale.x - scroll.scrollLeft * scale.x + offsets.x + htmlOffset.x,
      y: rect.y * scale.y - scroll.scrollTop * scale.y + offsets.y + htmlOffset.y
    };
  }
  function getClientRects(element2) {
    return Array.from(element2.getClientRects());
  }
  function getDocumentRect(element2) {
    const html = getDocumentElement(element2);
    const scroll = getNodeScroll(element2);
    const body = element2.ownerDocument.body;
    const width = max(html.scrollWidth, html.clientWidth, body.scrollWidth, body.clientWidth);
    const height = max(html.scrollHeight, html.clientHeight, body.scrollHeight, body.clientHeight);
    let x = -scroll.scrollLeft + getWindowScrollBarX(element2);
    const y = -scroll.scrollTop;
    if (getComputedStyle2(body).direction === "rtl") {
      x += max(html.clientWidth, body.clientWidth) - width;
    }
    return {
      width,
      height,
      x,
      y
    };
  }
  function getViewportRect(element2, strategy) {
    const win = getWindow(element2);
    const html = getDocumentElement(element2);
    const visualViewport = win.visualViewport;
    let width = html.clientWidth;
    let height = html.clientHeight;
    let x = 0;
    let y = 0;
    if (visualViewport) {
      width = visualViewport.width;
      height = visualViewport.height;
      const visualViewportBased = isWebKit();
      if (!visualViewportBased || visualViewportBased && strategy === "fixed") {
        x = visualViewport.offsetLeft;
        y = visualViewport.offsetTop;
      }
    }
    return {
      width,
      height,
      x,
      y
    };
  }
  function getInnerBoundingClientRect(element2, strategy) {
    const clientRect = getBoundingClientRect(element2, true, strategy === "fixed");
    const top = clientRect.top + element2.clientTop;
    const left = clientRect.left + element2.clientLeft;
    const scale = isHTMLElement(element2) ? getScale(element2) : createCoords(1);
    const width = element2.clientWidth * scale.x;
    const height = element2.clientHeight * scale.y;
    const x = left * scale.x;
    const y = top * scale.y;
    return {
      width,
      height,
      x,
      y
    };
  }
  function getClientRectFromClippingAncestor(element2, clippingAncestor, strategy) {
    let rect;
    if (clippingAncestor === "viewport") {
      rect = getViewportRect(element2, strategy);
    } else if (clippingAncestor === "document") {
      rect = getDocumentRect(getDocumentElement(element2));
    } else if (isElement(clippingAncestor)) {
      rect = getInnerBoundingClientRect(clippingAncestor, strategy);
    } else {
      const visualOffsets = getVisualOffsets(element2);
      rect = {
        x: clippingAncestor.x - visualOffsets.x,
        y: clippingAncestor.y - visualOffsets.y,
        width: clippingAncestor.width,
        height: clippingAncestor.height
      };
    }
    return rectToClientRect(rect);
  }
  function hasFixedPositionAncestor(element2, stopNode) {
    const parentNode = getParentNode(element2);
    if (parentNode === stopNode || !isElement(parentNode) || isLastTraversableNode(parentNode)) {
      return false;
    }
    return getComputedStyle2(parentNode).position === "fixed" || hasFixedPositionAncestor(parentNode, stopNode);
  }
  function getClippingElementAncestors(element2, cache) {
    const cachedResult = cache.get(element2);
    if (cachedResult) {
      return cachedResult;
    }
    let result = getOverflowAncestors(element2, [], false).filter((el) => isElement(el) && getNodeName(el) !== "body");
    let currentContainingBlockComputedStyle = null;
    const elementIsFixed = getComputedStyle2(element2).position === "fixed";
    let currentNode = elementIsFixed ? getParentNode(element2) : element2;
    while (isElement(currentNode) && !isLastTraversableNode(currentNode)) {
      const computedStyle = getComputedStyle2(currentNode);
      const currentNodeIsContaining = isContainingBlock(currentNode);
      if (!currentNodeIsContaining && computedStyle.position === "fixed") {
        currentContainingBlockComputedStyle = null;
      }
      const shouldDropCurrentNode = elementIsFixed ? !currentNodeIsContaining && !currentContainingBlockComputedStyle : !currentNodeIsContaining && computedStyle.position === "static" && !!currentContainingBlockComputedStyle && ["absolute", "fixed"].includes(currentContainingBlockComputedStyle.position) || isOverflowElement(currentNode) && !currentNodeIsContaining && hasFixedPositionAncestor(element2, currentNode);
      if (shouldDropCurrentNode) {
        result = result.filter((ancestor) => ancestor !== currentNode);
      } else {
        currentContainingBlockComputedStyle = computedStyle;
      }
      currentNode = getParentNode(currentNode);
    }
    cache.set(element2, result);
    return result;
  }
  function getClippingRect(_ref) {
    let {
      element: element2,
      boundary,
      rootBoundary,
      strategy
    } = _ref;
    const elementClippingAncestors = boundary === "clippingAncestors" ? isTopLayer(element2) ? [] : getClippingElementAncestors(element2, this._c) : [].concat(boundary);
    const clippingAncestors = [...elementClippingAncestors, rootBoundary];
    const firstClippingAncestor = clippingAncestors[0];
    const clippingRect = clippingAncestors.reduce((accRect, clippingAncestor) => {
      const rect = getClientRectFromClippingAncestor(element2, clippingAncestor, strategy);
      accRect.top = max(rect.top, accRect.top);
      accRect.right = min(rect.right, accRect.right);
      accRect.bottom = min(rect.bottom, accRect.bottom);
      accRect.left = max(rect.left, accRect.left);
      return accRect;
    }, getClientRectFromClippingAncestor(element2, firstClippingAncestor, strategy));
    return {
      width: clippingRect.right - clippingRect.left,
      height: clippingRect.bottom - clippingRect.top,
      x: clippingRect.left,
      y: clippingRect.top
    };
  }
  function getDimensions(element2) {
    const {
      width,
      height
    } = getCssDimensions(element2);
    return {
      width,
      height
    };
  }
  function getRectRelativeToOffsetParent(element2, offsetParent, strategy) {
    const isOffsetParentAnElement = isHTMLElement(offsetParent);
    const documentElement = getDocumentElement(offsetParent);
    const isFixed = strategy === "fixed";
    const rect = getBoundingClientRect(element2, true, isFixed, offsetParent);
    let scroll = {
      scrollLeft: 0,
      scrollTop: 0
    };
    const offsets = createCoords(0);
    if (isOffsetParentAnElement || !isOffsetParentAnElement && !isFixed) {
      if (getNodeName(offsetParent) !== "body" || isOverflowElement(documentElement)) {
        scroll = getNodeScroll(offsetParent);
      }
      if (isOffsetParentAnElement) {
        const offsetRect = getBoundingClientRect(offsetParent, true, isFixed, offsetParent);
        offsets.x = offsetRect.x + offsetParent.clientLeft;
        offsets.y = offsetRect.y + offsetParent.clientTop;
      } else if (documentElement) {
        offsets.x = getWindowScrollBarX(documentElement);
      }
    }
    const htmlOffset = documentElement && !isOffsetParentAnElement && !isFixed ? getHTMLOffset(documentElement, scroll) : createCoords(0);
    const x = rect.left + scroll.scrollLeft - offsets.x - htmlOffset.x;
    const y = rect.top + scroll.scrollTop - offsets.y - htmlOffset.y;
    return {
      x,
      y,
      width: rect.width,
      height: rect.height
    };
  }
  function isStaticPositioned(element2) {
    return getComputedStyle2(element2).position === "static";
  }
  function getTrueOffsetParent(element2, polyfill) {
    if (!isHTMLElement(element2) || getComputedStyle2(element2).position === "fixed") {
      return null;
    }
    if (polyfill) {
      return polyfill(element2);
    }
    let rawOffsetParent = element2.offsetParent;
    if (getDocumentElement(element2) === rawOffsetParent) {
      rawOffsetParent = rawOffsetParent.ownerDocument.body;
    }
    return rawOffsetParent;
  }
  function getOffsetParent(element2, polyfill) {
    const win = getWindow(element2);
    if (isTopLayer(element2)) {
      return win;
    }
    if (!isHTMLElement(element2)) {
      let svgOffsetParent = getParentNode(element2);
      while (svgOffsetParent && !isLastTraversableNode(svgOffsetParent)) {
        if (isElement(svgOffsetParent) && !isStaticPositioned(svgOffsetParent)) {
          return svgOffsetParent;
        }
        svgOffsetParent = getParentNode(svgOffsetParent);
      }
      return win;
    }
    let offsetParent = getTrueOffsetParent(element2, polyfill);
    while (offsetParent && isTableElement(offsetParent) && isStaticPositioned(offsetParent)) {
      offsetParent = getTrueOffsetParent(offsetParent, polyfill);
    }
    if (offsetParent && isLastTraversableNode(offsetParent) && isStaticPositioned(offsetParent) && !isContainingBlock(offsetParent)) {
      return win;
    }
    return offsetParent || getContainingBlock(element2) || win;
  }
  var getElementRects = async function(data) {
    const getOffsetParentFn = this.getOffsetParent || getOffsetParent;
    const getDimensionsFn = this.getDimensions;
    const floatingDimensions = await getDimensionsFn(data.floating);
    return {
      reference: getRectRelativeToOffsetParent(data.reference, await getOffsetParentFn(data.floating), data.strategy),
      floating: {
        x: 0,
        y: 0,
        width: floatingDimensions.width,
        height: floatingDimensions.height
      }
    };
  };
  function isRTL2(element2) {
    return getComputedStyle2(element2).direction === "rtl";
  }
  var platform = {
    convertOffsetParentRelativeRectToViewportRelativeRect,
    getDocumentElement,
    getClippingRect,
    getOffsetParent,
    getElementRects,
    getClientRects,
    getDimensions,
    getScale,
    isElement,
    isRTL: isRTL2
  };
  function rectsAreEqual(a, b) {
    return a.x === b.x && a.y === b.y && a.width === b.width && a.height === b.height;
  }
  function observeMove(element2, onMove) {
    let io = null;
    let timeoutId;
    const root = getDocumentElement(element2);
    function cleanup() {
      var _io;
      clearTimeout(timeoutId);
      (_io = io) == null || _io.disconnect();
      io = null;
    }
    function refresh(skip, threshold) {
      if (skip === void 0) {
        skip = false;
      }
      if (threshold === void 0) {
        threshold = 1;
      }
      cleanup();
      const elementRectForRootMargin = element2.getBoundingClientRect();
      const {
        left,
        top,
        width,
        height
      } = elementRectForRootMargin;
      if (!skip) {
        onMove();
      }
      if (!width || !height) {
        return;
      }
      const insetTop = floor(top);
      const insetRight = floor(root.clientWidth - (left + width));
      const insetBottom = floor(root.clientHeight - (top + height));
      const insetLeft = floor(left);
      const rootMargin = -insetTop + "px " + -insetRight + "px " + -insetBottom + "px " + -insetLeft + "px";
      const options = {
        rootMargin,
        threshold: max(0, min(1, threshold)) || 1
      };
      let isFirstUpdate = true;
      function handleObserve(entries) {
        const ratio = entries[0].intersectionRatio;
        if (ratio !== threshold) {
          if (!isFirstUpdate) {
            return refresh();
          }
          if (!ratio) {
            timeoutId = setTimeout(() => {
              refresh(false, 1e-7);
            }, 1e3);
          } else {
            refresh(false, ratio);
          }
        }
        if (ratio === 1 && !rectsAreEqual(elementRectForRootMargin, element2.getBoundingClientRect())) {
          refresh();
        }
        isFirstUpdate = false;
      }
      try {
        io = new IntersectionObserver(handleObserve, {
          ...options,
          // Handle <iframe>s
          root: root.ownerDocument
        });
      } catch (e) {
        io = new IntersectionObserver(handleObserve, options);
      }
      io.observe(element2);
    }
    refresh(true);
    return cleanup;
  }
  function autoUpdate(reference, floating, update, options) {
    if (options === void 0) {
      options = {};
    }
    const {
      ancestorScroll = true,
      ancestorResize = true,
      elementResize = typeof ResizeObserver === "function",
      layoutShift = typeof IntersectionObserver === "function",
      animationFrame = false
    } = options;
    const referenceEl = unwrapElement(reference);
    const ancestors = ancestorScroll || ancestorResize ? [...referenceEl ? getOverflowAncestors(referenceEl) : [], ...getOverflowAncestors(floating)] : [];
    ancestors.forEach((ancestor) => {
      ancestorScroll && ancestor.addEventListener("scroll", update, {
        passive: true
      });
      ancestorResize && ancestor.addEventListener("resize", update);
    });
    const cleanupIo = referenceEl && layoutShift ? observeMove(referenceEl, update) : null;
    let reobserveFrame = -1;
    let resizeObserver = null;
    if (elementResize) {
      resizeObserver = new ResizeObserver((_ref) => {
        let [firstEntry] = _ref;
        if (firstEntry && firstEntry.target === referenceEl && resizeObserver) {
          resizeObserver.unobserve(floating);
          cancelAnimationFrame(reobserveFrame);
          reobserveFrame = requestAnimationFrame(() => {
            var _resizeObserver;
            (_resizeObserver = resizeObserver) == null || _resizeObserver.observe(floating);
          });
        }
        update();
      });
      if (referenceEl && !animationFrame) {
        resizeObserver.observe(referenceEl);
      }
      resizeObserver.observe(floating);
    }
    let frameId;
    let prevRefRect = animationFrame ? getBoundingClientRect(reference) : null;
    if (animationFrame) {
      frameLoop();
    }
    function frameLoop() {
      const nextRefRect = getBoundingClientRect(reference);
      if (prevRefRect && !rectsAreEqual(prevRefRect, nextRefRect)) {
        update();
      }
      prevRefRect = nextRefRect;
      frameId = requestAnimationFrame(frameLoop);
    }
    update();
    return () => {
      var _resizeObserver2;
      ancestors.forEach((ancestor) => {
        ancestorScroll && ancestor.removeEventListener("scroll", update);
        ancestorResize && ancestor.removeEventListener("resize", update);
      });
      cleanupIo == null || cleanupIo();
      (_resizeObserver2 = resizeObserver) == null || _resizeObserver2.disconnect();
      resizeObserver = null;
      if (animationFrame) {
        cancelAnimationFrame(frameId);
      }
    };
  }
  var offset2 = offset;
  var shift2 = shift;
  var flip2 = flip;
  var size2 = size;
  var computePosition2 = (reference, floating, options) => {
    const cache = /* @__PURE__ */ new Map();
    const mergedOptions = {
      platform,
      ...options
    };
    const platformWithCache = {
      ...mergedOptions.platform,
      _c: cache
    };
    return computePosition(reference, floating, {
      ...mergedOptions,
      platform: platformWithCache
    });
  };

  // js/mixins/anchorable.js
  var useFixedStrategy = !(CSS.supports?.("selector(:popover-open)") ?? false);
  var Anchorable = class extends Mixin {
    boot({ options }) {
      options({
        reference: null,
        auto: true,
        position: "bottom start",
        gap: "5",
        offset: "0",
        matchWidth: false,
        crossAxis: false,
        scrollY: true
      });
      if (this.options().reference === null) return;
      if (this.options().position === null) return;
      let [setPosition, cleanupDurablePositioning] = createDurablePositionSetter(this.el, { scrollY: this.options().scrollY });
      let reposition = anchor(this.el, this.options().reference, setPosition, {
        position: this.options().position,
        gap: this.options().gap,
        offset: this.options().offset,
        matchWidth: this.options().matchWidth,
        crossAxis: this.options().crossAxis,
        scrollY: this.options().scrollY
      });
      let cleanupAutoUpdate = () => {
      };
      this.reposition = (...args) => {
        if (this.options().auto) {
          cleanupAutoUpdate = autoUpdate(this.options().reference, this.el, reposition);
        } else {
          reposition(null, ...args);
        }
      };
      this.cleanup = () => {
        cleanupAutoUpdate();
        cleanupDurablePositioning();
      };
    }
  };
  function anchor(target, invoke, setPosition, { position, offset: offsetValue, gap, matchWidth, crossAxis, scrollY }) {
    let elMaxHeight = window.getComputedStyle(target).maxHeight;
    elMaxHeight = elMaxHeight === "none" ? null : parseFloat(elMaxHeight);
    return (event, forceX, forceY) => {
      computePosition2(invoke, target, {
        strategy: useFixedStrategy ? "fixed" : "absolute",
        placement: compilePlacement(position, target),
        // Placements: ['top', 'top-start', 'top-end', 'right', 'right-start', 'right-end', 'bottom', 'bottom-start', 'bottom-end', 'left', 'left-start', 'left-end']
        middleware: [
          // Offset needs to be first, as per the Floating UI docs...
          offset2({
            mainAxis: Number(gap),
            alignmentAxis: Number(offsetValue)
          }),
          flip2(),
          shift2({ padding: 5, crossAxis }),
          size2({
            padding: 5,
            apply({ rects, elements, availableHeight }) {
              if (matchWidth) {
                Object.assign(elements.floating.style, {
                  width: `${rects.reference.width}px`
                });
              }
              let maxHeight = elMaxHeight;
              if (maxHeight === null) {
                maxHeight = scrollY ? elements.floating.scrollHeight : elements.floating.offsetHeight;
              }
              elements.floating.style.maxHeight = availableHeight > maxHeight ? "" : `${availableHeight}px`;
            }
          })
        ]
      }).then(({ x, y }) => {
        setPosition(forceX || x, forceY || y);
      });
    };
  }
  function compilePlacement(anchor2, el) {
    let placement = anchor2.split(" ");
    switch (placement[0]) {
      case "start":
        placement[0] = isRTL(el) ? "right" : "left";
        break;
      case "end":
        placement[0] = isRTL(el) ? "left" : "right";
        break;
    }
    return placement.join("-");
  }
  function createDurablePositionSetter(target, { scrollY = true }) {
    let update = (x, y, width, height) => {
      Object.assign(target.style, {
        position: useFixedStrategy ? "fixed" : "absolute",
        overflowY: scrollY ? "auto" : "hidden",
        left: `${x}px`,
        top: `${y}px`,
        // This is required to reset the `popover` default styles, otherwise the dropdown appears in the middle of the screen...
        right: "auto",
        bottom: "auto",
        width,
        maxHeight: height
      });
    };
    let lastX, lastY, lastWidth, lastHeight;
    let observer = new MutationObserver(() => update(lastX, lastY, lastWidth, lastHeight));
    return [
      (x, y) => {
        lastX = x;
        lastY = y;
        lastWidth = target.style.width;
        lastHeight = target.style.maxHeight;
        observer.disconnect();
        update(lastX, lastY, lastWidth, lastHeight);
        observer.observe(target, { attributeFilter: ["style"] });
      },
      () => {
        observer.disconnect();
      }
    ];
  }

  // js/selected.js
  var UISelected = class extends UIElement {
    boot() {
      this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
      if (!this.querySelector("template")) {
        let template = document.createElement("template");
        template.setAttribute("name", "placeholder");
        template.innerHTML = "<span>" + this.innerHTML + "</span>";
        this.innerHTML = "";
        this.appendChild(template);
      }
      if (!this.querySelector('template[name="options"]')) {
        let template = document.createElement("template");
        template.setAttribute("name", "options");
        template.innerHTML = "<div><slot></slot></div>";
        this.appendChild(template);
      }
      if (!this.querySelector('template[name="option"]')) {
        let template = document.createElement("template");
        template.setAttribute("name", "option");
        template.innerHTML = "<div><slot></slot></div>";
        this.appendChild(template);
      }
      this.templates = {
        placeholder: this.querySelector('template[name="placeholder"]'),
        overflow: this.querySelector('template[name="overflow"]'),
        options: this.querySelector('template[name="options"]'),
        option: this.querySelector('template[name="option"]')
      };
      this.templates.options.elsByValue = /* @__PURE__ */ new Map();
      this.max = this.templates.overflow?.getAttribute("max") ? this.templates.overflow.getAttribute("max") : Infinity;
      this.selecteds = /* @__PURE__ */ new Map();
      this.picker = this.closest("ui-select,ui-pillbox");
      this.multiple = this.picker.hasAttribute("multiple");
    }
    mount() {
      queueMicrotask(() => {
        this.picker._selectable.onInitAndChange(() => {
          this.render(true);
        });
        let optionsEl = this.picker.list();
        if (optionsEl) {
          new MutationObserver((mutations) => {
            queueMicrotask(() => this.render());
          }).observe(optionsEl, { childList: true });
        }
      });
    }
    render(retry) {
      if (!this.multiple) {
        let value3 = this.picker.value;
        if (Array.from(this.selecteds.keys()).includes(value3)) {
          return;
        }
        this.selecteds.clear();
        let selected = this.picker._selectable.findByValue(value3);
        if (selected) {
          this.selecteds.set(value3, selected);
        } else {
          if (!["", null, void 0].includes(value3)) {
            if (retry) return setTimeout(() => {
              this.render();
            });
            else throw `Could not find option for value "${value3}"`;
          }
        }
        this.templates.placeholder?.clearPlaceholder?.();
        this.templates.option?.clearOption?.();
        if (this.selecteds.size > 0) {
          this.renderOption();
        } else {
          this.renderPlaceholder();
        }
      } else {
        let values = this.picker.value;
        let removedValues = Array.from(this.selecteds.keys()).filter((i) => !values.includes(i));
        let newValues = values.filter((i) => !this.selecteds.has(i));
        removedValues.forEach((value3) => this.selecteds.delete(value3));
        let newSelecteds = /* @__PURE__ */ new Map();
        for (let value3 of newValues) {
          let selected = this.picker._selectable.findByValue(value3);
          if (!selected) {
            if (retry) return setTimeout(() => this.render());
            else throw `Could not find option for value "${value3}"`;
          }
          newSelecteds.set(value3, selected);
        }
        newSelecteds.forEach((selected, value3) => this.selecteds.set(value3, selected));
        this.templates.placeholder?.clearPlaceholder?.();
        this.templates.overflow?.clearOverflow?.();
        this.templates.options?.clearOptions?.();
        if (this.selecteds.size > 0) {
          this.renderOptions({
            hasOverflowed: (rendered) => {
              if (this.max === "auto") {
                let willOverflow = false;
                this.renderOverflow(this.selecteds.size, this.selecteds.size - rendered);
                if (this.clientWidth < this.scrollWidth) {
                  willOverflow = true;
                }
                this.templates.overflow?.clearOverflow?.();
                if (willOverflow) {
                  return true;
                }
              }
              return rendered > parseInt(this.max);
            },
            renderOverflow: (remainder) => {
              if (this.templates?.overflow?.getAttribute("mode") !== "append") {
                this.templates.options?.clearOptions?.();
              }
              this.renderOverflow(this.selecteds.size, remainder);
            }
          });
        } else {
          this.renderPlaceholder();
        }
      }
    }
    renderOptions({ hasOverflowed, renderOverflow }) {
      let container = document.createElement("div");
      container.style.display = "contents";
      let optionsEl = hydrateTemplate2(this.templates.options, {
        default: container
      });
      this.templates.options.after(optionsEl);
      this.templates.options.clearOptions = () => {
        optionsEl.remove();
        this.templates.options.clearOptions = () => {
        };
      };
      let rendered = 0;
      let shouldRenderOverflow = false;
      for (let [value3, selected] of this.selecteds) {
        let fragment2 = new DocumentFragment();
        fragment2.append(...selected.el.cloneNode(true).childNodes);
        let optionEl = hydrateTemplate2(this.templates.option, {
          text: selected.getSelectedLabel() ?? selected.getLabel(),
          default: selected.getSelectedLabel() ?? fragment2,
          value: value3
        });
        optionEl.setAttribute("data-value", value3);
        optionEl.setAttribute("data-appended", "");
        optionEl.deselect = () => selected.deselect();
        container.appendChild(optionEl);
        rendered++;
        if (hasOverflowed(rendered)) {
          shouldRenderOverflow = true;
          container.removeChild(optionEl);
          rendered--;
          break;
        }
      }
      let fragment = new DocumentFragment();
      fragment.append(...container.childNodes);
      container.replaceWith(fragment);
      if (shouldRenderOverflow) {
        renderOverflow(this.selecteds.size - rendered);
      }
    }
    renderOption() {
      for (let [value3, selected] of this.selecteds) {
        let fragment = new DocumentFragment();
        fragment.append(...selected.el.cloneNode(true).childNodes);
        let optionEl = hydrateTemplate2(this.templates.option, {
          text: selected.getSelectedLabel() ?? selected.getLabel(),
          default: selected.getSelectedLabel() ?? fragment,
          value: value3
        });
        optionEl.setAttribute("data-value", value3);
        optionEl.setAttribute("data-appended", value3);
        optionEl.deselect = () => selected.deselect();
        this.templates.option.after(optionEl);
        this.templates.option.clearOption = () => {
          optionEl.remove();
          this.templates.option.clearOption = () => {
          };
        };
      }
    }
    renderPlaceholder() {
      if (!this.templates.placeholder) return;
      let el = hydrateTemplate2(this.templates.placeholder);
      el.setAttribute("data-appended", "");
      this.templates.placeholder.after(el);
      this.templates.placeholder.clearPlaceholder = () => {
        el.remove();
        this.templates.placeholder.clearPlaceholder = () => {
        };
      };
    }
    renderOverflow(count, remainder) {
      if (!this.templates.overflow) return;
      let el = hydrateTemplate2(this.templates.overflow, {
        remainder,
        count: this.selecteds.size
      });
      el.setAttribute("data-appended", "");
      this.templates.overflow.after(el);
      this.templates.overflow.clearOverflow = () => {
        el.remove();
        this.templates.placeholder.clearOverflow = () => {
        };
      };
    }
  };
  var UISelectedRemove = class extends UIElement {
    boot() {
      this.addEventListener("click", (e) => {
        e.stopPropagation();
        let value3 = this.closest("[data-value]")?.getAttribute("data-value");
        if (value3 === void 0) return;
        let selectableGroup = this.closest("ui-pillbox")._selectable;
        let selectable = selectableGroup.selectableByValue(value3);
        if (selectable) {
          selectable.deselect();
        } else {
          selectableGroup.deselectByValue(value3);
        }
      });
    }
  };
  function hydrateTemplate2(template, slots = {}) {
    let fragment = template.content.cloneNode(true);
    Object.entries(slots).forEach(([key, value3]) => {
      let slotNodes = key === "default" ? fragment.querySelectorAll("slot:not([name])") : fragment.querySelectorAll(`slot[name="${key}"]`);
      slotNodes.forEach((i) => i.replaceWith(
        typeof value3 === "string" ? document.createTextNode(value3) : value3
      ));
    });
    return fragment.firstElementChild;
  }

  // js/mixins/selectable.js
  var SelectableGroup = class extends MixinGroup {
    groupOfType = Selectable;
    boot({ options }) {
      options({
        multiple: false
      });
      this.state = this.options().multiple ? /* @__PURE__ */ new Set() : null;
      this.onChanges = [];
    }
    onInitAndChange(callback) {
      callback();
      this.onChanges.push(callback);
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    changed(selectable, silent = false) {
      if (selectable.ungrouped) return;
      let value3 = selectable.value;
      let selected = selectable.isSelected();
      let multiple = this.options().multiple;
      if (selected) {
        multiple ? this.state.add(value3) : this.state = value3;
      } else {
        multiple ? this.state.delete(value3) : this.state = null;
      }
      if (!silent) {
        this.onChanges.forEach((i) => i(selectable));
      }
    }
    deselectByValue(value3, silent = false) {
      this.options().multiple ? this.state.delete(value3) : this.state = null;
      if (!silent) {
        this.onChanges.forEach((i) => i());
      }
    }
    getState() {
      return this.options().multiple ? Array.from(this.state) : this.state;
    }
    hasValue(value3) {
      return this.options().multiple ? this.state.has(value3) : this.state === value3;
    }
    setState(value3) {
      if (value3 === null || value3 === "") value3 = this.options().multiple ? [] : "";
      if (this.options().multiple) {
        if (!Array.isArray(value3)) value3 = [value3];
        value3 = value3.map((i) => i + "");
      } else {
        value3 = value3 + "";
      }
      this.state = this.options().multiple ? new Set(value3) : value3;
      let values = this.options().multiple ? value3 : [value3];
      this.walker().each((el) => {
        let selectable = el.use(Selectable);
        if (selectable.ungrouped) return;
        let selected = values.includes(selectable.value);
        if (selected && !selectable.isSelected()) {
          selectable.surgicallySelect();
        } else if (!selected && selectable.isSelected()) {
          selectable.surgicallyDeselect();
        }
      });
      this.onChanges.forEach((i) => i());
    }
    selected() {
      return this.walker().find((item) => item.use(Selectable).isSelected()).use(Selectable);
    }
    selecteds() {
      return this.walker().filter((el) => el.use(Selectable).isSelected()).map((el) => el.use(Selectable));
    }
    selectFirst() {
      this.walker().first()?.use(Selectable).select();
    }
    selectAll() {
      this.walker().filter((el) => !el.use(Selectable).isSelected()).map((el) => el.use(Selectable).select());
    }
    deselectAll() {
      this.walker().filter((el) => el.use(Selectable).isSelected()).map((el) => el.use(Selectable).deselect());
    }
    allAreSelected() {
      let selectableElements = this.walker().filter((el) => true);
      return selectableElements.length > 0 && this.walker().filter((el) => el.use(Selectable).isSelected()).length === selectableElements.length;
    }
    noneAreSelected() {
      return this.state === null || this.state?.size === 0;
    }
    isEmpty() {
      return this.noneAreSelected();
    }
    isNotEmpty() {
      return !this.isEmpty();
    }
    selectableByValue(value3) {
      return this.walker().find((el) => el.use(Selectable).value === value3)?.use(Selectable);
    }
    deselectOthers(except) {
      this.walker().each((el) => {
        if (el === except) return;
        el.use(Selectable).surgicallyDeselect();
      });
    }
    selectedTextValue() {
      if (!this.options().multiple) return this.convertValueStringToElementText(this.state);
      return Array.from(this.state).map((i) => {
        return this.convertValueStringToElementText(i);
      }).join(", ");
    }
    convertValueStringToElementText(value3) {
      let selected = this.findByValue(value3);
      if (selected) {
        return selected.selectedLabel || selected.label || selected.value;
      } else {
        return value3;
      }
    }
    findByValue(value3) {
      return this.selecteds().find((i) => i.value === value3);
    }
    walker() {
      return walker(this.el, (el, { skip, reject }) => {
        if (el[this.constructor.name] && el !== this.el) return reject();
        if (!el[this.groupOfType.name]) return skip();
        if (el.mixins.get(this.groupOfType.name).ungrouped) return skip();
      });
    }
  };
  var Selectable = class extends Mixin {
    boot({ options }) {
      this.groupedByType = SelectableGroup;
      options({
        ungrouped: false,
        togglable: false,
        value: void 0,
        label: void 0,
        selectedLabel: void 0,
        selectedInitially: false,
        dataAttr: "data-selected",
        ariaAttr: "aria-selected"
      });
      this.ungrouped = this.options().ungrouped;
      this.value = this.options().value === void 0 ? this.el.value : this.options().value;
      this.value = this.value + "";
      this.label = this.options().label;
      this.selectedLabel = this.options().selectedLabel;
      let state = this.options().selectedInitially;
      this.onSelects = [];
      this.onUnselects = [];
      this.onChanges = [];
      let initState = () => {
        if (this.group()) {
          if (this.group().hasValue(this.value)) state = true;
        }
        this.multiple = this.hasGroup() ? this.group().options().multiple : false;
        this.toggleable = this.options().toggleable || this.multiple;
        if (state) {
          this.select(true);
        } else {
          this.state = state;
          this.surgicallyDeselect(true);
        }
      };
      initState();
      if (!this.hasGroup() && !this.el.isConnected) {
        queueMicrotask(() => {
          if (this.hasGroup()) {
            initState();
          }
        });
      }
    }
    mount() {
      this.el.hasAttribute(this.options().ariaAttr) || setAttribute2(this.el, this.options().ariaAttr, "false");
    }
    onInitAndChange(callback) {
      callback();
      this.onChanges.push(callback);
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    onSelect(callback) {
      this.onSelects.push(callback);
    }
    onUnselect(callback) {
      this.onUnselects.push(callback);
    }
    setState(value3) {
      value3 ? this.select() : this.deselect();
    }
    getState() {
      return this.state;
    }
    // @todo: depricate in favor of "trigger"...
    press() {
      this.toggleable ? this.toggle() : this.select();
    }
    trigger() {
      this.toggleable ? this.toggle() : this.select();
    }
    toggle() {
      this.isSelected() ? this.deselect() : this.select();
    }
    isSelected() {
      return this.state;
    }
    select(silent = false) {
      let changed = !this.isSelected();
      this.toggleable || this.group()?.deselectOthers(this.el);
      this.state = true;
      setAttribute2(this.el, this.options().ariaAttr, "true");
      setAttribute2(this.el, this.options().dataAttr, "");
      if (changed) {
        if (!silent) {
          this.onSelects.forEach((i) => i());
          this.onChanges.forEach((i) => i());
        }
        this.group()?.changed(this, silent);
      }
    }
    surgicallySelect() {
      let changed = !this.isSelected();
      this.state = true;
      setAttribute2(this.el, this.options().ariaAttr, "true");
      setAttribute2(this.el, this.options().dataAttr, "");
      if (changed) {
        this.onSelects.forEach((i) => i());
        this.onChanges.forEach((i) => i());
      }
    }
    deselect(notify = true) {
      let changed = this.isSelected();
      this.state = false;
      setAttribute2(this.el, this.options().ariaAttr, "false");
      removeAttribute(this.el, this.options().dataAttr);
      if (changed) {
        this.onUnselects.forEach((i) => i());
        this.onChanges.forEach((i) => i());
        notify && this.group()?.changed(this);
      }
    }
    surgicallyDeselect(silent = false) {
      let changed = this.isSelected();
      this.state = false;
      setAttribute2(this.el, this.options().ariaAttr, "false");
      removeAttribute(this.el, this.options().dataAttr);
      if (changed && !silent) {
        this.onUnselects.forEach((i) => i());
        this.onChanges.forEach((i) => i());
      }
    }
    getValue() {
      return this.value;
    }
    getLabel() {
      return this.label;
    }
    getSelectedLabel() {
      return this.selectedLabel;
    }
  };

  // js/mixins/submittable.js
  var Submittable = class extends Mixin {
    boot({ options }) {
      options({
        name: void 0,
        value: void 0,
        includeWhenEmpty: true,
        shouldUpdateValue: true
      });
      this.name = this.options().name;
      this.value = this.options().value === void 0 ? this.el.value : this.options().value;
      this.state = false;
      this.observer = new MutationObserver(() => {
        this.renderHiddenInputs();
      });
      this.observer.observe(this.el, { childList: true });
    }
    mount() {
      this.renderHiddenInputs();
    }
    update(value3) {
      if (this.options().shouldUpdateValue) {
        this.value = value3;
      } else {
        this.state = !!value3;
      }
      this.renderHiddenInputs();
    }
    valueIsEmpty() {
      return this.value === void 0 || this.value === null || this.value === "";
    }
    renderHiddenInputs() {
      this.observer.disconnect();
      if (!this.name) return;
      let children = this.el.children;
      let oldInputs = [];
      for (let i = 0; i < children.length; i++) {
        let child = children[i];
        if (child.hasAttribute("data-flux-hidden")) oldInputs.push(child);
      }
      oldInputs.forEach((i) => i.remove());
      let hiddenInputs;
      if (this.options().shouldUpdateValue) {
        hiddenInputs = !this.valueIsEmpty() || this.options().includeWhenEmpty ? this.generateInputs(this.name, this.value) : [];
      } else {
        hiddenInputs = this.state || this.options().includeWhenEmpty ? this.generateInputs(this.name, this.value) : [];
      }
      hiddenInputs.forEach((hiddenInput) => {
        this.el.append(hiddenInput);
      });
      this.observer.observe(this.el, { childList: true });
    }
    generateInputs(name, value3, carry = []) {
      if (this.isObjectOrArray(value3)) {
        for (let key in value3) {
          carry = carry.concat(
            this.generateInputs(`${name}[${key}]`, value3[key])
          );
        }
      } else {
        let el = document.createElement("input");
        el.setAttribute("type", "hidden");
        el.setAttribute("name", name);
        el.setAttribute("value", value3 === null ? "" : "" + value3);
        el.setAttribute("data-flux-hidden", "");
        el.setAttribute("data-appended", "");
        return [el];
      }
      return carry;
    }
    isObjectOrArray(value3) {
      return typeof value3 === "object" && value3 !== null;
    }
    submitEnclosingForm() {
      let form = this.getAssociatedForm();
      if (form) {
        form.requestSubmit();
      }
    }
    getAssociatedForm() {
      let formId = this.el.getAttribute("form");
      if (formId) {
        return document.getElementById(formId) || null;
      }
      return this.el.closest("form");
    }
  };

  // js/select.js
  var UISelect = class extends UIControl {
    boot() {
      let list = this.list();
      this._controllable = new Controllable(this, { bubbles: true });
      this._selectable = new SelectableGroup(list, {
        multiple: this.hasAttribute("multiple")
      });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this._selectable.getState()
      });
      this._controllable.initial((initial) => initial && this._selectable.setState(initial));
      this._controllable.getter(() => this._selectable.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(detangled(() => {
        this._controllable.dispatch();
        this.dispatchEvent(new CustomEvent("select", { bubbles: false }));
      }));
      this._selectable.onInitAndChange(() => {
        this._submittable.update(this._selectable.getState());
      });
    }
    mount() {
      this._disableable = new Disableable(this);
      queueMicrotask(() => {
        this._submittable.update(this._selectable.getState());
      });
      let input = this.input();
      let button = this.button();
      let list = this.list();
      let multiple = this.hasAttribute("multiple");
      let autocomplete = this.hasAttribute("autocomplete");
      let strictAutocomplete = this.hasAttribute("autocomplete") && this.getAttribute("autocomplete").trim().split(" ").includes("strict");
      let listbox = this.querySelector("ui-options") || this;
      let listId = initListbox(listbox, "options", multiple);
      this._activatable = new ActivatableGroup(listbox, { filter: "data-hidden" });
      if (!input && !button) {
        this._disableable.onInitAndChange((disabled) => {
          disabled ? this.removeAttribute("tabindex") : this.setAttribute("tabindex", "0");
        });
      }
      if (this.hasAttribute("filter") && this.getAttribute("filter") !== "manual") {
        this._filterable = new FilterableGroup(list);
        this._filterable.onChange(() => {
          this._activatable.clearActive();
          if (this._filterable.hasResults()) {
            this._activatable.activateFirst();
          }
        });
        this.addEventListener("open", () => {
          if (this._filterable) {
            this._filterable.filter("");
          }
        });
        this.addEventListener("close", () => {
          if (this._filterable) {
            requestAnimationFrame(() => {
              this._filterable.filter("");
            });
          }
        });
      }
      let popoverEl = this.querySelector("[popover]:not(ui-tooltip > [popover])");
      let popoverInputEl = popoverEl?.querySelector('input:not([type="hidden"])');
      let inputEl = this.querySelector('input:not([type="hidden"])');
      inputEl = popoverEl?.contains(inputEl) ? null : inputEl;
      let buttonEl = this.querySelector("button,ui-button");
      buttonEl = popoverEl?.contains(buttonEl) ? null : buttonEl;
      if (!(popoverEl || inputEl)) {
        handleKeyboardNavigation(this, this._activatable);
        handleKeyboardSelection(this, this, this._activatable);
        handleActivationOnFocus(this, this._activatable, this._selectable);
      } else if (!popoverEl && inputEl) {
        let input2 = inputEl;
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            input2 && setAttribute2(input2, "disabled", "");
          } else {
            input2 && removeAttribute(input2, "disabled");
          }
        });
        handleInputClearing(this, input2, this._selectable, this._popoverable);
        handleActivationOnFocus(input2, this._activatable, this._selectable);
        handleAutocomplete(autocomplete, strictAutocomplete, this, input2, this._selectable, this._popoverable, this._filterable);
        preventInputEventsFromBubblingToSelectRoot(input2);
        highlightInputContentsWhenFocused(input2);
        this._filterable && filterResultsByInput(list, input2, this._filterable);
        trackActiveDescendant(input2, this._activatable, this._selectable);
        handleKeyboardNavigation(input2, this._activatable);
        handleKeyboardSelection(this, input2, this._activatable);
        handleMouseSelection(this, this._activatable);
      } else if (popoverEl && inputEl) {
        let input2 = inputEl;
        setAttribute2(input2, "role", "combobox");
        setAttribute2(input2, "aria-controls", listId);
        let popover = popoverEl;
        this._popoverable = new Popoverable(popover);
        this._anchorable = new Anchorable(popover, {
          reference: input2,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
        });
        handleAutocomplete(autocomplete, strictAutocomplete, this, input2, this._selectable, this._popoverable, this._filterable);
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            input2 && setAttribute2(input2, "disabled", "");
          } else {
            input2 && removeAttribute(input2, "disabled");
          }
        });
        this.querySelectorAll("button,ui-button").forEach((button2) => {
          if (popover.contains(button2)) return;
          setAttribute2(button2, "tabindex", "-1");
          setAttribute2(button2, "aria-controls", listId);
          setAttribute2(button2, "aria-haspopup", "listbox");
          linkExpandedStateToPopover(button2, this._popoverable);
          on(button2, "click", () => {
            this._popoverable.toggle();
            if (!isUsingTouch()) input2.focus();
          });
        });
        handleInputClearing(this, input2, this._selectable, this._popoverable);
        initPopover(this, input2, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable, [input2]);
        linkExpandedStateToPopover(input2, this._popoverable);
        preventInputEventsFromBubblingToSelectRoot(input2);
        highlightInputContentsWhenFocused(input2);
        this._filterable && filterResultsByInput(list, input2, this._filterable);
        trackActiveDescendant(input2, this._activatable, this._selectable);
        controlPopoverWithInput(input2, this._popoverable);
        controlPopoverWithKeyboard(input2, this._popoverable, this._activatable, this._selectable);
        openPopoverWithMouse(input2, this._popoverable);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(input2, this._activatable);
        handleKeyboardSelection(this, input2, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
      } else if (popoverEl && popoverInputEl) {
        let button2 = buttonEl;
        let input2 = popoverInputEl;
        let popover = popoverEl;
        setAttribute2(button2, "role", "combobox");
        setAttribute2(input2, "role", "combobox");
        setAttribute2(button2, "aria-controls", listId);
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            button2 && setAttribute2(button2, "disabled", "");
            input2 && setAttribute2(input2, "disabled", "");
          } else {
            button2 && removeAttribute(button2, "disabled");
            input2 && removeAttribute(input2, "disabled");
          }
        });
        this._popoverable = new Popoverable(popover);
        this._anchorable = new Anchorable(popover, {
          reference: button2,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0,
          scrollY: false
        });
        preventInputEventsFromBubblingToSelectRoot(input2);
        highlightInputContentsWhenFocused(input2);
        this._filterable && filterResultsByInput(list, input2, this._filterable);
        trackActiveDescendant(input2, this._activatable, this._selectable);
        focusInputWhenPopoverOpens(input2, this._popoverable);
        initPopover(this, button2, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable, [input2]);
        linkExpandedStateToPopover(button2, this._popoverable);
        handleInputClearing(this, input2, this._selectable, this._popoverable);
        controlPopoverWithKeyboard(button2, this._popoverable, this._activatable, this._selectable);
        togglePopoverWithMouse(button2, this._popoverable);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(input2, this._activatable);
        handleKeyboardSearchNavigation(button2, this._activatable, this._popoverable);
        handleKeyboardSelection(this, input2, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
      } else if (popoverEl) {
        let button2 = buttonEl;
        let popover = popoverEl;
        setAttribute2(button2, "role", "combobox");
        setAttribute2(button2, "aria-controls", listId);
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            button2 && setAttribute2(button2, "disabled", "");
            input && setAttribute2(input, "disabled", "");
          } else {
            button2 && removeAttribute(button2, "disabled");
            input && removeAttribute(input, "disabled");
          }
        });
        this._popoverable = new Popoverable(popover);
        this._anchorable = new Anchorable(popover, {
          reference: button2,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
        });
        initPopover(this, button2, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable);
        linkExpandedStateToPopover(button2, this._popoverable);
        controlPopoverWithKeyboard(button2, this._popoverable, this._activatable, this._selectable);
        togglePopoverWithMouse(button2, this._popoverable);
        trackActiveDescendant(button2, this._activatable, this._selectable);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(button2, this._activatable);
        handleKeyboardSearchNavigation(button2, this._activatable, this._popoverable);
        handleKeyboardSelection(this, button2, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
      }
      let observer = new MutationObserver(() => {
        requestAnimationFrame(() => {
          if (!this._popoverable || this._popoverable.getState()) {
            let firstSelectedOption = this._selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
            this._activatable.activateSelectedOrFirst(firstSelectedOption);
          } else {
            this._activatable.clearActive();
          }
        });
      });
      observer.observe(list, { childList: true });
    }
    unmount() {
      if (this._popoverable?.getState()) {
        let { unlock } = lockScroll();
        unlock();
      }
    }
    trigger() {
      return this.button() || this.input();
    }
    button() {
      return Array.from(this.querySelectorAll("button,ui-button")).find(
        (button) => button.nextElementSibling?.matches("[popover]")
      ) || null;
    }
    input() {
      return this.querySelector('input:not([type="hidden"])');
    }
    list() {
      return this.querySelector("ui-options") || this;
    }
    clear() {
      this._selectable.setState(null);
      this.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
    }
    open() {
      this._popoverable.setState(true);
    }
    close() {
      this._popoverable.setState(false);
    }
    deselectLast() {
      if (!this.hasAttribute("multiple") && this.value !== null) {
        this.value = null;
        this.dispatchEvent(new Event("input", { bubbles: false }));
        this.dispatchEvent(new Event("change", { bubbles: false }));
      }
      if (this.hasAttribute("multiple") && this.value.length !== 0) {
        this.value = this.value.slice(0, -1);
        this.dispatchEvent(new Event("input", { bubbles: false }));
        this.dispatchEvent(new Event("change", { bubbles: false }));
      }
    }
  };
  element("selected-remove", UISelectedRemove);
  element("selected", UISelected);
  element("select", UISelect);
  function handleKeyboardNavigation(el, activatable) {
    on(el, "keydown", (e) => {
      if (!["ArrowDown", "ArrowUp"].includes(e.key)) return;
      if (e.key === "ArrowDown") {
        activatable.activateNext();
        e.preventDefault();
        e.stopPropagation();
      } else if (e.key === "ArrowUp") {
        activatable.activatePrev();
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }
  function handleKeyboardSearchNavigation(el, activatable, popoverable) {
    search(el, (query) => {
      activatable.activateBySearch(query);
      if (!popoverable.getState()) {
        activatable.getActive()?.click();
      }
    });
  }
  function handleKeyboardSelection(root, el, activatable) {
    on(el, "keydown", (e) => {
      if (e.key === "Enter") {
        let activeEl = activatable.getActive();
        e.preventDefault();
        e.stopPropagation();
        if (!activeEl) return;
        if (activeEl._disableable?.isDisabled()) return;
        activeEl.click();
        root.dispatchEvent(new CustomEvent("interaction", {
          bubbles: false,
          cancelable: false,
          detail: { optionEl: activeEl }
        }));
        if (activeEl.hasAttribute("action")) {
          root.dispatchEvent(new CustomEvent("action", {
            bubbles: false,
            cancelable: false,
            detail: { optionEl: activeEl }
          }));
        }
      }
    });
  }
  function handleMouseSelection(root, activatable, pointerdown = false) {
    on(root, pointerdown ? "pointerdown" : "click", (e) => {
      if (e.target.closest("ui-option, ui-option-create")) {
        let option = e.target.closest("ui-option, ui-option-create");
        if (option._disableable.isDisabled()) return;
        option._selectable?.trigger();
        root.dispatchEvent(new CustomEvent("interaction", {
          bubbles: false,
          cancelable: false,
          detail: { optionEl: option }
        }));
        if (option.hasAttribute("action")) {
          root.dispatchEvent(new CustomEvent("action", {
            bubbles: false,
            cancelable: false,
            detail: { optionEl: option }
          }));
        }
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }
  function handleActivationOnFocus(el, activatable, selectable) {
    on(el, "focus", () => {
      let firstSelectedOption = selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
      activatable.activateSelectedOrFirst(firstSelectedOption);
    });
    on(el, "blur", () => {
      activatable.clearActive();
    });
  }
  function initListbox(el, multiple) {
    let listId = assignId(el, "options");
    setAttribute2(el, "role", "listbox");
    setAttribute2(el, "aria-multiselectable", multiple ? "true" : "false");
    return listId;
  }
  function linkExpandedStateToPopover(el, popoverable) {
    setAttribute2(el, "aria-haspopup", "listbox");
    let refreshPopover = () => {
      setAttribute2(el, "aria-expanded", popoverable.getState() ? "true" : "false");
      popoverable.getState() ? setAttribute2(el, "data-open", "") : removeAttribute(el, "data-open", "");
    };
    popoverable.onChange(() => {
      refreshPopover();
    });
    refreshPopover();
  }
  function initPopover(root, trigger, popover, popoverable, anchorable) {
    let refreshPopover = () => {
      Array.from([root, popover]).forEach((i) => {
        popoverable.getState() ? setAttribute2(i, "data-open", "") : removeAttribute(i, "data-open", "");
      });
      popoverable.getState() ? anchorable.reposition() : anchorable.cleanup();
    };
    popoverable.onChange(() => refreshPopover());
    refreshPopover();
    popoverable.onChange(() => {
      if (popoverable.getState()) {
        root.dispatchEvent(new Event("open", {
          bubbles: false,
          cancelable: false
        }));
      } else {
        root.dispatchEvent(new Event("close", {
          bubbles: false,
          cancelable: false
        }));
      }
    });
  }
  function controlActivationWithPopover(popoverable, activatable, selectable) {
    popoverable.onChange(() => {
      if (popoverable.getState()) {
        let firstSelectedOption = selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
        setTimeout(() => {
          activatable.activateSelectedOrFirst(firstSelectedOption);
        });
      } else {
        activatable.clearActive();
      }
    });
  }
  function controlPopoverWithEscapeKey(root, popoverable) {
    on(root, "keydown", (e) => {
      if (e.key === "Escape" && popoverable.getState()) {
        popoverable.setState(false);
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    });
  }
  function controlPopoverWithKeyboard(button, popoverable) {
    on(button, "keydown", (e) => {
      if (!["ArrowDown", "ArrowUp"].includes(e.key)) return;
      if (e.key === "ArrowDown") {
        if (!popoverable.getState()) {
          popoverable.setState(true);
          e.preventDefault();
          e.stopImmediatePropagation();
        }
      } else if (e.key === "ArrowUp") {
        if (!popoverable.getState()) {
          popoverable.setState(true);
          e.preventDefault();
          e.stopImmediatePropagation();
        }
      }
    });
  }
  function openPopoverWithMouse(el, popoverable) {
    on(el, "click", () => {
      if (!popoverable.getState()) {
        popoverable.setState(true);
        el.focus();
      }
    });
  }
  function togglePopoverWithMouse(button, popoverable) {
    on(button, "click", () => {
      popoverable.setState(!popoverable.getState());
      button.focus();
    });
  }
  function focusInputWhenPopoverOpens(input, popoverable) {
    popoverable.onChange(() => {
      if (popoverable.getState() && !isUsingTouch()) {
        setTimeout(() => input.focus());
      }
    });
  }
  function filterResultsByInput(list, input, filterable) {
    on(input, "input", (e) => {
      filterable.filter(e.target.value);
    });
    let original = Object.getOwnPropertyDescriptor(Object.getPrototypeOf(input), "value");
    Object.defineProperty(input, "value", {
      get() {
        return original.get.call(this);
      },
      set(value3) {
        original.set.call(this, value3);
        filterable.filter(value3);
      }
    });
    let observer = new MutationObserver(() => {
      requestAnimationFrame(() => {
        filterable.filter(filterable.lastSearch);
      });
    });
    observer.observe(list, { childList: true });
  }
  function highlightInputContentsWhenFocused(input) {
    on(input, "focus", () => input.select());
  }
  function preventInputEventsFromBubblingToSelectRoot(input) {
    on(input, "change", (e) => e.stopPropagation());
    on(input, "input", (e) => e.stopPropagation());
  }
  function controlPopoverWithInput(input, popoverable) {
    on(input, "keydown", (e) => {
      if (/^[a-zA-Z0-9]$/.test(e.key) || e.key === "Backspace") {
        popoverable.getState() || popoverable.setState(true);
      }
    });
  }
  function handleInputClearing(root, input, selectable, popoverable) {
    let shouldClear = root.hasAttribute("clear");
    if (!shouldClear) return;
    let setInputValue = (value3) => {
      if (input.value === value3) return;
      input.value = value3;
      input.dispatchEvent(new Event("input", { bubbles: false }));
    };
    let clear = root.getAttribute("clear");
    let clearOnAction = clear === "" || clear.split(" ").includes("action");
    let clearOnSelect = clear === "" || clear.split(" ").includes("select");
    let clearOnClose = clear === "" || clear.split(" ").includes("close");
    let clearOnEsc = clear === "" || clear.split(" ").includes("esc");
    if (clear === "none") clearOnAction = clearOnSelect = clearOnClose = clearOnEsc = false;
    if (clearOnAction) {
      root.addEventListener("action", (e) => {
        setInputValue("");
      });
    } else if (clearOnSelect) {
      selectable.onChange(() => {
        queueMicrotask(() => setInputValue(""));
      });
    }
    if (clearOnClose) {
      popoverable.onChange(() => {
        if (!popoverable.getState()) {
          setInputValue("");
        }
      });
    }
    if (clearOnEsc) {
      on(input, "keydown", (e) => {
        if (e.key === "Escape") {
          setInputValue("");
        }
      });
    }
  }
  function handlePopoverClosing(root, selectable, popoverable, multiple) {
    let closeOnInteraction = !multiple;
    let closeOnAction = !multiple;
    let closeOnSelect = !multiple;
    if (root.hasAttribute("close")) {
      let close = root.getAttribute("close");
      closeOnInteraction = close === "";
      closeOnAction = close.split(" ").includes("action");
      closeOnSelect = close.split(" ").includes("select");
      if (close === "none") return;
    }
    if (closeOnInteraction || closeOnAction) {
      root.addEventListener("interaction", (e) => {
        let optionEl = e.detail.optionEl;
        if (!optionEl.hasAttribute("action")) {
          popoverable.setState(false);
          return;
        }
        setTimeout(() => {
          if (!optionEl.hasAttribute("data-flux-loading") && !optionEl.hasAttribute("data-loading")) {
            popoverable.setState(false);
            return;
          }
          let observer = new MutationObserver(() => {
            requestAnimationFrame(() => {
              if (!root.querySelector("input[data-invalid]")) {
                popoverable.setState(false);
              }
            });
            observer.disconnect();
          });
          observer.observe(optionEl, { attributes: true, attributeFilter: ["data-loading", "data-flux-loading"] });
        }, 10);
      });
    } else if (closeOnSelect) {
      selectable.onChange(() => {
        popoverable.setState(false);
      });
    }
  }
  function trackActiveDescendant(input, activatable, selectable) {
    activatable.onChange(() => {
      let activeEl = activatable.getActive();
      activeEl ? setAttribute2(input, "aria-activedescendant", activeEl.id) : removeAttribute(input, "aria-activedescendant");
    });
    selectable.onChange((selectableItem) => {
      if (!selectableItem) return;
      let activeEl = selectableItem.el;
      activeEl && setAttribute2(input, "aria-activedescendant", activeEl.id);
    });
  }
  function handleAutocomplete(autocomplete, isStrict, root, input, selectable, popoverable, filterable) {
    if (!autocomplete) {
      setAttribute2(input, "autocomplete", "off");
      setAttribute2(input, "aria-autocomplete", "none");
      return;
    }
    let setInputValue = (value3) => {
      if (input.value === value3) return;
      input.value = value3;
      input.dispatchEvent(new Event("input", { bubbles: false }));
    };
    setAttribute2(input, "autocomplete", "off");
    setAttribute2(input, "aria-autocomplete", "list");
    if (input.value !== "" && selectable.isEmpty()) {
      selectable.setState(input.value);
    }
    let sync = () => {
      if (selectable.isNotEmpty()) {
        requestAnimationFrame(() => {
          setInputValue(selectable.selectedTextValue());
          if (isStrict) setAttribute2(input, "data-selected", selectable.selectedTextValue());
        });
      } else {
        setInputValue("");
        removeAttribute(input, "data-selected");
      }
    };
    queueMicrotask(() => {
      if (selectable.isEmpty() && input.value !== "") {
        selectable.setState(input.value);
      }
      selectable.onInitAndChange(() => sync());
    });
    root.addEventListener("interaction", (e) => {
      if (e.detail.optionEl._selectable?.isSelected()) sync();
    });
    if (isStrict) {
      popoverable.onChange(() => {
        if (!popoverable.getState()) {
          requestAnimationFrame(() => {
            setInputValue(input.getAttribute("data-selected") || "");
          });
        }
      });
    }
  }
  function preventScrollWhenPopoverIsOpen(root, popoverable, except = []) {
    let { lock, unlock } = lockScroll(popoverable.el, false, except);
    popoverable.onChange(() => {
      popoverable.getState() ? lock() : unlock();
    });
  }

  // js/pillbox.js
  var UIPillbox = class extends UISelect {
    mount() {
      this._disableable = new Disableable(this);
      queueMicrotask(() => {
        this._submittable.update(this._selectable.getState());
      });
      let input = this.input();
      let list = this.list();
      let multiple = this.hasAttribute("multiple");
      let triggerEl = this.querySelector("ui-pillbox-trigger");
      let listbox = this.querySelector("ui-options") || this;
      let listId = initListbox(listbox, "options", multiple);
      this._activatable = new ActivatableGroup(listbox, { filter: "data-hidden" });
      if (this.hasAttribute("filter") && this.getAttribute("filter") !== "manual") {
        this._filterable = new FilterableGroup(list);
        this._filterable.onChange(() => {
          this._activatable.clearActive();
          if (this._filterable.hasResults()) {
            this._activatable.activateFirst();
          }
        });
        this.addEventListener("close", () => {
          if (this._filterable) {
            this._filterable.filter("");
          }
        });
      }
      let popoverEl = this.querySelector("[popover]:not(ui-tooltip > [popover])");
      let popoverInputEl = popoverEl?.querySelector('input:not([type="hidden"])');
      let inlineInputEl = this.querySelector("ui-selected input");
      if (!popoverEl) throw new Error("Popover element not found");
      if (popoverInputEl) {
        let trigger = triggerEl;
        let input2 = popoverInputEl;
        let popover = popoverEl;
        setAttribute2(trigger, "role", "button");
        setAttribute2(input2, "aria-autocomplete", "list");
        setAttribute2(input2, "aria-controls", listId);
        setAttribute2(input2, "role", "combobox");
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            trigger && setAttribute2(trigger, "disabled", "");
            input2 && setAttribute2(input2, "disabled", "");
          } else {
            trigger && removeAttribute(trigger, "disabled");
            input2 && removeAttribute(input2, "disabled");
          }
        });
        this._popoverable = new Popoverable(popover);
        this._anchorable = new Anchorable(popover, {
          reference: trigger,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0,
          scrollY: false
        });
        trackActiveDescendant(input2, this._activatable, this._selectable);
        preventInputEventsFromBubblingToSelectRoot(input2);
        highlightInputContentsWhenFocused(input2);
        this._filterable && filterResultsByInput(list, input2, this._filterable);
        focusInputWhenPopoverOpens(input2, this._popoverable);
        initPopover(this, trigger, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable, [input2]);
        linkExpandedStateToPopover(trigger, this._popoverable);
        handleInputClearing(this, input2, this._selectable, this._popoverable);
        controlPopoverWithKeyboard(trigger, this._popoverable, this._activatable, this._selectable);
        togglePopoverWithMouse(trigger, this._popoverable);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(input2, this._activatable);
        handleKeyboardSearchNavigation(trigger, this._activatable, this._popoverable);
        handleKeyboardSelection(this, input2, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
        openPopoverWithSpacebar(trigger);
      } else if (inlineInputEl) {
        let trigger = triggerEl;
        let input2 = inlineInputEl;
        let popover = popoverEl;
        setAttribute2(trigger, "role", "button");
        setAttribute2(input2, "aria-autocomplete", "list");
        setAttribute2(input2, "aria-controls", listId);
        setAttribute2(input2, "role", "combobox");
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            trigger && setAttribute2(trigger, "disabled", "");
            input2 && setAttribute2(input2, "disabled", "");
          } else {
            trigger && removeAttribute(trigger, "disabled");
            input2 && removeAttribute(input2, "disabled");
          }
        });
        this._popoverable = new Popoverable(popover, {
          triggers: [input2, trigger]
          // Make sure that focusing the input doesn't close the popover...
        });
        this._anchorable = new Anchorable(popover, {
          reference: trigger,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
        });
        trackActiveDescendant(input2, this._activatable, this._selectable);
        preventInputEventsFromBubblingToSelectRoot(input2);
        highlightInputContentsWhenFocused(input2);
        this._filterable && filterResultsByInput(list, input2, this._filterable);
        focusInputWhenPopoverOpens(input2, this._popoverable);
        initPopover(this, trigger, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable, [trigger]);
        linkExpandedStateToPopover(trigger, this._popoverable);
        handleInputClearing(this, input2, this._selectable, this._popoverable);
        controlPopoverWithInput(input2, this._popoverable);
        controlPopoverWithKeyboard(input2, this._popoverable, this._activatable, this._selectable);
        openPopoverWithMouse(trigger, this._popoverable, input2);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(input2, this._activatable);
        handleKeyboardSearchNavigation(trigger, this._activatable, this._popoverable);
        handleKeyboardSelection(this, input2, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
        focusInputOnSelection(this, this._selectable, input2);
        hideInputPlaceholderWhenNotEmpty(this._selectable, input2);
        deselectLastItemWithBackspace(this._selectable, input2);
        forwardClicksToInputWhenPopoverIsOpen(this._popoverable, trigger, input2);
      } else {
        let trigger = triggerEl;
        let popover = popoverEl;
        setAttribute2(trigger, "role", "combobox");
        setAttribute2(trigger, "aria-controls", listId);
        setAttribute2(trigger, "aria-autocomplete", "none");
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            trigger && setAttribute2(trigger, "disabled", "");
            input && setAttribute2(input, "disabled", "");
          } else {
            trigger && removeAttribute(trigger, "disabled");
            input && removeAttribute(input, "disabled");
          }
        });
        this._popoverable = new Popoverable(popover);
        this._anchorable = new Anchorable(popover, {
          reference: trigger,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
        });
        initPopover(this, trigger, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable);
        linkExpandedStateToPopover(trigger, this._popoverable);
        controlPopoverWithKeyboard(trigger, this._popoverable, this._activatable, this._selectable);
        togglePopoverWithMouse(trigger, this._popoverable);
        trackActiveDescendant(trigger, this._activatable, this._selectable);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(trigger, this._activatable);
        handleKeyboardSearchNavigation(trigger, this._activatable, this._popoverable);
        handleKeyboardSelection(this, trigger, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
        openPopoverWithSpacebar(trigger);
      }
      let observer = new MutationObserver(() => {
        requestAnimationFrame(() => {
          if (!this._popoverable || this._popoverable.getState()) {
            let firstSelectedOption = this._selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
            this._activatable.activateSelectedOrFirst(firstSelectedOption);
          } else {
            this._activatable.clearActive();
          }
        });
      });
      observer.observe(list, { childList: true });
    }
    list() {
      return this.querySelector("ui-options") || this;
    }
  };
  var UIPillboxTrigger = class extends UIElement {
    boot() {
      if (this.querySelector("input")) {
        setAttribute2(this, "tabindex", "-1");
      } else {
        setAttribute2(this, "tabindex", "0");
      }
      this._disableable = new Disableable(this);
      let disableClick = (e) => {
        e.preventDefault();
        e.stopImmediatePropagation();
      };
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.addEventListener("click", disableClick, true);
        } else {
          this.removeEventListener("click", disableClick, true);
        }
      });
    }
    mount() {
    }
  };
  element("pillbox-trigger", UIPillboxTrigger);
  element("pillbox", UIPillbox);
  function openPopoverWithSpacebar(el) {
    on(el, "keydown", (e) => {
      if (e.key === " ") {
        e.preventDefault();
        e.stopImmediatePropagation();
        el.click();
      }
    });
  }
  function focusInputOnSelection(root, selectable, input) {
    selectable.onChange(() => {
      if (document.activeElement !== input && document.activeElement.closest("ui-pillbox") === root) {
        input.focus();
      }
    });
  }
  function hideInputPlaceholderWhenNotEmpty(selectable, input) {
    selectable.onInitAndChange(() => {
      if (selectable.noneAreSelected()) {
        setAttribute2(input, "placeholder", input.getAttribute("data-placeholder"));
      } else {
        setAttribute2(input, "placeholder", "");
      }
    });
  }
  function deselectLastItemWithBackspace(selectable, input) {
    on(input, "keydown", (e) => {
      if (input.value == "" && e.key === "Backspace") {
        let last = selectable.findByValue(
          selectable.getState().at(-1)
        );
        last?.deselect();
      }
    });
  }
  function forwardClicksToInputWhenPopoverIsOpen(popoverable, trigger, input) {
    on(trigger, "click", (e) => {
      if (popoverable.getState()) {
        input.focus();
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    });
  }

  // js/options.js
  var UIOptions = class extends UIElement {
    boot() {
      setAttribute2(this, "tabindex", "-1");
      if (this.hasAttribute("popover")) {
        this.addEventListener("lofi-close-popovers", () => {
          this.hidePopover();
        });
      }
      setAttribute2(this, "role", "listbox");
    }
    mount() {
      let picker = this.closest("ui-autocomplete, ui-combobox, ui-select, ui-pillbox");
      let input = picker?.input();
      if (input) displayEmptyAndCreateOptions(this, input, picker._filterable);
    }
  };
  var UIOption = class extends UIElement {
    mount() {
      let target = this;
      this._disableable = new Disableable(target, {
        disableWithParent: false
      });
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(target, "aria-disabled", "true");
        } else {
          removeAttribute(target, "aria-disabled");
        }
      });
      let id = assignId(target, "option");
      setAttribute2(target, "role", "option");
      this._filterable = new Filterable(target, {
        mirror: this,
        keep: !!this.closest("ui-option-empty, ui-empty") || this.getAttribute("filter") === "manual"
      });
      this._activatable = new Activatable(target);
      if (!this.hasAttribute("action")) {
        this._selectable = new Selectable(target, {
          value: this._pendingValue ?? this.getValue(),
          label: this.getLabel(),
          selectedLabel: this.getSelectedLabel(),
          selectedInitially: this.hasAttribute("selected")
        });
      }
      let observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.attributeName === "selected") {
            if (this.hasAttribute("selected")) this._selectable.setState(true);
            else this._selectable.setState(false);
          }
          if (mutation.attributeName === "value") {
            if (this._selectable) {
              this._selectable.value = this.getValue();
            }
          }
        });
      });
      observer.observe(this, { attributeFilter: ["selected", "value"] });
    }
    get value() {
      return this._selectable?.value ?? this._pendingValue ?? this.getValue();
    }
    set value(val) {
      if (!this._selectable) {
        this._pendingValue = val + "";
        return;
      }
      this._selectable.value = val + "";
    }
    get selected() {
      if (!this?._selectable) return false;
      return this._selectable.isSelected();
    }
    set selected(value3) {
      if (!this?._selectable) return false;
      this._selectable.setState(value3);
    }
    getSelectedLabel() {
      return this.getAttribute("selected-label");
    }
    getLabel() {
      return this.hasAttribute("label") ? this.getAttribute("label") : this.textContent.trim();
    }
    getValue() {
      return this.hasAttribute("value") ? this.getAttribute("value") : this.textContent.trim();
    }
  };
  var UIOptionCreate = class extends UIElement {
    mount() {
      this._activatable = new Activatable(this);
      this._filterable = new Filterable(this, { keep: true });
      this._disableable = new Disableable(this, {
        disableWithParent: false
      });
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(this, "aria-disabled", "true");
        } else {
          removeAttribute(this, "aria-disabled");
        }
      });
    }
  };
  var UIOptionEmpty = class extends UIElement {
  };
  var UIEmpty = class extends UIOptionEmpty {
  };
  element("option-create", UIOptionCreate);
  element("options", UIOptions);
  element("option", UIOption);
  element("option-empty", UIOptionEmpty);
  element("empty", UIEmpty);
  function normalizeScopedSelector(selector) {
    if (!CSS.supports("selector(&)")) {
      return selector.replace("&", ":scope");
    }
    return selector;
  }
  function displayEmptyAndCreateOptions(list, input, filterable) {
    let empty = list.querySelector(normalizeScopedSelector("& > ui-option-empty, & > ui-empty"));
    let create = list.querySelector(normalizeScopedSelector("& > ui-option-create"));
    let minLength = create?.hasAttribute("min-length") ? parseInt(create.getAttribute("min-length")) : void 0;
    setAttribute2(empty, "data-hidden", "");
    if (create) setAttribute2(create, "data-hidden", "");
    let isHidden = (el) => el.hasAttribute("data-hidden");
    let isUnique = (el) => el.textContent.toLowerCase().trim() !== input.value.toLowerCase().trim();
    let getItems = () => Array.from(list.querySelectorAll(normalizeScopedSelector("& > ui-option")));
    let showLoading = () => {
      if (empty.hasAttribute("when-loading")) {
        let emptyContent = empty.textContent;
        empty.textContent = empty.getAttribute("when-loading");
        empty.__hideLoading = () => {
          empty.textContent = emptyContent;
          empty.__hideLoading = void 0;
        };
      }
    };
    let revertDisable;
    let debounceEnable;
    let refresh = () => {
      clearTimeout(revertDisable);
      clearTimeout(debounceEnable);
      if (input.hasAttribute("data-flux-loading") || input.hasAttribute("data-loading")) return;
      let items = getItems();
      let isEmpty = items.every((i) => isHidden(i));
      if (create) {
        if (minLength && input.value.trim().length < minLength) {
          setAttribute2(create, "data-hidden", "");
        } else {
          if (items.every((i) => isUnique(i))) {
            removeAttribute(create, "data-hidden");
          } else {
            setAttribute2(create, "data-hidden", "");
          }
        }
        if (isEmpty) create._activatable.activate(true);
        let enable = () => {
          removeAttribute(create, "disabled");
        };
        if (filterable) {
          enable();
        } else {
          debounceEnable = setTimeout(enable, 150);
        }
      }
      if (isEmpty && (!create || isHidden(create))) {
        removeAttribute(empty, "data-hidden");
      } else {
        setAttribute2(empty, "data-hidden", "");
      }
      empty.__hideLoading?.();
    };
    if (filterable) {
      filterable.onChange(() => refresh());
      if (create) {
        on(input, "input", () => {
          if (minLength && input.value.trim().length < minLength) {
            setAttribute2(create, "data-hidden", "");
          }
        });
      }
    } else {
      let observer = new MutationObserver(() => {
        requestAnimationFrame(() => refresh());
      });
      observer.observe(list, { childList: true });
      observer.observe(input, { attributes: true, attributeFilter: ["data-loading", "data-flux-loading"] });
      if (create) {
        on(input, "input", (e) => {
          clearTimeout(revertDisable);
          clearTimeout(debounceEnable);
          let isEmpty = getItems().every((i) => isHidden(i));
          if (isEmpty && input.value.trim() === "" && !isHidden(create)) {
            showLoading();
            removeAttribute(empty, "data-hidden");
            setAttribute2(create, "data-hidden", "");
          } else if (input.value.trim() === "" && !isHidden(create)) {
            setAttribute2(create, "data-hidden", "");
          }
          if (minLength && input.value.trim().length >= minLength) {
            removeAttribute(create, "data-hidden");
          }
          setAttribute2(create, "disabled", "");
          revertDisable = setTimeout(() => {
            removeAttribute(create, "disabled");
            if (isEmpty) create._activatable.activate();
          }, 200);
        });
      }
    }
    refresh();
  }

  // js/calendar/date.js
  var DateValue = class _DateValue {
    constructor(year, month, day = 1) {
      this._date = new Date(Date.UTC(year, month - 1, day));
    }
    isBetween(min2, max2) {
      if (!min2 && !max2) return true;
      if (!min2) return this._date <= max2._date;
      if (!max2) return this._date >= min2._date;
      return this._date >= min2._date && this._date <= max2._date;
    }
    isSameDay(date) {
      if (!date) return false;
      return this._date.getUTCDate() === date._date.getUTCDate() && this._date.getUTCMonth() === date._date.getUTCMonth() && this._date.getUTCFullYear() === date._date.getUTCFullYear();
    }
    isBefore(date) {
      if (!date) return false;
      return this._date < date._date;
    }
    isAfter(date) {
      if (!date) return false;
      return this._date > date._date;
    }
    /** Immutable manipulators */
    incrementDays(days) {
      let copy = this.getCopy();
      copy._date.setUTCDate(copy._date.getUTCDate() + days);
      return copy;
    }
    addMonths(months) {
      let copy = this.getCopy();
      copy._date.setUTCMonth(copy._date.getUTCMonth() + months);
      return copy;
    }
    addDays(days) {
      let copy = this.getCopy();
      copy._date.setUTCDate(copy._date.getUTCDate() + days);
      return copy;
    }
    /** Getters */
    getYear() {
      return this._date.getUTCFullYear();
    }
    getMonth() {
      return this._date.getUTCMonth() + 1;
    }
    getPaddedMonth() {
      return String(this.getMonth()).padStart(2, "0");
    }
    getDay() {
      return this._date.getUTCDate();
    }
    getPaddedDay() {
      return String(this.getDay()).padStart(2, "0");
    }
    getDate() {
      return this._date;
    }
    getCopy() {
      return new _DateValue(this.getYear(), this.getMonth(), this.getDay());
    }
    getDayOfWeek() {
      return this._date.getUTCDay();
    }
    getDaysInMonth() {
      return new _DateValue(this.getYear(), this.getMonth() + 1, 0).getDay();
    }
    getFirstDayOfMonth() {
      return new _DateValue(this.getYear(), this.getMonth(), 1).getDayOfWeek();
    }
    getWeekNumber() {
      let firstDayOfYear = new Date(Date.UTC(this._date.getUTCFullYear(), 0, 1));
      let firstThursday = new Date(firstDayOfYear);
      firstThursday.setUTCDate(1 + (11 - firstDayOfYear.getUTCDay()) % 7);
      let targetThursday = new Date(this._date);
      targetThursday.setUTCDate(this._date.getUTCDate() + (11 - this._date.getUTCDay()) % 7);
      let weeksBetween = Math.floor((targetThursday.getTime() - firstThursday.getTime()) / (7 * 24 * 60 * 60 * 1e3));
      return weeksBetween + 1;
    }
    /** Setters */
    setDay(day) {
      this._date.setUTCDate(day);
    }
    setMonth(month) {
      this._date.setUTCMonth(month - 1);
    }
    setYear(year) {
      this._date.setUTCFullYear(year);
    }
    /** Static factories: */
    static fromIsoDateString(isoString) {
      if (!isoString) return null;
      let [year, month, day] = isoString.split("T")[0]?.split("-")?.map(Number);
      return new _DateValue(year, month, day);
    }
    static fromLocalDate(date) {
      if (!date) return null;
      return new _DateValue(date.getUTCFullYear(), date.getUTCMonth() + 1, date.getUTCDate());
    }
    static fromDate(date) {
      if (!date) return null;
      return new _DateValue(date.getFullYear(), date.getMonth() + 1, date.getDate());
    }
    static fromParts(year, month, day) {
      return new _DateValue(year, month, day);
    }
    static today() {
      return _DateValue.fromDate(/* @__PURE__ */ new Date());
    }
    static firstDayOfMonth(date) {
      return new _DateValue(date.getYear(), date.getMonth(), 1);
    }
    /** Formats */
    toIsoDateString() {
      return [this.getYear(), this.getPaddedMonth(), this.getPaddedDay()].join("-");
    }
    toParts() {
      return [this.getYear(), this.getMonth(), this.getDay()];
    }
    toDate() {
      return new Date(this.getYear(), this.getMonth() - 1, this.getDay());
    }
    toFormattedString(locale, options) {
      return this.toDate().toLocaleDateString(locale, options);
    }
  };

  // js/calendar/render.js
  function renderMonth(monthEl, config, viewState, metadata) {
    let headingTemplate = monthEl.querySelector('template[name="heading"]');
    let weekdayTemplate = monthEl.querySelector('template[name="weekday"]');
    let weekTemplate = monthEl.querySelector('template[name="week"]');
    headingTemplate && renderHeading(headingTemplate, config, viewState);
    weekdayTemplate && renderWeekdays(weekdayTemplate, config);
    weekTemplate && renderDates(weekTemplate, config, viewState, metadata);
  }
  function renderHeading(template, config, viewState) {
    renderTemplate(template, (hydrate) => {
      return hydrate({
        slots: {
          default: new Intl.DateTimeFormat(config.locale, {
            month: template.hasAttribute("display") ? template.getAttribute("display") : "long",
            year: "numeric",
            timeZone: "UTC"
          }).format(new DateValue(viewState.year, viewState.month).getDate())
        }
      });
    });
  }
  function renderWeekdays(template, config) {
    let format = (date) => {
      if (template.hasAttribute("display")) {
        return new Intl.DateTimeFormat(config.locale, { weekday: template.getAttribute("display") }).format(date);
      }
      let safeTwoCharLocales = ["en", "es", "de", "fr", "it", "pt"];
      if (safeTwoCharLocales.includes(config.locale.split("-")[0])) {
        let formatter2 = new Intl.DateTimeFormat(config.locale, { weekday: "short" });
        return formatter2.format(date).slice(0, 2);
      }
      let formatter = new Intl.DateTimeFormat(config.locale, { weekday: "narrow" });
      return formatter.format(date);
    };
    let weekdays = Array.from({ length: 7 }, (_, idx) => {
      let adjustedIdx = (idx + config.startDay) % 7;
      let date = new Date(2024, 0, adjustedIdx + 7);
      return format(date);
    });
    renderTemplate(template, (hydrate) => {
      return weekdays.map((weekday) => hydrate({ slots: { default: weekday } }));
    });
  }
  function renderDates(template, config, viewState, metadata) {
    let month = viewState.month;
    let year = viewState.year;
    let firstDay = (new DateValue(year, month).getFirstDayOfMonth() - config.startDay + 7) % 7;
    let totalDays = new DateValue(year, month).getDaysInMonth();
    let [prevYear, prevMonth] = new DateValue(year, month).addMonths(-1).toParts();
    let prevMonthDays = new DateValue(prevYear, prevMonth).getDaysInMonth();
    let [displayDates, actualDates] = [[], []];
    for (let i = 0; i < firstDay; i++) {
      displayDates.push(0);
      let prevDate = prevMonthDays - firstDay + i + 1;
      actualDates.push(new DateValue(prevYear, prevMonth, prevDate).toIsoDateString());
    }
    for (let i = 1; i <= totalDays; i++) {
      displayDates.push(i);
      actualDates.push(new DateValue(year, month, i).toIsoDateString());
    }
    let totalCells = firstDay + totalDays;
    let remainingDays = (7 - totalCells % 7) % 7;
    let [nextYear, nextMonth] = new DateValue(year, month).addMonths(1).toParts();
    for (let i = 1; i <= remainingDays; i++) {
      displayDates.push(0);
      actualDates.push(new DateValue(nextYear, nextMonth, i).toIsoDateString());
    }
    if (config.fixedWeeks) {
      let currentWeeks = Math.ceil(displayDates.length / 7);
      let targetWeeks = config.fixedWeeks;
      let weeksDiff = targetWeeks - currentWeeks;
      if (weeksDiff > 0) {
        for (let i = 0; i < weeksDiff * 7; i++) {
          displayDates.push(0);
          actualDates.push(DateValue.fromIsoDateString(actualDates[actualDates.length - 1]).addDays(1).toIsoDateString());
        }
      } else if (weeksDiff < 0) {
        displayDates.length = targetWeeks * 7;
        actualDates.length = targetWeeks * 7;
      }
    }
    let splitIntoWeeks = (arr) => Array.from(
      { length: Math.ceil(arr.length / 7) },
      (_, idx) => arr.slice(idx * 7, (idx + 1) * 7)
    );
    let displayWeeks = splitIntoWeeks(displayDates);
    let actualWeeks = splitIntoWeeks(actualDates);
    renderTemplate(template, (hydrate) => {
      return displayWeeks.map((week, weekIdx) => {
        let weekEl = hydrate();
        let actualWeekDates = actualWeeks[weekIdx];
        let numberTemplate = weekEl.querySelector('template[name="number"]');
        if (numberTemplate) {
          let fourthDayIdx = (4 - config.startDay + 7) % 7;
          let weekNumber = DateValue.fromIsoDateString(actualWeekDates[fourthDayIdx]).getWeekNumber();
          renderTemplate(numberTemplate, (hydrate2) => {
            return hydrate2({ slots: { default: weekNumber } });
          });
        }
        renderTemplate(weekEl.querySelector('template[name="day"]'), (hydrate2) => {
          return week.map((day, dayIdx) => {
            if (day === 0) {
              let weekDate = DateValue.fromIsoDateString(actualWeekDates[dayIdx]);
              let dayNumber = weekDate.getDay();
              return hydrate2({
                slots: { default: dayNumber },
                attrs: { "data-date": actualWeekDates[dayIdx], "data-outside": "" }
              });
            }
            let date = DateValue.fromIsoDateString(actualWeekDates[dayIdx]);
            let dayEl = hydrate2({
              slots: { default: day },
              attrs: { "data-date": actualWeekDates[dayIdx] }
            });
            let subtext = metadata.subtext(date);
            let details = metadata.details(date);
            let variant = metadata.variant(date);
            if (![false, null, void 0].includes(variant)) {
              dayEl.dataset.dateVariant = variant;
            }
            if (![false, null, void 0].includes(subtext)) {
              let template2 = dayEl.querySelector('template[name="subtext"]');
              template2 && renderTemplate(template2, (hydrate3) => hydrate3({ slots: { default: subtext } }));
            }
            if (![false, null, void 0].includes(details)) {
              let template2 = dayEl.querySelector('template[name="details"]');
              template2 && renderTemplate(template2, (hydrate3) => hydrate3({ slots: { default: details } }));
            }
            return dayEl;
          });
        });
        return weekEl;
      });
    });
  }
  function updateMonth(el, disabled, config, viewState, selectable, validator) {
    let getFirstDateToMakeTabbable = (month, year) => {
      return selectable.lowerBound(() => {
        let isCurrentMonth = DateValue.today().getMonth() === month && DateValue.today().getYear() === year;
        return isCurrentMonth ? DateValue.today() : new DateValue(year, month);
      });
    };
    let tabbable = getFirstDateToMakeTabbable(viewState.month, viewState.year);
    el.querySelectorAll("[data-date]").forEach((cell) => {
      setAttribute2(cell, "role", "gridcell");
      let button = cell.querySelector("button,ui-button");
      let date = dateFromCell(cell);
      if (!date) return;
      button ? setAttribute2(button, "aria-label", date.toFormattedString(config.locale, { day: "numeric", month: "long", year: "numeric", weekday: "long" })) : setAttribute2(cell, "aria-label", date.toFormattedString(config.locale, { day: "numeric", month: "long", year: "numeric", weekday: "long" }));
      if (!validator.isValid(date)) {
        setAttribute2(cell, "disabled", "");
        setAttribute2(cell, "aria-disabled", "true");
        button && setAttribute2(button, "disabled", "");
        validator.isUnavailable(date) && setAttribute2(cell, "data-unavailable", "");
      } else if (disabled) {
        setAttribute2(cell, "disabled", "");
        setAttribute2(cell, "aria-disabled", "true");
        button && setAttribute2(button, "disabled", "");
      } else {
        removeAttribute(cell, "disabled");
        removeAttribute(cell, "data-unavailable");
        removeAttribute(cell, "aria-disabled");
        button && removeAttribute(button, "disabled");
      }
      if (DateValue.today().isSameDay(date)) {
        setAttribute2(cell, "data-today", "");
      } else {
        removeAttribute(cell, "data-today", "");
      }
      selectable.attributes(cell, date);
      if (date.isSameDay(tabbable)) {
        button && setAttribute2(button, "tabindex", "0");
      } else {
        button && setAttribute2(button, "tabindex", "-1");
      }
    });
  }
  function renderTemplate(template, callback) {
    if (!template) return;
    let cleanup = () => {
      let sibling = template.nextElementSibling;
      while (sibling && sibling.hasAttribute("data-appended")) {
        let toRemove = sibling;
        sibling = sibling.nextElementSibling;
        toRemove.remove();
      }
    };
    cleanup();
    let hydrated = callback(({ slots = {}, attrs = {} } = {}) => {
      let clone = template.content.cloneNode(true).firstElementChild;
      Object.entries(slots).forEach(([key, value3]) => {
        let slotNodes = key === "default" ? clone.querySelectorAll("slot:not([name])") : clone.querySelectorAll(`slot[name="${key}"]`);
        slotNodes.forEach((i) => i.replaceWith(
          typeof value3 === "string" ? document.createTextNode(value3) : value3
        ));
      });
      clone.querySelectorAll("slot").forEach((slot) => slot.remove());
      Object.entries(attrs).forEach(([key, value3]) => {
        clone.setAttribute(key, value3);
      });
      return clone;
    });
    hydrated = Array.isArray(hydrated) ? hydrated : [hydrated];
    hydrated.reverse().forEach((node) => {
      setAttribute2(node, "data-appended", "");
      template.after(node);
    });
    return { cleanup };
  }
  function dateFromCell(cell) {
    if (!cell) return null;
    if (!cell.hasAttribute("data-date")) return null;
    return DateValue.fromIsoDateString(cell.getAttribute("data-date"));
  }

  // js/time-picker/selectable.js
  var SelectableModes = class {
    static SINGLE = "single";
    static MULTIPLE = "multiple";
  };
  var ObservableTrigger = class {
    static SELECTION = "selection";
    static ACTIVATION = "activation";
    static VIEW_CHANGE = "view-change";
  };
  var Selectable2 = class _Selectable {
    constructor(selection, config, observable) {
      this.config = config;
      this.observable = observable;
      this.selection = selection;
    }
    select(value3) {
      let previousSelection = this.selection;
      this.selection = this.selection.select(value3, this.config);
      if (this.selection.shouldNotify(previousSelection)) this.observable.notify(ObservableTrigger.SELECTION);
      else this.observable.notify(ObservableTrigger.ACTIVATION);
    }
    setValue(value3) {
      let previousSelection = this.selection;
      this.selection = _Selectable.createSelectionFromValue(value3, this.config);
      if (this.selection.shouldNotify(previousSelection)) this.observable.notify(ObservableTrigger.SELECTION);
      else this.observable.notify(ObservableTrigger.ACTIVATION);
    }
    getValue() {
      return this.selection.value();
    }
    isSelectable(value3) {
      return this.selection.selectable(value3, this.config);
    }
    /** Passthrough methods */
    contains(...params) {
      return this.selection.contains(...params);
    }
    hasSelection() {
      return this.selection.hasSelection();
    }
    lowerBound(...params) {
      return this.selection.lowerBound(...params);
    }
    upperBound(...params) {
      return this.selection.upperBound(...params);
    }
    display(...params) {
      return this.selection.display(this.config, ...params);
    }
    attributes(...params) {
      return this.selection.attributes(...params);
    }
    static createSelectionFromValue(value3, config) {
      if (config.mode === SelectableModes.MULTIPLE) {
        value3 ??= [];
        return new MultipleSelection(value3);
      } else {
        if (!value3) return new NoneSelection();
        return new SingleSelection(value3);
      }
    }
    static createFromValueStringAttribute(value3, config, observable) {
      let selection = _Selectable.createSelectionFromValueStringAttribute(value3, config);
      return new _Selectable(selection, config, observable);
    }
    static createSelectionFromValueStringAttribute(value3, config) {
      if (config.mode === SelectableModes.MULTIPLE) {
        value3 = value3 ? value3.split(",").map((i) => i.trim()) : [];
      }
      return _Selectable.createSelectionFromValue(value3, config);
    }
  };
  var Selection = class {
    contains() {
    }
    shouldNotify(previousSelection) {
      return true;
    }
    hasSelection() {
      return false;
    }
    lowerBound(fallback) {
      return value(fallback);
    }
    upperBound(fallback) {
      return this.lowerBound(fallback);
    }
    selectable(value3, config) {
      return true;
    }
    display(config) {
    }
    value() {
    }
    attributes(el, value3) {
      if (this.contains(value3)) {
        setAttribute2(el, "data-selected", "");
        setAttribute2(el, "aria-selected", "true");
      } else {
        removeAttribute(el, "data-selected");
        setAttribute2(el, "aria-selected", "false");
      }
    }
  };
  var NoneSelection = class _NoneSelection extends Selection {
    contains() {
      return false;
    }
    shouldNotify(selection) {
      return !(selection instanceof _NoneSelection);
    }
    hasSelection() {
      return false;
    }
    lowerBound(fallback) {
      return value(fallback);
    }
    select(value3) {
      return new SingleSelection(value3);
    }
    display(config) {
      return "";
    }
    value() {
      return null;
    }
  };
  var SingleSelection = class _SingleSelection extends Selection {
    constructor(value3) {
      super();
      this._value = value3;
    }
    contains(value3) {
      return this._value === value3;
    }
    shouldNotify(previousSelection) {
      if (previousSelection instanceof _SingleSelection && this._value === previousSelection._value) return false;
      return true;
    }
    hasSelection() {
      return true;
    }
    lowerBound() {
      return this._value;
    }
    select(value3) {
      if (this._value === value3) return new NoneSelection();
      return new _SingleSelection(value3);
    }
    display(config) {
      return formatTime(this._value, config.timeFormat, config.locale);
    }
    value() {
      return this._value;
    }
  };
  var MultipleSelection = class _MultipleSelection extends Selection {
    constructor(values) {
      super();
      this._values = values.sort((a, b) => timeInMinutes(a) < timeInMinutes(b) ? -1 : 1);
    }
    contains(value3) {
      return this._values.some((i) => i === value3);
    }
    shouldNotify(previousSelection) {
      if (previousSelection instanceof _MultipleSelection && this._values.every((i) => previousSelection._values.includes(i)) && previousSelection._values.every((i) => this._values.includes(i))) return false;
      return true;
    }
    hasSelection() {
      return this._values.length > 0;
    }
    lowerBound(fallback) {
      let sortedValues = this._values.sort((a, b) => timeInMinutes(a) < timeInMinutes(b) ? -1 : 1);
      return sortedValues[0] || value(fallback);
    }
    upperBound(fallback) {
      let sortedValues = this._values.sort((a, b) => timeInMinutes(a) < timeInMinutes(b) ? -1 : 1);
      return sortedValues[sortedValues.length - 1] || value(fallback);
    }
    select(value3) {
      let containsValue = this._values.some((i) => i === value3);
      if (containsValue) return new _MultipleSelection(this._values.filter((i) => i !== value3));
      return new _MultipleSelection([...this._values, value3]);
    }
    display(config) {
      return this._values.sort((a, b) => timeInMinutes(a) < timeInMinutes(b) ? -1 : 1).map((i) => formatTime(i, config.timeFormat, config.locale)).join(", ");
    }
    value() {
      return this._values;
    }
  };
  function value(i = null) {
    return typeof i === "function" ? i() : i;
  }

  // js/time-picker/trigger.js
  var UITimePickerTrigger = class extends UIElement {
    boot() {
      this.picker = this.closest("ui-time-picker");
      let inputs = this.querySelectorAll("input");
      this._disableable = new Disableable(this);
      let disableClick = (e) => {
        e.preventDefault();
        e.stopImmediatePropagation();
      };
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.addEventListener("click", disableClick, true);
        } else {
          this.removeEventListener("click", disableClick, true);
        }
      });
      if (inputs.length === 0) return;
      if (inputs.length < 2) throw new Error("Time picker inputs missing");
      this.hourInput = inputs[0];
      this.minuteInput = inputs[1];
      this.meridiemInput = inputs[2] || null;
      if (this.meridiemInput && this.use24HourFormat()) {
        this.meridiemInput.style.display = "none";
        this.meridiemInput = null;
      }
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.hourInput.setAttribute("disabled", "");
          this.minuteInput.setAttribute("disabled", "");
          this.meridiemInput?.setAttribute("disabled", "");
        } else {
          this.hourInput.removeAttribute("disabled");
          this.minuteInput.removeAttribute("disabled");
          this.meridiemInput?.removeAttribute("disabled");
        }
      });
      this.hourInput.placeholder = this.meridiemInput ? "hh" : "HH";
      this.minuteInput.placeholder = "mm";
      this.meridiemInput && (this.meridiemInput.placeholder = getLocalisedMeridiem(0, this.picker.config.locale) || "AM");
      this.clearInputs();
      initCursorSelectionListeners(this.hourInput);
      initCursorSelectionListeners(this.minuteInput);
      this.meridiemInput && initCursorSelectionListeners(this.meridiemInput);
      this.hourInput.addEventListener("keydown", (e) => {
        if (e.key === "ArrowRight") return this.minuteInput.focus();
      });
      this.minuteInput.addEventListener("keydown", (e) => {
        if (e.key === "ArrowLeft") return this.hourInput.focus();
        if (e.key === "ArrowRight") return this.meridiemInput?.focus();
      });
      this.meridiemInput?.addEventListener("keydown", (e) => {
        if (e.key === "ArrowLeft") return this.minuteInput.focus();
      });
      blockDefaultInputs(this.hourInput);
      blockDefaultInputs(this.minuteInput);
      this.meridiemInput && blockDefaultInputs(this.meridiemInput, { isMeridiem: true });
      this.initHourInputListeners();
      this.initMinuteInputListeners();
      this.meridiemInput && this.initMeridiemInputListeners();
      this.meridiemInput && this.setMeridiemWidthBasedOnLocale();
      this._lastFocusedInput = null;
      this.addEventListener("mousedown", (e) => {
        if (e.target.closest("input")) return;
        if (this.picker._popoverable?.state) return;
        let active = document.activeElement;
        if (active === this.hourInput || active === this.minuteInput || active === this.meridiemInput) {
          this._lastFocusedInput = active;
        } else {
          this._lastFocusedInput = null;
        }
      });
      this.picker.observable.subscribe(ObservableTrigger.SELECTION, () => {
        this.updateInputsFromSelectable(this.picker.selectable);
      });
      this.updateInputsFromSelectable(this.picker.selectable);
    }
    focus() {
      if (this.hourInput) return this.hourInput.focus();
      this.querySelector("button,ui-button")?.focus();
    }
    use24HourFormat() {
      if (this.picker.config.timeFormat === "24-hour") return true;
      if (this.picker.config.timeFormat === "12-hour") return false;
      return localeIs24HourFormat(this.picker.config.locale);
    }
    processTime() {
      if (this.hourInput.value === "" || this.minuteInput.value === "") return;
      if (this.meridiemInput && this.meridiemInput.value === "") return;
      let meridiem = null;
      if (this.meridiemInput?.value) {
        let locale = this.picker.config.locale;
        let am = getLocalisedMeridiem(0, locale);
        meridiem = this.meridiemInput.value === am ? "AM" : "PM";
      }
      let time = getTime(
        this.hourInput.value,
        this.minuteInput.value,
        meridiem
      );
      if (!time) return;
      this.updateSelectable(time);
      this.updateInputsFromSelectable(this.picker.selectable);
    }
    updateSelectable(time) {
      this.picker.selectable.setValue(time);
    }
    updateInputsFromSelectable(selectable) {
      let time = selectable.getValue();
      this.updateInputs(time);
    }
    // Update the hour and meridiem display without touching minute...
    // When forceMeridiem is true (e.g., from arrow keys), always update meridiem.
    // When false (e.g., from typing), only update if meridiem is empty or the hour forces a conversion (13+, 0).
    updateHourDisplay(hours, { forceMeridiem = false } = {}) {
      hours = parseInt(hours);
      if (isNaN(hours)) return;
      if (this.meridiemInput) {
        let hasExistingMeridiem = this.meridiemInput.value && !("showingPlaceholder" in this.meridiemInput.dataset);
        if (forceMeridiem || !hasExistingMeridiem || hours > 12 || hours === 0) {
          let meridiem = getLocalisedMeridiem(hours, this.picker.config.locale);
          this.meridiemInput.value = meridiem;
          this.meridiemInput.style.color = "";
          delete this.meridiemInput.dataset.showingPlaceholder;
        }
        if (hours > 12) {
          hours = hours - 12;
        } else if (hours === 0) {
          hours = 12;
        }
      }
      this.hourInput.value = hours.toString().padStart(2, "0");
      this.hourInput.style.color = "";
      this.hourInput.classList.remove("font-mono");
    }
    // Time is in 24 hour format
    updateInputs(time) {
      if (!time) return this.clearInputs();
      removeAttribute(this.picker, "data-empty");
      let [hours, minutes] = time.split(":");
      let meridiem = null;
      if (this.meridiemInput) {
        hours = parseInt(hours);
        meridiem = getLocalisedMeridiem(hours, this.picker.config.locale);
        if (hours > 12) {
          hours = hours - 12;
        } else if (hours === 0) {
          hours = 12;
        }
        hours = hours.toString();
      }
      this.hourInput.value = hours.padStart(2, "0");
      this.hourInput.style.color = "";
      this.hourInput.classList.remove("font-mono");
      this.minuteInput.value = minutes.padStart(2, "0");
      this.minuteInput.style.color = "";
      this.minuteInput.classList.remove("font-mono");
      if (this.meridiemInput) {
        this.meridiemInput.value = meridiem;
        this.meridiemInput.style.color = "";
        delete this.meridiemInput.dataset.showingPlaceholder;
      }
    }
    clearInputs() {
      this.hourInput.value = "";
      this.hourInput.style.color = "";
      this.hourInput.classList.add("font-mono");
      this.minuteInput.value = "";
      this.minuteInput.style.color = "";
      this.minuteInput.classList.add("font-mono");
      if (this.meridiemInput) {
        this.meridiemInput.value = "";
        this.meridiemInput.style.color = "";
      }
      let focused = [this.hourInput, this.minuteInput, this.meridiemInput].find((i) => i === document.activeElement);
      if (focused) highlightInputContents(focused);
      setAttribute2(this.picker, "data-empty", "");
    }
    initHourInputListeners() {
      let isEditing = false;
      this.hourInput.addEventListener("keydown", (e) => {
        if (e.key === "Backspace") {
          if (this.hourInput.value === "" || this.hourInput.value === this.hourInput.placeholder) return;
          isEditing = false;
          this.hourInput.value = "";
          this.hourInput.style.color = "";
          this.hourInput.classList.add("font-mono");
          highlightInputContents(this.hourInput);
          return;
        }
        if (!["ArrowUp", "ArrowDown"].includes(e.key)) return;
        e.preventDefault();
        e.stopPropagation();
        let hours = parseInt(this.hourInput.value);
        if (!isNaN(hours) && this.meridiemInput && this.meridiemInput.value && !("showingPlaceholder" in this.meridiemInput.dataset)) {
          let isPM = this.meridiemInput.value === getLocalisedMeridiem(12, this.picker.config.locale);
          if (isPM && hours !== 12) hours += 12;
          if (!isPM && hours === 12) hours = 0;
        }
        if (e.key === "ArrowUp") {
          if (isNaN(hours) || hours === 23) {
            hours = 0;
          } else {
            hours++;
          }
        } else if (e.key === "ArrowDown") {
          if (isNaN(hours) || hours === 0) {
            hours = 23;
          } else {
            hours--;
          }
        }
        this.updateHourDisplay(hours, { forceMeridiem: true });
        this.processTime();
        highlightInputContents(this.hourInput);
      });
      this.hourInput.addEventListener("keydown", (e) => {
        if (!/[0-9]/.test(e.key)) return;
        let number = parseInt(e.key);
        if (this.hourInput.value === "" || !isEditing) {
          isEditing = true;
          this.hourInput.value = `0${number}`;
          this.hourInput.style.color = "";
          this.hourInput.classList.remove("font-mono");
          number > 2 ? this.minuteInput.focus() : highlightInputContents(this.hourInput);
          return;
        }
        let existingNumber = parseInt(this.hourInput.value);
        if (existingNumber === 2 && (number >= 0 && number <= 3)) {
          this.hourInput.value = `${existingNumber}${number}`;
          this.minuteInput.focus();
          return;
        }
        if (existingNumber === 1 || existingNumber === 0) {
          this.hourInput.value = `${existingNumber}${number}`;
          this.minuteInput.focus();
          return;
        }
        this.hourInput.value = `0${number}`;
        number > 2 ? this.minuteInput.focus() : highlightInputContents(this.hourInput);
      });
      this.hourInput.addEventListener("blur", (e) => {
        isEditing = false;
        if (this.hourInput.value !== "" && this.hourInput.value !== this.hourInput.placeholder) {
          this.updateHourDisplay(this.hourInput.value);
        }
        this.processTime();
      });
    }
    initMinuteInputListeners() {
      let isEditing = false;
      this.minuteInput.addEventListener("keydown", (e) => {
        if (e.key === "Backspace") {
          if (this.minuteInput.value === "" || this.minuteInput.value === this.minuteInput.placeholder) {
            this.hourInput.focus();
            return;
          }
          isEditing = false;
          this.minuteInput.value = "";
          this.minuteInput.style.color = "";
          this.minuteInput.classList.add("font-mono");
          highlightInputContents(this.minuteInput);
          return;
        }
        if (!["ArrowUp", "ArrowDown"].includes(e.key)) return;
        e.preventDefault();
        e.stopPropagation();
        let number = parseInt(this.minuteInput.value);
        if (e.key === "ArrowUp") {
          if (isNaN(number) || number === 59) {
            number = 0;
          } else {
            number = number + 1;
          }
        } else if (e.key === "ArrowDown") {
          if (isNaN(number) || number === 0) {
            number = 59;
          } else {
            number = number - 1;
          }
        }
        this.minuteInput.value = number.toString().padStart(2, "0");
        this.minuteInput.style.color = "";
        this.minuteInput.classList.remove("font-mono");
        this.processTime();
        highlightInputContents(this.minuteInput);
      });
      this.minuteInput.addEventListener("keydown", (e) => {
        if (!/[0-9]/.test(e.key)) return;
        let number = parseInt(e.key);
        if (this.minuteInput.value === "" || !isEditing) {
          isEditing = true;
          this.minuteInput.value = `0${number}`;
          this.minuteInput.style.color = "";
          this.minuteInput.classList.remove("font-mono");
          this.processTime();
          number > 5 ? this.meridiemInput?.focus() : highlightInputContents(this.minuteInput);
          return;
        }
        let existingNumber = parseInt(this.minuteInput.value);
        if (existingNumber <= 5) {
          this.minuteInput.value = `${existingNumber}${number}`;
          this.processTime();
          this.meridiemInput?.focus();
          return;
        }
        this.minuteInput.value = `0${number}`;
        this.processTime();
        number > 5 ? this.meridiemInput?.focus() : highlightInputContents(this.minuteInput);
      });
      this.minuteInput.addEventListener("blur", (e) => {
        isEditing = false;
        this.processTime();
      });
    }
    initMeridiemInputListeners() {
      this.meridiemInput?.addEventListener("keydown", (e) => {
        if (e.key === "Backspace") {
          if (this.meridiemInput.value === "" || "showingPlaceholder" in this.meridiemInput.dataset) {
            this.minuteInput.focus();
            return;
          }
          this.meridiemInput.value = "";
          this.meridiemInput.style.color = "";
          highlightInputContents(this.meridiemInput);
          return;
        }
        if (!["ArrowUp", "ArrowDown"].includes(e.key)) return;
        e.preventDefault();
        e.stopPropagation();
        clearPlaceholderValue(this.meridiemInput);
        let locale = this.picker.config.locale;
        let am = getLocalisedMeridiem(0, locale);
        let pm = getLocalisedMeridiem(12, locale);
        let value3 = this.meridiemInput.value;
        if (e.key === "ArrowUp") {
          if (value3 === "" || value3 === pm) {
            value3 = am;
          } else {
            value3 = pm;
          }
        } else if (e.key === "ArrowDown") {
          if (value3 === "" || value3 === am) {
            value3 = pm;
          } else {
            value3 = am;
          }
        }
        this.meridiemInput.value = value3;
        this.meridiemInput.style.color = "";
        delete this.meridiemInput.dataset.showingPlaceholder;
        this.processTime();
        highlightInputContents(this.meridiemInput);
      });
      this.meridiemInput?.addEventListener("keydown", (e) => {
        if (!["a", "p", "A", "P"].includes(e.key)) return;
        clearPlaceholderValue(this.meridiemInput);
        let locale = this.picker.config.locale;
        if (e.key === "a" || e.key === "A") {
          this.meridiemInput.value = getLocalisedMeridiem(0, locale);
        } else if (e.key === "p" || e.key === "P") {
          this.meridiemInput.value = getLocalisedMeridiem(12, locale);
        }
        this.meridiemInput.style.color = "";
        delete this.meridiemInput.dataset.showingPlaceholder;
        this.processTime();
        highlightInputContents(this.meridiemInput);
      });
    }
    // Not all locales have the same width meridiem, like US is `AM`, Canada is `a.m.`, and Japan is `午後`.
    // Alphabetic scripts (Latin, Greek, etc.) use ch units since character widths are similar to `0`.
    // CJK, Hangul, and other wide scripts are measured from a rendered span instead...
    setMeridiemWidthBasedOnLocale() {
      let meridiem = getLocalisedMeridiem(12, this.picker.config.locale);
      if (!meridiem) return;
      let isAlphabetic = /^[\u0000-\u024F\u0370-\u03FF\u1E00-\u1EFF\s.]+$/.test(meridiem);
      if (isAlphabetic) {
        this.meridiemInput.style.width = `calc(${meridiem.length}ch + 2px)`;
        return;
      }
      let span = document.createElement("span");
      span.style.cssText = "position:absolute;visibility:hidden;white-space:nowrap";
      span.style.font = getComputedStyle(this.meridiemInput).font;
      span.textContent = meridiem;
      document.body.appendChild(span);
      this.meridiemInput.style.width = `${span.getBoundingClientRect().width}px`;
      span.remove();
    }
  };
  function initCursorSelectionListeners(input) {
    input.addEventListener("focus", (e) => {
      highlightInputContents(input);
    });
    input.addEventListener("blur", (e) => {
      clearPlaceholderValue(input);
    });
    input.addEventListener("mousedown", (e) => {
      e.preventDefault();
      input.focus();
      highlightInputContents(input);
    });
    input.addEventListener("mouseup", (e) => {
      e.preventDefault();
      highlightInputContents(input);
    });
  }
  function blockDefaultInputs(input, { isMeridiem = false } = {}) {
    input.addEventListener("keydown", (e) => {
      if (/[0-9]/.test(e.key) && !isMeridiem) {
        clearPlaceholderValue(input);
        input.classList.remove("font-mono");
      }
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      if (["Tab", "Backspace"].includes(e.key)) return;
      e.preventDefault();
    });
  }
  function highlightInputContents(input) {
    if (!input.value && input.placeholder) {
      input.style.color = getComputedStyle(input, "::placeholder").color;
      input.value = input.placeholder;
      input.classList.add("font-mono");
      input.dataset.showingPlaceholder = "";
    }
    input.setSelectionRange(0, input.value.length);
  }
  function clearPlaceholderValue(input) {
    if ("showingPlaceholder" in input.dataset) {
      input.value = "";
      input.style.color = "";
      delete input.dataset.showingPlaceholder;
    }
  }
  function getLocalisedMeridiem(hours, locale) {
    let formatter = new Intl.DateTimeFormat(locale, {
      hour: "numeric",
      hour12: true
    });
    let parts = formatter.formatToParts(new Date(2024, 0, 1, hours, 0));
    let medridiem = parts.find((part) => part.type === "dayPeriod")?.value;
    if (medridiem) return medridiem;
    return hours >= 12 ? "PM" : "AM";
  }
  function getTime(hour, minute, meridiem = null) {
    hour = hour.padStart(2, "0");
    minute = minute.padStart(2, "0");
    let time = meridiem ? hour + ":" + minute + " " + meridiem : hour + ":" + minute;
    return convertTimeStringTo24HourFormat(time);
  }
  function localeIs24HourFormat(locale) {
    let formatter = new Intl.DateTimeFormat(locale, { hour: "numeric" });
    let midnight = formatter.format(new Date(2024, 0, 1, 0, 0));
    return midnight.startsWith("00");
  }
  function convertTimeStringTo24HourFormat(time) {
    let match = time.trim().match(/^(\d{1,2}):(\d{2})(?:\s*(am|pm|AM|PM))?$/i);
    if (!match) throw new Error(`Invalid time string: ${time}`);
    let hours = parseInt(match[1], 10);
    let minutes = parseInt(match[2], 10);
    let meridiem = match[3]?.toUpperCase();
    if (minutes > 59 || hours > 23 || meridiem !== "AM" && meridiem !== "PM" && meridiem !== void 0) throw new Error(`Invalid time string: ${time}`);
    if (hours >= 13 || hours === 0) {
      return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;
    }
    if (meridiem) {
      if (hours === 12) {
        hours = meridiem === "AM" ? 0 : 12;
      } else if (meridiem === "PM") {
        hours += 12;
      }
    }
    return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;
  }

  // js/time-picker/index.js
  var UITimePicker = class extends UIControl {
    boot() {
      this.config = {
        timeFormat: this.hasAttribute("time-format") ? this.getAttribute("time-format") : "auto",
        interval: this.interval(),
        min: this.min(),
        max: this.max(),
        unavailable: this.unavailable(),
        openTo: this.openTo(),
        locale: this.hasAttribute("locale") ? this.getAttribute("locale") : navigator.language,
        mode: this.hasAttribute("multiple") ? SelectableModes.MULTIPLE : SelectableModes.SINGLE,
        dropdown: this.hasAttribute("dropdown") && this.getAttribute("dropdown") === "false" ? false : true
      };
      this.observable = new Observable();
      this._controllable = new Controllable(this, { bubbles: true });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.getAttribute("value"),
        includeWhenEmpty: false
      });
      this.selectable = Selectable2.createFromValueStringAttribute(this.getAttribute("value"), this.config, this.observable);
      this._controllable.getter(() => this.selectable.getValue());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this.selectable.setValue(value3);
      }));
      this.observable.subscribe(ObservableTrigger.SELECTION, detangled(() => {
        this._controllable.dispatch();
        this.dispatchEvent(new CustomEvent("input", { bubbles: false }));
      }));
      this.observable.subscribe(ObservableTrigger.SELECTION, () => {
        this._submittable.update(this.selectable.getValue());
      });
      this._disableable = new Disableable(this);
      if (!this.config.dropdown) return;
      let popoverEl = this.querySelector("[popover]");
      if (!popoverEl) return;
      let triggerEl = this.trigger();
      let optionsEl = this.querySelector("ui-time-picker-options");
      let optionsId = initOptions(optionsEl, this.config.mode === SelectableModes.MULTIPLE);
      this._popoverable = new Popoverable(popoverEl, {
        triggers: [triggerEl]
      });
      this._anchorable = new Anchorable(popoverEl, {
        reference: triggerEl,
        position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
        gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
        offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0,
        matchWidth: true
      });
      this._activatable = new ActivatableGroup(optionsEl, { filter: "data-hidden" });
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(triggerEl, "disabled", "");
          triggerEl.querySelectorAll("button,ui-button").forEach((button) => setAttribute2(button, "disabled", ""));
        } else {
          removeAttribute(triggerEl, "disabled");
          triggerEl.querySelectorAll("button,ui-button").forEach((button) => removeAttribute(button, "disabled"));
        }
      });
      triggerEl.addEventListener("click", (e) => {
        this._popoverable.toggle();
      });
      this._popoverable.onChange((isOpen) => {
        if (!isOpen && triggerEl._lastFocusedInput) {
          let input = triggerEl._lastFocusedInput;
          triggerEl._lastFocusedInput = null;
          input.focus();
        }
      });
      triggerEl.querySelectorAll("button,ui-button").forEach((button) => {
        initTriggerButton(button, this._popoverable, optionsId);
        trackActiveDescendant2(button, this._activatable, this.observable);
      });
      initPopover2(this, popoverEl, this._popoverable, this._anchorable);
      preventScrollWhenPopoverIsOpen2(this, this._popoverable);
      controlPopoverWithKeyboard(triggerEl, this._popoverable);
      controlPopoverWithEscapeKey(this, this._popoverable);
      handleKeyboardNavigation(triggerEl, this._activatable);
      handleKeyboardSearchNavigation(triggerEl, this._activatable, this._popoverable);
      handleKeyboardSelection(this, triggerEl, this._activatable);
      controlActivationWithPopover2(this._popoverable, this._activatable, this.selectable, this.config.openTo);
      handlePopoverClosing2(this, this.config.mode, this.observable, this.selectable, this._popoverable);
      let observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (!["interval", "unavailable", "max", "min", "open-to"].includes(mutation.attributeName)) return;
          this.config.interval = this.interval();
          this.config.unavailable = this.unavailable();
          this.config.min = this.min();
          this.config.max = this.max();
          this.config.openTo = this.openTo();
          this.observable.notify(ObservableTrigger.VIEW_CHANGE);
        });
      });
      observer.observe(this, { attributes: true });
    }
    trigger() {
      return this.querySelector("ui-time-picker-trigger");
    }
    list() {
      return this.querySelector("ui-options") || this;
    }
    clear() {
      this.selectable.setValue(null);
      this.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
    }
    interval() {
      if (this.hasAttribute("interval")) {
        return parseInt(this.getAttribute("interval"));
      }
      return 30;
    }
    min() {
      if (this.hasAttribute("min")) {
        let min2 = this.getAttribute("min");
        if (min2 === "now") {
          return (/* @__PURE__ */ new Date()).toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit", hour12: false });
        }
        if (validateTimeString(min2)) {
          return min2;
        }
        return console.warn(`Invalid min attribute: ${min2}`);
      }
      return void 0;
    }
    max() {
      if (this.hasAttribute("max")) {
        let max2 = this.getAttribute("max");
        if (max2 === "now") {
          return (/* @__PURE__ */ new Date()).toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit", hour12: false });
        }
        if (validateTimeString(max2)) {
          return max2;
        }
        return console.warn(`Invalid max attribute: ${max2}`);
      }
      return void 0;
    }
    unavailable() {
      if (this.hasAttribute("unavailable")) {
        let unavailable = this.getAttribute("unavailable").split(",").map((i) => i.trim());
        if (unavailable.every((i) => i.includes("-") ? validateTimeRangeString(i) : validateTimeString(i))) {
          return unavailable;
        }
        return console.warn(`Invalid unavailable attribute: ${unavailable}`);
      }
      return [];
    }
    openTo() {
      if (this.hasAttribute("open-to")) {
        let openTo = this.getAttribute("open-to");
        if (openTo === "now") {
          return (/* @__PURE__ */ new Date()).toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit", hour12: false });
        }
        if (validateTimeString(openTo)) {
          return openTo;
        }
        return console.warn(`Invalid open-to attribute: ${openTo}`);
      }
      return void 0;
    }
  };
  var UISelectedTime = class extends UIElement {
    boot() {
      this.picker = this.closest("ui-time-picker");
      if (!this.querySelector('template[name="time"]')) {
        let template = document.createElement("template");
        template.setAttribute("name", "time");
        template.innerHTML = "<div><slot></slot></div>";
      }
      this.templates = {
        placeholder: this.querySelector('template[name="placeholder"]'),
        time: this.querySelector('template[name="time"]')
      };
      this.picker.observable.subscribe(ObservableTrigger.SELECTION, () => {
        this.render(this.picker);
      });
      this.render(this.picker);
    }
    mount() {
      this.render(this.picker);
    }
    render(picker) {
      this.templates.placeholder?.clearPlaceholder?.();
      this.templates.time?.clearTime?.();
      if (picker.selectable.hasSelection()) {
        let { cleanup } = renderTemplate(this.templates.time, (hydrate) => {
          return hydrate({ slots: { default: picker.selectable.display(picker.config.locale) } });
        });
        this.templates.time.clearTime = cleanup;
      } else {
        if (!this.templates.placeholder) return;
        let { cleanup } = renderTemplate(this.templates.placeholder, (hydrate) => {
          return hydrate({ slots: {} });
        });
        this.templates.placeholder.clearPlaceholder = cleanup;
      }
    }
  };
  var UITimePickerOptions = class extends UIElement {
    boot() {
      this.picker = this.closest("ui-time-picker");
      this.render();
      this.picker.observable.subscribe(ObservableTrigger.SELECTION, () => {
        this.render();
      });
      this.picker.observable.subscribe(ObservableTrigger.VIEW_CHANGE, () => {
        this.render();
      });
      let { enable: enableListeners, disable: disableListeners } = initializeEventListeners(this, this.picker.selectable);
      this.picker._disableable.onInitAndChange((disabled) => {
        disabled ? disableListeners() : enableListeners();
      });
    }
    render() {
      let template = this.querySelector('template[name="option"]');
      if (!template) return;
      renderTemplate(template, (hydrate) => {
        let times = generateTimes(this.picker.config);
        return times.map(({ value: value3, label }) => {
          let isDisabled = this.picker.config.unavailable.some((unavailableTime) => timesAreOverlapping(value3, unavailableTime));
          let attrs = {
            "data-time": value3,
            "role": "option"
          };
          if (isDisabled) attrs.disabled = "";
          let el = hydrate({ attrs, slots: { default: label } });
          this.picker.selectable.attributes(el, value3);
          el._activatable = new Activatable(el);
          assignId(el, "option");
          return el;
        });
      });
    }
  };
  element("time-picker", UITimePicker);
  element("time-picker-trigger", UITimePickerTrigger);
  element("selected-time", UISelectedTime);
  element("time-picker-options", UITimePickerOptions);
  function initOptions(options, multiple) {
    let optionsId = assignId(options, "options");
    setAttribute2(options, "role", "listbox");
    setAttribute2(options, "aria-multiselectable", multiple ? "true" : "false");
    return optionsId;
  }
  function initTriggerButton(el, popoverable, optionsId) {
    setAttribute2(el, "role", "combobox");
    setAttribute2(el, "aria-controls", optionsId);
    setAttribute2(el, "aria-haspopup", "listbox");
    linkExpandedStateToPopover2(el, popoverable);
    on(el, "click", (e) => {
      e.stopPropagation();
      popoverable.setState(!popoverable.getState());
      el.focus();
    });
  }
  function initPopover2(root, popover, popoverable, anchorable) {
    let refreshPopover = () => {
      Array.from([root, popover]).forEach((i) => {
        popoverable.getState() ? setAttribute2(i, "data-open", "") : removeAttribute(i, "data-open", "");
      });
      popoverable.getState() ? anchorable.reposition() : anchorable.cleanup();
    };
    popoverable.onChange(() => refreshPopover());
    refreshPopover();
    popoverable.onChange(() => {
      if (popoverable.getState()) {
        root.dispatchEvent(new Event("open", {
          bubbles: false,
          cancelable: false
        }));
      } else {
        root.dispatchEvent(new Event("close", {
          bubbles: false,
          cancelable: false
        }));
      }
    });
  }
  function preventScrollWhenPopoverIsOpen2(root, popoverable) {
    let { lock, unlock } = lockScroll(popoverable.el);
    popoverable.onChange(() => {
      popoverable.getState() ? lock() : unlock();
    });
  }
  function linkExpandedStateToPopover2(el, popoverable) {
    setAttribute2(el, "aria-haspopup", "listbox");
    let refreshPopover = () => {
      setAttribute2(el, "aria-expanded", popoverable.getState() ? "true" : "false");
      popoverable.getState() ? setAttribute2(el, "data-open", "") : removeAttribute(el, "data-open", "");
    };
    popoverable.onChange(() => {
      refreshPopover();
    });
    refreshPopover();
  }
  function trackActiveDescendant2(el, activatable) {
    activatable.onChange(() => {
      let activeEl = activatable.getActive();
      activeEl ? setAttribute2(el, "aria-activedescendant", activeEl.id) : removeAttribute(el, "aria-activedescendant");
    });
  }
  function handlePopoverClosing2(root, mode, observable, selectable, popoverable) {
    if (mode === SelectableModes.MULTIPLE) return;
    observable.subscribe(ObservableTrigger.SELECTION, () => {
      if (selectable.hasSelection()) {
        popoverable.setState(false);
      }
    });
  }
  function initializeEventListeners(el, selectable) {
    let blocked = false;
    let readonly = el.hasAttribute("readonly");
    on(el, "click", (e) => {
      if (blocked || readonly) return;
      let option = e.target.closest("[data-time]");
      if (!option) return;
      let time = option.getAttribute("data-time");
      setTimeout(() => {
        validateTimeString(time) && selectable.select(time);
      });
    });
    return {
      enable: () => {
      },
      disable: () => {
      }
    };
  }
  function validateTimeString(time) {
    return new RegExp(`^${timeRegex()}$`).test(time);
  }
  function validateTimeRangeString(time) {
    return new RegExp(`^${timeRegex()}-${timeRegex()}$`).test(time);
  }
  function timeRegex() {
    return "([01]?[0-9]|2[0-3]):[0-5][0-9]";
  }
  function timesAreOverlapping(first, second) {
    let [firstStart, firstEnd] = first.split("-").map((t) => {
      let [hours, minutes] = t.split(":").map(Number);
      return hours * 60 + minutes;
    });
    let [secondStart, secondEnd] = second.split("-").map((t) => {
      let [hours, minutes] = t.split(":").map(Number);
      return hours * 60 + minutes;
    });
    if (isNaN(firstEnd)) firstEnd = firstStart;
    if (isNaN(secondEnd)) secondEnd = secondStart;
    return !(firstEnd < secondStart || firstStart > secondEnd);
  }
  function formatTime(time, timeFormat, locale) {
    let [hours, minutes] = time.split(":").map(Number);
    let hour12Map = {
      "12-hour": true,
      "24-hour": false,
      "auto": void 0
    };
    let formatter = new Intl.DateTimeFormat(locale, {
      hour: "numeric",
      minute: "numeric",
      hour12: hour12Map[timeFormat]
    });
    return formatter.format(new Date(2024, 0, 1, hours, minutes));
  }
  function timeInMinutes(time) {
    if (!time) return null;
    let [hours, minutes] = time.split(":");
    if (isNaN(hours) || isNaN(minutes)) return null;
    return parseInt(hours) * 60 + parseInt(minutes);
  }
  function controlActivationWithPopover2(popoverable, activatable, selectable, openTo = null) {
    popoverable.onChange(() => {
      if (popoverable.getState()) {
        let selectedValues = selectable.getValue();
        if (!Array.isArray(selectedValues)) {
          selectedValues = selectedValues === null ? [] : [selectedValues];
        }
        let selectables = activatable.filterAwareWalker();
        let firstSelectedOption = selectables.find((el) => selectedValues.includes(el.getAttribute("data-time")));
        let firstOptionToActivate = null;
        if (!firstSelectedOption && openTo) {
          firstOptionToActivate = findCloestOptionToOpenTo(activatable, openTo);
        }
        setTimeout(() => {
          let optionToScrollTo = firstSelectedOption || firstOptionToActivate;
          activatable.activateSelectedOrFirst(optionToScrollTo);
          optionToScrollTo?.scrollIntoView({ block: "nearest" });
        });
      } else {
        activatable.clearActive();
      }
    });
  }
  function findCloestOptionToOpenTo(activatable, openTo) {
    let openToInMinutes = timeInMinutes(openTo);
    let closestOption = null;
    let closestOptionTimeInMinutes = null;
    activatable.filterAwareWalker().walk((option, bail) => {
      let optionTime = option.getAttribute("data-time");
      let optionTimeInMinutes = timeInMinutes(optionTime);
      if (optionTimeInMinutes > openToInMinutes) return;
      if (Math.abs(optionTimeInMinutes - openToInMinutes) < Math.abs(closestOptionTimeInMinutes - openToInMinutes)) {
        closestOption = option;
        closestOptionTimeInMinutes = optionTimeInMinutes;
      }
    });
    return closestOption;
  }
  function generateTimes({ interval = 30, timeFormat = "auto", locale = navigator.language, min: min2, max: max2 } = {}) {
    let times = [];
    let minInMinutes = 0;
    let maxInMinutes = 24 * 60 - 1;
    if (min2) {
      let minDate = new Date(2024, 0, 1);
      let [minHours, minMinutes] = min2.split(":");
      minDate.setHours(minHours, minMinutes);
      minInMinutes = minDate.getHours() * 60 + minDate.getMinutes();
    }
    if (max2) {
      let maxDate = new Date(2024, 0, 1, 23, 59, 59);
      let [maxHours, maxMinutes] = max2.split(":");
      maxDate.setHours(maxHours, maxMinutes);
      maxInMinutes = maxDate.getHours() * 60 + maxDate.getMinutes();
    }
    for (let minutes = minInMinutes; minutes <= maxInMinutes; minutes += interval) {
      let hours = Math.floor(minutes / 60);
      let mins = minutes % 60;
      let value3 = `${String(hours).padStart(2, "0")}:${String(mins).padStart(2, "0")}`;
      let label = formatTime(value3, timeFormat, locale);
      times.push({ value: value3, label });
    }
    return times;
  }

  // js/mixins/disclosable.js
  var Disclosable = class extends Mixin {
    boot({ options }) {
      this.onChanges = [];
      this.state = false;
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    getState() {
      return this.state;
    }
    setState(value3) {
      let oldState = this.state;
      this.state = !!value3;
      if (this.state !== oldState) {
        this.onChanges.forEach((i) => i());
      }
    }
  };

  // js/disclosure.js
  var UIDisclosure = class extends UIElement {
    boot() {
      let button = this.button();
      let details = this.details();
      if (!button) {
        return console.warn("ui-disclosure: no trigger element found", this);
      } else if (!details) {
        return console.warn("ui-disclosure: no panel element found", this);
      }
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          if (!button.hasAttribute("disabled")) {
            setAttribute2(button, "disabled", "");
          }
        } else {
          if (button.hasAttribute("disabled")) {
            removeAttribute(button, "disabled");
          }
        }
      });
      this._controllable = new Controllable(this, { disabled: this.disabled });
      details._disclosable = new Disclosable(details);
      this._controllable.initial((initial) => initial && details._disclosable.setState(true));
      this._controllable.getter(() => details._disclosable.getState());
      this._controllable.setter((value3) => details._disclosable.setState(value3));
      details._disclosable.onChange(() => {
        this.dispatchEvent(new CustomEvent("lofi-disclosable-change", { bubbles: true }));
        this._controllable.dispatch();
      });
      let refresh = () => {
        if (details._disclosable.getState()) {
          setAttribute2(this, "data-open", "");
          setAttribute2(button, "data-open", "");
          setAttribute2(details, "data-open", "");
        } else {
          removeAttribute(this, "data-open");
          removeAttribute(button, "data-open");
          removeAttribute(details, "data-open");
        }
      };
      details._disclosable.onChange(() => refresh());
      refresh();
      on(button, "click", (e) => {
        if (!this.disabled) {
          details._disclosable.setState(!details._disclosable.getState());
        }
      });
      let id = assignId(details, "disclosure");
      setAttribute2(button, "aria-controls", id);
      setAttribute2(button, "aria-expanded", "false");
      details._disclosable.onChange(() => {
        details._disclosable.getState() ? setAttribute2(button, "aria-expanded", "true") : setAttribute2(button, "aria-expanded", "false");
      });
      if (this.hasAttribute("open")) {
        details._disclosable.setState(true);
      }
    }
    button() {
      return this.querySelector("button,ui-button");
    }
    details() {
      return this.lastElementChild;
    }
  };
  var UIDisclosureGroup = class _UIDisclosureGroup extends UIElement {
    boot() {
      this.exclusive = this.hasAttribute("exclusive");
      if (this.exclusive) {
        on(this, "lofi-disclosable-change", (e) => {
          e.stopPropagation();
          if (e.target.localName === "ui-disclosure" && e.target.value) {
            this.disclosureWalker().each((el) => {
              if (el === e.target) return;
              el.value = false;
            });
          }
        });
      }
    }
    disclosureWalker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UIDisclosureGroup && el !== this) return reject();
        if (el.localName !== "ui-disclosure") return reject();
      });
    }
  };
  element("disclosure", UIDisclosure);
  element("disclosure-group", UIDisclosureGroup);

  // js/resizable.js
  var UIResizable = class extends UIElement {
    boot() {
    }
  };
  var UIGrip = class extends UIElement {
    boot() {
      let dimension = this.hasAttribute("resize") ? this.getAttribute("resize") : "both";
      let shrink = this.hasAttribute("shrink");
      let container = this.closest("ui-resizable");
      let maxWidth, maxHeight;
      let hasAttemptedAResizeYet = false;
      if (!container) throw "Resizable container not found";
      this.addEventListener("pointerdown", (e) => {
        if (!hasAttemptedAResizeYet) {
          maxWidth = container.offsetWidth;
          maxHeight = container.offsetHeight;
          hasAttemptedAResizeYet = true;
        }
        let startX = e.clientX;
        let startY = e.clientY;
        let startWidth = parseInt(getComputedStyle(container).width, 10);
        let startHeight = parseInt(getComputedStyle(container).height, 10);
        let resize = (e2) => {
          let width = startWidth + (e2.clientX - startX);
          let height = startHeight + (e2.clientY - startY);
          if (dimension === "width" || dimension === "both") {
            if (shrink) {
              container.style.width = `${Math.min(width, maxWidth)}px`;
            } else {
              container.style.width = `${width}px`;
            }
          }
          if (dimension === "height" || dimension === "both") {
            if (shrink) {
              container.style.height = `${Math.min(height, maxHeight)}px`;
            } else {
              container.style.height = `${height}px`;
            }
          }
        };
        document.addEventListener("pointermove", resize);
        document.addEventListener("pointerup", () => {
          this.releasePointerCapture(e.pointerId);
          document.removeEventListener("pointermove", resize);
        }, { once: true });
        this.setPointerCapture(e.pointerId);
      });
    }
  };
  element("resizable", UIResizable);
  element("grip", UIGrip);

  // js/checkbox.js
  var UICheckboxGroup = class _UICheckboxGroup extends UIControl {
    boot() {
      this._disableable = new Disableable(this);
      let undoDisableds = [];
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.walker().each((el) => {
            if (!el.hasAttribute("disabled")) {
              el.setAttribute("disabled", "");
              undoDisableds.push(() => el.removeAttribute("disabled"));
            }
          });
        } else {
          undoDisableds.forEach((fn) => fn());
          undoDisableds = [];
        }
      });
      this._selectable = new SelectableGroup(this, { multiple: true });
      this._controllable = new Controllable(this, { disabled: this._disabled, bubbles: true });
      this.walker().each((el) => {
        el.addEventListener("input", (e) => e.stopPropagation());
        el.addEventListener("change", (e) => e.stopPropagation());
      });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.getAttribute("value"),
        includeWhenEmpty: false
      });
      this._controllable.initial((initial) => initial && this._selectable.setState(initial));
      this._controllable.getter(() => this._selectable.getState());
      this._detangled = detangle();
      this._controllable.setter(this._detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(this._detangled(() => {
        this._controllable.dispatch();
      }));
      this._selectable.onInitAndChange(() => {
        this._submittable.update(this._selectable.getState());
      });
      setAttribute2(this, "role", "group");
      queueMicrotask(() => {
        this._submittable.update(this._selectable.getState());
      });
    }
    initCheckAll(checkAll) {
      let detangled = detangle();
      checkAll._selectable.onChange(detangled(() => {
        if (checkAll.indeterminate) {
          this.selectAll();
          checkAll.checked = true;
          checkAll.indeterminate = false;
        } else if (checkAll.checked) {
          this.selectAll();
          checkAll.checked = true;
          checkAll.indeterminate = false;
        } else {
          this.deselectAll();
          checkAll.checked = false;
          checkAll.indeterminate = false;
        }
      }));
      let setCheckAllIndeterminate = () => {
        if (this.allAreSelected()) {
          checkAll.indeterminate = false;
          checkAll._selectable.select();
        } else if (this.noneAreSelected()) {
          checkAll.indeterminate = false;
          checkAll._selectable.deselect();
        } else {
          checkAll.indeterminate = true;
        }
      };
      this._selectable.onChange(detangled(() => {
        setCheckAllIndeterminate();
      }));
      setCheckAllIndeterminate();
    }
    selectAll() {
      this.walker({ checkable: true }).filter((el) => !el.use(Selectable).isSelected()).map((el) => el.use(Selectable).select());
    }
    deselectAll() {
      this.walker({ checkable: true }).filter((el) => el.use(Selectable).isSelected()).map((el) => el.use(Selectable).deselect());
    }
    allAreSelected() {
      let selectableElements = this.walker({ checkable: true }).filter((el) => true);
      return selectableElements.length > 0 && this.walker({ checkable: true }).filter((el) => el.use(Selectable).isSelected()).length === selectableElements.length;
    }
    noneAreSelected() {
      return this.walker({ checkable: true }).filter((el) => el.use(Selectable).isSelected()).length === 0;
    }
    walker({ checkable } = {}) {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UICheckboxGroup) return reject();
        if (!(el.localName === "ui-checkbox")) return skip();
        if (checkable && (el.disabled || el.use(Selectable).ungrouped)) return skip();
      });
    }
  };
  var UICheckbox = class extends UIControl {
    boot() {
      let button = this;
      this.isIndeterminate = false;
      this._disableable = new Disableable(this);
      if (this.hasAttribute("all")) {
        this._selectable = new Selectable(button, {
          ungrouped: true,
          toggleable: true,
          value: this.hasAttribute("value") ? this.getAttribute("value") : Math.random().toString(36).substring(2, 10),
          label: this.hasAttribute("label") ? this.getAttribute("label") : null,
          selectedInitially: this.hasAttribute("checked"),
          dataAttr: "data-checked",
          ariaAttr: "aria-checked"
        });
        queueMicrotask(() => {
          this.closest("ui-checkbox-group")?.initCheckAll(this);
        });
      } else {
        this._selectable = new Selectable(button, {
          toggleable: true,
          dataAttr: "data-checked",
          ariaAttr: "aria-checked",
          value: this.hasAttribute("value") ? this.getAttribute("value") : Math.random().toString(36).substring(2, 10),
          label: this.hasAttribute("label") ? this.getAttribute("label") : null,
          selectedInitially: this.hasAttribute("checked")
        });
        this._submittable = new Submittable(this, {
          name: this.getAttribute("name"),
          value: this.getAttribute("value") ?? "on",
          // Default value for checkboxes...
          includeWhenEmpty: false,
          shouldUpdateValue: false
        });
        this._selectable.onChange(() => {
          if (this.indeterminate) this.indeterminate = false;
        });
        this._selectable.onInitAndChange(() => {
          this._submittable.update(this._selectable.isSelected());
        });
        queueMicrotask(() => {
          this._submittable.update(this._selectable.isSelected());
        });
      }
      this._detangled = detangle();
      this._selectable.onChange(this._detangled(() => {
        this.dispatchEvent(new Event("input", { bubbles: true, cancelable: true }));
        this.dispatchEvent(new Event("change", { bubbles: true, cancelable: true }));
      }));
      setAttribute2(button, "role", "checkbox");
      this._disableable.onInitAndChange((disabled) => {
        disabled ? removeAttribute(button, "tabindex", "0") : setAttribute2(button, "tabindex", "0");
      });
      on(button, "click", this._disableable.disabled((e) => {
        e.preventDefault();
        e.stopPropagation();
      }), { capture: true });
      on(button, "click", this._disableable.enabled((e) => {
        this._selectable.press();
      }));
      on(button, "keydown", this._disableable.enabled((e) => {
        if (e.key === "Enter") {
          this.closest("form")?.requestSubmit();
        }
      }));
      on(button, "keydown", this._disableable.enabled((e) => {
        if (e.key === " ") {
          e.preventDefault();
          e.stopPropagation();
        }
      }));
      on(button, "keyup", this._disableable.enabled((e) => {
        if (e.key === " ") {
          this._selectable.press();
          e.preventDefault();
          e.stopPropagation();
        }
      }));
      respondToLabelClick(button);
    }
    get value() {
      return this._selectable?.getValue();
    }
    set value(val) {
      if (!this._selectable) return;
      this._selectable.value = val + "";
      if (this._submittable) {
        this._submittable.value = val + "";
      }
    }
    get checked() {
      return this._selectable.isSelected();
    }
    set checked(value3) {
      let groupDetangled = this.closest("ui-checkbox-group")?._detangled || ((i) => i);
      this._detangled(groupDetangled(() => {
        value3 ? this._selectable.select() : this._selectable.deselect();
      }))();
    }
    get indeterminate() {
      return this.isIndeterminate;
    }
    set indeterminate(value3) {
      this.isIndeterminate = !!value3;
      if (this.isIndeterminate) {
        setAttribute2(this, "data-indeterminate", "");
      } else {
        removeAttribute(this, "data-indeterminate");
      }
    }
  };
  element("checkbox-group", UICheckboxGroup);
  element("checkbox", UICheckbox);
  function respondToLabelClick(el) {
    el.closest("label")?.addEventListener("click", (e) => {
      if (!el.contains(e.target)) {
        el._selectable.press();
      }
    });
  }

  // js/dropdown.js
  var UIDropdown = class extends UIElement {
    boot() {
      let trigger = this.trigger();
      let overlay = this.overlay();
      if (!trigger) {
        return console.warn("ui-dropdown: no trigger element found", this);
      } else if (!overlay) {
        return console.warn("ui-dropdown: no [popover] overlay found", this);
      }
      this._disabled = this.hasAttribute("disabled");
      this._controllable = new Controllable(this);
      overlay._popoverable = new Popoverable(overlay);
      overlay._anchorable = new Anchorable(overlay, {
        reference: trigger,
        position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
        gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
        offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
      });
      overlay._popoverable.onChange(() => {
        overlay._popoverable.getState() ? overlay._anchorable.reposition() : overlay._anchorable.cleanup();
      });
      if (!this.hasAttribute("hover")) {
        let { lock, unlock } = lockScroll(overlay._popoverable.el);
        overlay._popoverable.onChange(() => {
          overlay._popoverable.getState() ? lock() : unlock();
        });
      }
      this._controllable.initial((initial) => overlay._popoverable.setState(initial));
      this._controllable.getter(() => overlay._popoverable.getState());
      let detangled = detangle();
      this._controllable.setter((value3) => overlay._popoverable.setState(value3));
      overlay._popoverable.onChange(detangled(() => this._controllable.dispatch()));
      if (this.hasAttribute("hover")) {
        let off = () => {
        };
        interest(trigger, overlay, {
          gain() {
            overlay._popoverable.setState(true);
            let listener = on(document, "scroll", () => {
              if (overlay._popoverable.getState()) {
                overlay._popoverable.setState(false);
                off();
              }
            });
            off = listener.off;
          },
          lose() {
            overlay._popoverable.setState(false);
            off();
          },
          focusable: false
        });
      }
      on(trigger, "click", () => overlay._popoverable.toggle());
      if (overlay._popoverable.getState()) {
        setAttribute2(this, "data-open", "");
        setAttribute2(trigger, "data-open", "");
        setAttribute2(overlay, "data-open", "");
      } else {
        removeAttribute(this, "data-open");
        removeAttribute(trigger, "data-open");
        removeAttribute(overlay, "data-open");
      }
      overlay._popoverable.onChange(() => {
        if (overlay._popoverable.getState()) {
          setAttribute2(this, "data-open", "");
          setAttribute2(trigger, "data-open", "");
          setAttribute2(overlay, "data-open", "");
        } else {
          removeAttribute(this, "data-open");
          removeAttribute(trigger, "data-open");
          removeAttribute(overlay, "data-open");
        }
      });
      let id = assignId(overlay, "dropdown");
      setAttribute2(trigger, "aria-haspopup", "true");
      setAttribute2(trigger, "aria-controls", id);
      setAttribute2(trigger, "aria-expanded", overlay._popoverable.getState() ? "true" : "false");
      overlay._popoverable.onChange(() => {
        setAttribute2(trigger, "aria-expanded", overlay._popoverable.getState() ? "true" : "false");
      });
      overlay._popoverable.onChange(() => {
        setTimeout(
          () => overlay._popoverable.getState() ? overlay.onPopoverShow?.() : overlay.onPopoverHide?.()
        );
      });
    }
    unmount() {
      if (this.overlay()?._popoverable?.getState() && !this.hasAttribute("hover")) {
        let { unlock } = lockScroll();
        unlock();
      }
    }
    trigger() {
      return this.querySelector("button,ui-button,a");
    }
    overlay() {
      return this.lastElementChild?.matches("[popover]") && this.lastElementChild;
    }
  };
  element("dropdown", UIDropdown);

  // js/file-upload.js
  var UIFileUpload = class extends UIElement {
    boot() {
      this.type = "file";
    }
    mount() {
      this._disableable = new Disableable(this);
      this._files = [];
      this.inputEl = this.querySelector('input[data-slot="receiver"]');
      if (this.inputEl) {
        this.forwardAcceptAttribute();
        this.configureSRInput();
      }
      this.triggerEl = this.findTriggerElement();
      this.rootEl = this.closest("ui-file-upload");
      let hasMultiple = this.hasAttribute("multiple") || this.rootEl && this.rootEl.hasAttribute("multiple");
      if (hasMultiple) {
        setAttribute2(this.inputEl, "multiple", "");
      }
      Object.defineProperty(this, "multiple", {
        get() {
          return hasMultiple;
        }
      });
      Object.defineProperty(this, "files", {
        get() {
          return this._files;
        },
        set(newValue) {
          if (newValue === null || newValue === "") {
            this._files = [];
          } else if (Array.isArray(newValue)) {
            this._files = newValue;
          } else if (newValue instanceof FileList) {
            this._files = Array.from(newValue);
          } else {
            this._files = [newValue];
          }
          this.dispatchEvent(new Event("change", { bubbles: true }));
        }
      });
      this.observer = new MutationObserver(() => {
        this.ensureInputsExist();
      });
      this.observer.observe(this, { childList: true });
      this.style.setProperty("--flux-file-upload-progress", "0%");
      this.style.setProperty("--flux-file-upload-progress-as-string", "'0%'");
      on(this, "livewire-upload-start", () => {
        setAttribute2(this, "data-loading", "");
        this.disabled = true;
        this.style.setProperty("--flux-file-upload-progress", "0");
        this.style.setProperty("--flux-file-upload-progress-as-string", "'0%'");
      });
      on(this, "livewire-upload-finish", () => {
        removeAttribute(this, "data-loading");
        this.disabled = false;
        this.style.setProperty("--flux-file-upload-progress", "100%");
        this.style.setProperty("--flux-file-upload-progress-as-string", "'100%'");
      });
      on(this, "livewire-upload-cancel", () => {
        removeAttribute(this, "data-loading");
        this.disabled = false;
        this.style.setProperty("--flux-file-upload-progress", "0%");
        this.style.setProperty("--flux-file-upload-progress-as-string", "'0%'");
      });
      on(this, "livewire-upload-error", () => {
        removeAttribute(this, "data-loading");
        this.disabled = false;
        this.style.setProperty("--flux-file-upload-progress", "0%");
        this.style.setProperty("--flux-file-upload-progress-as-string", "'0%'");
      });
      on(this, "livewire-upload-progress", (e) => {
        let percentage = e.detail.progress;
        this.style.setProperty("--flux-file-upload-progress", `${percentage}%`);
        this.style.setProperty("--flux-file-upload-progress-as-string", `'${percentage}%'`);
      });
      this._disableable.onInitAndChange((disabled) => {
        this.inputEl.disabled = disabled;
        if (this.triggerEl) {
          if (disabled) {
            removeAttribute(this.triggerEl, "tabindex");
          } else {
            setAttribute2(this.triggerEl, "tabindex", "0");
          }
        }
      });
      on(this.inputEl, "click", (e) => {
        e.stopPropagation();
        this.clear();
      });
      this.initDragListeners();
      this.initClickListeners();
    }
    addFiles(newFiles) {
      let files = Array.from(newFiles);
      let hasMultiple = this.multiple;
      if (hasMultiple) {
        this._files = [...this._files, ...files];
      } else {
        this._files = files.slice(0, 1);
      }
      this.dispatchEvent(new Event("change", { bubbles: true }));
    }
    removeFile(index) {
      this._files = this._files.filter((_, i) => i !== index);
      this.dispatchEvent(new Event("change", { bubbles: true }));
    }
    clear() {
      this.inputEl.value = null;
      this._files = [];
    }
    forwardAcceptAttribute() {
      if (this.hasAttribute("accept")) {
        this.inputEl.setAttribute("accept", this.getAttribute("accept"));
      }
    }
    configureSRInput() {
      this.inputEl.tabIndex = -1;
      Object.assign(this.inputEl.style, {
        position: "absolute",
        width: "1px",
        height: "1px",
        padding: "0",
        margin: "-1px",
        overflow: "hidden",
        clip: "rect(0, 0, 0, 0)",
        whiteSpace: "nowrap",
        border: "0"
      });
    }
    findTriggerElement() {
      let children = Array.from(this.children);
      return children.find((child) => !child.hasAttribute("data-slot"));
    }
    ensureInputsExist() {
      if (!this.inputEl || !this.querySelector('input[data-slot="receiver"]')) {
        this.inputEl = this.querySelector('input[data-slot="receiver"]');
        if (this.inputEl) {
          this.configureSRInput();
          let hasMultiple = this.hasAttribute("multiple") || this.rootEl && this.rootEl.hasAttribute("multiple");
          if (hasMultiple) {
            setAttribute2(this.inputEl, "multiple", "");
          }
          this.inputEl.disabled = this.disabled;
        }
      }
      if (!this.triggerEl || !this.contains(this.triggerEl)) {
        this.triggerEl = this.findTriggerElement();
        if (this.triggerEl && !this.disabled) {
          setAttribute2(this.triggerEl, "tabindex", "0");
        } else if (this.triggerEl && this.disabled) {
          removeAttribute(this.triggerEl, "tabindex");
        }
      }
    }
    initDragListeners() {
      if (!this.triggerEl) return;
      on(this.triggerEl, "dragenter", this._disableable.enabled((e) => {
        e.preventDefault();
        setAttribute2(this, "data-dragging", "");
        this.clear();
      }));
      on(this.triggerEl, "dragover", this._disableable.enabled((e) => {
        e.preventDefault();
        setAttribute2(this, "data-dragging", "");
      }));
      on(this.triggerEl, "drop", this._disableable.enabled((e) => {
        e.preventDefault();
        removeAttribute(this, "data-dragging");
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
          this.addFiles(e.dataTransfer.files);
        }
      }));
      on(this.triggerEl, "dragleave", (e) => {
        removeAttribute(this, "data-dragging");
      });
    }
    initClickListeners() {
      if (!this.triggerEl) return;
      on(this.triggerEl, "click", this._disableable.disabled((e) => {
        e.preventDefault();
        e.stopPropagation();
      }), { capture: true });
      on(this.triggerEl, "click", this._disableable.enabled((e) => {
        e.preventDefault();
        this.inputEl.click();
      }));
      on(this.inputEl, "change", (e) => {
        e.stopPropagation();
        if (this.inputEl.files && this.inputEl.files.length > 0 && !this.disabled) {
          this.addFiles(this.inputEl.files);
        }
      });
      on(this.triggerEl, "keydown", this._disableable.enabled((e) => {
        if (["Enter", " "].includes(e.key)) {
          e.preventDefault();
        }
        if (["Enter"].includes(e.key)) {
          this.inputEl.click();
        }
      }));
      on(this.triggerEl, "keyup", this._disableable.enabled((e) => {
        if (e.key === " ") {
          e.preventDefault();
          this.inputEl.click();
        }
      }));
      on(this.triggerEl, "focus", this._disableable.enabled(() => {
        setAttribute2(this, "data-focus", "");
      }));
      on(this.triggerEl, "blur", () => {
        removeAttribute(this, "data-focus");
      });
    }
  };
  element("file-upload", UIFileUpload);

  // js/calendar/observable.js
  var ObservableTrigger2 = class {
    static SELECTION = 1;
    static ACTIVATION = 2;
    static VIEW_CHANGE = 3;
  };
  var Observable2 = class {
    constructor() {
      this.subscribers = [];
      this.subscriberTypes = /* @__PURE__ */ new WeakMap();
    }
    subscribe(type, callback) {
      let types = Array.isArray(type) ? type : [type];
      this.subscribers.push(callback);
      if (!this.subscriberTypes.has(callback)) this.subscriberTypes.set(callback, []);
      this.subscriberTypes.get(callback).push(...types);
    }
    notify(type) {
      let types = Array.isArray(type) ? type : [type];
      this.subscribers.forEach((callback) => {
        if (types.length && !this.subscriberTypes.get(callback)?.some((t) => types.includes(t))) return;
        callback();
      });
    }
  };

  // js/calendar/selectable.js
  var CalendarModes = class {
    static SINGLE = "single";
    static MULTIPLE = "multiple";
    static RANGE = "range";
  };
  var Selectable3 = class _Selectable {
    constructor(selection, config, observable) {
      this.config = config;
      this.observable = observable;
      this.selection = selection;
      this.active = null;
      this.displayResolver = (i) => i;
    }
    setDisplayResolver(resolver) {
      this.displayResolver = resolver;
    }
    select(date) {
      let previousSelection = this.selection;
      this.selection = this.selection.select(date, this.config);
      if (this.selection.shouldNotify(previousSelection)) this.observable.notify(ObservableTrigger2.SELECTION);
      else this.observable.notify(ObservableTrigger2.ACTIVATION);
    }
    activate(date) {
      let memo = this.active;
      if (!date) {
        this.active = null;
      } else {
        this.active = date;
      }
      this.selection.activate(date);
      if (memo !== this.active) this.observable.notify(ObservableTrigger2.ACTIVATION);
    }
    setValue(value3) {
      let previousSelection = this.selection;
      this.selection = this.createSelectionFromValue(value3, this.config);
      if (this.selection.shouldNotify(previousSelection)) this.observable.notify(ObservableTrigger2.SELECTION);
      else this.observable.notify(ObservableTrigger2.ACTIVATION);
    }
    getValue() {
      return this.selection.value();
    }
    isSelectable(date) {
      return this.selection.selectable(date, this.config);
    }
    /** Passthrough methods */
    contains(...params) {
      return this.selection.contains(...params);
    }
    hasSelection() {
      return this.selection.hasSelection();
    }
    lowerBound(...params) {
      return this.selection.lowerBound(...params);
    }
    upperBound(...params) {
      return this.selection.upperBound(...params);
    }
    display(...params) {
      return this.displayResolver(this.selection.display(...params));
    }
    attributes(...params) {
      return this.selection.attributes(...params);
    }
    createSelectionFromValue(value3, config) {
      if (config.mode === CalendarModes.MULTIPLE) {
        let dates = value3 ? value3.map((i) => DateValue.fromIsoDateString(i)) : [];
        return new MultipleSelection2(dates);
      } else if (config.mode === CalendarModes.RANGE) {
        let { start, end, preset } = value3 || {};
        if (preset && presets[preset] && preset !== "custom") {
          return new PresetRangeSelection(preset, config);
        }
        if (!start && !end) return new EmptyRangeSelection();
        if (!end) return new PartialRangeSelection(DateValue.fromIsoDateString(start));
        return new RangeSelection(DateValue.fromIsoDateString(start), DateValue.fromIsoDateString(end));
      } else {
        if (!value3) return new NoneSelection2();
        return new SingleSelection2(DateValue.fromIsoDateString(value3));
      }
    }
    static createFromValueStringAttribute(value3, config, observable) {
      let selection = _Selectable.createSelectionFromValueStringAttribute(value3, config);
      return new _Selectable(selection, config, observable);
    }
    static createSelectionFromValueStringAttribute(value3, config) {
      if (config.mode === CalendarModes.MULTIPLE) {
        let dates = value3 ? value3.split(",").map((i) => i.trim()).map((i) => DateValue.fromIsoDateString(i)) : [];
        return new MultipleSelection2(dates);
      } else if (config.mode === CalendarModes.RANGE) {
        if (!value3) return new EmptyRangeSelection();
        let [start, end] = value3.split("/").map((i) => i.trim());
        if (!end && start && presets[start] && start !== "custom") {
          return new PresetRangeSelection(start, config);
        }
        start = DateValue.fromIsoDateString(start);
        if (!end) {
          return new PartialRangeSelection(start);
        }
        end = DateValue.fromIsoDateString(end);
        return new RangeSelection(start, end);
      } else {
        if (!value3) return new NoneSelection2();
        return new SingleSelection2(DateValue.fromIsoDateString(value3));
      }
    }
  };
  var DeferredSelectable = class {
    constructor(selectable, observable) {
      this.selectable = selectable;
      this.observable = observable;
      this._selection = this.selectable.selection;
      this.selectable.observable.subscribe(ObservableTrigger2.SELECTION, () => {
        this._blockSync() || this.sync();
      });
      this._blockSync = () => {
      };
    }
    blockSyncIf(condition) {
      this._blockSync = condition;
    }
    sync() {
      this._selection = this.selectable.selection;
      this.observable.notify(ObservableTrigger2.SELECTION);
    }
    setValue(value3) {
      return this.selectable.setValue(value3);
    }
    getValue() {
      return this._selection.value();
    }
    isSelectable(date) {
      return this._selection.selectable(date, this.config);
    }
    hasSelection() {
      return this._selection.hasSelection();
    }
    display(...params) {
      return this.selectable.displayResolver(this._selection.display(...params));
    }
  };
  var Selection2 = class {
    contains() {
    }
    shouldNotify(previousSelection) {
      return true;
    }
    hasSelection() {
      return false;
    }
    lowerBound(fallback) {
      return value2(fallback);
    }
    upperBound(fallback) {
      return this.lowerBound(fallback);
    }
    selectable(date, config) {
      return true;
    }
    display() {
    }
    value() {
    }
    activate() {
    }
    attributes(el, date) {
      if (this.contains(date)) {
        setAttribute2(el, "data-selected", "");
        setAttribute2(el, "aria-selected", "true");
      } else {
        removeAttribute(el, "data-selected");
        setAttribute2(el, "aria-selected", "false");
      }
    }
  };
  var NoneSelection2 = class _NoneSelection extends Selection2 {
    shouldNotify(selection) {
      return !(selection instanceof _NoneSelection);
    }
    hasSelection() {
      return false;
    }
    contains() {
      return false;
    }
    select(date) {
      return new SingleSelection2(date);
    }
    lowerBound(fallback) {
      return value2(fallback);
    }
    display(locale) {
      return "";
    }
    value() {
      return null;
    }
  };
  var SingleSelection2 = class _SingleSelection extends Selection2 {
    constructor(date) {
      super();
      this._date = date;
    }
    hasSelection() {
      return true;
    }
    shouldNotify(previousSelection) {
      if (previousSelection instanceof _SingleSelection && this._date.isSameDay(previousSelection._date)) return false;
      return true;
    }
    select(date) {
      if (this._date.isSameDay(date)) return new NoneSelection2();
      return new _SingleSelection(date);
    }
    contains(date) {
      return this._date.isSameDay(date);
    }
    lowerBound() {
      return this._date;
    }
    display(locale) {
      return this._date.toFormattedString(locale, { day: "numeric", month: "short", year: "numeric" });
    }
    value() {
      return this._date.toIsoDateString();
    }
  };
  var MultipleSelection2 = class _MultipleSelection extends Selection2 {
    constructor(dates) {
      super();
      this._dates = dates;
    }
    hasSelection() {
      return this._dates.length > 0;
    }
    shouldNotify(previousSelection) {
      if (previousSelection instanceof _MultipleSelection && this._dates.every((i) => previousSelection._dates.includes(i)) && previousSelection._dates.every((i) => this._dates.includes(i))) return false;
      return true;
    }
    select(date) {
      let containsDate = this._dates.some((i) => i.isSameDay(date));
      if (containsDate) return new _MultipleSelection(this._dates.filter((i) => !i.isSameDay(date)));
      return new _MultipleSelection([...this._dates, date]);
    }
    contains(date) {
      return this._dates.some((i) => i.isSameDay(date));
    }
    lowerBound(fallback) {
      let sortedDates = this._dates.sort((a, b) => a.isBefore(b) ? -1 : 1);
      return sortedDates[0] || value2(fallback);
    }
    upperBound(fallback) {
      let sortedDates = this._dates.sort((a, b) => a.isBefore(b) ? -1 : 1);
      return sortedDates[sortedDates.length - 1] || value2(fallback);
    }
    display(locale) {
      return this._dates.map((i) => i.toFormattedString(locale, { day: "numeric", month: "short", year: "numeric" })).join(", ");
    }
    value() {
      return this._dates.map((i) => i.toIsoDateString());
    }
  };
  var EmptyRangeSelection = class _EmptyRangeSelection extends Selection2 {
    constructor() {
      super();
    }
    hasSelection() {
      return false;
    }
    shouldNotify(previousSelection) {
      return !(previousSelection instanceof _EmptyRangeSelection || previousSelection instanceof PartialRangeSelection);
    }
    select(date) {
      return new PartialRangeSelection(date);
    }
    contains() {
      return false;
    }
    lowerBound(fallback) {
      return value2(fallback);
    }
    display(locale) {
      return "";
    }
    value() {
      return null;
    }
  };
  var PartialRangeSelection = class _PartialRangeSelection extends Selection2 {
    constructor(start) {
      super();
      this._start = start;
      this._endPreview = null;
    }
    hasSelection() {
      return false;
    }
    shouldNotify(previousSelection) {
      return false;
    }
    select(date, config) {
      if (date.isBefore(this._start)) return new _PartialRangeSelection(date);
      if (date.isSameDay(this._start) && config.minRange > 1) {
        return new EmptyRangeSelection();
      }
      return new RangeSelection(this._start, date);
    }
    activate(date) {
      this._endPreview = date;
    }
    selectable(date, config) {
      if (config.maxRange) {
        if (date.isBefore(this._start)) return false;
        if (this._start.addDays(config.maxRange - 1).isBefore(date)) return false;
      }
      if (config.minRange) {
        if (this._start.isSameDay(date)) return true;
        if (date.isBefore(this._start)) return false;
        if (this._start.addDays(config.minRange - 1).isAfter(date)) return false;
      }
      let nextUnavailable = config.unavailable.filter((d) => d.isAfter(this._start)).sort((a, b) => a.toDate() - b.toDate())[0];
      let prevUnavailable = config.unavailable.filter((d) => d.isBefore(this._start)).sort((a, b) => b.toDate() - a.toDate())[0];
      if (date.isAfter(this._start)) {
        return !nextUnavailable || date.isBefore(nextUnavailable);
      }
      if (date.isBefore(this._start)) {
        return !prevUnavailable || date.isAfter(prevUnavailable);
      }
      return true;
    }
    contains(date) {
      return date.isSameDay(this._start);
    }
    lowerBound() {
      return this._start;
    }
    display(locale) {
      return new Intl.DateTimeFormat(locale, { day: "numeric", month: "short", year: "numeric", timeZone: "UTC" }).format(this._start.getDate()) + " -";
    }
    value() {
      return null;
    }
    attributes(el, date) {
      if (this._endPreview && date.isBetween(this._start, this._endPreview)) {
        setAttribute2(el, "data-in-range", "");
        setAttribute2(el, "aria-selected", "true");
      } else {
        removeAttribute(el, "data-in-range");
        removeAttribute(el, "aria-selected");
      }
      date.isSameDay(this._start) ? setAttribute2(el, "data-selected", "") : removeAttribute(el, "data-selected");
      date.isSameDay(this._start) ? setAttribute2(el, "aria-selected", "true") : removeAttribute(el, "aria-selected");
      date.isSameDay(this._start) ? setAttribute2(el, "data-start", "") : removeAttribute(el, "data-start");
      date.isSameDay(this._endPreview) ? setAttribute2(el, "data-end-preview", "") : removeAttribute(el, "data-end-preview");
    }
  };
  var RangeSelection = class _RangeSelection extends Selection2 {
    constructor(start, end) {
      super();
      this._start = start;
      this._end = end;
    }
    hasSelection() {
      return true;
    }
    shouldNotify(previousSelection) {
      if (previousSelection instanceof _RangeSelection && this._start.isSameDay(previousSelection._start) && this._end.isSameDay(previousSelection._end)) return false;
      return true;
    }
    select(date) {
      return new PartialRangeSelection(date);
    }
    contains(date) {
      return date.isBetween(this._start, this._end);
    }
    lowerBound() {
      return this._start;
    }
    upperBound() {
      return this._end;
    }
    display(locale) {
      return new Intl.DateTimeFormat(locale, { day: "numeric", month: "short", year: "numeric", timeZone: "UTC" }).formatRange(this._start.getDate(), this._end.getDate()).replaceAll("\u2009", " ");
    }
    value() {
      return { start: this._start.toIsoDateString(), end: this._end.toIsoDateString() };
    }
    attributes(el, date) {
      date.isSameDay(this._start) || date.isSameDay(this._end) ? setAttribute2(el, "data-selected", "") : removeAttribute(el, "data-selected");
      date.isSameDay(this._start) ? setAttribute2(el, "data-start", "") : removeAttribute(el, "data-start");
      date.isSameDay(this._end) ? setAttribute2(el, "data-end", "") : removeAttribute(el, "data-end");
      if (this.contains(date)) {
        setAttribute2(el, "data-in-range", "");
        setAttribute2(el, "aria-selected", "true");
      } else {
        removeAttribute(el, "data-in-range");
        removeAttribute(el, "aria-selected");
      }
    }
  };
  var PresetRangeSelection = class _PresetRangeSelection extends Selection2 {
    constructor(preset, config) {
      super();
      this._preset = preset;
      this._config = config;
    }
    get _start() {
      let [start] = presets[this._preset](this._config);
      return this._config.min && start.isBefore(this._config.min) ? this._config.min : start;
    }
    get _end() {
      let [, end] = presets[this._preset](this._config);
      return this._config.max && end.isAfter(this._config.max) ? this._config.max : end;
    }
    hasSelection() {
      return true;
    }
    shouldNotify(previousSelection) {
      return !(previousSelection instanceof _PresetRangeSelection && this._preset === previousSelection._preset);
    }
    select(date) {
      return new PartialRangeSelection(date, this._config);
    }
    contains(date) {
      return date.isBetween(this._start, this._end);
    }
    lowerBound() {
      if (this._preset === "allTime") return DateValue.today();
      return this._start;
    }
    upperBound() {
      if (this._preset === "allTime") return DateValue.today();
      return DateValue.today();
    }
    display(locale) {
      return new Intl.DateTimeFormat(locale, { day: "numeric", month: "short", year: "numeric", timeZone: "UTC" }).formatRange(this._start.getDate(), this._end.getDate()).replaceAll("\u2009", " ");
    }
    value() {
      return { start: this._start.toIsoDateString(), end: this._end.toIsoDateString(), preset: this._preset };
    }
    attributes(el, date) {
      date.isSameDay(this._start) || date.isSameDay(this._end) ? setAttribute2(el, "data-selected", "") : removeAttribute(el, "data-selected");
      date.isSameDay(this._start) ? setAttribute2(el, "data-start", "") : removeAttribute(el, "data-start");
      date.isSameDay(this._end) ? setAttribute2(el, "data-end", "") : removeAttribute(el, "data-end");
      if (this.contains(date)) {
        setAttribute2(el, "data-in-range", "");
        setAttribute2(el, "aria-selected", "true");
      } else {
        removeAttribute(el, "data-in-range");
        removeAttribute(el, "aria-selected");
      }
    }
  };
  function value2(i = null) {
    return typeof i === "function" ? i() : i;
  }
  var presets = {
    today: () => {
      return [DateValue.today(), DateValue.today()];
    },
    yesterday: () => {
      let date = DateValue.today().incrementDays(-1);
      return [date, date];
    },
    thisWeek: (config) => {
      let start = DateValue.today().getCopy().incrementDays(config.startDay - DateValue.today().getDayOfWeek());
      let end = start.incrementDays(6);
      return [start, end];
    },
    lastWeek: (config) => {
      let start = DateValue.today().incrementDays(config.startDay - (DateValue.today().getDayOfWeek() + 7));
      let end = start.incrementDays(6);
      return [start, end];
    },
    last7Days: () => {
      let end = DateValue.today();
      let start = DateValue.today().incrementDays(-6);
      return [start, end];
    },
    thisMonth: () => {
      let start = DateValue.fromParts(DateValue.today().getYear(), DateValue.today().getMonth(), 1);
      let end = DateValue.fromParts(DateValue.today().getYear(), DateValue.today().getMonth(), DateValue.today().getDaysInMonth());
      return [start, end];
    },
    lastMonth: () => {
      let month = DateValue.today().getMonth() - 1;
      let year = DateValue.today().getYear();
      if (month < 1) {
        month += 12;
        year--;
      }
      let start = DateValue.fromParts(year, month, 1);
      let end = DateValue.fromParts(year, month, start.getDaysInMonth());
      return [start, end];
    },
    thisQuarter: () => {
      let quarter = Math.floor((DateValue.today().getMonth() - 1) / 3);
      let startMonth = quarter * 3 + 1;
      let endMonth = startMonth + 2;
      let start = DateValue.fromParts(DateValue.today().getYear(), startMonth, 1);
      let endMonthStart = DateValue.fromParts(DateValue.today().getYear(), endMonth, 1);
      let end = DateValue.fromParts(DateValue.today().getYear(), endMonth, endMonthStart.getDaysInMonth());
      return [start, end];
    },
    lastQuarter: () => {
      let quarter = Math.floor((DateValue.today().getMonth() - 4) / 3);
      let startMonth = quarter * 3 + 1;
      let endMonth = startMonth + 2;
      let year = DateValue.today().getYear();
      if (startMonth < 1) {
        startMonth += 12;
        endMonth += 12;
        year--;
      }
      let start = DateValue.fromParts(year, startMonth, 1);
      let endMonthStart = DateValue.fromParts(year, endMonth, 1);
      let end = DateValue.fromParts(year, endMonth, endMonthStart.getDaysInMonth());
      return [start, end];
    },
    thisYear: () => {
      let start = DateValue.fromParts(DateValue.today().getYear(), 1, 1);
      let end = DateValue.fromParts(DateValue.today().getYear(), 12, 31);
      return [start, end];
    },
    lastYear: () => {
      let year = DateValue.today().getYear() - 1;
      let start = DateValue.fromParts(year, 1, 1);
      let end = DateValue.fromParts(year, 12, 31);
      return [start, end];
    },
    last14Days: () => {
      let end = DateValue.today();
      let start = DateValue.today().incrementDays(-13);
      return [start, end];
    },
    last30Days: () => {
      let end = DateValue.today();
      let start = DateValue.today().incrementDays(-29);
      return [start, end];
    },
    last3Months: () => {
      let end = DateValue.today();
      let day = DateValue.today().getDay() + 1;
      let month = DateValue.today().getMonth() - 3;
      let year = DateValue.today().getYear();
      if (month < 1) {
        month += 12;
        year--;
      }
      let start = DateValue.fromParts(year, month, day);
      return [start, end];
    },
    last6Months: () => {
      let end = DateValue.today();
      let day = DateValue.today().getDay() + 1;
      let month = DateValue.today().getMonth() - 6;
      let year = DateValue.today().getYear();
      if (month < 1) {
        month += 12;
        year--;
      }
      let start = DateValue.fromParts(year, month, day);
      return [start, end];
    },
    yearToDate: () => {
      let start = DateValue.fromParts(DateValue.today().getYear(), 1, 1);
      let end = DateValue.today();
      return [start, end];
    },
    tomorrow: () => {
      let date = DateValue.today().incrementDays(1);
      return [date, date];
    },
    nextWeek: (config) => {
      let start = DateValue.today().incrementDays(7 + config.startDay - DateValue.today().getDayOfWeek());
      let end = start.incrementDays(6);
      return [start, end];
    },
    next7Days: () => {
      let start = DateValue.today();
      let end = DateValue.today().incrementDays(6);
      return [start, end];
    },
    nextMonth: () => {
      let month = DateValue.today().getMonth() + 1;
      let year = DateValue.today().getYear();
      if (month > 12) {
        month -= 12;
        year++;
      }
      let start = DateValue.fromParts(year, month, 1);
      let end = DateValue.fromParts(year, month, start.getDaysInMonth());
      return [start, end];
    },
    nextQuarter: () => {
      let quarter = Math.floor((DateValue.today().getMonth() - 1) / 3) + 1;
      let startMonth = quarter * 3 + 1;
      let year = DateValue.today().getYear();
      if (startMonth > 12) {
        startMonth -= 12;
        year++;
      }
      let endMonth = startMonth + 2;
      if (endMonth > 12) {
        endMonth -= 12;
        year++;
      }
      let start = DateValue.fromParts(year, startMonth, 1);
      let endMonthStart = DateValue.fromParts(year, endMonth, 1);
      let end = DateValue.fromParts(year, endMonth, endMonthStart.getDaysInMonth());
      return [start, end];
    },
    nextYear: () => {
      let year = DateValue.today().getYear() + 1;
      let start = DateValue.fromParts(year, 1, 1);
      let end = DateValue.fromParts(year, 12, 31);
      return [start, end];
    },
    next14Days: () => {
      let start = DateValue.today();
      let end = DateValue.today().incrementDays(13);
      return [start, end];
    },
    next30Days: () => {
      let start = DateValue.today();
      let end = DateValue.today().incrementDays(29);
      return [start, end];
    },
    next3Months: () => {
      let start = DateValue.today();
      let day = DateValue.today().getDay();
      let month = DateValue.today().getMonth() + 3;
      let year = DateValue.today().getYear();
      if (month > 12) {
        month -= 12;
        year++;
      }
      let end = DateValue.fromParts(year, month, day).incrementDays(-1);
      return [start, end];
    },
    next6Months: () => {
      let start = DateValue.today();
      let day = DateValue.today().getDay();
      let month = DateValue.today().getMonth() + 6;
      let year = DateValue.today().getYear();
      if (month > 12) {
        month -= 12;
        year++;
      }
      let end = DateValue.fromParts(year, month, day).incrementDays(-1);
      return [start, end];
    },
    allTime: (config) => {
      if (!config.min) throw new Error("Min date is required for allTime preset");
      let start = config.min;
      let end = DateValue.today();
      return [start, end];
    }
  };

  // js/calendar/view.js
  var CalendarViewState = class {
    constructor(month, year, config, observable) {
      this.month = month;
      this.year = year;
      this.config = config;
      this.observable = observable;
    }
    generateOffsetState(offset3) {
      return new CalendarOffsetViewState(this, offset3);
    }
    canNavigatePrevious() {
      let [prevYear, prevMonth] = new DateValue(this.year, this.month).addMonths(-1).toParts();
      return !(this.config.min && DateValue.fromParts(prevYear, prevMonth).isBefore(DateValue.firstDayOfMonth(this.config.min)));
    }
    canNavigateNext() {
      let [nextYear, nextMonth] = new DateValue(this.year, this.month).addMonths(this.config.months).toParts();
      return !(this.config.max && DateValue.fromParts(nextYear, nextMonth).isAfter(this.config.max));
    }
    nextMonth() {
      if (!this.canNavigateNext()) return;
      this.adjustMonth(1);
    }
    previousMonth() {
      this.adjustMonth(-1);
    }
    setMonth(month) {
      if (!month) return;
      if (this.isWithinMinAndMax(month, this.year)) {
        this.month = month;
      }
      this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
    }
    setYear(year) {
      if (!year) return;
      let month = this.month;
      if (!this.isWithinMinAndMax(month, year)) {
        if (this.config.min && DateValue.fromParts(year, month).isBefore(DateValue.firstDayOfMonth(this.config.min))) {
          month = this.config.min.getMonth();
        } else if (this.config.max && DateValue.fromParts(year, month).isAfter(this.config.max)) {
          month = this.config.max.getMonth();
        }
      }
      if (this.isWithinMinAndMax(month, year)) {
        this.month = month;
        this.year = year;
      }
      this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
    }
    setDate(date) {
      if (!date) return;
      if (this.isWithinMinAndMax(date.getMonth(), date.getYear())) {
        this.month = date.getMonth();
        this.year = date.getYear();
      }
      this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
    }
    isWithinMinAndMax(month, year) {
      if (this.config.min && DateValue.fromParts(year, month).isBefore(DateValue.firstDayOfMonth(this.config.min))) return false;
      if (this.config.max && DateValue.fromParts(year, month).isAfter(this.config.max)) return false;
      return true;
    }
    adjustMonth(delta) {
      let [nextYear, nextMonth] = new DateValue(this.year, this.month).addMonths(delta).toParts();
      if (this.config.min && DateValue.fromParts(nextYear, nextMonth).isBefore(DateValue.firstDayOfMonth(this.config.min))) return;
      if (this.config.max && DateValue.fromParts(nextYear, nextMonth).isAfter(this.config.max)) return;
      [this.month, this.year] = [nextMonth, nextYear];
      this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
    }
  };
  var CalendarOffsetViewState = class {
    constructor(viewState, offset3 = 0) {
      this.offset = offset3;
      this.viewState = viewState;
    }
    get month() {
      let [, newMonth] = new DateValue(this.viewState.year, this.viewState.month).addMonths(this.offset).toParts();
      return newMonth;
    }
    get year() {
      let [newYear] = new DateValue(this.viewState.year, this.viewState.month).addMonths(this.offset).toParts();
      return newYear;
    }
  };

  // js/calendar/validator.js
  var Validator = class {
    constructor(selectable, metadata, config) {
      this.min = config.min;
      this.max = config.max;
      this.unavailable = config.unavailable;
      this.selectable = selectable;
      this.metadata = metadata;
    }
    isBetweenMinMax(date) {
      return date.isBetween(this.min, this.max);
    }
    isUnavailable(date) {
      return this.unavailable.some((i) => i.isSameDay(date)) || this.metadata.unavailable(date);
    }
    isBlocked(date) {
      return !this.selectable.isSelectable(date);
    }
    isValid(date) {
      return this.isBetweenMinMax(date) && !this.isUnavailable(date) && !this.isBlocked(date);
    }
  };

  // js/calendar/meta.js
  var DateMetadata = class {
    constructor(observable) {
      this.metadata = {};
      this.observable = observable;
    }
    resetFromJSON(json) {
      this.reset(JSON.parse(json));
    }
    appendFromJSON(json) {
      this.append(JSON.parse(json));
    }
    append(object) {
      this.metadata = { ...this.metadata, ...object };
      this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
    }
    reset(object) {
      this.metadata = object;
      this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
    }
    get(date) {
      return this.metadata[date.toIsoDateString()];
    }
    has(date) {
      return this.metadata[date.toIsoDateString()] !== void 0;
    }
    subtext(date) {
      return this.metadata[date.toIsoDateString()]?.subtext;
    }
    details(date) {
      return this.metadata[date.toIsoDateString()]?.details;
    }
    variant(date) {
      return this.metadata[date.toIsoDateString()]?.variant;
    }
    unavailable(date) {
      return !!this.metadata[date.toIsoDateString()]?.unavailable;
    }
  };

  // js/calendar/index.js
  var UICalendar = class extends UIElement {
    boot() {
      let elDrivingConfig = this.closest("ui-date-picker") || this;
      this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
      let [months, onMonthsChange] = responsiveMonthsAttributeValue(this, elDrivingConfig);
      this.observable = new Observable2();
      let locale = elDrivingConfig.hasAttribute("locale") ? elDrivingConfig.getAttribute("locale") : getLocale();
      this.config = {
        mode: elDrivingConfig.getAttribute("mode") === "range" ? CalendarModes.RANGE : elDrivingConfig.hasAttribute("multiple") ? CalendarModes.MULTIPLE : CalendarModes.SINGLE,
        months,
        min: this.getMinDate(elDrivingConfig),
        max: this.getMaxDate(elDrivingConfig),
        maxRange: elDrivingConfig.hasAttribute("max-range") ? parseInt(elDrivingConfig.getAttribute("max-range")) : null,
        minRange: elDrivingConfig.hasAttribute("min-range") ? parseInt(elDrivingConfig.getAttribute("min-range")) : null,
        // Newer browsers use getWeekInfo() but have removed weekInfo (from Chrome 143+), older browsers use weekInfo, so the startDay calculation needs to account for that...
        startDay: elDrivingConfig.hasAttribute("start-day") ? parseInt(elDrivingConfig.getAttribute("start-day")) : (new Intl.Locale(locale).getWeekInfo?.()?.firstDay ?? new Intl.Locale(locale).weekInfo?.firstDay ?? 7) % 7,
        unavailable: this.getUnavailableDates(elDrivingConfig),
        locale,
        fixedWeeks: elDrivingConfig.hasAttribute("fixed-weeks") ? 6 : null
      };
      onMonthsChange((months2) => {
        this.config.months = months2;
        this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
      });
      this.selectable = Selectable3.createFromValueStringAttribute(elDrivingConfig.getAttribute("value"), this.config, this.observable);
      this.metadata = new DateMetadata(this.observable);
      this.validator = new Validator(this.selectable, this.metadata, this.config);
      let openToDate = elDrivingConfig.hasAttribute("open-to") && (elDrivingConfig.hasAttribute("force-open-to") || this.selectable.lowerBound() === null) ? this.getOpenTo(elDrivingConfig) : this.selectable.lowerBound(() => DateValue.today());
      this.viewState = new CalendarViewState(openToDate.getMonth(), openToDate.getYear(), this.config, this.observable);
      this._disableable = new Disableable(this, {
        disableWithParent: false
      });
      if (!this.hasAttribute("static")) {
        let { enable: enableListeners, disable: disableListeners } = initializeEventListeners2(this, this.selectable, this.viewState, this.validator);
        this._disableable.onInitAndChange((disabled) => {
          disabled ? disableListeners() : enableListeners();
        });
      }
      this._controllable = new Controllable(this, { bubbles: true });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.selectable.getValue()
      });
      let detangled = detangle();
      this._controllable.initial((initial) => initial && this.selectable.setValue(initial));
      this._controllable.getter(() => this.selectable.getValue());
      this._controllable.setter(detangled((value3) => {
        this.selectable.setValue(value3);
      }));
      this.observable.subscribe(ObservableTrigger2.SELECTION, detangled(() => {
        this.dispatchEvent(new Event("select", { bubbles: false, cancelable: true }));
        this._controllable.dispatch();
      }));
      this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
        this._submittable.update(this.selectable.getValue());
      });
      this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
        this.anchorSelection(elDrivingConfig);
      });
      this.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
        this.dispatchEvent(new Event("navigate", { bubbles: false, cancelable: true }));
      });
      queueMicrotask(() => {
        this.closest("ui-date-picker")?.bootWithCalendar(this);
      });
      let observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (!["unavailable", "max", "min"].includes(mutation.attributeName)) return;
          this.config.unavailable = this.getUnavailableDates(elDrivingConfig);
          this.config.min = this.getMinDate(elDrivingConfig);
          this.config.max = this.getMaxDate(elDrivingConfig);
          this.validator.unavailable = this.config.unavailable;
          this.validator.min = this.config.min;
          this.validator.max = this.config.max;
          this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
        });
      });
      observer.observe(elDrivingConfig, { attributes: true });
    }
    getOpenTo(el) {
      if (!el.hasAttribute("open-to")) return;
      let openTo = el.getAttribute("open-to");
      openTo = openTo === "today" ? DateValue.today() : DateValue.fromIsoDateString(openTo);
      if (!this.validator.isBetweenMinMax(openTo)) {
        return this.config.min;
      }
      return openTo;
    }
    navigateNext() {
      this.viewState.nextMonth();
    }
    navigatePrevious() {
      this.viewState.previousMonth();
    }
    anchorSelection(elDrivingConfig) {
      let focusedEl = document.activeElement;
      let isFocusedOnADateButton = this.contains(focusedEl) && focusedEl.closest("[data-date]") !== null;
      if (isFocusedOnADateButton) return;
      if (elDrivingConfig.hasAttribute("force-open-to")) return;
      this.viewState.setDate(
        this.selectable.lowerBound(DateValue.today())
      );
    }
    appendMetadata(object) {
      this.metadata.append(object);
    }
    resetMetadata(object = {}) {
      this.metadata.reset(object);
    }
    visibleDateRange() {
      return [
        DateValue.fromParts(this.viewState.year, this.viewState.month, 1).toIsoDateString(),
        DateValue.fromParts(this.viewState.year, this.viewState.month + 1, 0).toIsoDateString()
      ];
    }
    getUnavailableDates(el) {
      return el.hasAttribute("unavailable") ? el.getAttribute("unavailable").split(",").map((i) => DateValue.fromIsoDateString(i.trim())) : [];
    }
    getMinDate(el) {
      return el.hasAttribute("min") ? el.getAttribute("min") === "today" ? DateValue.today() : DateValue.fromIsoDateString(el.getAttribute("min")) : null;
    }
    getMaxDate(el) {
      return el.hasAttribute("max") ? el.getAttribute("max") === "today" ? DateValue.today() : DateValue.fromIsoDateString(el.getAttribute("max")) : null;
    }
  };
  var UICalendarPrevious = class extends UIElement {
    boot() {
      let calendar = this.closest("ui-calendar");
      this._disableable = calendar._disableable;
      initFauxButton(this, () => this._disableable.isDisabled(), () => calendar.navigatePrevious());
      let setDisabled = () => {
        if (calendar.viewState.canNavigatePrevious() && !this._disableable.isDisabled()) {
          removeAttribute(this, "disabled");
        } else {
          setAttribute2(this, "disabled", "");
        }
      };
      calendar.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, setDisabled);
      this._disableable.onInitAndChange(() => {
        setDisabled();
      });
    }
  };
  var UICalendarNext = class extends UIElement {
    boot() {
      let calendar = this.closest("ui-calendar");
      this._disableable = calendar._disableable;
      initFauxButton(this, () => this._disableable.isDisabled(), () => calendar.navigateNext());
      let setDisabled = () => {
        if (calendar.viewState.canNavigateNext() && !this._disableable.isDisabled()) {
          removeAttribute(this, "disabled");
        } else {
          setAttribute2(this, "disabled", "");
        }
      };
      calendar.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, setDisabled);
      this._disableable.onInitAndChange(() => {
        setDisabled();
      });
    }
  };
  var UICalendarMonth = class extends UIElement {
    boot() {
      let calendar = this.closest("ui-calendar");
      this._disableable = calendar._disableable;
      this.offset = this.hasAttribute("offset") ? parseInt(this.getAttribute("offset")) : 0;
      this.config = calendar.config;
      this.viewState = calendar.viewState;
      this.offsetState = this.viewState.generateOffsetState(this.offset);
      this.validator = calendar.validator;
      let select = this.querySelector("select");
      let display = this.hasAttribute("display") ? this.getAttribute("display") : "long";
      if (select) {
        this.renderSelectOptions(select, display);
        select.value = this.viewState.month;
        select.addEventListener("change", this._disableable.enabled(() => {
          this.viewState.setMonth(parseInt(select.value));
        }));
        calendar.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
          this.renderSelectOptions(select, display);
          select.value = this.viewState.month;
        });
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            setAttribute2(select, "disabled", "");
          } else {
            removeAttribute(select, "disabled");
          }
        });
      } else {
        let update = () => this.textContent = new Intl.DateTimeFormat(this.config.locale, {
          month: this.hasAttribute("display") ? this.getAttribute("display") : "long",
          timeZone: "UTC"
        }).format(new DateValue(this.offsetState.year, this.offsetState.month).getDate());
        update();
        this.viewState.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => update());
      }
    }
    renderSelectOptions(select, display) {
      let months = Array.from({ length: 12 }, (_, idx) => idx + 1);
      let renderableMonths = months.map((month) => {
        let firstDayOfMonth = new DateValue(this.viewState.year, month);
        let lastDayOfMonth = new DateValue(this.viewState.year, month + 1, 0);
        if (!this.validator.isBetweenMinMax(firstDayOfMonth) && !this.validator.isBetweenMinMax(lastDayOfMonth)) {
          return null;
        }
        return {
          month,
          label: new Intl.DateTimeFormat(this.config.locale, { month: display, timeZone: "UTC" }).format(new DateValue(2024, month).getDate())
        };
      }).filter(Boolean);
      renderTemplate(select.querySelector("template"), (hydrate) => {
        if (renderableMonths.length === 0) {
          let month = this.viewState.month;
          let label = new Intl.DateTimeFormat(this.config.locale, { month: display, timeZone: "UTC" }).format(new DateValue(2024, month).getDate());
          let option = hydrate({ slots: { default: label } });
          option.setAttribute("value", month);
          return option;
        }
        return renderableMonths.map((month) => {
          let option = hydrate({ slots: { default: month.label } });
          option.setAttribute("value", month.month);
          return option;
        });
      });
    }
  };
  var UICalendarYear = class extends UIElement {
    boot() {
      let calendar = this.closest("ui-calendar");
      this._disableable = calendar._disableable;
      this.offset = this.hasAttribute("offset") ? parseInt(this.getAttribute("offset")) : 0;
      this.config = calendar.config;
      this.viewState = calendar.viewState;
      this.offsetState = this.viewState.generateOffsetState(this.offset);
      this.numberOfPastYears = 100;
      this.numberOfFutureYears = 10;
      this.validator = calendar.validator;
      let select = this.querySelector("select");
      if (select) {
        if (this.config.min) this.numberOfPastYears = this.viewState.year - this.config.min.getYear();
        if (this.config.max) this.numberOfFutureYears = this.config.max.getYear() - this.viewState.year;
        this.renderSelectOptions(select);
        select.value = this.viewState.year;
        select.addEventListener("change", this._disableable.enabled(() => {
          this.viewState.setYear(parseInt(select.value));
        }));
        calendar.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
          if (this.yearIsLessThanMinimum(this.viewState.year)) {
            this.numberOfPastYears += 100;
            this.renderSelectOptions(select);
          }
          if (this.yearIsGreaterThanMaximum(this.viewState.year)) {
            this.numberOfFutureYears += 10;
            this.renderSelectOptions(select);
          }
          select.value = this.viewState.year;
        });
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            setAttribute2(select, "disabled", "");
          } else {
            removeAttribute(select, "disabled");
          }
        });
      } else {
        let update = () => this.textContent = new Intl.DateTimeFormat(this.config.locale, {
          year: this.hasAttribute("display") ? this.getAttribute("display") : "numeric",
          timeZone: "UTC"
        }).format(new DateValue(this.offsetState.year, this.offsetState.month).getDate());
        update();
        this.viewState.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => update());
      }
    }
    yearIsLessThanMinimum(year) {
      return year < this.minimumYear();
    }
    yearIsGreaterThanMaximum(year) {
      return year > this.maximumYear();
    }
    minimumYear() {
      return (/* @__PURE__ */ new Date()).getFullYear() - this.numberOfPastYears;
    }
    maximumYear() {
      return (/* @__PURE__ */ new Date()).getFullYear() + this.numberOfFutureYears;
    }
    renderSelectOptions(select) {
      let currentYear = (/* @__PURE__ */ new Date()).getFullYear();
      let years = Array.from(
        { length: this.numberOfPastYears + this.numberOfFutureYears + 1 },
        (_, i) => currentYear - this.numberOfPastYears + i
      );
      let renderableYears = years.map((year) => {
        let yearStart = new DateValue(year, 1);
        let yearEnd = new DateValue(year, 12, 31);
        if (this.config.min && yearEnd.isBefore(this.config.min)) return null;
        if (this.config.max && yearStart.isAfter(this.config.max)) return null;
        return year;
      }).filter(Boolean);
      renderTemplate(select.querySelector("template"), (hydrate) => {
        if (renderableYears.length === 0) {
          let year = this.viewState.year;
          return hydrate({ slots: { default: year } });
        }
        return renderableYears.map((year) => hydrate({ slots: { default: year } }));
      });
    }
  };
  var UICalendarMonths = class extends UIElement {
    boot() {
      let calendar = this.closest("ui-calendar");
      this._disableable = calendar._disableable;
      this.offset = this.hasAttribute("offset") ? parseInt(this.getAttribute("offset")) : 0;
      this.config = calendar.config;
      this.selectable = calendar.selectable;
      this.observable = calendar.observable;
      this.metadata = calendar.metadata;
      this.validator = calendar.validator;
      this.viewState = calendar.viewState;
      this.offsetState = this.viewState.generateOffsetState(this.offset);
      this.monthEls = [];
      this.renderMonths();
      let render = () => this.monthEls.forEach((month) => renderMonth(month, this.config, month.offsetState, this.metadata));
      let update = () => this.monthEls.forEach((month) => updateMonth(month, this._disableable.isDisabled(), this.config, month.offsetState, this.selectable, this.validator));
      render();
      update();
      let months = this.config.months;
      this.viewState.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
        if (months !== this.config.months) {
          months = this.config.months;
          this.renderMonths();
        }
        render();
        update();
      });
      this.observable.subscribe([ObservableTrigger2.SELECTION, ObservableTrigger2.ACTIVATION], () => {
        update();
      });
      this._disableable.onChange(() => update());
    }
    renderMonths() {
      let template = this.querySelector('template:not([name]), template[name="month"]');
      renderTemplate(template, (hydrate) => {
        let monthOffsetTemplate = Array.from({ length: this.config.months }).map((_, idx) => idx);
        this.monthEls = monthOffsetTemplate.map((offset3) => {
          let offsetState = this.viewState.generateOffsetState(offset3);
          let monthEl = hydrate({ attrs: { "data-month": "" } });
          monthEl.offsetState = offsetState;
          let head = monthEl.querySelector("thead")?.set;
          setAttribute2(monthEl, "role", "grid");
          head && setAttribute2(head, "aria-hidden", "true");
          return monthEl;
        });
        return this.monthEls;
      });
    }
  };
  var UICalendarToday = class extends UIElement {
    boot() {
      let calendar = this.closest("ui-calendar, ui-date-picker");
      this._disableable = calendar._disableable;
      this.behavior = this.hasAttribute("behavior") ? this.getAttribute("behavior") : "auto";
      if (calendar.hasAttribute("static")) {
        this.behavior = "none";
      }
      initFauxButton(this, () => this._disableable.isDisabled(), () => this.navigateAndSelectToday());
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(this, "disabled", "");
        } else {
          removeAttribute(this, "disabled");
        }
      });
      let template = this.querySelector("template");
      template && renderTemplate(template, (hydrate) => {
        return hydrate({ slots: { default: DateValue.today().getDay() } });
      });
    }
    navigateAndSelectToday() {
      let calendar = this.closest("ui-calendar, ui-date-picker");
      let isViewingCurrentMonth = () => {
        let viewState = calendar.viewState;
        let today = DateValue.today();
        return viewState.year === today.getYear() && viewState.month === today.getMonth();
      };
      let shouldSelect = this.behavior === "select" || this.behavior === "auto" && isViewingCurrentMonth();
      if (shouldSelect) {
        if (calendar.config.mode === CalendarModes.RANGE) {
          calendar.selectable.setValue({ start: DateValue.today().toIsoDateString(), end: DateValue.today().toIsoDateString() });
        } else if (calendar.config.mode === CalendarModes.MULTIPLE) {
          calendar.selectable.setValue([DateValue.today().toIsoDateString()]);
        } else {
          calendar.selectable.setValue(DateValue.today().toIsoDateString());
        }
      }
      calendar.viewState.setMonth(DateValue.today().getMonth());
      calendar.viewState.setYear(DateValue.today().getYear());
    }
  };
  var UICalendarPresets = class extends UIElement {
    boot() {
      let calendar = this.closest("ui-calendar");
      this._disableable = calendar._disableable;
      this.offset = this.hasAttribute("offset") ? parseInt(this.getAttribute("offset")) : 0;
      this.selectable = calendar.selectable;
      this.observable = calendar.observable;
      this.config = calendar.config;
      this.viewState = calendar.viewState;
      this.offsetState = this.viewState.generateOffsetState(this.offset);
      if (this.config.mode !== CalendarModes.RANGE) throw "Presets can only be used with range calendars";
      let selectable = this.firstElementChild;
      if (selectable) {
        let getLabelFromPreset = (preset) => {
          if (selectable._selectable instanceof SelectableGroup) {
            let option = selectable._selectable.findByValue(preset);
            if (!option) return null;
            return option?.el?.textContent;
          } else if (selectable instanceof HTMLSelectElement) {
            return selectable.querySelector(`option[value="${preset}"]`)?.textContent;
          }
          return null;
        };
        this.selectable.setDisplayResolver((i) => {
          let preset = this.selectable.getValue()?.preset || "";
          if (preset) {
            let label = getLabelFromPreset(preset);
            if (label) return label;
          }
          return i;
        });
        selectable.value = this.selectable.getValue()?.preset || "custom";
        selectable.addEventListener("change", this._disableable.enabled(() => {
          if (["", "custom"].includes(selectable.value)) {
            let { start, end } = this.selectable.getValue() || {};
            if (start && end) this.selectable.setValue({ start, end });
          } else {
            this.selectable.setValue({ preset: selectable.value });
          }
        }));
        this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
          let value3 = this.selectable.getValue();
          selectable.value = value3?.preset || "custom";
        });
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            setAttribute2(selectable, "disabled", "");
          } else {
            removeAttribute(selectable, "disabled");
          }
        });
      }
    }
  };
  var UICalendarInputs = class extends UIElement {
    boot() {
      let calendar = this.closest("ui-calendar");
      this._disableable = calendar._disableable;
      this.offset = this.hasAttribute("offset") ? parseInt(this.getAttribute("offset")) : 0;
      this.selectable = calendar.selectable;
      this.observable = calendar.observable;
      this.config = calendar.config;
      this.viewState = calendar.viewState;
      this.offsetState = this.viewState.generateOffsetState(this.offset);
      let [firstInput, secondInput] = this.querySelectorAll("input");
      if (this.config.mode === CalendarModes.RANGE) {
        syncInputStateWithRangeSelectionState(firstInput, secondInput, calendar);
      } else {
        syncInputStateWithSingleSelectionState(firstInput, calendar);
      }
    }
  };
  element("calendar", UICalendar);
  element("calendar-today", UICalendarToday);
  element("calendar-presets", UICalendarPresets);
  element("calendar-previous", UICalendarPrevious);
  element("calendar-inputs", UICalendarInputs);
  element("calendar-next", UICalendarNext);
  element("calendar-months", UICalendarMonths);
  element("calendar-month", UICalendarMonth);
  element("calendar-year", UICalendarYear);
  function initializeEventListeners2(el, selectable, viewState, validator) {
    let blocked = false;
    let readonly = el.hasAttribute("readonly");
    on(el, "click", (e) => {
      if (blocked || readonly) return;
      let cell = e.target.closest("[data-date]");
      if (!cell) return;
      let date = dateFromCell2(cell);
      validator.isValid(date) && selectable.select(date);
    });
    on(el, "mouseover", (e) => {
      if (blocked || readonly) return;
      let cell = e.target.closest("[data-date]");
      if (!cell) return;
      let date = dateFromCell2(cell);
      if (!validator.isValid(date)) return;
      selectable.activate(date);
    });
    on(el, "mouseout", () => {
      if (blocked || readonly) return;
      selectable.activate(null);
    });
    on(el, "focusin", (e) => {
      if (blocked || readonly) return;
      let cell = e.target.closest("[data-date]");
      if (!cell) return;
      let date = dateFromCell2(cell);
      if (!validator.isValid(date)) return;
      selectable.activate(date);
    });
    on(el, "keydown", (e) => {
      if (blocked) return;
      let cell = e.target.closest("[data-date]");
      if (!cell) return;
      let handled = true;
      let direction;
      let focusedDate = dateFromCell2(cell);
      let adjustActive = (adjustment) => {
        let activeDate = focusedDate.getCopy();
        let direction2 = Math.sign(adjustment);
        let safetyCounter = 366;
        activeDate.setDay(activeDate.getDay() + adjustment);
        while (!validator.isValid(activeDate) && safetyCounter > 0) {
          activeDate.setDay(activeDate.getDay() + direction2);
          safetyCounter--;
        }
        focusedDate = activeDate;
      };
      switch (e.key) {
        case "ArrowRight":
          direction = "forward";
          adjustActive(1);
          break;
        case "ArrowLeft":
          direction = "backward";
          adjustActive(-1);
          break;
        case "ArrowUp":
          direction = "backward";
          adjustActive(-7);
          break;
        case "ArrowDown":
          direction = "forward";
          adjustActive(7);
          break;
        case "Home":
          viewState.previousMonth();
          break;
        case "End":
          viewState.nextMonth();
          break;
        case "PageUp":
          if (e.shiftKey) {
            viewState.previousMonth();
          } else {
            viewState.previousMonth();
          }
          break;
        case "PageDown":
          if (e.shiftKey) {
            viewState.nextMonth();
          } else {
            viewState.nextMonth();
          }
          break;
        default:
          handled = false;
      }
      if (handled) {
        e.preventDefault();
        e.stopPropagation();
        let cell2 = Array.from(el.querySelectorAll("[data-date]")).find((cell3) => dateFromCell2(cell3).isSameDay(focusedDate));
        if (!cell2 && direction) {
          if (direction === "forward") {
            viewState.nextMonth();
          } else {
            viewState.previousMonth();
          }
          cell2 = Array.from(el.querySelectorAll("[data-date]")).find((cell3) => dateFromCell2(cell3).isSameDay(focusedDate));
        }
        cell2?.querySelector("button,ui-button")?.focus();
      }
    });
    return { enable() {
      blocked = false;
    }, disable() {
      blocked = true;
    } };
  }
  function dateFromCell2(cell) {
    if (!cell) return null;
    if (!cell.hasAttribute("data-date")) return null;
    return DateValue.fromIsoDateString(cell.getAttribute("data-date"));
  }
  function responsiveMonthsAttributeValue(el, configEl) {
    let months = parseInt(configEl.getAttribute("sm:months") || configEl.getAttribute("months") || 1);
    let callback;
    let media = matchMedia("(min-width: 640px)");
    let onChange = () => callback?.(media.matches ? months : 1);
    media.addEventListener("change", onChange);
    el.onUnmount(() => media.removeEventListener("change", onChange));
    return [media.matches ? months : 1, (fn) => callback = fn];
  }
  function syncInputStateWithSingleSelectionState(input, subject) {
    preventInputEventsFromBubblingToSelectRoot2(input);
    let detangled = detangle();
    let sync = detangled(() => {
      let selected = subject.selectable.getValue();
      input.value = selected || "";
    });
    subject.observable.subscribe(ObservableTrigger2.SELECTION, sync);
    sync();
    input.addEventListener("change", detangled(() => {
      let date = input.valueAsDate;
      if (date) {
        subject.selectable.setValue(
          DateValue.fromLocalDate(date).toIsoDateString()
        );
        subject.viewState.setDate(DateValue.fromLocalDate(date));
      } else {
        subject.selectable.setValue("");
      }
    }));
  }
  function syncInputStateWithRangeSelectionState(input, secondInput, subject) {
    preventInputEventsFromBubblingToSelectRoot2(input);
    preventInputEventsFromBubblingToSelectRoot2(secondInput);
    let detangled = detangle();
    let setInputsFromValue = detangled(() => {
      let { start, end } = subject.selectable.getValue() || {};
      input.value = start || "";
      secondInput.value = end || "";
    });
    let setValueFromInputs = detangled(() => {
      let start = input.valueAsDate;
      let end = secondInput.valueAsDate;
      if (start && end) {
        subject.selectable.setValue(
          { start: DateValue.fromLocalDate(start).toIsoDateString(), end: DateValue.fromLocalDate(end).toIsoDateString() }
        );
      } else if (start) {
        subject.selectable.setValue(
          { start: DateValue.fromLocalDate(start).toIsoDateString(), end: null }
        );
      } else {
        subject.selectable.setValue(
          { start: null, end: null }
        );
      }
    });
    subject.observable.subscribe(ObservableTrigger2.SELECTION, setInputsFromValue);
    setInputsFromValue();
    input.addEventListener("change", setValueFromInputs);
    secondInput.addEventListener("change", setValueFromInputs);
  }
  function preventInputEventsFromBubblingToSelectRoot2(input) {
    on(input, "change", (e) => e.stopPropagation());
    on(input, "input", (e) => e.stopPropagation());
  }

  // js/calendar/trigger.js
  var UIDatePickerTrigger = class extends UIControl {
    boot() {
      this.picker = this.closest("ui-date-picker");
      this._disableable = new Disableable(this);
      let disableClick = (e) => {
        e.preventDefault();
        e.stopImmediatePropagation();
      };
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.addEventListener("click", disableClick, true);
        } else {
          this.removeEventListener("click", disableClick, true);
        }
      });
      this.dayInput = this.querySelector("[data-flux-day-input]");
      this.monthInput = this.querySelector("[data-flux-month-input]");
      this.yearInput = this.querySelector("[data-flux-year-input]");
      if (!this.dayInput || !this.monthInput || !this.yearInput) return;
      let locale = this.picker?.getAttribute("locale") || navigator.language;
      let { order: inputOrder, separator, placeholders } = getLocaleInfo(locale);
      this.dayInput.setAttribute("placeholder", placeholders.day);
      this.monthInput.setAttribute("placeholder", placeholders.month);
      this.yearInput.setAttribute("placeholder", placeholders.year);
      this.inputs = inputOrder.map((type) => {
        if (type === "day") return this.dayInput;
        if (type === "month") return this.monthInput;
        if (type === "year") return this.yearInput;
      });
      let container = this.querySelector("[data-flux-date-inputs]");
      if (container) {
        while (container.firstChild) {
          container.removeChild(container.firstChild);
        }
        this.inputs.forEach((input, i) => {
          container.appendChild(input);
          if (i < this.inputs.length - 1) {
            let separatorEl = document.createElement("span");
            separatorEl.textContent = ` ${separator} `;
            separatorEl.style.userSelect = "none";
            container.appendChild(separatorEl);
          }
        });
      }
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.dayInput.setAttribute("disabled", "");
          this.monthInput.setAttribute("disabled", "");
          this.yearInput.setAttribute("disabled", "");
        } else {
          this.dayInput.removeAttribute("disabled");
          this.monthInput.removeAttribute("disabled");
          this.yearInput.removeAttribute("disabled");
        }
      });
      this.clearInputs();
      initCursorSelectionListeners2(this.dayInput);
      initCursorSelectionListeners2(this.monthInput);
      initCursorSelectionListeners2(this.yearInput);
      this.inputs.forEach((input, i) => {
        input.addEventListener("keydown", (e) => {
          if (e.key === "ArrowRight" && i < this.inputs.length - 1) {
            return this.inputs[i + 1].focus();
          }
          if (e.key === "ArrowLeft" && i > 0) {
            return this.inputs[i - 1].focus();
          }
          if (e.key === " " && !this.hasAttribute("data-bare")) {
            e.preventDefault();
            this.picker?._dialogable?.toggle();
          }
        });
      });
      blockDefaultInputs2(this.dayInput);
      blockDefaultInputs2(this.monthInput);
      blockDefaultInputs2(this.yearInput);
      this.initTwoDigitInputListeners(this.dayInput, {
        max: () => this.maxDay(),
        advanceThreshold: 3,
        handleSecondDigit: (existing, digit) => {
          if (existing === 3 && digit <= 1) return `${existing}${digit}`;
          if (existing <= 2) {
            let v = `${existing}${digit}`;
            return v === "00" ? "01" : v;
          }
          return null;
        }
      });
      this.initTwoDigitInputListeners(this.monthInput, {
        max: 12,
        advanceThreshold: 1,
        handleSecondDigit: (existing, digit) => {
          if (existing === 1 && digit <= 2) return `${existing}${digit}`;
          if (existing === 0) return digit === 0 ? "01" : `0${digit}`;
          return null;
        },
        onValueChange: () => this.clampDay()
      });
      this.initYearInputListeners();
    }
    connectToPicker(picker, rangeRole = null) {
      this.picker = picker;
      this.rangeRole = rangeRole;
      queueMicrotask(() => {
        let pickerLabelledBy = this.picker.getAttribute("aria-labelledby");
        let triggerLabelledBy = this.getAttribute("aria-labelledby");
        if (pickerLabelledBy && triggerLabelledBy && pickerLabelledBy !== triggerLabelledBy) {
          setAttribute2(this, "aria-labelledby", `${pickerLabelledBy} ${triggerLabelledBy}`);
        }
      });
      this.picker.observable.subscribe(ObservableTrigger2.SELECTION, () => {
        this.updateInputsFromSelectable(this.picker.selectable);
      });
      this.updateInputsFromSelectable(this.picker.selectable);
      this.addEventListener("mousedown", (event) => {
        if (!event.target.closest("input")) {
          event.preventDefault();
        }
      });
      if (!this.hasAttribute("data-bare")) {
        this.addEventListener("click", () => {
          this.picker._dialogable.toggle();
        });
      }
    }
    focus() {
      if (this.inputs?.length) return this.inputs[0].focus();
      this.querySelector("button,ui-button")?.focus();
    }
    processDate() {
      let day = this.dayInput.value;
      let month = this.monthInput.value;
      let year = this.yearInput.value;
      if (day === "" || month === "" || year === "") return;
      let dayNum = parseInt(day);
      let monthNum = parseInt(month);
      let yearNum = parseInt(year);
      if (isNaN(dayNum) || isNaN(monthNum) || isNaN(yearNum)) return;
      if (monthNum < 1 || monthNum > 12) return;
      if (dayNum < 1) return;
      if (yearNum < 100) return;
      let maxDay = new Date(yearNum, monthNum, 0).getDate();
      if (dayNum > maxDay) {
        dayNum = maxDay;
        this.dayInput.value = dayNum.toString().padStart(2, "0");
      }
      let isoDate = `${yearNum.toString().padStart(4, "0")}-${monthNum.toString().padStart(2, "0")}-${dayNum.toString().padStart(2, "0")}`;
      if (this.rangeRole) {
        this.picker._rangeTriggerDates = this.picker._rangeTriggerDates || { start: null, end: null };
        this.picker._rangeTriggerDates[this.rangeRole] = isoDate;
        let { start, end } = this.picker._rangeTriggerDates;
        if (start && end) {
          this.picker.selectable.setValue({ start, end });
        } else if (start) {
          this.picker.selectable.setValue({ start, end: null });
        } else {
          this.picker.selectable.setValue({ start: null, end: null });
        }
      } else {
        this.picker.selectable.setValue(isoDate);
      }
      this.picker.viewState.setDate(DateValue.fromIsoDateString(isoDate));
    }
    updateInputsFromSelectable(selectable) {
      let value3 = selectable.getValue();
      if (this.rangeRole && value3 && typeof value3 === "object") {
        this.picker._rangeTriggerDates = this.picker._rangeTriggerDates || { start: null, end: null };
        this.picker._rangeTriggerDates[this.rangeRole] = value3[this.rangeRole] || null;
        this.updateInputs(value3[this.rangeRole] || null);
        return;
      }
      this.updateInputs(value3);
    }
    updateInputs(isoDate) {
      if (!isoDate) return this.clearInputs();
      removeAttribute(this.picker, "data-empty");
      let [year, month, day] = isoDate.split("T")[0].split("-");
      this.dayInput.value = day.padStart(2, "0");
      this.dayInput.style.color = "";
      this.dayInput.classList.remove("font-mono");
      this.monthInput.value = month.padStart(2, "0");
      this.monthInput.style.color = "";
      this.monthInput.classList.remove("font-mono");
      this.yearInput.value = year.padStart(4, "0");
      this.yearInput.style.color = "";
      this.yearInput.classList.remove("font-mono");
    }
    clearInputs() {
      this.dayInput.value = "";
      this.dayInput.style.color = "";
      this.dayInput.classList.add("font-mono");
      this.monthInput.value = "";
      this.monthInput.style.color = "";
      this.monthInput.classList.add("font-mono");
      this.yearInput.value = "";
      this.yearInput.style.color = "";
      this.yearInput.classList.add("font-mono");
      let focused = this.inputs.find((s) => s === document.activeElement);
      if (focused) highlightInputContents2(focused);
      setAttribute2(this.picker, "data-empty", "");
    }
    maxDay() {
      let month = parseInt(this.monthInput.value);
      let year = parseInt(this.yearInput.value);
      if (isNaN(month) || month < 1 || month > 12) return 31;
      if (isNaN(year) || year < 1) year = 2e3;
      return new Date(year, month, 0).getDate();
    }
    clampDay() {
      let day = parseInt(this.dayInput.value);
      if (isNaN(day)) return;
      let max2 = this.maxDay();
      if (day > max2) {
        this.dayInput.value = max2.toString().padStart(2, "0");
      }
    }
    initTwoDigitInputListeners(input, { max: max2, advanceThreshold, handleSecondDigit, onValueChange }) {
      let isEditing = false;
      input.addEventListener("keydown", (e) => {
        if (e.key === "Backspace") {
          if (input.value === "" || input.value === input.placeholder) {
            let idx = this.inputs.indexOf(input);
            if (idx > 0) {
              this.inputs[idx - 1].focus();
            }
            return;
          }
          input.value = "";
          highlightInputContents2(input);
          return;
        }
        if (!["ArrowUp", "ArrowDown"].includes(e.key)) return;
        e.preventDefault();
        e.stopPropagation();
        let number = parseInt(input.value);
        let maxVal = typeof max2 === "function" ? max2() : max2;
        if (e.key === "ArrowUp") {
          if (isNaN(number) || number >= maxVal) {
            number = 1;
          } else {
            number++;
          }
        } else if (e.key === "ArrowDown") {
          if (isNaN(number) || number === 1) {
            number = maxVal;
          } else {
            number--;
          }
        }
        input.value = number.toString().padStart(2, "0");
        input.style.color = "";
        input.classList.remove("font-mono");
        onValueChange?.();
        this.processDate();
        highlightInputContents2(input);
      });
      let nextInput = this.inputs[this.inputs.indexOf(input) + 1];
      input.addEventListener("keydown", (e) => {
        if (!/[0-9]/.test(e.key)) return;
        let digit = parseInt(e.key);
        if (input.value === "" || !isEditing) {
          isEditing = true;
          input.value = `0${digit}`;
          onValueChange?.();
          digit > advanceThreshold ? nextInput?.focus() : highlightInputContents2(input);
          return;
        }
        let existing = parseInt(input.value);
        let completed = handleSecondDigit(existing, digit);
        if (completed) {
          input.value = completed;
          onValueChange?.();
          nextInput?.focus();
          return;
        }
        input.value = `0${digit}`;
        onValueChange?.();
        digit > advanceThreshold ? nextInput?.focus() : highlightInputContents2(input);
      });
      input.addEventListener("blur", () => {
        isEditing = false;
        if (input.value === "00") {
          input.value = "01";
        }
        onValueChange?.();
        this.processDate();
      });
    }
    initYearInputListeners() {
      let buffer = "";
      this.yearInput.addEventListener("keydown", (e) => {
        if (e.key === "Backspace") {
          buffer = "";
          if (this.yearInput.value === "" || this.yearInput.value === this.yearInput.placeholder) {
            let idx = this.inputs.indexOf(this.yearInput);
            if (idx > 0) {
              this.inputs[idx - 1].focus();
            }
            return;
          }
          this.yearInput.value = "";
          highlightInputContents2(this.yearInput);
          return;
        }
        if (!["ArrowUp", "ArrowDown"].includes(e.key)) return;
        e.preventDefault();
        e.stopPropagation();
        let number = parseInt(this.yearInput.value);
        if (e.key === "ArrowUp") {
          if (isNaN(number)) {
            number = (/* @__PURE__ */ new Date()).getFullYear();
          } else {
            number++;
          }
        } else if (e.key === "ArrowDown") {
          if (isNaN(number)) {
            number = (/* @__PURE__ */ new Date()).getFullYear();
          } else if (number > 1) {
            number--;
          }
        }
        this.yearInput.value = number.toString().padStart(4, "0");
        this.yearInput.style.color = "";
        this.yearInput.classList.remove("font-mono");
        buffer = "";
        this.clampDay();
        this.processDate();
        highlightInputContents2(this.yearInput);
      });
      let nextInput = this.inputs[this.inputs.indexOf(this.yearInput) + 1];
      this.yearInput.addEventListener("keydown", (e) => {
        if (!/[0-9]/.test(e.key)) return;
        let number = parseInt(e.key);
        buffer += number.toString();
        if (buffer.length >= 4) {
          this.yearInput.value = buffer.slice(-4) === "0000" ? "0001" : buffer.slice(-4);
          buffer = "";
          this.clampDay();
          this.processDate();
          if (nextInput) {
            nextInput.focus();
          } else {
            highlightInputContents2(this.yearInput);
          }
          return;
        }
        this.yearInput.value = buffer.padStart(4, "0");
        highlightInputContents2(this.yearInput);
      });
      this.yearInput.addEventListener("blur", () => {
        buffer = "";
        if (this.yearInput.value === "0000") {
          this.yearInput.value = "0001";
        }
        this.clampDay();
        this.processDate();
      });
    }
  };
  function getLocaleInfo(locale) {
    try {
      let formatter = new Intl.DateTimeFormat(locale, {
        year: "numeric",
        month: "2-digit",
        day: "2-digit"
      });
      let parts = formatter.formatToParts(new Date(2024, 2, 15));
      let order = parts.filter((p) => ["day", "month", "year"].includes(p.type)).map((p) => p.type);
      let literal = parts.find((p) => p.type === "literal");
      let separator = literal?.value?.trim() || "/";
      let placeholders = { day: "dd", month: "mm", year: "yyyy" };
      parts.forEach((p) => {
        if (p.type === "day") placeholders.day = p.value.replace(/\d/g, "d");
        if (p.type === "month") placeholders.month = p.value.replace(/\d/g, "m");
        if (p.type === "year") placeholders.year = p.value.replace(/\d/g, "y");
      });
      return { order, separator, placeholders };
    } catch {
      return {
        order: ["day", "month", "year"],
        separator: "/",
        placeholders: { day: "dd", month: "mm", year: "yyyy" }
      };
    }
  }
  function initCursorSelectionListeners2(input) {
    input.addEventListener("focus", () => {
      highlightInputContents2(input);
    });
    input.addEventListener("blur", () => {
      clearPlaceholderValue2(input);
    });
    input.addEventListener("mousedown", (e) => {
      e.preventDefault();
      input.focus();
      highlightInputContents2(input);
    });
    input.addEventListener("mouseup", (e) => {
      e.preventDefault();
      highlightInputContents2(input);
    });
  }
  function blockDefaultInputs2(input) {
    input.addEventListener("keydown", (e) => {
      if (/[0-9]/.test(e.key)) {
        clearPlaceholderValue2(input);
        input.classList.remove("font-mono");
      }
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      if (["Tab"].includes(e.key)) return;
      e.preventDefault();
    });
  }
  function highlightInputContents2(input) {
    if (!input.value && input.placeholder) {
      input.style.color = getComputedStyle(input, "::placeholder").color;
      input.value = input.placeholder;
      input.classList.add("font-mono");
    }
    input.setSelectionRange(0, input.value.length);
  }
  function clearPlaceholderValue2(input) {
    if (input.value === input.placeholder) {
      input.value = "";
      input.style.color = "";
    }
  }

  // js/calendar/picker.js
  var UIDatePicker = class extends UIControl {
    bootWithCalendar(calendar) {
      let isRange = calendar.config.mode === CalendarModes.RANGE;
      this.calendar = calendar;
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        this.calendar.disabled = disabled;
      });
      this.observable = new Observable2();
      this.selectable = new DeferredSelectable(this.calendar.selectable, this.observable);
      this.viewState = this.calendar.viewState;
      this.querySelector("ui-selected-date")?.bootWithCalendar();
      this.querySelector("ui-date-picker-select")?.bootWithCalendar();
      let dropdownEnabled = !(this.hasAttribute("dropdown") && this.getAttribute("dropdown") === "false");
      let popoverEl = this.querySelector("dialog");
      if (!popoverEl) return;
      let dateFieldEls = Array.from(this.querySelectorAll("ui-date-picker-trigger")).filter((el) => !popoverEl?.contains(el));
      let dateFieldEl = dateFieldEls[0] || null;
      let secondDateFieldEl = dateFieldEls[1] || null;
      let inputEl = dateFieldEl ? null : this.querySelector("input");
      inputEl = popoverEl?.contains(inputEl) ? null : inputEl;
      let secondInputEl = dateFieldEl ? null : Array.from(this.querySelectorAll("input")).find((i) => i !== inputEl);
      secondInputEl = popoverEl?.contains(secondInputEl) ? null : secondInputEl;
      if (!dateFieldEl) {
        inputEl && inputEl.addEventListener("click", (e) => e.preventDefault());
        secondInputEl && secondInputEl.addEventListener("click", (e) => e.preventDefault());
      }
      let buttonEl = this.querySelector("button,ui-button");
      buttonEl = popoverEl?.contains(buttonEl) ? null : buttonEl;
      let calendarId = assignId(this.calendar, "calendar");
      this.triggerEl = dateFieldEl || inputEl || buttonEl;
      if (!this.triggerEl) return;
      if (dateFieldEl) {
        setAttribute2(this.triggerEl, "role", "group");
      } else {
        setAttribute2(this.triggerEl, "role", "combobox");
      }
      setAttribute2(this.triggerEl, "aria-controls", calendarId);
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) setAttribute2(this.triggerEl, "disabled", "");
        else removeAttribute(this.triggerEl, "disabled");
      });
      if (dropdownEnabled) {
        this._dialogable = new Dialogable(popoverEl, {
          clickOutside: !this.hasAttribute("disable-click-outside"),
          triggers: dateFieldEl ? [buttonEl].filter(Boolean) : [buttonEl, inputEl, secondInputEl].filter(Boolean)
        });
        this._closeable = new Closeable(this);
        this._closeable.onClose(() => this._dialogable.hide());
        this._anchorable = new Anchorable(popoverEl, {
          reference: this.triggerEl,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
        });
        if (isIOS() && !dateFieldEl) {
          inputEl && this.showIOSOverlay(inputEl);
          secondInputEl && this.showIOSOverlay(secondInputEl);
        }
        this.querySelectorAll("button,ui-button").forEach((button) => {
          if (button === this.triggerEl) return;
          if (popoverEl.contains(button)) return;
          setAttribute2(button, "aria-controls", calendarId);
          setAttribute2(button, "aria-haspopup", "combobox");
          linkExpandedStateToPopover3(button, this._dialogable);
          on(button, "click", () => this._dialogable.toggle());
        });
        initPopover3(this, this.triggerEl, popoverEl, this._dialogable, this._anchorable);
        if (!dateFieldEl) {
          linkExpandedStateToPopover3(this.triggerEl, this._dialogable);
        }
        preventScrollWhenPopoverIsOpen3(this, this._dialogable);
        if (!dateFieldEl) {
          controlPopoverWithKeyboard2(this.triggerEl, this._dialogable);
        }
        handlePopoverClosing3(this, this.calendar.config.mode, this.observable, this.selectable, this._dialogable);
        let bareTriggerEls = Array.from(popoverEl.querySelectorAll("ui-date-picker-trigger[data-bare]"));
        if (bareTriggerEls.length === 2 && isRange) {
          bareTriggerEls[0].connectToPicker(this, "start");
          bareTriggerEls[1].connectToPicker(this, "end");
        } else if (bareTriggerEls.length === 1) {
          bareTriggerEls[0].connectToPicker(this);
        }
      }
      if (dateFieldEl && secondDateFieldEl && isRange) {
        dateFieldEl.connectToPicker(this, "start");
        secondDateFieldEl.connectToPicker(this, "end");
      } else if (dateFieldEl) {
        dateFieldEl.connectToPicker(this);
      } else if (isRange && popoverEl && inputEl && secondInputEl) {
        highlightInputContentsWhenFocused2(inputEl);
        highlightInputContentsWhenFocused2(secondInputEl);
        syncInputStateWithRangeSelectionState(inputEl, secondInputEl, this);
      } else if (popoverEl && inputEl) {
        let input = inputEl;
        highlightInputContentsWhenFocused2(input);
        syncInputStateWithSingleSelectionState(input, this);
      } else if (popoverEl) {
        let button = buttonEl;
        togglePopoverWithMouse2(button, this._dialogable);
      }
      this._controllable = new Controllable(this, { bubbles: true });
      this.calendar.addEventListener("change", (e) => e.stopPropagation());
      this.calendar.addEventListener("input", (e) => e.stopPropagation());
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.selectable.getValue()
      });
      let detangled = detangle();
      this._controllable.initial((initial) => initial && this.selectable.setValue(initial));
      this._controllable.getter(() => this.selectable.getValue());
      this._controllable.setter(detangled((value3) => this.selectable.setValue(value3)));
      this.observable.subscribe(ObservableTrigger2.SELECTION, detangled(() => {
        this.dispatchEvent(new Event("select", { bubbles: false, cancelable: true }));
        this._controllable.dispatch();
      }));
      this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
        this._submittable.update(this.selectable.getValue());
      });
      this.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
        this.dispatchEvent(new Event("navigate", { bubbles: false, cancelable: true }));
      });
    }
    unmount() {
      if (this._dialogable?.getState()) {
        let { unlock } = lockScroll();
        unlock();
      }
    }
    showIOSOverlay(inputEl) {
      let overlay = document.createElement("button");
      overlay.setAttribute("data-flux-ios-overlay", "");
      overlay.setAttribute("data-appended", "");
      overlay.classList.add("absolute", "inset-0");
      inputEl.after(overlay);
    }
    input() {
      return this.querySelector("input");
    }
    clear() {
      this.selectable.setValue(null);
      this.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
    }
    open() {
      this._dialogable.setState(true);
    }
    close() {
      this._dialogable.setState(false);
    }
    trigger() {
      return this.triggerEl;
    }
  };
  var UISelectedDate = class extends UIElement {
    bootWithCalendar() {
      this.picker = this.closest("ui-date-picker");
      this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
      if (!this.querySelector('template[name="date"]')) {
        let template = document.createElement("template");
        template.setAttribute("name", "date");
        template.innerHTML = "<div><slot></slot></div>";
        this.appendChild(template);
      }
      this.templates = {
        placeholder: this.querySelector('template[name="placeholder"]'),
        date: this.querySelector('template[name="date"]')
      };
      this.picker.observable.subscribe(ObservableTrigger2.SELECTION, () => {
        this.render(this.picker);
      });
      this.render(this.picker);
    }
    render(picker) {
      this.templates.placeholder?.clearPlaceholder?.();
      this.templates.date?.clearDate?.();
      if (picker.selectable.hasSelection()) {
        let { cleanup } = renderTemplate(this.templates.date, (hydrate) => {
          return hydrate({ slots: { default: picker.selectable.display(this.picker.calendar.config.locale) } });
        });
        this.templates.date.clearDate = cleanup;
      } else {
        if (!this.templates.placeholder) return;
        let { cleanup } = renderTemplate(this.templates.placeholder, (hydrate) => {
          return hydrate({ slots: {} });
        });
        this.templates.placeholder.clearPlaceholder = cleanup;
      }
    }
  };
  var UIDatePickerSelect = class extends UIElement {
    bootWithCalendar() {
      this.picker = this.closest("ui-date-picker");
      this.picker.selectable.blockSyncIf(() => {
        return this.getBoundingClientRect().width > 0;
      });
      initFauxButton(this, () => this.picker._disableable.isDisabled(), () => {
        this.picker.selectable.sync();
        this.picker._dialogable.setState(false);
      });
    }
  };
  element("date-picker", UIDatePicker);
  element("date-picker-select", UIDatePickerSelect);
  element("selected-date", UISelectedDate);
  element("date-picker-trigger", UIDatePickerTrigger);
  inject(({ css }) => css`
/* For Chrome, Safari, Edge */
ui-date-picker input[type="date"]::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
`);
  function linkExpandedStateToPopover3(el, dialogable) {
    setAttribute2(el, "aria-haspopup", "listbox");
    let refreshPopover = () => {
      setAttribute2(el, "aria-expanded", dialogable.getState() ? "true" : "false");
      dialogable.getState() ? setAttribute2(el, "data-open", "") : removeAttribute(el, "data-open", "");
    };
    dialogable.onChange(() => {
      refreshPopover();
    });
    refreshPopover();
  }
  function initPopover3(root, trigger, popover, dialogable, anchorable) {
    let refreshPopover = () => {
      Array.from([root, popover]).forEach((i) => {
        dialogable.getState() ? setAttribute2(i, "data-open", "") : removeAttribute(i, "data-open", "");
      });
      dialogable.getState() && anchorable.reposition();
    };
    dialogable.onChange(() => refreshPopover());
    refreshPopover();
    dialogable.onChange(() => {
      if (dialogable.getState()) {
        root.dispatchEvent(new Event("open", {
          bubbles: false,
          cancelable: false
        }));
      } else {
        root.dispatchEvent(new Event("close", {
          bubbles: false,
          cancelable: false
        }));
      }
    });
  }
  function controlPopoverWithKeyboard2(button, dialogable) {
    on(button, "keydown", (e) => {
      if (!["ArrowDown", "ArrowUp", "Escape"].includes(e.key)) return;
      if (e.key === "ArrowDown") {
        if (!dialogable.getState()) {
          dialogable.setState(true);
          e.preventDefault();
          e.stopImmediatePropagation();
        }
      } else if (e.key === "ArrowUp") {
        if (!dialogable.getState()) {
          dialogable.setState(true);
          e.preventDefault();
          e.stopImmediatePropagation();
        }
      } else if (e.key === "Escape") {
        if (dialogable.getState()) {
          dialogable.setState(false);
        }
      }
    });
  }
  function togglePopoverWithMouse2(button, dialogable) {
    on(button, "click", () => {
      dialogable.setState(!dialogable.getState());
    });
  }
  function highlightInputContentsWhenFocused2(input) {
    on(input, "focus", () => input.select());
  }
  function preventScrollWhenPopoverIsOpen3(root, dialogable) {
    let { lock, unlock } = lockScroll(dialogable.el);
    dialogable.onChange(() => {
      dialogable.getState() ? lock() : unlock();
    });
  }
  function handlePopoverClosing3(root, mode, observable, selectable, dialogable) {
    let closeOnSelect = mode !== CalendarModes.MULTIPLE;
    if (root.hasAttribute("close")) {
      let close = root.getAttribute("close");
      closeOnSelect = close.split(" ").includes("auto");
      if (close === "manual") closeOnSelect = false;
    }
    if (!closeOnSelect) return;
    observable.subscribe(ObservableTrigger2.SELECTION, () => {
      if (selectable.hasSelection()) {
        dialogable.setState(false);
      }
    });
  }

  // js/composer.js
  var UIComposer = class extends UIControl {
    boot() {
      setAttribute2(this, "role", "group");
      this.config = {
        submit: this.getAttribute("submit") || "cmd-enter",
        rows: Number(this.getAttribute("rows")) || 2,
        maxRows: Number(this.getAttribute("max-rows")) || 10
      };
      let textarea = this.querySelector("textarea");
      let editor = this.querySelector("ui-editor");
      if (textarea) {
        this._inputEl = textarea;
        this._initTextarea();
      } else {
        this._inputEl = editor;
        this._initEditor();
      }
      this.state = {
        onChanges: [],
        getValue: () => {
          return this._inputEl.value;
        },
        setValue: (value3) => {
          this._inputEl.value = value3;
          this.state.onChanges.forEach((i) => i(this.state.getValue()));
        },
        onChange: (callback) => {
          this.state.onChanges.push(callback);
        }
      };
      this._controllable = new Controllable(this);
      this._disableable = new Disableable(this);
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.state.getValue()
      });
      this.state.onChange((value3) => {
        this._submittable.update(value3);
      });
      this._controllable.initial((initial) => initial && this.state.setValue(initial));
      this._controllable.getter(() => this.state.getValue());
      this._controllable.setter((v) => this.state.setValue(v));
      let undoDisableActions = () => {
      };
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          undoDisableActions = this._disableActions();
          setAttribute2(this._inputEl, "disabled", "");
        } else {
          undoDisableActions();
          undoDisableActions = () => {
          };
          removeAttribute(this._inputEl, "disabled");
        }
      });
      on(this._inputEl, "input", (e) => {
        this._controllable.dispatch();
        e.stopPropagation();
      });
    }
    _resizeTextarea() {
      setAttribute2(this._inputEl, "style", "height: auto");
      let style = window.getComputedStyle(this._inputEl);
      let height = this._inputEl.scrollHeight;
      let lineHeight = parseFloat(style.lineHeight);
      if (isNaN(lineHeight)) {
        lineHeight = parseFloat(style.fontSize) * 1.2;
      }
      let borderTop = parseFloat(style.borderTopWidth);
      let borderBottom = parseFloat(style.borderBottomWidth);
      let paddingTop = parseFloat(style.paddingTop);
      let paddingBottom = parseFloat(style.paddingBottom);
      let minHeight = this.config.rows * lineHeight;
      let maxHeight = this.config.maxRows * lineHeight;
      if (style.boxSizing === "border-box") {
        height += borderTop + borderBottom;
        minHeight += paddingTop + paddingBottom + borderTop + borderBottom;
        maxHeight += paddingTop + paddingBottom + borderTop + borderBottom;
      } else {
        height -= paddingTop + paddingBottom;
      }
      if (height < minHeight) height = minHeight;
      let overflow = "hidden";
      if (height > maxHeight) {
        height = maxHeight;
        overflow = "auto";
      }
      setAttribute2(this._inputEl, "style", `height: ${height}px; overflow-y: ${overflow}`);
    }
    focusInput() {
      this._inputEl.focus();
    }
    _resizeEditor() {
      let contentEl = this._inputEl.querySelector('[data-slot="content"]');
      if (!contentEl) return;
      let style = window.getComputedStyle(contentEl);
      let lineHeight = parseFloat(style.lineHeight);
      if (isNaN(lineHeight)) {
        lineHeight = parseFloat(style.fontSize) * 1.2;
      }
      let borderTop = parseFloat(style.borderTopWidth);
      let borderBottom = parseFloat(style.borderBottomWidth);
      let paddingTop = parseFloat(style.paddingTop);
      let paddingBottom = parseFloat(style.paddingBottom);
      let minHeight = this.config.rows * lineHeight;
      let maxHeight = this.config.maxRows * lineHeight;
      if (style.boxSizing === "border-box") {
        minHeight += paddingTop + paddingBottom + borderTop + borderBottom;
        maxHeight += paddingTop + paddingBottom + borderTop + borderBottom;
      }
      setAttribute2(contentEl, "style", `min-height: ${minHeight}px; max-height: ${maxHeight}px; height: auto; overflow-y: auto;`);
    }
    _initTextarea() {
      if (!this._inputEl.hasAttribute("rows")) {
        setAttribute2(this._inputEl, "rows", this.config.rows);
      }
      if (this.hasAttribute("placeholder")) {
        setAttribute2(this._inputEl, "placeholder", this.getAttribute("placeholder"));
      }
      this._inputEl.addEventListener("input", () => this._resizeTextarea());
      this._resizeTextarea();
      on(this._inputEl, "keydown", (e) => this._handleSubmitKeydown(e));
    }
    _initEditor() {
      if (this.hasAttribute("placeholder")) {
        setAttribute2(this._inputEl, "placeholder", this.getAttribute("placeholder"));
      }
      this.addEventListener("flux:editor:ready", () => {
        this._resizeEditor();
      });
      on(this._inputEl, "keydown", (e) => this._handleSubmitKeydown(e), { capture: true });
    }
    _handleSubmitKeydown(e) {
      if (["cmd-enter", "super-enter", "ctrl-enter"].includes(this.config.submit) && (e.metaKey || e.ctrlKey) && e.key === "Enter") {
        e.preventDefault();
        e.stopPropagation();
        this._submittable.submitEnclosingForm();
      } else if (this.config.submit === "enter" && e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        e.stopPropagation();
        this._submittable.submitEnclosingForm();
      } else if (this.config.submit === "shift-enter" && e.key === "Enter" && e.shiftKey) {
        e.preventDefault();
        e.stopPropagation();
        this._submittable.submitEnclosingForm();
      }
    }
    _disableActions() {
      let undos = [];
      let actionEls = this.querySelectorAll("button");
      actionEls.forEach((el) => {
        if (!el.hasAttribute("disabled")) {
          setAttribute2(el, "disabled", "");
          undos.push(() => {
            removeAndReleaseAttribute(el, "disabled");
          });
        }
      });
      return () => {
        undos.forEach((undo) => undo());
        undos = [];
      };
    }
  };
  element("composer", UIComposer);

  // js/progress.js
  var UIProgress = class extends UIElement {
    boot() {
      this._controllable = new Controllable(this);
      this._controllable.initial((initial) => initial != null && this.setValue(parseFloat(initial)));
      this._controllable.getter(() => this.currentValue);
      this._controllable.setter((v) => this.setValue(parseFloat(v)));
      setAttribute2(this, "role", "progressbar");
      setAttribute2(this, "aria-valuemin", 0);
      if (this.currentValue === void 0) {
        this.setValue(this.hasAttribute("value") ? parseFloat(this.getAttribute("value")) : 0);
      }
      new MutationObserver(() => this.updateVisual()).observe(this, {
        attributes: true,
        attributeFilter: ["max"]
      });
    }
    setValue(value3) {
      this.currentValue = value3;
      this.updateVisual();
    }
    updateVisual() {
      let max2 = this.hasAttribute("max") ? parseFloat(this.getAttribute("max")) : 100;
      let pct = Math.max(0, Math.min(100, this.currentValue / max2 * 100));
      this.style.setProperty("--flux-progress", pct);
      this.style.setProperty("--flux-progress-percentage", pct + "%");
      setAttribute2(this, "aria-valuenow", this.currentValue);
      setAttribute2(this, "aria-valuemax", max2);
    }
  };
  element("progress", UIProgress);

  // js/context.js
  var UIContext = class extends UIElement {
    boot() {
      let trigger = this.trigger();
      let overlay = this.overlay();
      this._disabled = this.hasAttribute("disabled");
      this._popoverable = new Popoverable(overlay);
      let virtualReference = {
        getBoundingClientRect: () => ({ x: 0, y: 0, width: 0, height: 0, top: 0, left: 0, right: 0, bottom: 0 })
      };
      this._anchorable = new Anchorable(overlay, {
        reference: virtualReference,
        auto: false,
        position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
        gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
        offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
      });
      let { lock, unlock } = lockScroll(this._popoverable.el);
      this._popoverable.onChange(() => {
        this._popoverable.getState() ? lock() : unlock();
      });
      on(trigger, "contextmenu", (e) => {
        e.preventDefault();
        virtualReference.getBoundingClientRect = () => ({
          x: e.clientX,
          y: e.clientY,
          width: 0,
          height: 0,
          top: e.clientY,
          left: e.clientX,
          right: e.clientX,
          bottom: e.clientY
        });
        this._anchorable.reposition();
        this._popoverable.setState(true);
        if (this.hasAttribute("detail")) {
          setAttribute2(overlay, "data-detail", this.getAttribute("detail"));
        }
      });
      if (this.hasAttribute("detail")) {
        setAttribute2(overlay, "data-detail", "");
      }
    }
    unmount() {
      if (this.overlay()?._popoverable?.getState()) {
        let { unlock } = lockScroll();
        unlock();
      }
    }
    trigger() {
      return this.firstElementChild;
    }
    overlay() {
      if (this.hasAttribute("target")) {
        let target = this.getAttribute("target");
        return document.getElementById(target);
      } else {
        return this.lastElementChild.matches("[popover]") && this.lastElementChild;
      }
    }
  };
  element("context", UIContext);

  // js/mixins/focusable.js
  var FocusableGroup = class extends MixinGroup {
    groupOfType = Focusable;
    boot({ options }) {
      options({ wrap: false, ensureTabbable: true });
    }
    mount() {
      this.options().ensureTabbable && this.ensureTabbable();
    }
    focusFirst() {
      let target;
      target = target || this.walker().find((i) => i.hasAttribute("autofocus"));
      target = target || this.walker().find((i) => i.getAttribute("tabindex") === "0");
      target = target || this.walker().find((i) => i.getAttribute("tabindex") === "-1");
      target = target || this.walker().find((i) => isFocusable3(i));
      target?.focus();
    }
    focusPrev() {
      this.moveFocus((from) => {
        return this.options().wrap ? this.walker().prevOrLast(from) : this.walker().prev(from);
      });
    }
    focusNext() {
      this.moveFocus((from) => {
        return this.options().wrap ? this.walker().nextOrFirst(from) : this.walker().next(from);
      });
    }
    focusBySearch(query) {
      let found = this.walker().find((i) => i.textContent.toLowerCase().trim().startsWith(query.toLowerCase()));
      found?.use(Focusable).tabbable();
      found?.use(Focusable).focus();
    }
    moveFocus(callback) {
      let tabbable = this.walker().find((i) => i.use(Focusable).isTabbable());
      let to = callback(tabbable);
      to?.use(Focusable).focus();
    }
    ensureTabbable() {
      this.walker().findOrFirst((el) => {
        el.use(Focusable).isTabbable();
      })?.use(Focusable).tabbable();
    }
    wipeTabbables() {
      this.walker().each((el) => {
        el.use(Focusable).untabbable();
      });
    }
    untabbleOthers(except) {
      this.walker().each((el) => {
        if (el === except) return;
        el.use(Focusable).untabbable();
      });
    }
    walker() {
      return walker(this.el, (el, { skip, reject }) => {
        if (el[this.constructor.name] && el !== this.el) return reject();
        if (!el[this.groupOfType.name]) return skip();
        if (el.hasAttribute("disabled")) return reject();
      });
    }
  };
  var Focusable = class extends Mixin {
    groupedByType = FocusableGroup;
    boot({ options }) {
      options({ hover: false, disableable: null, tabbable: false, tabbableAttr: null });
    }
    mount() {
      let disableable = this.options().disableable;
      if (!disableable) throw "Focusable requires a Disableable instance...";
      if (!this.el.hasAttribute("tabindex")) {
        this.options().tabbable ? this.tabbable() : this.untabbable();
      }
      this.pauseFocusListener = this.on("focus", disableable.enabled(() => {
        this.focus(false);
      })).pause;
      this.on("focus", disableable.enabled(() => {
        isUsingKeyboard() && setAttribute2(this.el, "data-focus", "");
      }));
      this.on("blur", disableable.enabled(() => {
        removeAttribute(this.el, "data-focus");
      }));
      this.options().hover && this.on("pointerenter", disableable.enabled(() => {
        this.group()?.untabbleOthers(this.el);
        this.tabbable();
      }));
      this.options().hover && this.on("pointerleave", disableable.enabled((e) => {
        this.untabbable();
      }));
    }
    focus(focusProgramatically = true) {
      this.group()?.untabbleOthers(this.el);
      this.tabbable();
      focusProgramatically && this.pauseFocusListener(() => {
        this.el.focus({ focusVisible: false });
      });
    }
    tabbable() {
      setAttribute2(this.el, "tabindex", "0");
      this.options().tabbableAttr && setAttribute2(this.el, this.options().tabbableAttr, "");
    }
    untabbable() {
      setAttribute2(this.el, "tabindex", "-1");
      this.options().tabbableAttr && removeAttribute(this.el, this.options().tabbableAttr);
    }
    isTabbable() {
      return this.el.getAttribute("tabindex") === "0";
    }
  };

  // js/menu.js
  var UIMenu = class _UIMenu extends UIElement {
    boot() {
      this._focusable = new FocusableGroup(this, { wrap: false, ensureTabbable: false });
      on(this, "keydown", (e) => {
        if (["ArrowDown"].includes(e.key)) {
          e.target === this ? this._focusable.focusFirst() : this._focusable.focusNext();
          e.preventDefault();
          e.stopPropagation();
        } else if (["ArrowUp"].includes(e.key)) {
          e.target === this ? this._focusable.focusFirst() : this._focusable.focusPrev();
          e.preventDefault();
          e.stopPropagation();
        }
      });
      search(this, (query) => this._focusable.focusBySearch(query));
      if (this.hasAttribute("popover")) {
        this.addEventListener("lofi-close-popovers", (e) => {
          if (this.hasAttribute("keep-open")) return;
          if (e.detail?.immediate) {
            this.hidePopover();
          } else {
            setTimeout(() => this.hidePopover(), 50);
          }
        });
      }
      if (this.parentElement.localName === "ui-dropdown") {
        let dropdown = this.parentElement;
        on(dropdown.trigger(), "keydown", (e) => {
          if (e.key === "ArrowDown") {
            this.fromArrowDown = true;
            this.showPopover();
            e.preventDefault();
            e.stopPropagation();
          }
        });
      }
      setAttribute2(this, "role", "menu");
      setAttribute2(this, "tabindex", "-1");
    }
    mount() {
      this.initializeMenuItems();
      let observer = new MutationObserver((mutations) => {
        this.initializeMenuItems();
      });
      observer.observe(this, { childList: true, subtree: true });
    }
    onPopoverShow() {
      requestAnimationFrame(() => {
        if (this.fromArrowDown) {
          this._focusable.focusFirst();
          this.fromArrowDown = false;
        } else {
          this.focus();
        }
      });
    }
    onPopoverHide() {
      this._focusable.wipeTabbables();
    }
    initializeMenuItems() {
      this.walker().each((el) => {
        if (el._disableable) return;
        initializeMenuItem(el);
      });
    }
    walker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UIMenu) return reject();
        if (el instanceof UISelect) return reject();
        if (!["a", "button"].includes(el.localName)) return skip();
      });
    }
  };
  var UISubmenu = class extends UIElement {
    boot() {
    }
  };
  var UIMenuCheckbox = class extends UIElement {
    boot() {
      let button = this;
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(button, "aria-disabled", "true");
        } else {
          removeAttribute(button, "aria-disabled");
        }
      });
      assignId(button, "menu-checkbox");
      setAttribute2(button, "role", "menuitemcheckbox");
      button._focusable = new Focusable(button, { disableable: this._disableable, hover: true, tabbableAttr: "data-active" });
      button._selectable = new Selectable(button, {
        toggleable: true,
        value: this.hasAttribute("value") ? this.getAttribute("value") : button.textContent.trim(),
        label: this.hasAttribute("label") ? this.getAttribute("label") : button.textContent.trim(),
        dataAttr: "data-checked",
        ariaAttr: "aria-checked",
        selectedInitially: this.hasAttribute("checked")
      });
      this._controllable = new Controllable(this);
      this._controllable.initial((initial) => initial && button._selectable.setState(initial));
      this._controllable.getter(() => button._selectable.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(detangled(() => {
        this._controllable.dispatch();
      }));
      on(button, "click", this._disableable.enabled(() => {
        if (!this.hasAttribute("keep-open")) {
          this.dispatchEvent(new CustomEvent("lofi-close-popovers", { bubbles: true }));
        }
        button._selectable.press();
      }));
      respondToKeyboardClick(button);
    }
  };
  var UIMenuRadio = class extends UIElement {
    boot() {
      let button = this;
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(button, "aria-disabled", "true");
        } else {
          removeAttribute(button, "aria-disabled");
        }
      });
      assignId(button, "menu-radio");
      setAttribute2(button, "role", "menuitemradio");
      button._focusable = new Focusable(button, { disableable: this._disableable, hover: true, tabbableAttr: "data-active" });
      button._selectable = new Selectable(button, {
        toggleable: false,
        value: this.hasAttribute("value") ? this.getAttribute("value") : button.textContent.trim(),
        label: this.hasAttribute("label") ? this.getAttribute("label") : button.textContent.trim(),
        dataAttr: "data-checked",
        ariaAttr: "aria-checked",
        selectedInitially: this.hasAttribute("checked")
      });
      on(button, "click", this._disableable.enabled(() => {
        if (!this.hasAttribute("keep-open")) {
          this.dispatchEvent(new CustomEvent("lofi-close-popovers", { bubbles: true }));
        }
        button._selectable.press();
      }));
      respondToKeyboardClick(button);
    }
  };
  var UIMenuRadioGroup = class extends UIElement {
    boot() {
      this._selectable = new SelectableGroup(this);
      this._controllable = new Controllable(this);
      setAttribute2(this, "role", "group");
      this._controllable.initial((initial) => initial && this._selectable.setState(initial));
      this._controllable.getter(() => this._selectable.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(detangled(() => {
        this._controllable.dispatch();
      }));
      on(this, "lofi-close-popovers", (e) => {
        if (this.hasAttribute("keep-open")) {
          e.preventDefault();
          e.stopPropagation();
        }
      });
    }
  };
  var UIMenuCheckboxGroup = class extends UIElement {
    boot() {
      this._selectable = new SelectableGroup(this, { multiple: true });
      this._controllable = new Controllable(this);
      setAttribute2(this, "role", "group");
      this._controllable.initial((initial) => initial && this._selectable.setState(initial));
      this._controllable.getter(() => this._selectable.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(detangled(() => {
        this._controllable.dispatch();
      }));
    }
  };
  element("menu", UIMenu);
  element("submenu", UISubmenu);
  element("menu-checkbox", UIMenuCheckbox);
  element("menu-radio", UIMenuRadio);
  element("menu-radio-group", UIMenuRadioGroup);
  element("menu-checkbox-group", UIMenuCheckboxGroup);
  function respondToKeyboardClick(el) {
    on(el, "keydown", (e) => {
      if (e.key === "Enter") {
        el.click();
        e.preventDefault();
        e.stopPropagation();
      }
    });
    on(el, "keydown", (e) => {
      if (e.key === " ") {
        e.preventDefault();
        e.stopPropagation();
      }
    });
    on(el, "keyup", (e) => {
      if (e.key === " ") {
        el.click();
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }
  function initializeMenuItem(el) {
    let link = el.querySelector("a");
    let button = el;
    let submenu = el.parentElement.matches("ui-submenu") && el.parentElement.querySelector("ui-menu[popover]");
    let target = link || button;
    el._disableable = new Disableable(el);
    el._disableable.onInitAndChange((disabled) => {
      if (disabled) {
        setAttribute2(target, "aria-disabled", "true");
      } else {
        removeAttribute(target, "aria-disabled");
      }
    });
    assignId(target, "menu-item");
    setAttribute2(target, "role", "menuitem");
    target._focusable = new Focusable(target, { disableable: el._disableable, hover: true, tabbableAttr: "data-active" });
    if (!submenu) {
      let handler = el._disableable.enabled(() => {
        if (!el.hasAttribute("keep-open")) {
          el.dispatchEvent(new CustomEvent("lofi-close-popovers", { bubbles: true, detail: { immediate: true } }));
        }
      });
      on(el, "mouseup", handler);
      on(el, "click", (e) => !e.isTrusted && handler());
      respondToKeyboardClick(button);
    } else {
      submenu._popoverable = new Popoverable(submenu, { triggers: [button] });
      submenu._anchorable = new Anchorable(submenu, {
        reference: button,
        position: submenu.hasAttribute("position") ? submenu.getAttribute("position") : isRTL(submenu) ? "left start" : "right start",
        gap: submenu.hasAttribute("gap") ? submenu.getAttribute("gap") : "-5",
        crossAxis: true
      });
      button.addEventListener("click", el._disableable.enabled((e) => {
        submenu._popoverable.setState(true);
      }));
      let { clear } = interest(button, submenu, {
        gain() {
          submenu._popoverable.setState(true);
        },
        lose() {
          submenu._popoverable.setState(false);
        },
        focusable: false,
        useSafeArea: true
      });
      submenu._popoverable.onChange(() => {
        if (!submenu._popoverable.getState()) {
          clear();
          submenu._focusable.wipeTabbables();
        }
        submenu._popoverable.getState() ? submenu._anchorable.reposition() : submenu._anchorable.cleanup();
      });
      on(button, "keydown", (e) => {
        if (e.key === "Enter") {
          submenu._popoverable.setState(true);
          setTimeout(() => submenu._focusable.focusFirst());
        }
      });
      on(button, "keydown", (e) => {
        if (e.key === "ArrowRight") {
          submenu._popoverable.setState(true);
          setTimeout(() => submenu._focusable.focusFirst());
        }
      });
      on(submenu, "keydown", (e) => {
        if (e.key === "ArrowLeft") {
          submenu._popoverable.setState(false);
          button.focus();
          e.stopPropagation();
        }
      });
    }
  }

  // js/toolbar.js
  var UIToolbar = class _UIToolbar extends UIElement {
    mount() {
      this._focusable = new FocusableGroup(this, { wrap: true });
      this._disableable = new Disableable(this);
      let undoDisableds = [];
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(this, "aria-disabled", "true");
          this.walker().each((el) => {
            if (!el.hasAttribute("disabled")) {
              setAttribute2(el, "disabled", "true");
              setAttribute2(el, "aria-disabled", "true");
              undoDisableds.push(() => {
                removeAndReleaseAttribute(el, "disabled");
                removeAndReleaseAttribute(el, "aria-disabled");
              });
            }
          });
        } else {
          removeAndReleaseAttribute(this, "aria-disabled");
          undoDisableds.forEach((fn) => fn());
          undoDisableds = [];
        }
      });
      on(this, "keydown", (e) => {
        if (["ArrowRight"].includes(e.key)) {
          e.target === this ? this._focusable.focusFirst() : this._focusable.focusNext();
          e.preventDefault();
          e.stopPropagation();
        } else if (["ArrowLeft"].includes(e.key)) {
          e.target === this ? this._focusable.focusFirst() : this._focusable.focusPrev();
          e.preventDefault();
          e.stopPropagation();
        }
      });
      setAttribute2(this, "role", "toolbar");
      this.initializeToolbarItems();
    }
    initializeToolbarItems() {
      this.walker().each((el) => {
        if (el._disableable) return;
        initializeToolbarItem(el);
      });
    }
    walker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UIToolbar) return reject();
        if (el.hasAttribute("popover")) return reject();
        if (el instanceof UIMenu) return reject();
        if (el instanceof UIOptions) return reject();
        if (!["a", "button"].includes(el.localName)) return skip();
      });
    }
  };
  element("toolbar", UIToolbar);
  function initializeToolbarItem(el) {
    el._disableable = new Disableable(el);
    el._disabled = el.hasAttribute("disabled");
    let link = el.querySelector("a");
    let button = el;
    let target = link || button;
    if (el._disabled) {
      setAttribute2(target, "disabled", "");
      setAttribute2(target, "aria-disabled", "true");
    }
    if (el._disabled) return;
    target._focusable = new Focusable(target, { disableable: el._disableable });
  }

  // js/tooltip.js
  var UITooltip = class extends UIElement {
    boot() {
      let type = this.hasAttribute("label") ? "label" : "description";
      let button = this.button();
      let overlay = this.overlay();
      if (!button) {
        return console.warn("ui-tooltip: no trigger element found", this);
      } else if (!overlay) {
        return;
      }
      overlay._popoverable = new Popoverable(overlay, { scope: "tooltip" });
      overlay._anchorable = new Anchorable(overlay, {
        reference: button,
        position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
        gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
        offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
      });
      overlay._popoverable.onChange(() => {
        overlay._popoverable.getState() ? overlay._anchorable.reposition() : overlay._anchorable.cleanup();
      });
      this._disableable = new Disableable(this);
      let removeInterest;
      this._disableable.onInitAndChange((disabled) => {
        if (removeInterest) {
          removeInterest();
          removeInterest = null;
        }
        if (!disabled) {
          let result = interest(this, overlay, {
            gain() {
              overlay._popoverable.setState(true);
            },
            lose() {
              overlay._popoverable.setState(false);
            },
            focusable: true,
            useSafeArea: false
          });
          removeInterest = result.remove;
        }
      });
      let observer = new MutationObserver(() => {
        if (this.getAttribute("draggable") === "true") {
          overlay._popoverable.setState(false);
        }
      });
      observer.observe(this, { attributeFilter: ["draggable"] });
      let id = assignId(overlay, "tooltip");
      let interactive = this.hasAttribute("interactive");
      let wantsLabel = this.hasAttribute("label") || button.textContent.trim() === "";
      if (interactive) {
        setAttribute2(button, "aria-controls", id);
        setAttribute2(button, "aria-expanded", "false");
        overlay._popoverable.onChange(() => {
          overlay._popoverable.getState() ? setAttribute2(button, "aria-expanded", "true") : setAttribute2(button, "aria-expanded", "false");
        });
      } else {
        if (wantsLabel) setAttribute2(button, "aria-labelledby", id);
        else setAttribute2(button, "aria-describedby", id);
        setAttribute2(overlay, "aria-hidden", "true");
      }
      setAttribute2(overlay, "role", "tooltip");
    }
    button() {
      return this.firstElementChild;
    }
    overlay() {
      return this.lastElementChild !== this.button() && this.lastElementChild.tagName !== "TEMPLATE" && this.lastElementChild;
    }
  };
  element("tooltip", UITooltip);

  // js/sidebar.js
  var EVENTS = {
    STATE_CHANGED: "stateChanged",
    DESKTOP_COLLAPSED: "desktopCollapsed",
    DESKTOP_EXPANDED: "desktopExpanded",
    MOBILE_COLLAPSED: "mobileCollapsed",
    MOBILE_EXPANDED: "mobileExpanded",
    VIEWPORT_ENTER_MOBILE: "viewportEnterMobile",
    VIEWPORT_ENTER_DESKTOP: "viewportEnterDesktop"
  };
  var UISidebar = class extends UIElement {
    boot() {
      this.config = {
        breakpoint: this.hasAttribute("breakpoint") ? this.getAttribute("breakpoint") : 1024,
        collapsible: false,
        persist: this.hasAttribute("persist") ? ["false", "none"].includes(this.getAttribute("persist")) ? false : true : true,
        sticky: this.hasAttribute("sticky") ? true : false
      };
      this.observable = new Observable();
      this.state = {
        active: false,
        viewportDesktop: true,
        viewportMobile: false,
        collapsedMobile: true,
        collapsedDesktop: false
      };
      if (this.config.sticky) {
        this.setStickyPositionStyles();
      }
      if (this.hasAttribute("collapsible")) {
        let value3 = this.getAttribute("collapsible");
        if (value3 === "true") {
          this.config.collapsible = true;
        } else if (value3 === "false") {
          this.config.collapsible = false;
        } else if (value3 === "mobile") {
          this.config.collapsible = "mobile";
        }
      }
      if (this.config.persist && this.config.collapsible) {
        this.state.collapsedDesktop = JSON.parse(localStorage.getItem("flux-sidebar-collapsed-desktop"));
      }
      this.removeAttribute("data-flux-sidebar-cloak");
      this.observable.subscribe(EVENTS.VIEWPORT_ENTER_DESKTOP, () => {
        let reapplyTransition = setStyle(this, "transition", "none");
        setTimeout(reapplyTransition);
        this.state.viewportDesktop = true;
        this.state.viewportMobile = false;
        this.observable.notify(EVENTS.STATE_CHANGED);
      });
      this.observable.subscribe(EVENTS.VIEWPORT_ENTER_MOBILE, () => {
        let reapplyTransition = setStyle(this, "transition", "none");
        setTimeout(reapplyTransition);
        this.state.viewportDesktop = false;
        this.state.viewportMobile = true;
        this.observable.notify(EVENTS.STATE_CHANGED);
      });
      this.observable.subscribe(EVENTS.DESKTOP_COLLAPSED, () => {
        this.state.collapsedDesktop = true;
        this.observable.notify(EVENTS.STATE_CHANGED);
      });
      this.observable.subscribe(EVENTS.DESKTOP_EXPANDED, () => {
        this.state.collapsedDesktop = false;
        this.observable.notify(EVENTS.STATE_CHANGED);
      });
      this.observable.subscribe(EVENTS.MOBILE_COLLAPSED, () => {
        this.state.collapsedMobile = true;
        this.observable.notify(EVENTS.STATE_CHANGED);
      });
      this.observable.subscribe(EVENTS.MOBILE_EXPANDED, () => {
        this.state.collapsedMobile = false;
        this.observable.notify(EVENTS.STATE_CHANGED);
      });
      this.observable.subscribe(EVENTS.STATE_CHANGED, () => {
        if (this.config.persist) {
          localStorage.setItem("flux-sidebar-collapsed-desktop", JSON.stringify(this.state.collapsedDesktop));
        }
        this.updateDataAttributes(this);
      });
      new ViewportResizeObserver(this.observable, this.config);
      document.addEventListener("flux-sidebar-toggle", () => {
        if (this.state.viewportDesktop) {
          this.state.collapsedDesktop ? this.observable.notify(EVENTS.DESKTOP_EXPANDED) : this.observable.notify(EVENTS.DESKTOP_COLLAPSED);
        } else {
          this.state.collapsedMobile ? this.observable.notify(EVENTS.MOBILE_EXPANDED) : this.observable.notify(EVENTS.MOBILE_COLLAPSED);
        }
      });
      this.addEventListener("click", (e) => {
        if (!(e.target === this)) return;
        if (!this.state.collapsedDesktop) return;
        this.observable.notify(EVENTS.DESKTOP_EXPANDED);
      });
      this.addEventListener("mouseenter", (e) => {
        this.state.active = true;
        this.observable.notify(EVENTS.STATE_CHANGED);
      });
      this.addEventListener("mouseleave", (e) => {
        this.state.active = false;
        this.observable.notify(EVENTS.STATE_CHANGED);
      });
      this.addEventListener("focusin", (e) => {
        this.state.active = true;
        this.observable.notify(EVENTS.STATE_CHANGED);
      });
      this.addEventListener("focusout", (e) => {
        this.state.active = false;
        this.observable.notify(EVENTS.STATE_CHANGED);
      });
    }
    setStickyPositionStyles() {
      let offsetTop = this.offsetTop;
      let pageScrollYValue = window.pageYOffset;
      if (pageScrollYValue > 0) {
        window.scrollTo(window.scrollX, 0);
        offsetTop = this.offsetTop;
        window.scrollTo(window.scrollX, pageScrollYValue);
      }
      this.style.position = "sticky";
      this.style.top = offsetTop + "px";
      this.style.maxHeight = `calc(100dvh - ${offsetTop}px)`;
    }
    updateDataAttributes(el) {
      let isFullCollapsible = this.config.collapsible === true;
      if (isFullCollapsible) {
        this.state.active ? setAttribute2(el, "data-flux-sidebar-active", "") : removeAndReleaseAttribute(el, "data-flux-sidebar-active");
      }
      if (this.state.viewportDesktop) {
        removeAndReleaseAttribute(el, "data-flux-sidebar-on-mobile");
        setAttribute2(el, "data-flux-sidebar-on-desktop", "");
        removeAndReleaseAttribute(el, "data-flux-sidebar-collapsed-mobile");
        if (this.state.collapsedDesktop) {
          if (isFullCollapsible) {
            setAttribute2(el, "data-flux-sidebar-collapsed-desktop", "");
          }
        } else {
          removeAndReleaseAttribute(el, "data-flux-sidebar-collapsed-desktop");
        }
      } else {
        removeAndReleaseAttribute(el, "data-flux-sidebar-on-desktop");
        setAttribute2(el, "data-flux-sidebar-on-mobile", "");
        removeAndReleaseAttribute(el, "data-flux-sidebar-collapsed-desktop");
        if (this.state.collapsedMobile) {
          setAttribute2(el, "data-flux-sidebar-collapsed-mobile", "");
        } else {
          removeAndReleaseAttribute(el, "data-flux-sidebar-collapsed-mobile");
        }
      }
    }
  };
  var ViewportResizeObserver = class {
    constructor(observable, { breakpoint }) {
      this.observable = observable;
      this.breakpoint = breakpoint;
      this.watchForViewportChanges();
    }
    watchForViewportChanges() {
      let breakpoint = typeof this.breakpoint === "number" ? `${this.breakpoint}px` : this.breakpoint;
      let viewport = matchMedia(`(min-width: ${breakpoint})`);
      viewport.matches ? this.observable.notify(EVENTS.VIEWPORT_ENTER_DESKTOP) : this.observable.notify(EVENTS.VIEWPORT_ENTER_MOBILE);
      viewport.addEventListener("change", () => {
        viewport.matches ? this.observable.notify(EVENTS.VIEWPORT_ENTER_DESKTOP) : this.observable.notify(EVENTS.VIEWPORT_ENTER_MOBILE);
      });
    }
  };
  var UISidebarToggle = class extends UIElement {
    mount() {
      let button = this.querySelector("button,ui-button");
      on(button || this, "click", () => {
        this.dispatchEvent(new CustomEvent("flux-sidebar-toggle", { bubbles: true }));
      });
      queueMicrotask(() => {
        let sidebar = document.querySelector("ui-sidebar");
        sidebar.updateDataAttributes(this);
        sidebar.observable.subscribe(EVENTS.STATE_CHANGED, () => {
          sidebar.updateDataAttributes(this);
        });
      });
    }
  };
  element("sidebar", UISidebar);
  element("sidebar-toggle", UISidebarToggle);

  // js/slider.js
  var UISlider = class extends UIElement {
    boot() {
      this.thumbs = this.querySelectorAll("[data-flux-slider-thumb]");
      this.indicator = this.querySelector("[data-flux-slider-indicator]");
      this.inputEls = this.querySelectorAll('input[type="range"]');
      this.config = {
        range: this.range(),
        min: this.min(),
        max: this.max(),
        step: this.step(),
        bigStep: this.bigStep(),
        minStepsBetween: this.minStepsBetween(),
        rangeStartString: this.rangeStartString(),
        rangeEndString: this.rangeEndString()
      };
      this.state = {
        onChanges: [],
        getValue: () => {
          return this.config.range ? [parseFloat(this.inputEls[0].value), parseFloat(this.inputEls[1].value)] : parseFloat(this.inputEls[0].value);
        },
        getValueAsArray: () => {
          return this.config.range ? this.state.getValue() : [this.state.getValue()];
        },
        setValue: (value3) => {
          if (this.config.range) {
            this.inputEls[0].value = value3[0];
            this.inputEls[1].value = value3[1];
          } else {
            this.inputEls[0].value = value3;
          }
          this.state.onChanges.forEach((i) => i(this.state.getValue()));
        },
        setValueInRange: (index, value3) => {
          let newValues = [...this.state.getValue()];
          newValues[index] = value3;
          this.state.setValue(newValues);
        },
        setValueFromString: (value3) => {
          if (this.config.range) {
            this.state.setValue(value3.split(","));
          } else {
            this.state.setValue(value3);
          }
        },
        increment: (index, increment) => {
          let values = this.state.getValueAsArray();
          if (this.config.range) {
            values[index] = this.constrainRangeValue(index, values[index] + increment);
            this.state.setValueInRange(index, values[index]);
          } else {
            this.state.setValue(clamp2(values[index] + increment, this.config.min, this.config.max));
          }
        },
        onChange: (callback) => {
          this.state.onChanges.push(callback);
        }
      };
      this._controllable = new Controllable(this);
      this._disableable = new Disableable(this);
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.state.getValue()
      });
      this.state.onChange((value3) => {
        this.repositionThumbsAndIndicator();
        this.updateTicks();
        this._submittable.update(value3);
        if (this.config.range) {
          this.inputEls[0].ariaValueText = `${value3[0]} ${this.config.rangeStartString}`;
          this.inputEls[1].ariaValueText = `${value3[1]} ${this.config.rangeEndString}`;
        }
      });
      this._controllable.initial((initial) => initial && this.state.setValue(initial));
      this._controllable.getter(() => this.state.getValue());
      this._controllable.setter((v) => this.state.setValue(v));
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.inputEls.forEach((el) => el.setAttribute("disabled", ""));
        } else {
          this.inputEls.forEach((el) => el.removeAttribute("disabled"));
        }
      });
      this.setNativeInputAttributes();
      this.setInitialValue();
      this.setupExtraInputIfExists();
      this.repositionTicks();
      this.registerNativeInputListeners();
      this.registerPointerListeners();
      this.registerMutationObserver();
      new ResizeObserver(() => {
        this.repositionThumbsAndIndicator();
        this.repositionTicks();
      }).observe(this);
    }
    mount() {
      this.setupFieldIfExists();
    }
    setNativeInputAttributes() {
      for (let i = 0; i < this.inputEls.length; i++) {
        this.inputEls[i].setAttribute("min", this.config.min);
        this.inputEls[i].setAttribute("max", this.config.max);
        this.inputEls[i].setAttribute("step", this.config.step);
      }
    }
    setInitialValue() {
      if (this.hasAttribute("value")) {
        this.state.setValueFromString(this.getAttribute("value"));
      } else {
        this.state.setValue(this.state.getValue());
      }
    }
    setupExtraInputIfExists() {
      let extraInput = this.closest("ui-field")?.querySelector("[data-flux-input] > input");
      if (!extraInput) return;
      this.extraInput = extraInput;
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(extraInput, "disabled", "");
        } else {
          removeAttribute(extraInput, "disabled");
        }
      });
    }
    setupFieldIfExists() {
      let field = this.closest("ui-field");
      if (!field) return;
      queueMicrotask(() => {
        if (field.label) on(field.label, "click", () => this.inputEls[0].focus());
        if (field.label && this.extraInput) setAttribute2(this.extraInput, "aria-labelledby", field.label.id);
        if (field.description && this.extraInput) setAttribute2(this.extraInput, "aria-describedby", field.description.id);
        for (let i = 0; i < this.inputEls.length; i++) {
          if (field.label) setAttribute2(this.inputEls[i], "aria-labelledby", field.label.id);
          if (field.description) setAttribute2(this.inputEls[i], "aria-describedby", field.description.id);
        }
      });
    }
    registerNativeInputListeners() {
      for (let i = 0; i < this.inputEls.length; i++) {
        on(this.inputEls[i], "input", (e) => {
          e.stopPropagation();
        });
        on(this.inputEls[i], "change", (e) => {
          let value3 = parseFloat(e.target.value);
          if (this.config.range) {
            value3 = this.constrainRangeValue(i, value3);
            this.state.setValueInRange(i, value3);
          } else {
            this.state.setValue(value3);
          }
          this._controllable.dispatch();
          e.stopPropagation();
        });
        on(this.inputEls[i], "keydown", (e) => {
          if (!e.shiftKey) return;
          switch (e.key) {
            case "ArrowUp":
              this.state.increment(i, this.config.bigStep);
              break;
            case "ArrowDown":
              this.state.increment(i, this.config.bigStep * -1);
              break;
            case "ArrowRight":
              this.state.increment(i, this.config.bigStep * (isRTL(this) ? -1 : 1));
              break;
            case "ArrowLeft":
              this.state.increment(i, this.config.bigStep * (isRTL(this) ? 1 : -1));
              break;
            default:
              return;
          }
          this._controllable.dispatch();
          e.preventDefault();
        });
        on(this.inputEls[i], "focus", () => this.raiseThumb(i));
        on(this.inputEls[i], "blur", () => this.lowerThumb(i));
      }
    }
    registerPointerListeners() {
      on(this, "pointerdown", (e) => {
        if (this._disableable.isDisabled()) return;
        if (e.button !== 0) return;
        let index = this.getThumbIndexFromX(e.clientX);
        let thumbRect = this.thumbs[index].getBoundingClientRect();
        let thumbMidpoint = thumbRect.left + thumbRect.width / 2;
        let pointerOffset = 0;
        if (Math.abs(e.clientX - thumbMidpoint) < thumbRect.width / 2) {
          pointerOffset = e.clientX - thumbMidpoint;
        }
        let initialValues = this.state.getValueAsArray();
        let lastValues = this.state.getValueAsArray();
        let setValue = (e2) => {
          let value3 = this.getValueFromX(e2.clientX - pointerOffset);
          if (this.config.range) {
            value3 = this.constrainRangeValue(index, value3);
          }
          let newValues = [...lastValues];
          newValues[index] = value3;
          if (lastValues.toString() !== newValues.toString()) {
            lastValues = newValues;
            this.config.range ? this.state.setValueInRange(index, value3) : this.state.setValue(value3);
            this.dispatchEvent(new Event("input", {
              bubbles: false,
              cancelable: true
            }));
          }
        };
        setValue(e);
        this.setPointerCapture(e.pointerId);
        requestAnimationFrame(() => {
          this.inputEls[index].focus();
        });
        document.addEventListener("pointermove", setValue);
        document.addEventListener("pointerup", (e2) => {
          document.removeEventListener("pointermove", setValue);
          this.releasePointerCapture(e2.pointerId);
          requestAnimationFrame(() => {
            this.lowerThumb(index);
          });
          if (initialValues.toString() !== lastValues.toString()) {
            this.dispatchEvent(new Event("change", {
              bubbles: false,
              cancelable: true
            }));
          }
        }, { once: true });
      });
    }
    registerMutationObserver() {
      let observer = new MutationObserver(() => {
        this.config.min = this.min();
        this.config.max = this.max();
        this.config.step = this.step();
        this.config.bigStep = this.bigStep();
        this.config.minStepsBetween = this.minStepsBetween();
        this.setNativeInputAttributes();
        this.repositionThumbsAndIndicator();
        this.repositionTicks();
      });
      observer.observe(this, {
        attributes: true,
        attributeFilter: ["min", "max", "step", "big-step", "min-steps-between"]
      });
    }
    repositionThumbsAndIndicator() {
      if (this.config.range) {
        let value3 = this.state.getValue();
        let startPercentage = this.getPercentageFromValue(value3[0]);
        let endPercentage = this.getPercentageFromValue(value3[1]);
        this.thumbs[0].style.insetInlineStart = startPercentage + "%";
        this.thumbs[1].style.insetInlineStart = endPercentage + "%";
        this.indicator.style.insetInlineStart = startPercentage + "%";
        this.indicator.style.insetInlineEnd = 100 - endPercentage + "%";
      } else {
        let value3 = this.state.getValue();
        let percentage = this.getPercentageFromValue(value3);
        this.thumbs[0].style.insetInlineStart = percentage + "%";
        this.indicator.style.insetInlineStart = 0;
        this.indicator.style.insetInlineEnd = 100 - percentage + "%";
      }
    }
    repositionTicks() {
      this.querySelectorAll("[data-flux-slider-tick]").forEach((tick) => {
        let tickValue = parseFloat(tick.getAttribute("data-value"));
        if (isNaN(tickValue)) return;
        let percentage = this.getPercentageFromValue(tickValue);
        setAttribute2(tick, "style", `inset-inline-start: ${percentage}%`);
      });
    }
    updateTicks() {
      let values = this.state.getValueAsArray();
      let activeRange = this.config.range ? values : [this.state.min, values[0]];
      this.querySelectorAll("[data-flux-slider-tick]").forEach((tick) => {
        let tickValue = parseFloat(tick.getAttribute("data-value"));
        if (isNaN(tickValue)) return;
        for (let i = 0; i < values.length; i++) {
          if (values[i] == tickValue) {
            setAttribute2(tick, "data-current", "");
          } else {
            removeAttribute(tick, "data-current");
          }
        }
        if (tickValue >= activeRange[0] || tickValue <= activeRange[1]) {
          setAttribute2(tick, "data-active", "");
        } else {
          removeAttribute(tick, "data-active");
        }
      });
    }
    raiseThumb(index) {
      this.thumbs[index].style.zIndex = 10;
    }
    lowerThumb(index) {
      this.thumbs[index].style.zIndex = null;
    }
    getThumbIndexFromX(x) {
      let closestIndex;
      let closestDistance;
      for (let i = 0; i < this.thumbs.length; i++) {
        let rect = this.thumbs[i].getBoundingClientRect();
        let midpoint = (rect.left + rect.right) / 2;
        let distance = Math.abs(midpoint - x);
        if (closestDistance && Math.abs(closestDistance - distance) < rect.width / 2) {
          let trackRect = this.getBoundingClientRect();
          let distanceFromMax = trackRect.right - rect.right;
          if (distanceFromMax < rect.width / 4) {
            continue;
          }
        }
        if (closestDistance === void 0 || distance <= closestDistance) {
          closestDistance = distance;
          closestIndex = i;
        }
      }
      return closestIndex;
    }
    getPercentageFromValue(value3) {
      let current = value3 - this.config.min;
      let total = this.config.max - this.config.min;
      if (total == 0) return 0;
      let fraction = clamp2(current / total, 0, 100);
      let trackRect = this.getBoundingClientRect();
      let thumbRect = this.thumbs[0].getBoundingClientRect();
      let trackPadding = thumbRect.width / 2;
      let width = trackRect.width - trackPadding * 2;
      let offset3 = width * fraction + trackPadding;
      let adjustedPercentage = offset3 / trackRect.width * 100;
      return adjustedPercentage;
    }
    getValueFromX(x) {
      let min2 = this.config.min;
      let max2 = this.config.max;
      let step = this.config.step;
      let trackRect = this.getBoundingClientRect();
      let thumbRect = this.thumbs[0].getBoundingClientRect();
      let trackPadding = thumbRect.width / 2;
      let trackWidth = trackRect.width - trackPadding * 2;
      let trackLeft = trackRect.left + trackPadding;
      let relativeX = x - trackLeft;
      let rescaled = clamp2(relativeX / trackWidth, 0, 1);
      rescaled = isRTL(this) ? 1 - rescaled : rescaled;
      let value3 = (max2 - min2) * rescaled + min2;
      value3 = roundValueToStep(value3, step, min2);
      value3 = clamp2(value3, min2, max2);
      return value3;
    }
    constrainRangeValue(index, value3) {
      let newValues = [...this.state.getValue()];
      newValues[index] = value3;
      let stepsBetween = (newValues[1] - newValues[0]) / this.config.step;
      if (stepsBetween < this.config.minStepsBetween) {
        let limits = [
          newValues[1] - this.config.minStepsBetween * this.config.step,
          newValues[0] + this.config.minStepsBetween * this.config.step
        ];
        return limits[index];
      }
      return value3;
    }
    range() {
      return this.hasAttribute("range");
    }
    min() {
      return this.hasAttribute("min") ? parseFloat(this.getAttribute("min")) : 0;
    }
    max() {
      return this.hasAttribute("max") ? parseFloat(this.getAttribute("max")) : 100;
    }
    step() {
      if (!this.hasAttribute("step")) {
        return 1;
      }
      let step = parseFloat(this.getAttribute("step"));
      return step <= 0 || isNaN(step) ? 1 : step;
    }
    bigStep() {
      if (!this.hasAttribute("big-step")) {
        return this.step();
      }
      let bigStep = parseFloat(this.getAttribute("big-step"));
      return bigStep <= 0 || isNaN(bigStep) ? this.step() : bigStep;
    }
    minStepsBetween() {
      return this.hasAttribute("min-steps-between") ? parseFloat(this.getAttribute("min-steps-between")) : 0;
    }
    rangeStartString() {
      return this.getAttribute("data-flux-aria-range-start") || "start range";
    }
    rangeEndString() {
      return this.getAttribute("data-flux-aria-range-end") || "end range";
    }
  };
  function clamp2(value3, min2, max2) {
    return Math.min(Math.max(value3, min2), max2);
  }
  function roundValueToStep(value3, step, min2) {
    let precision;
    if (Math.abs(step) >= 1) {
      precision = step.toString().split(".")[1]?.length || 0;
    } else {
      let parts = step.toExponential().split("e-");
      let matissaDecimalPart = parts[0].split(".")[1];
      precision = (matissaDecimalPart ? matissaDecimalPart.length : 0) + parseInt(parts[1], 10);
    }
    let nearest = Math.round((value3 - min2) / step) * step + min2;
    return Number(nearest.toFixed(precision));
  }
  element("slider", UISlider);

  // js/color-picker.js
  var UIColorPicker = class extends UIControl {
    // Lifecycle
    boot() {
      this.listeners = [];
      this.syncingValueAttribute = false;
      this.scrollLocked = false;
      this.pointerCleanup = null;
      this.previewEls = [];
      this.sliderEls = [];
      this.swatchContainers = [];
      this.swatchEls = [];
      this.dropperButtons = [];
      this.copyButtons = [];
      this.state = createEmptyState();
      this.committedState = createEmptyState();
      this._disableable = new Disableable(this);
      this._controllable = new Controllable(this, { bubbles: true });
      this._closeable = new Closeable(this);
      this._submittable = new Submittable(this, {
        name: this.resolveName(),
        value: null,
        includeWhenEmpty: false
      });
      let detangled = detangle();
      this.onStateChange(() => {
        this._submittable.update(this.getValue());
        this.syncUI();
      });
      this._controllable.initial((initial) => {
        if (initial !== void 0 && initial !== null && initial !== "") {
          this.setValue(initial, { dispatch: false });
        } else {
          this.setValue(this.getAttribute("value"), { dispatch: false });
        }
      });
      this._controllable.getter(() => this.getValue());
      this._controllable.setter(detangled((value3) => {
        this.setValue(value3, { dispatch: false });
        if (this.isDeferred()) {
          this.syncCommittedStateFromDraft();
        }
      }));
      this._disableable.onInitAndChange((disabled) => this.syncDisabledState(disabled));
      this.observeAttributes();
    }
    mount() {
      this.cacheDom();
      if (!this.popoverEl || !this.triggerEl) return;
      this.setupPopover();
      this.setupTrigger();
      this.setupArea();
      this.setupSliders();
      this.setupPopoverInput();
      this.setupSwatches();
      this.setupDropper();
      this.setupCopyButton();
      this.setupFieldIfExists();
      this.syncDisabledState(this._disableable.isDisabled());
      this.syncUI();
    }
    cacheDom() {
      this.popoverEl = this.querySelector("dialog");
      this.triggerEl = this.querySelector("[data-flux-color-picker-trigger]") || this.querySelector("[data-flux-color-picker-button]");
      this.inputEl = this.querySelector("[data-flux-color-picker-input]");
      this.triggerControl = this.inputEl || this.triggerEl;
      this.popoverInputEl = this.querySelector("[data-flux-color-picker-popover-input]");
      this.areaEl = this.querySelector("[data-flux-color-picker-area]");
      this.areaThumb = this.querySelector("[data-flux-color-picker-area-thumb]");
      this.areaHue = this.querySelector("[data-flux-color-picker-area-hue]");
      this.buttonEl = this.querySelector("[data-flux-color-picker-button]");
      this.swatchButton = this.querySelector("[data-flux-color-picker-swatch-button]");
      this.previewEls = Array.from(this.querySelectorAll("[data-flux-color-picker-preview]"));
      this.sliderEls = Array.from(this.querySelectorAll("[data-flux-color-picker-slider]"));
      this.swatchContainers = Array.from(this.querySelectorAll("[data-flux-color-picker-swatches]"));
      this.swatchEls = Array.from(this.querySelectorAll("[data-flux-color-picker-swatch]"));
      this.dropperButtons = Array.from(this.querySelectorAll("[data-flux-color-picker-dropper]"));
      this.copyButtons = Array.from(this.querySelectorAll("[data-flux-color-picker-copy]"));
      this.mobileMediaQuery = window.matchMedia("(max-width: 639px)");
      this.initialFocusEl = this.querySelector("[data-flux-color-picker-initial-focus]") || this.areaThumb || this.popoverInputEl;
    }
    unmount() {
      this.pointerCleanup?.();
      this.pointerCleanup = null;
    }
    observeAttributes() {
      this.attributeObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.attributeName === "disabled") {
            this.syncDisabledState(this._disableable.isDisabled());
            return;
          }
          if (mutation.attributeName === "value") {
            if (this.syncingValueAttribute) return;
            this.setValue(this.getAttribute("value"), { dispatch: false });
            return;
          }
          if (mutation.attributeName === "format") {
            if (!this.state.empty) this.state.value = this.formatValue(this.state);
            if (!this.committedState.empty) this.committedState.value = this.formatValue(this.committedState);
            this.syncValueAttribute();
            this.notify();
          }
        });
      });
      this.attributeObserver.observe(this, { attributes: true, attributeFilter: ["disabled", "format", "value"] });
      this.onUnmount(() => this.attributeObserver.disconnect());
    }
    // State
    resolveName() {
      let name = this.getAttribute("name");
      if (name) return name;
      return Array.from(this.attributes).find((attribute) => attribute.name.startsWith("wire:model"))?.value;
    }
    onStateChange(callback) {
      this.listeners.push(callback);
      return () => {
        this.listeners = this.listeners.filter((listener) => listener !== callback);
      };
    }
    notify() {
      this.listeners.forEach((listener) => listener(this.state));
    }
    getFormat() {
      return this.getAttribute("format") || "hex";
    }
    supportsAlpha() {
      return ["hexa", "rgba", "hsla"].includes(this.getFormat());
    }
    getValue() {
      return this.committedState.empty ? null : this.committedState.value;
    }
    getDraftValue() {
      return this.state.empty ? null : this.state.value;
    }
    clear() {
      this.setValue(null);
      this.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
    }
    setValue(value3, options = {}) {
      let { dispatch = true } = options;
      if (value3 === void 0 || value3 === null || value3 === "") {
        this.writeState(createEmptyState(), { dispatch });
      } else {
        let parsed = parseColor(value3);
        if (!parsed) return false;
        this.writeState({
          hsv: rgbToHsv(parsed.r, parsed.g, parsed.b),
          alpha: parsed.a,
          empty: false
        }, { dispatch });
      }
      return true;
    }
    updateColor(partial, options = {}) {
      let { dispatch = true } = options;
      let nextState;
      if (this.state.empty) {
        nextState = {
          hsv: {
            h: clamp3(partial.h ?? 0, 0, 360),
            s: clamp3(partial.s ?? 0, 0, 100),
            v: clamp3(partial.v ?? 0, 0, 100)
          },
          alpha: clamp3(options.alpha ?? 1, 0, 1),
          empty: false
        };
      } else {
        nextState = {
          ...this.state,
          hsv: {
            h: clamp3(partial.h ?? this.state.hsv.h, 0, 360),
            s: clamp3(partial.s ?? this.state.hsv.s, 0, 100),
            v: clamp3(partial.v ?? this.state.hsv.v, 0, 100)
          },
          alpha: clamp3(options.alpha ?? this.state.alpha, 0, 1),
          empty: false
        };
      }
      this.writeState(nextState, { dispatch });
    }
    afterStateMutation({ dispatch = true } = {}) {
      if (this.isDeferred()) {
        this.notify();
        return;
      }
      this.syncCommittedState();
      this.notify();
      if (dispatch) this._controllable.dispatch();
    }
    writeState(state, { dispatch = true } = {}) {
      this.state = hydrateState(this, state);
      this.afterStateMutation({ dispatch });
    }
    syncCommittedState() {
      this.committedState = cloneState(this.state);
      this.syncValueAttribute();
    }
    syncCommittedStateFromDraft() {
      this.syncCommittedState();
    }
    commit() {
      this.syncCommittedState();
      this._submittable.update(this.getValue());
      this.notify();
      this._controllable.dispatch();
      this.close();
    }
    isDeferred() {
      let selectEl = this.querySelector("ui-color-picker-select");
      return !!selectEl && selectEl.getBoundingClientRect().width > 0;
    }
    setHue(value3, options = {}) {
      this.updateColor({ h: value3 }, options);
    }
    setAlpha(value3, options = {}) {
      this.updateColor({}, { ...options, alpha: value3 });
    }
    setSaturationValue(saturation, value3, options = {}) {
      this.updateColor({ s: saturation, v: value3 }, options);
    }
    getRgb() {
      if (this.state.empty) return null;
      return hsvToRgb(this.state.hsv.h, this.state.hsv.s, this.state.hsv.v);
    }
    getHsl() {
      let rgb = this.getRgb();
      if (!rgb) return null;
      return rgbToHsl(rgb.r, rgb.g, rgb.b);
    }
    formatValue(state = this.state) {
      if (state.empty) return null;
      let rgb = hsvToRgb(state.hsv.h, state.hsv.s, state.hsv.v);
      let hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
      let alpha = clamp3(state.alpha, 0, 1);
      switch (this.getFormat()) {
        case "hexa":
          return `${rgbToHex(rgb.r, rgb.g, rgb.b)}${alphaToHex(alpha)}`;
        case "rgb":
          return `rgb(${rgb.r} ${rgb.g} ${rgb.b})`;
        case "rgba":
          return `rgb(${rgb.r} ${rgb.g} ${rgb.b} / ${formatAlpha(alpha)})`;
        case "hsl":
          return `hsl(${Math.round(hsl.h)} ${Math.round(hsl.s)}% ${Math.round(hsl.l)}%)`;
        case "hsla":
          return `hsl(${Math.round(hsl.h)} ${Math.round(hsl.s)}% ${Math.round(hsl.l)}% / ${formatAlpha(alpha)})`;
        default:
          return rgbToHex(rgb.r, rgb.g, rgb.b);
      }
    }
    syncValueAttribute() {
      this.syncingValueAttribute = true;
      if (this.committedState.empty || !this.committedState.value) {
        removeAttribute(this, "value");
      } else {
        setAttribute2(this, "value", this.committedState.value);
      }
      queueMicrotask(() => {
        this.syncingValueAttribute = false;
      });
    }
    // Interaction
    toggle() {
      if (this._disableable.isDisabled()) return;
      this._dialogable?.toggle();
    }
    open() {
      if (this._disableable.isDisabled()) return;
      this._dialogable?.setState(true);
    }
    close() {
      this._dialogable?.setState(false);
    }
    trigger() {
      return this.triggerControl || this.triggerEl;
    }
    isMobileViewport() {
      return this.mobileMediaQuery?.matches ?? window.matchMedia("(max-width: 639px)").matches;
    }
    setupPopover() {
      this._dialogable = new Dialogable(this.popoverEl, {
        clickOutside: true,
        triggers: [this.triggerControl]
      });
      this._anchorable = new Anchorable(this.popoverEl, {
        reference: this.triggerEl,
        position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
        gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
        offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
      });
      this.linkPopoverAria();
      initPopover4(this, this.popoverEl, this._dialogable, this._anchorable, () => {
        queueMicrotask(() => this.initialFocusEl?.focus());
      });
      this._closeable.onClose(() => this._dialogable.setState(false));
      this._dialogable.onChange(() => {
        this.state = cloneState(this.committedState);
        this.notify();
      });
      this.getAriaTargets().forEach((el) => linkExpandedStateToPopover4(el, this._dialogable));
      preventScrollWhenPopoverIsOpen4(this, this._dialogable);
      controlPopoverWithKeyboard3(this.triggerControl, this._dialogable);
    }
    linkPopoverAria() {
      let popoverId = assignId(this.popoverEl, "color-picker-popover");
      this.getAriaTargets().forEach((el) => {
        setAttribute2(el, "aria-haspopup", "dialog");
        setAttribute2(el, "aria-controls", popoverId);
      });
    }
    setupTrigger() {
      if (this.inputEl) {
        on(this.triggerEl, "click", (event) => {
          if (event.target !== this.triggerEl && event.target !== this.inputEl) return;
          if (this.isMobileViewport()) {
            this.open();
            return;
          }
          this.inputEl.focus();
        });
        this.setupMobileTriggerInputMode(this.inputEl);
        this.setupInput(this.inputEl);
      }
      if (this.buttonEl) {
        on(this.buttonEl, "click", (event) => {
          if (event.target.closest('[aria-label="Clear color"]')) return;
          if (this._disableable.isDisabled()) return;
          this.toggle();
        });
      }
      if (this.swatchButton) {
        on(this.swatchButton, "click", (event) => {
          if (this._disableable.isDisabled()) return;
          event.stopPropagation();
          this.toggle();
        });
      }
    }
    setupInput(inputEl) {
      on(inputEl, "mousedown", (event) => {
        if (document.activeElement === inputEl) return;
        event.preventDefault();
        inputEl.focus();
      });
      on(inputEl, "focus", () => inputEl.select());
      on(inputEl, "change", (event) => {
        event.stopPropagation();
        let value3 = inputEl.value.trim();
        if (value3 === "") {
          this.setValue(null);
        } else if (!this.setValue(value3)) {
          inputEl.value = this.getValue() || "";
        }
      });
      on(inputEl, "input", (event) => event.stopPropagation());
      on(inputEl, "blur", () => {
        inputEl.value = this.getValue() || "";
      });
      on(inputEl, "keydown", (event) => {
        if (event.key === "Enter") {
          event.preventDefault();
          inputEl.blur();
        }
      });
    }
    setupArea() {
      if (!this.areaEl || !this.areaThumb) return;
      on(this.areaEl, "touchmove", (event) => {
        event.preventDefault();
      });
      on(this.areaEl, "pointerdown", (event) => {
        if (this._disableable.isDisabled()) return;
        if (event.button !== 0) return;
        event.preventDefault();
        this.setAreaFromPointer(event);
        this.areaEl.setPointerCapture(event.pointerId);
        this.pointerCleanup?.();
        let move = (moveEvent) => this.setAreaFromPointer(moveEvent);
        let up = (upEvent) => {
          this.pointerCleanup?.();
          this.pointerCleanup = null;
          document.removeEventListener("pointermove", move);
          this.areaEl.releasePointerCapture(upEvent.pointerId);
          this.dispatchEvent(new Event("change", { bubbles: false }));
        };
        document.addEventListener("pointermove", move);
        document.addEventListener("pointerup", up, { once: true });
        this.pointerCleanup = () => {
          document.removeEventListener("pointermove", move);
          document.removeEventListener("pointerup", up);
        };
      });
      on(this.areaEl, "keydown", (event) => {
        if (this._disableable.isDisabled()) return;
        let step = event.shiftKey ? 10 : 2;
        let { s, v } = this.state.hsv;
        switch (event.key) {
          case "ArrowRight":
            s += step;
            break;
          case "ArrowLeft":
            s -= step;
            break;
          case "ArrowUp":
            v += step;
            break;
          case "ArrowDown":
            v -= step;
            break;
          case "Home":
            s = 0;
            break;
          case "End":
            s = 100;
            break;
          case "PageUp":
            v = 100;
            break;
          case "PageDown":
            v = 0;
            break;
          default:
            return;
        }
        this.setSaturationValue(s, v);
        event.preventDefault();
      });
    }
    setAreaFromPointer(event) {
      let rect = this.areaEl.getBoundingClientRect();
      let saturation = clamp3((event.clientX - rect.left) / rect.width * 100, 0, 100);
      let value3 = clamp3((1 - (event.clientY - rect.top) / rect.height) * 100, 0, 100);
      this.setSaturationValue(saturation, value3, { dispatch: false });
      this.dispatchEvent(new Event("input", { bubbles: false }));
    }
    setupSliders() {
      this.sliderEls.forEach((slider) => {
        let channel = slider.getAttribute("data-channel");
        on(slider, "input", (event) => {
          event.stopPropagation();
          this.updateSliderValue(channel, parseFloat(slider.value));
          this.dispatchEvent(new Event("input", { bubbles: false }));
        });
        on(slider, "change", (event) => {
          event.stopPropagation();
          this.dispatchEvent(new Event("change", { bubbles: false }));
        });
      });
    }
    updateSliderValue(channel, value3) {
      if (channel === "alpha") {
        this.setAlpha(value3 / 100, { dispatch: false });
      } else {
        this.setHue(value3, { dispatch: false });
      }
    }
    setupPopoverInput() {
      if (!this.popoverInputEl) return;
      this.setupInput(this.popoverInputEl);
    }
    setupMobileTriggerInputMode(inputEl) {
      let mq = this.mobileMediaQuery;
      let apply3 = () => {
        if (mq.matches) setAttribute2(inputEl, "inputmode", "none");
        else removeAttribute(inputEl, "inputmode");
      };
      apply3();
      mq.addEventListener("change", apply3);
      this.onUnmount(() => mq.removeEventListener("change", apply3));
      on(inputEl, "mousedown", (event) => {
        if (!mq.matches) return;
        event.preventDefault();
        event.stopImmediatePropagation();
      });
    }
    setupSwatches() {
      this.swatchContainers.forEach((container) => {
        on(container, "click", (event) => {
          let swatch = event.target.closest("[data-flux-color-picker-swatch]");
          if (!swatch || this._disableable.isDisabled()) return;
          this.commitSwatch(swatch);
        });
        on(container, "keydown", (event) => {
          if (this._disableable.isDisabled()) return;
          let current = event.target.closest("[data-flux-color-picker-swatch]");
          if (!current) return;
          let swatches = Array.from(container.querySelectorAll("[data-flux-color-picker-swatch]"));
          let index = swatches.indexOf(current);
          let columns = deriveSwatchColumns(swatches);
          let target = null;
          switch (event.key) {
            case "ArrowRight":
              target = swatches[index + 1];
              break;
            case "ArrowLeft":
              target = swatches[index - 1];
              break;
            case "ArrowDown":
              target = swatches[index + columns];
              break;
            case "ArrowUp":
              target = swatches[index - columns];
              break;
            case "Home":
              target = swatches[0];
              break;
            case "End":
              target = swatches[swatches.length - 1];
              break;
            case "Enter":
            case " ":
              this.commitSwatch(current);
              event.preventDefault();
              return;
            default:
              return;
          }
          if (target) {
            this.focusSwatch(target);
            event.preventDefault();
          }
        });
      });
    }
    commitSwatch(swatch) {
      let value3 = swatch.getAttribute("data-value") || swatch.getAttribute("value");
      if (!value3) return;
      this.setValue(value3);
      this.announceSelection(this.getSwatchLabel(swatch));
      if (this.isDeferred() && this.isMobileViewport()) {
        this.commit();
      }
    }
    getSwatchLabel(swatch) {
      return swatch.getAttribute("aria-label") || swatch.getAttribute("title") || swatch.getAttribute("data-value") || swatch.getAttribute("value");
    }
    focusSwatch(swatch) {
      this.swatchEls.forEach((el) => {
        setAttribute2(el, "tabindex", el === swatch ? "0" : "-1");
      });
      swatch.focus();
    }
    announceSelection(label) {
      let region = this.querySelector("[data-flux-color-picker-announce]");
      if (!region) return;
      region.textContent = "";
      setTimeout(() => {
        region.textContent = `${label} selected`;
      }, 150);
    }
    setupDropper() {
      this.dropperButtons.forEach((button) => {
        if (!window.EyeDropper) {
          setAttribute2(button, "disabled", "");
          setAttribute2(button, "hidden", "");
          return;
        }
        on(button, "click", async () => {
          try {
            let eyeDropper = new EyeDropper();
            let result = await eyeDropper.open();
            if (result.sRGBHex) {
              this.setValue(result.sRGBHex);
            }
          } catch {
          }
        });
      });
    }
    setupCopyButton() {
      this.copyButtons.forEach((button) => {
        on(button, "click", (event) => {
          event.stopPropagation();
          let value3 = this.getValue();
          if (value3 && navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(value3);
          }
        });
      });
    }
    // Rendering
    syncUI() {
      let committedValue = this.getValue();
      let draftValue = this.getDraftValue();
      this.previewEls.forEach((el) => {
        el.style.backgroundColor = committedValue ? this.getCheckerboardColor(this.committedState) : "transparent";
      });
      if (this.inputEl && document.activeElement !== this.inputEl) {
        this.inputEl.value = committedValue || "";
      }
      if (this.popoverInputEl && document.activeElement !== this.popoverInputEl) {
        this.popoverInputEl.value = draftValue || "";
      }
      this.syncTriggerValueText(committedValue);
      if (this.areaHue) {
        this.areaHue.style.backgroundColor = this.getAreaColor(this.state);
      }
      this.syncAreaThumb();
      this.syncSliders();
      this.syncSwatches(draftValue);
      this.syncSwatchButtonLabel(committedValue);
      this.syncSwatchTabIndex();
    }
    syncTriggerValueText(value3) {
      let valueSpan = this.querySelector("[data-flux-color-picker-value]");
      if (valueSpan) valueSpan.textContent = value3 || "";
    }
    syncAreaThumb() {
      if (!this.areaThumb) return;
      let saturation = Math.round(this.state.hsv.s);
      let brightness = Math.round(this.state.hsv.v);
      this.areaThumb.style.left = `${this.state.hsv.s}%`;
      this.areaThumb.style.top = `${100 - this.state.hsv.v}%`;
      setAttribute2(this.areaThumb, "aria-valuenow", saturation);
      setAttribute2(this.areaThumb, "aria-valuetext", `Saturation ${saturation}%, brightness ${brightness}%`);
    }
    syncSliders() {
      this.sliderEls.forEach((slider) => {
        let channel = slider.getAttribute("data-channel");
        if (channel === "alpha") {
          slider.value = Math.round(this.state.alpha * 100);
          this.syncAlphaTrack(slider);
        } else {
          slider.value = Math.round(this.state.hsv.h);
        }
      });
    }
    syncAlphaTrack(slider) {
      let track = slider.querySelector("[data-flux-color-picker-slider-track]");
      if (track) {
        track.style.background = `linear-gradient(to right, transparent, ${this.getSolidColor(this.state)})`;
      }
    }
    syncSwatches(draftValue) {
      this.swatchEls.forEach((swatch) => {
        let swatchValue = swatch.getAttribute("data-value") || swatch.getAttribute("value");
        let selected = draftValue && swatchValue && colorsMatch(swatchValue, draftValue);
        if (selected) setAttribute2(swatch, "data-selected", "");
        else removeAttribute(swatch, "data-selected");
        setAttribute2(swatch, "aria-selected", selected ? "true" : "false");
      });
    }
    syncSwatchButtonLabel(committedValue) {
      if (!this.swatchButton) return;
      if (this.swatchButton.dataset.baseLabel === void 0) {
        this.swatchButton.dataset.baseLabel = this.swatchButton.getAttribute("aria-label") || "";
      }
      let base = this.swatchButton.dataset.baseLabel;
      let committedLabel = committedValue ? this.getCommittedSwatchLabel(committedValue) : null;
      setAttribute2(this.swatchButton, "aria-label", committedLabel ? `${base}, ${committedLabel}` : base);
    }
    getCommittedSwatchLabel(committedValue) {
      let swatch = this.swatchEls.find((s) => {
        let value3 = s.getAttribute("data-value") || s.getAttribute("value");
        return value3 && colorsMatch(value3, committedValue);
      });
      return swatch ? this.getSwatchLabel(swatch) : committedValue;
    }
    syncSwatchTabIndex() {
      if (!this.swatchEls.length) return;
      let focused = this.querySelector("[data-flux-color-picker-swatch]:focus");
      if (focused) return;
      let selected = this.swatchEls.find((s) => s.getAttribute("aria-selected") === "true");
      let target = selected || this.swatchEls[0];
      this.swatchEls.forEach((s) => setAttribute2(s, "tabindex", s === target ? "0" : "-1"));
    }
    syncDisabledState(disabled) {
      this.querySelectorAll("button, input").forEach((el) => {
        if (disabled) setAttribute2(el, "disabled", "");
        else removeAttribute(el, "disabled");
      });
      this.previewEls.forEach((el) => {
        el.style.opacity = disabled ? "0.65" : "";
      });
    }
    // Presentation helpers
    getAreaColor(state = this.state) {
      return `hsl(${state.hsv.h} 100% 50%)`;
    }
    getSolidColor(state = this.state) {
      if (state.empty) return "transparent";
      let rgb = hsvToRgb(state.hsv.h, state.hsv.s, state.hsv.v);
      return `rgb(${rgb.r} ${rgb.g} ${rgb.b})`;
    }
    getCheckerboardColor(state = this.state) {
      if (state.empty) return "transparent";
      let rgb = hsvToRgb(state.hsv.h, state.hsv.s, state.hsv.v);
      return `rgb(${rgb.r} ${rgb.g} ${rgb.b} / ${formatAlpha(state.alpha)})`;
    }
    setupFieldIfExists() {
      let field = this.closest("ui-field");
      if (!field) return;
      queueMicrotask(() => {
        if (field.label) {
          let clickListener = on(field.label, "click", () => this.triggerControl?.focus());
          this.onUnmount(() => clickListener.off());
        }
        if (field.label && this.triggerControl) setAttribute2(this.triggerControl, "aria-labelledby", field.label.id);
        if (field.description && this.triggerControl) setAttribute2(this.triggerControl, "aria-describedby", field.description.id);
      });
    }
    getAriaTargets() {
      return [this.triggerControl, this.swatchButton].filter(Boolean);
    }
  };
  function createEmptyState() {
    return {
      hsv: { h: 0, s: 0, v: 0 },
      alpha: 1,
      value: null,
      empty: true
    };
  }
  function cloneState(state) {
    return {
      hsv: { ...state.hsv },
      alpha: state.alpha,
      value: state.value,
      empty: state.empty
    };
  }
  function hydrateState(picker, state) {
    let nextState = cloneState(state);
    nextState.value = nextState.empty ? null : picker.formatValue(nextState);
    return nextState;
  }
  function clamp3(value3, min2, max2) {
    return Math.min(Math.max(value3, min2), max2);
  }
  function normalizeHue(value3) {
    let normalized = value3 % 360;
    return normalized < 0 ? normalized + 360 : normalized;
  }
  function formatAlpha(value3) {
    return Number(value3.toFixed(2)).toString();
  }
  function alphaToHex(alpha) {
    return Math.round(alpha * 255).toString(16).padStart(2, "0");
  }
  function rgbToHex(r, g, b) {
    return `#${[r, g, b].map((component) => component.toString(16).padStart(2, "0")).join("")}`;
  }
  function normalizeColorInput(input) {
    let value3 = input.trim();
    if (/^[0-9a-fA-F]{3}$|^[0-9a-fA-F]{6}$|^[0-9a-fA-F]{8}$/.test(value3)) {
      return `#${value3}`;
    }
    if (/^\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}$/.test(value3)) {
      return `rgb(${value3.replace(/,/g, " ").replace(/\s+/g, " ")})`;
    }
    return value3;
  }
  var _colorCtx;
  function parseColor(input) {
    if (!input) return null;
    let normalized = normalizeColorInput(input);
    if (!_colorCtx) _colorCtx = document.createElement("canvas").getContext("2d");
    let ctx = _colorCtx;
    ctx.fillStyle = "#000000";
    ctx.fillStyle = normalized;
    let parsed = ctx.fillStyle;
    if (parsed === "#000000" && normalized !== "#000000" && normalized !== "#000" && normalized !== "black") {
      ctx.fillStyle = "#ffffff";
      ctx.fillStyle = normalized;
      if (ctx.fillStyle === "#ffffff") return null;
      parsed = ctx.fillStyle;
    }
    if (parsed.startsWith("#")) {
      let hex = parsed.slice(1);
      return {
        r: parseInt(hex.slice(0, 2), 16),
        g: parseInt(hex.slice(2, 4), 16),
        b: parseInt(hex.slice(4, 6), 16),
        a: hex.length === 8 ? parseInt(hex.slice(6, 8), 16) / 255 : 1
      };
    }
    let match = parsed.match(/[\d.]+/g);
    if (!match) return null;
    return {
      r: parseFloat(match[0]),
      g: parseFloat(match[1]),
      b: parseFloat(match[2]),
      a: match[3] === void 0 ? 1 : parseFloat(match[3])
    };
  }
  function rgbToHsv(r, g, b) {
    r /= 255;
    g /= 255;
    b /= 255;
    let max2 = Math.max(r, g, b);
    let min2 = Math.min(r, g, b);
    let delta = max2 - min2;
    let h = 0;
    if (delta !== 0) {
      if (max2 === r) h = 60 * ((g - b) / delta % 6);
      else if (max2 === g) h = 60 * ((b - r) / delta + 2);
      else h = 60 * ((r - g) / delta + 4);
    }
    return {
      h: normalizeHue(h),
      s: max2 === 0 ? 0 : delta / max2 * 100,
      v: max2 * 100
    };
  }
  function hsvToRgb(h, s, v) {
    s /= 100;
    v /= 100;
    let c = v * s;
    let x = c * (1 - Math.abs(h / 60 % 2 - 1));
    let m = v - c;
    let r = 0;
    let g = 0;
    let b = 0;
    if (h >= 0 && h < 60) [r, g, b] = [c, x, 0];
    else if (h < 120) [r, g, b] = [x, c, 0];
    else if (h < 180) [r, g, b] = [0, c, x];
    else if (h < 240) [r, g, b] = [0, x, c];
    else if (h < 300) [r, g, b] = [x, 0, c];
    else [r, g, b] = [c, 0, x];
    return {
      r: Math.round((r + m) * 255),
      g: Math.round((g + m) * 255),
      b: Math.round((b + m) * 255)
    };
  }
  function rgbToHsl(r, g, b) {
    r /= 255;
    g /= 255;
    b /= 255;
    let max2 = Math.max(r, g, b);
    let min2 = Math.min(r, g, b);
    let h = 0;
    let s = 0;
    let l = (max2 + min2) / 2;
    if (max2 !== min2) {
      let d = max2 - min2;
      s = l > 0.5 ? d / (2 - max2 - min2) : d / (max2 + min2);
      switch (max2) {
        case r:
          h = (g - b) / d + (g < b ? 6 : 0);
          break;
        case g:
          h = (b - r) / d + 2;
          break;
        default:
          h = (r - g) / d + 4;
          break;
      }
      h /= 6;
    }
    return {
      h: h * 360,
      s: s * 100,
      l: l * 100
    };
  }
  function colorsMatch(a, b) {
    let parsedA = parseColor(a);
    let parsedB = parseColor(b);
    if (!parsedA || !parsedB) return false;
    return parsedA.r === parsedB.r && parsedA.g === parsedB.g && parsedA.b === parsedB.b;
  }
  function deriveSwatchColumns(swatches) {
    if (!swatches.length) return 1;
    let firstTop = swatches[0].offsetTop;
    let cols = 0;
    for (let swatch of swatches) {
      if (swatch.offsetTop !== firstTop) break;
      cols++;
    }
    return cols || 1;
  }
  var UIColorPickerSelect = class extends UIElement {
    mount() {
      this.picker = this.closest("ui-color-picker");
      if (!this.picker) return;
      initFauxButton(this, () => this.picker._disableable?.isDisabled?.() ?? false, () => {
        this.picker.commit();
      });
    }
  };
  element("color-picker", UIColorPicker);
  element("color-picker-select", UIColorPickerSelect);
  function linkExpandedStateToPopover4(el, dialogable) {
    let refresh = () => {
      setAttribute2(el, "aria-expanded", dialogable.getState() ? "true" : "false");
      dialogable.getState() ? setAttribute2(el, "data-open", "") : removeAttribute(el, "data-open");
    };
    dialogable.onChange(() => refresh());
    refresh();
  }
  function initPopover4(root, popover, dialogable, anchorable, onOpen = () => {
  }) {
    let refresh = () => {
      Array.from([root, popover]).forEach((el) => {
        dialogable.getState() ? setAttribute2(el, "data-open", "") : removeAttribute(el, "data-open");
      });
      if (dialogable.getState()) {
        anchorable.reposition();
        onOpen();
      }
    };
    dialogable.onChange(() => refresh());
    refresh();
    dialogable.onChange(() => {
      if (dialogable.getState()) {
        root.dispatchEvent(new Event("open", { bubbles: false, cancelable: false }));
      } else {
        root.dispatchEvent(new Event("close", { bubbles: false, cancelable: false }));
      }
    });
  }
  function controlPopoverWithKeyboard3(el, dialogable) {
    on(el, "keydown", (event) => {
      if (event.key === "ArrowDown" && !dialogable.getState()) {
        dialogable.setState(true);
        event.preventDefault();
        event.stopImmediatePropagation();
      } else if (event.key === "Escape" && dialogable.getState()) {
        dialogable.setState(false);
        event.preventDefault();
        event.stopImmediatePropagation();
      }
    });
  }
  function preventScrollWhenPopoverIsOpen4(root, dialogable) {
    let { lock, unlock } = lockScroll(dialogable.el);
    let isMobile = () => !window.matchMedia("(min-width: 640px)").matches;
    dialogable.onChange(() => {
      let shouldLock = dialogable.getState() && isMobile();
      if (shouldLock && !root.scrollLocked) {
        lock();
        root.scrollLocked = true;
      } else if (!shouldLock && root.scrollLocked) {
        unlock();
        root.scrollLocked = false;
      }
    });
    root.onUnmount(() => {
      if (root.scrollLocked) {
        unlock();
        root.scrollLocked = false;
      }
    });
  }

  // js/switch.js
  var UISwitch = class extends UIControl {
    boot() {
      let button = this;
      this._disableable = new Disableable(this);
      this._selectable = new Selectable(button, {
        toggleable: true,
        dataAttr: "data-checked",
        ariaAttr: "aria-checked",
        value: this.hasAttribute("value") ? this.getAttribute("value") : null,
        label: this.hasAttribute("label") ? this.getAttribute("label") : null,
        selectedInitially: this.hasAttribute("checked")
      });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.getAttribute("value") ?? "on",
        // Default value for checkboxes...
        includeWhenEmpty: false,
        shouldUpdateValue: false
      });
      this.value = this._selectable.getValue();
      this._detangled = detangle();
      this._selectable.onChange(this._detangled(() => {
        this.dispatchEvent(new Event("input", { bubbles: false, cancelable: true }));
        this.dispatchEvent(new Event("change", { bubbles: false, cancelable: true }));
      }));
      setAttribute2(button, "role", "switch");
      this._selectable.onInitAndChange(() => {
        this._submittable.update(this._selectable.getState());
      });
      this._disableable.onInitAndChange((disabled) => {
        disabled ? removeAttribute(button, "tabindex", "0") : setAttribute2(button, "tabindex", "0");
      });
      on(button, "click", this._disableable.disabled((e) => {
        e.preventDefault();
        e.stopPropagation();
      }), { capture: true });
      on(button, "click", this._disableable.enabled((e) => {
        this._selectable.press();
      }));
      on(button, "keydown", this._disableable.enabled((e) => {
        if (e.key === "Enter") {
          this._selectable.press();
          e.preventDefault();
          e.stopPropagation();
        }
      }));
      on(button, "keydown", this._disableable.enabled((e) => {
        if (e.key === " ") {
          e.preventDefault();
          e.stopPropagation();
        }
      }));
      on(button, "keyup", this._disableable.enabled((e) => {
        if (e.key === " ") {
          this._selectable.press();
          e.preventDefault();
          e.stopPropagation();
        }
      }));
      respondToLabelClick2(button);
    }
    get checked() {
      return this._selectable.isSelected();
    }
    set checked(value3) {
      this._detangled(() => {
        value3 ? this._selectable.select() : this._selectable.deselect();
      })();
    }
  };
  function respondToLabelClick2(el) {
    el.closest("label")?.addEventListener("click", (e) => {
      if (!el.contains(e.target)) {
        el.click();
      }
    });
  }
  element("switch", UISwitch);

  // js/field.js
  var UIField = class _UIField extends UIElement {
    mount() {
      this.control = this.fieldWalker().find((el) => this.isControl(el));
      if (!this.control) return;
    }
    associateLabelWithControl(control) {
      if (!control) return;
      if (!this.label) return;
      this.control = control;
      if (this.control.hasAttribute("aria-labelledby")) return;
      setAttribute2(this.elOrButton(this.control), "aria-labelledby", this.label.id);
      if (this.control && !(this.control instanceof UIControl) && this.hasAttribute("disabled")) {
        this.control.setAttribute("disabled", "");
      }
    }
    associateDescriptionWithControl(control) {
      if (!control) return;
      if (!this.description) return;
      this.control = control;
      if (this.control.hasAttribute("aria-describedby")) return;
      setAttribute2(this.elOrButton(this.control), "aria-describedby", this.description.id);
    }
    associateLabel(label) {
      this.label = label;
      on(label, "click", (e) => {
        if (["a", "button", "ui-button"].includes(e.target.localName)) return;
        this.focusOrTogggle(this.control);
      });
      this.control && this.associateLabelWithControl(this.control);
    }
    associateDescription(description) {
      this.description = description;
      this.control && this.associateDescriptionWithControl(this.control);
    }
    fieldWalker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UIField && el !== this) return reject();
        if (el.parentElement.localName === "ui-editor" && el !== this) return reject();
      });
    }
    isControl(el) {
      if (el instanceof UIControl) return true;
      if (el.matches("input, textarea, select")) return true;
      return false;
    }
    focusOrTogggle(control) {
      if (!control) return;
      if (control.disabled || control.hasAttribute("disabled")) return;
      let isCheckable = control.localName === "input" && ["checkbox", "radio"].includes(control.type) || ["ui-switch", "ui-radio", "ui-checkbox"].includes(control.localName);
      if (isCheckable) {
        control.click();
        control.focus();
      } else if (control.localName === "input" && ["file"].includes(control.type)) {
        control.click();
      } else if (["ui-select", "ui-date-picker", "ui-time-picker", "ui-otp", "ui-color-picker"].includes(control.localName)) {
        control.trigger()?.focus();
      } else if (["ui-editor"].includes(control.localName)) {
        control.focus();
      } else if (["ui-composer"].includes(control.localName)) {
        control.focusInput();
      } else {
        control.focus();
      }
    }
    elOrButton(el) {
      if (el instanceof UIElement && el.firstElementChild instanceof HTMLButtonElement) return el.firstElementChild;
      return el;
    }
  };
  var UILabel = class extends UIElement {
    mount() {
      assignId(this, "label");
      setAttribute2(this, "aria-hidden", "true");
      this.closest("ui-field")?.associateLabel(this);
    }
  };
  var UIDescription = class extends UIElement {
    mount() {
      assignId(this, "description");
      setAttribute2(this, "aria-hidden", "true");
      this.closest("ui-field")?.associateDescription(this);
    }
  };
  element("field", UIField);
  element("label", UILabel);
  element("description", UIDescription);

  // js/legend.js
  var UILegend = class extends UIElement {
    mount() {
      let id = assignId(this, "legend");
      let fieldset = this.closest("fieldset");
      if (fieldset) {
        setAttribute2(fieldset, "aria-labelledby", id);
      }
    }
  };
  element("legend", UILegend);

  // js/chart/chart.js
  function generateChartObject(config) {
    let scaleFns = {
      "time": scaleTime,
      "linear": scaleLinear,
      "categorical": scaleCategorical
    };
    let dataMemoKey = 0;
    let dimensionsMemoKey = 0;
    let memoizedValues = {};
    let memoize = (name, key, fn) => {
      let memoKey = `${name}-${key}`;
      for (let existingKey in memoizedValues) {
        if (existingKey.startsWith(`${name}-`) && !existingKey.endsWith(String(key))) {
          delete memoizedValues[existingKey];
        }
      }
      if (!memoizedValues[memoKey]) {
        memoizedValues[memoKey] = fn();
      }
      return memoizedValues[memoKey];
    };
    let chart = {
      locale: config.locale,
      data: [],
      stacksMetadata: config.stacksMetadata,
      groupsMetadata: config.groupsMetadata,
      width: config.width,
      height: config.height,
      inset: config.inset,
      axes: {
        x: {
          axis: "x",
          key: config.axes.x.key,
          format: config.axes.x.format,
          inset: { start: 0, end: 0 },
          position: config.axes.x.position || "bottom",
          tickStart: config.axes.x.tickStart,
          tickEnd: config.axes.x.tickEnd,
          tickCount: config.axes.x.tickCount,
          tickStep: config.axes.x.tickStep,
          tickValues: config.axes.x.tickValues,
          tickSuffix: config.axes.x.tickSuffix,
          tickPrefix: config.axes.x.tickPrefix,
          asArea: config.axes.x.asArea,
          get rawValues() {
            return memoize("x.rawValues", dataMemoKey, () => chart.data.map((d) => d[this.key]));
          },
          get type() {
            return memoize("x.type", dataMemoKey, () => {
              return config.axes.x.scale || (this.rawValues.every((v) => isDateish(v)) ? "time" : "categorical");
            });
          },
          get interval() {
            return memoize("x.interval", dataMemoKey, () => {
              return config.axes.x.interval || "auto";
            });
          },
          get values() {
            return memoize("x.values", dataMemoKey, () => this.rawValues.map((v) => {
              if (this.type === "time") {
                return dateFromString(v);
              } else if (this.type === "linear") {
                return Number(v);
              } else {
                return v;
              }
            }));
          },
          get points() {
            return memoize("x.points", dataMemoKey + dimensionsMemoKey, () => {
              return chart.data.map((datum) => {
                let { start, center, end, width } = this.scale(datum[this.key], { area: this.asArea });
                return {
                  start,
                  center,
                  end,
                  width,
                  datum
                };
              });
            });
          },
          get ticks() {
            return memoize("x.ticks", dataMemoKey, () => {
              let xTickValues = generateTickValues(chart.data, this, generateDomain(this.type, this.values));
              let xTickLabels = generateTickLabels(xTickValues, this.type, this.format, this.tickSuffix, this.tickPrefix, this.interval, chart.locale);
              return xTickValues.map((value3, index) => ({ value: value3, label: xTickLabels[index] }));
            });
          },
          get domain() {
            return memoize("x.domain", dataMemoKey, () => generateDomain(this.type, [...this.ticks.map((t) => t.value), ...this.values]));
          },
          get range() {
            return memoize("x.range", dimensionsMemoKey, () => [chart.inset.left + this.inset.start, chart.width - chart.inset.right - this.inset.end]);
          },
          get scale() {
            return memoize("x.scale", dimensionsMemoKey + dataMemoKey, () => scaleFns[this.type](this.domain, this.range, this.values));
          }
        },
        y: {
          axis: "y",
          keys: config.axes.y.keys,
          format: config.axes.y.format,
          inset: { start: 0, end: 0 },
          position: config.axes.y.position || "left",
          tickStart: config.axes.y.tickStart,
          tickEnd: config.axes.y.tickEnd,
          tickCount: config.axes.y.tickCount,
          tickStep: config.axes.y.tickStep,
          tickValues: config.axes.y.tickValues,
          tickSuffix: config.axes.y.tickSuffix,
          tickPrefix: config.axes.y.tickPrefix,
          get values() {
            return memoize("y.values", dataMemoKey, () => {
              let groupsData = chart.data.flatMap((d) => chart.groupsMetadata.reduce((acc, groupMetadata) => {
                groupMetadata.children.forEach((itemMetadata) => {
                  if (itemMetadata.type === "bar") {
                    if (!itemMetadata.field) return;
                    if (d[itemMetadata.field] === void 0) return;
                    acc.push(d[itemMetadata.field]);
                  }
                  if (itemMetadata.type === "stack") {
                    let gap = Number(itemMetadata.gap.replace("px", ""));
                    let positiveValue = 0;
                    let negativeValue = 0;
                    let hasPositive = false;
                    let hasNegative = false;
                    itemMetadata.children.forEach((childMetadata) => {
                      if (childMetadata.type === "bar") {
                        if (!childMetadata.field) return;
                        if (d[childMetadata.field] === void 0) return;
                        let val = d[childMetadata.field];
                        if (val >= 0) {
                          if (hasPositive) positiveValue += gap;
                          positiveValue += val;
                          hasPositive = true;
                        } else {
                          if (hasNegative) negativeValue -= gap;
                          negativeValue += val;
                          hasNegative = true;
                        }
                      }
                    });
                    if (hasPositive) acc.push(positiveValue);
                    if (hasNegative) acc.push(negativeValue);
                  }
                });
                return acc;
              }, []));
              let stacksData = chart.data.flatMap((d) => chart.stacksMetadata.reduce((acc, stackMetadata) => {
                let gap = Number(stackMetadata.gap.replace("px", ""));
                let positiveValue = 0;
                let negativeValue = 0;
                let hasPositive = false;
                let hasNegative = false;
                stackMetadata.children.forEach((childMetadata) => {
                  if (childMetadata.type === "bar") {
                    if (!childMetadata.field) return;
                    if (d[childMetadata.field] === void 0) return;
                    let val = d[childMetadata.field];
                    if (val >= 0) {
                      if (hasPositive) positiveValue += gap;
                      positiveValue += val;
                      hasPositive = true;
                    } else {
                      if (hasNegative) negativeValue -= gap;
                      negativeValue += val;
                      hasNegative = true;
                    }
                  }
                });
                if (hasPositive) acc.push(positiveValue);
                if (hasNegative) acc.push(negativeValue);
                return acc;
              }, []));
              let data = chart.data.flatMap((d) => this.keys.reduce((acc, key) => {
                if (d[key] === void 0) {
                  return acc;
                }
                acc.push(d[key]);
                return acc;
              }, []));
              return [...groupsData, ...stacksData, ...data];
            });
          },
          get type() {
            return memoize("y.type", dataMemoKey, () => {
              return config.axes.y.scale || "linear";
            });
          },
          get interval() {
            return memoize("y.interval", dataMemoKey, () => {
              return config.axes.y.interval || "auto";
            });
          },
          get ticks() {
            return memoize("y.ticks", dataMemoKey, () => {
              let yTickValues = generateTickValues(chart.data, this, generateDomain(this.type, this.values));
              let yTickLabels = generateTickLabels(yTickValues, this.type, this.format, this.tickSuffix, this.tickPrefix, this.interval, chart.locale);
              return yTickValues.map((value3, index) => ({ value: value3, label: yTickLabels[index] }));
            });
          },
          get domain() {
            return memoize("y.domain", dataMemoKey, () => generateDomain(this.type, [...this.ticks.map((t) => t.value), ...this.values]));
          },
          get range() {
            return memoize("y.range", dimensionsMemoKey, () => [chart.height - chart.inset.bottom - this.inset.end, chart.inset.top + this.inset.start]);
          },
          get scale() {
            return memoize("y.scale", dimensionsMemoKey + dataMemoKey, () => scaleFns[this.type](this.domain, this.range, this.values));
          }
        }
      },
      get series() {
        return memoize("series", dataMemoKey + dimensionsMemoKey, () => this.axes.y.keys.reduce((acc, key) => {
          if (!chart.data[0].hasOwnProperty(key)) {
            console.warn(`ui-chart: series field "${key}" does not exist`);
            return acc;
          }
          let points = chart.data.map((datum) => ({
            x: chart.axes.x.scale(datum[chart.axes.x.key], { area: chart.axes.x.asArea }).center,
            y: chart.axes.y.scale(datum[key]).center,
            datum
          }));
          let series = {
            field: key,
            values: chart.data.map((d) => d[key]),
            bars({ barWidth, minHeight, radius }) {
              let {
                start: groupStart,
                center: groupCenter,
                width: fullGroupWidth
              } = chart.axes.x.scale(chart.axes.x.domain[0], { area: chart.axes.x.asArea });
              let barRadius = radius;
              if (barWidth.match(/^(\d+)%$/)) {
                barWidth = fullGroupWidth * Number(barWidth.replace("%", "")) / 100;
              } else {
                barWidth = Number(barWidth.replace("px", ""));
              }
              let groupStartOffset = (fullGroupWidth - barWidth) / 2;
              return chart.data.map((d) => {
                let value3 = d[key];
                let { start } = chart.axes.x.scale(d[chart.axes.x.key], { area: chart.axes.x.asArea });
                let x = start + groupStartOffset;
                let y = value3 >= 0 ? chart.axes.y.scale(value3).center : chart.axes.y.scale(0).center;
                let height = value3 >= 0 ? chart.axes.y.scale(0).center - y : chart.axes.y.scale(value3).center - y;
                let width = barWidth;
                let radius2 = barRadius;
                if (height < minHeight) {
                  y -= minHeight - height;
                  height = minHeight;
                }
                if (value3 < 0) {
                  radius2 = {
                    topLeft: radius2.bottomLeft,
                    topRight: radius2.bottomRight,
                    bottomRight: radius2.topRight,
                    bottomLeft: radius2.topLeft
                  };
                }
                return {
                  x,
                  y,
                  width,
                  height,
                  path: () => {
                    return getBarPath(x, y, width, height, radius2);
                  }
                };
              });
            },
            linePath(curve = "smooth") {
              let points2 = this.points.map((p) => [p.x, p.y]);
              return {
                smooth: smoothX,
                none: noCurve
              }[curve](points2);
            },
            areaPath(curve = "smooth") {
              let linePath = {
                smooth: smoothX,
                none: noCurve
              }[curve](this.points.map((p) => [p.x, p.y]));
              let firstPointX = this.points[0].x;
              let lastPointX = this.points[this.points.length - 1].x;
              let baselineY = chart.axes.y.scale(chart.axes.y.domain[0]).center;
              let areaPath = `${linePath} L${lastPointX},${baselineY} L${firstPointX},${baselineY} Z`;
              return areaPath;
            },
            points
          };
          acc[key] = series;
          return acc;
        }, {}));
      },
      get stackedSeries() {
        return memoize("stackedSeries", dataMemoKey + dimensionsMemoKey, () => {
          let data = {};
          let filteredMetadata = [];
          return chart.stacksMetadata.map((stackMetadata) => {
            stackMetadata.children.forEach((childMetadata) => {
              if (childMetadata.type === "bar") {
                if (!childMetadata.field) return;
                if (!chart.data[0].hasOwnProperty(childMetadata.field)) {
                  console.warn(`ui-chart: series field "${childMetadata.field}" does not exist`);
                  return;
                }
                data[childMetadata.field] = chart.data.map((d) => d[childMetadata.field]);
                filteredMetadata.push(childMetadata);
              }
            });
            let series = {
              bars() {
                let {
                  start: groupStart,
                  center: groupCenter,
                  width: fullGroupWidth
                } = chart.axes.x.scale(chart.axes.x.domain[0], { area: chart.axes.x.asArea });
                let stackWidth = stackMetadata.width ?? "90%";
                if (stackWidth.match(/^(\d+)%$/)) {
                  stackWidth = fullGroupWidth * Number(stackWidth.replace("%", "")) / 100;
                } else {
                  stackWidth = Number(stackWidth.replace("px", ""));
                }
                let stackStartOffset = (fullGroupWidth - stackWidth) / 2;
                let stackGap = Number(stackMetadata.gap.replace("px", ""));
                let previousPositiveHeight = [];
                let previousNegativeHeight = [];
                let stackRadius = stackMetadata.radius;
                let topBarField = [];
                let bottomBarField = [];
                if (stackRadius) {
                  filteredMetadata.forEach((itemMetadata) => {
                    if (itemMetadata.type === "bar") {
                      data[itemMetadata.field].forEach((value3, index) => {
                        if (value3 >= 0) {
                          topBarField[index] = itemMetadata.field;
                          if (bottomBarField[index] === void 0) {
                            bottomBarField[index] = itemMetadata.field;
                          }
                        } else {
                          bottomBarField[index] = itemMetadata.field;
                          if (topBarField[index] === void 0) {
                            topBarField[index] = itemMetadata.field;
                          }
                        }
                      });
                    }
                  });
                }
                return filteredMetadata.reduce((acc, itemMetadata) => {
                  if (itemMetadata.type === "bar") {
                    let bars = [];
                    data[itemMetadata.field].forEach((value3, index) => {
                      if (previousPositiveHeight[index] === void 0) {
                        previousPositiveHeight[index] = 0;
                        previousNegativeHeight[index] = 0;
                      }
                      let { start } = chart.axes.x.scale(chart.data[index][chart.axes.x.key], { area: chart.axes.x.asArea });
                      let x = start + stackStartOffset;
                      let y = value3 >= 0 ? chart.axes.y.scale(value3).center : chart.axes.y.scale(0).center;
                      let height = value3 >= 0 ? chart.axes.y.scale(0).center - y : chart.axes.y.scale(value3).center - y;
                      let width = stackWidth;
                      let minHeight = Number(itemMetadata.minHeight.replace("px", ""));
                      let radius = itemMetadata.radius;
                      if (stackRadius) {
                        let isTopBar = itemMetadata.field === topBarField[index];
                        let isBottomBar = itemMetadata.field === bottomBarField[index];
                        let visualRadius = { topLeft: 0, topRight: 0, bottomLeft: 0, bottomRight: 0 };
                        if (isTopBar) {
                          visualRadius.topLeft = stackRadius.topLeft;
                          visualRadius.topRight = stackRadius.topRight;
                        }
                        if (isBottomBar) {
                          visualRadius.bottomLeft = stackRadius.bottomLeft;
                          visualRadius.bottomRight = stackRadius.bottomRight;
                        }
                        if (value3 < 0) {
                          radius = {
                            topLeft: visualRadius.bottomLeft,
                            topRight: visualRadius.bottomRight,
                            bottomLeft: visualRadius.topLeft,
                            bottomRight: visualRadius.topRight
                          };
                        } else {
                          radius = visualRadius;
                        }
                      }
                      y = value3 >= 0 ? y - previousPositiveHeight[index] : y + previousNegativeHeight[index];
                      if (height < minHeight) {
                        y -= minHeight - height;
                        height = minHeight;
                      }
                      if (value3 < 0) {
                        radius = {
                          topLeft: radius.bottomLeft,
                          topRight: radius.bottomRight,
                          bottomRight: radius.topRight,
                          bottomLeft: radius.topLeft
                        };
                      }
                      let bar = {
                        x,
                        y,
                        width,
                        height,
                        path: () => {
                          return getBarPath(x, y, width, height, radius);
                        }
                      };
                      if (value3 >= 0) {
                        previousPositiveHeight[index] += height + stackGap;
                      } else {
                        previousNegativeHeight[index] += height + stackGap;
                      }
                      bars.push(bar);
                    });
                    acc[itemMetadata.field] = bars;
                  }
                  return acc;
                }, {});
              }
            };
            return series;
          });
        });
      },
      get groupedSeries() {
        return memoize("groupedSeries", dataMemoKey + dimensionsMemoKey, () => chart.groupsMetadata.map((groupMetadata) => {
          let data = {};
          let filteredMetadata = [];
          groupMetadata.children.forEach((itemMetadata) => {
            if (itemMetadata.type === "bar") {
              if (!itemMetadata.field) return;
              if (!chart.data[0].hasOwnProperty(itemMetadata.field)) {
                console.warn(`ui-chart: series field "${itemMetadata.field}" does not exist`);
                return;
              }
              data[itemMetadata.field] = chart.data.map((d) => d[itemMetadata.field]);
              filteredMetadata.push(itemMetadata);
            } else if (itemMetadata.type === "stack") {
              let childrenMetadata = [];
              itemMetadata.children.forEach((child) => {
                if (child.type === "bar") {
                  if (!child.field) return;
                  if (!chart.data[0].hasOwnProperty(child.field)) {
                    console.warn(`ui-chart: series field "${child.field}" does not exist`);
                    return;
                  }
                  data[child.field] = chart.data.map((d) => d[child.field]);
                  childrenMetadata.push(child);
                }
              });
              if (childrenMetadata.length > 0) {
                let stackMetadata = {
                  ...itemMetadata,
                  children: childrenMetadata
                };
                filteredMetadata.push(stackMetadata);
              }
            }
          });
          let series = {
            keys: filteredMetadata,
            // @todo: Review whether these are needed...
            values: data,
            // @todo: Review whether these are needed...
            bars() {
              let {
                start: groupStart,
                center: groupCenter,
                width: fullGroupWidth
              } = chart.axes.x.scale(chart.axes.x.domain[0], { area: chart.axes.x.asArea });
              let groupWidth = groupMetadata.width ?? "90%";
              if (groupWidth.match(/^(\d+)%$/)) {
                groupWidth = fullGroupWidth * Number(groupWidth.replace("%", "")) / 100;
              } else {
                groupWidth = Number(groupWidth.replace("px", ""));
              }
              let groupStartOffset = (fullGroupWidth - groupWidth) / 2;
              let gap = groupMetadata.gap ?? "2%";
              if (gap.match(/^(\d+)%$/)) {
                gap = groupWidth * Number(gap.replace("%", "")) / 100;
              } else {
                gap = Number(gap.replace("px", ""));
              }
              let countOfBars = filteredMetadata.length;
              let totalGap = gap * (countOfBars - 1);
              let countOfBarsWithoutWidth = countOfBars;
              let totalSpecifiedWidths = filteredMetadata.reduce((acc, itemMetadata) => {
                if (!itemMetadata.width) return acc;
                if (itemMetadata.width.match(/^(\d+)%$/)) {
                  acc += groupWidth * Number(itemMetadata.width.replace("%", "")) / 100;
                } else {
                  acc += Number(itemMetadata.width.replace("px", ""));
                }
                countOfBarsWithoutWidth--;
                return acc;
              }, 0);
              let barWidth = countOfBarsWithoutWidth > 0 ? (groupWidth - totalSpecifiedWidths - totalGap) / countOfBarsWithoutWidth : 0;
              if (barWidth === 0) {
                groupStartOffset = (fullGroupWidth - (totalSpecifiedWidths + totalGap)) / 2;
              }
              let itemOffset = groupStartOffset;
              return filteredMetadata.reduce((acc, itemMetadata, index) => {
                let width = barWidth;
                if (itemMetadata.width) {
                  if (itemMetadata.width.match(/^(\d+)%$/)) {
                    width = groupWidth * Number(itemMetadata.width.replace("%", "")) / 100;
                  } else {
                    width = Number(itemMetadata.width.replace("px", ""));
                  }
                }
                if (index > 0) {
                  itemOffset += gap;
                }
                if (itemMetadata.type === "bar") {
                  let bars = [];
                  data[itemMetadata.field].forEach((value3, index2) => {
                    let { start: groupStart2 } = chart.axes.x.scale(chart.data[index2][chart.axes.x.key], { area: chart.axes.x.asArea });
                    let x = groupStart2 + itemOffset;
                    let y = value3 >= 0 ? chart.axes.y.scale(value3).center : chart.axes.y.scale(0).center;
                    let height = value3 >= 0 ? chart.axes.y.scale(0).center - y : chart.axes.y.scale(value3).center - y;
                    let minHeight = Number(itemMetadata.minHeight.replace("px", ""));
                    let radius = itemMetadata.radius;
                    if (height < minHeight) {
                      y -= minHeight - height;
                      height = minHeight;
                    }
                    if (value3 < 0) {
                      radius = {
                        topLeft: radius.bottomLeft,
                        topRight: radius.bottomRight,
                        bottomRight: radius.topRight,
                        bottomLeft: radius.topLeft
                      };
                    }
                    let bar = {
                      x,
                      y,
                      width,
                      height,
                      path: () => {
                        return getBarPath(x, y, width, height, radius);
                      }
                    };
                    bars.push(bar);
                  });
                  acc[itemMetadata.field] = bars;
                }
                if (itemMetadata.type === "stack") {
                  let stack = [];
                  let stackGap = Number(itemMetadata.gap.replace("px", ""));
                  let previousPositiveHeight = [];
                  let previousNegativeHeight = [];
                  let stackRadius = itemMetadata.radius;
                  let topBarField = [];
                  let bottomBarField = [];
                  if (stackRadius) {
                    itemMetadata.children.forEach((child) => {
                      if (child.type === "bar") {
                        data[child.field].forEach((value3, index2) => {
                          if (value3 >= 0) {
                            topBarField[index2] = child.field;
                            if (bottomBarField[index2] === void 0) {
                              bottomBarField[index2] = child.field;
                            }
                          } else {
                            bottomBarField[index2] = child.field;
                            if (topBarField[index2] === void 0) {
                              topBarField[index2] = child.field;
                            }
                          }
                        });
                      }
                    });
                  }
                  itemMetadata.children.forEach((child) => {
                    if (child.type === "bar") {
                      let bars = [];
                      data[child.field].forEach((value3, index2) => {
                        if (previousPositiveHeight[index2] === void 0) {
                          previousPositiveHeight[index2] = 0;
                          previousNegativeHeight[index2] = 0;
                        }
                        let { start } = chart.axes.x.scale(chart.data[index2][chart.axes.x.key], { area: chart.axes.x.asArea });
                        let x = start + itemOffset;
                        let y = value3 >= 0 ? chart.axes.y.scale(value3).center : chart.axes.y.scale(0).center;
                        let height = value3 >= 0 ? chart.axes.y.scale(0).center - y : chart.axes.y.scale(value3).center - y;
                        let minHeight = Number(child.minHeight.replace("px", ""));
                        let radius = child.radius;
                        if (stackRadius) {
                          let isTopBar = child.field === topBarField[index2];
                          let isBottomBar = child.field === bottomBarField[index2];
                          let visualRadius = { topLeft: 0, topRight: 0, bottomLeft: 0, bottomRight: 0 };
                          if (isTopBar) {
                            visualRadius.topLeft = stackRadius.topLeft;
                            visualRadius.topRight = stackRadius.topRight;
                          }
                          if (isBottomBar) {
                            visualRadius.bottomLeft = stackRadius.bottomLeft;
                            visualRadius.bottomRight = stackRadius.bottomRight;
                          }
                          if (value3 < 0) {
                            radius = {
                              topLeft: visualRadius.bottomLeft,
                              topRight: visualRadius.bottomRight,
                              bottomLeft: visualRadius.topLeft,
                              bottomRight: visualRadius.topRight
                            };
                          } else {
                            radius = visualRadius;
                          }
                        }
                        if (height < minHeight) {
                          y -= minHeight - height;
                          height = minHeight;
                        }
                        y = value3 >= 0 ? y - previousPositiveHeight[index2] : y + previousNegativeHeight[index2];
                        if (value3 < 0) {
                          radius = {
                            topLeft: radius.bottomLeft,
                            topRight: radius.bottomRight,
                            bottomRight: radius.topRight,
                            bottomLeft: radius.topLeft
                          };
                        }
                        let bar = {
                          x,
                          y,
                          width,
                          height,
                          path: () => {
                            return getBarPath(x, y, width, height, radius);
                          }
                        };
                        if (value3 >= 0) {
                          previousPositiveHeight[index2] += height + stackGap;
                        } else {
                          previousNegativeHeight[index2] += height + stackGap;
                        }
                        bars.push(bar);
                      });
                      acc[child.field] = bars;
                    }
                  });
                }
                itemOffset += width;
                return acc;
              }, {});
            }
          };
          return series;
        }));
      },
      closestXPoint(xPosition) {
        return this.closestXPoints(xPosition)[0];
      },
      closestXPoints(xPosition) {
        return memoize("closestXPoints", dataMemoKey + dimensionsMemoKey + xPosition, () => {
          let closestPoints = [];
          let minDistance = Infinity;
          let lastPoint = null;
          this.axes.x.points.forEach((point) => {
            const distance = Math.abs(point.center - xPosition);
            if (distance < minDistance) {
              minDistance = distance;
              lastPoint = point;
            }
          });
          lastPoint && closestPoints.push(lastPoint);
          return closestPoints;
        });
      },
      updateDimensions(dimensions, inset) {
        this.width = dimensions.width;
        this.height = dimensions.height;
        this.inset = inset;
        dimensionsMemoKey++;
      },
      updateData(rawData) {
        let data = JSON.parse(JSON.stringify(rawData));
        if (data[0] !== void 0 && typeof data[0] !== "object") {
          data = data.map((d, i) => {
            return {
              value: d
            };
          });
        }
        if (!data.every((d) => d.index)) {
          data.forEach((d, i) => {
            d.index = i;
          });
        }
        this.data = data;
        dataMemoKey++;
      }
    };
    chart.updateData(config.data);
    return chart;
  }
  function generateDomain(type, values) {
    let deduplicatedValues = [...new Set(values)];
    if (type === "time") {
      return generateTimeDomain(deduplicatedValues);
    } else if (type === "linear") {
      deduplicatedValues.sort((a, b) => Number(a) - Number(b));
      if (deduplicatedValues.length === 1) {
        if (deduplicatedValues[0] === 0) {
          return [
            Number(deduplicatedValues[0]),
            1
          ];
        } else if (deduplicatedValues[0] > 0) {
          return [
            0,
            Number(deduplicatedValues[0])
          ];
        } else {
          return [
            Number(deduplicatedValues[0]),
            0
          ];
        }
      }
      return [Number(deduplicatedValues[0]), Number(deduplicatedValues[deduplicatedValues.length - 1])];
    } else {
      return [
        deduplicatedValues[0],
        deduplicatedValues[deduplicatedValues.length - 1]
      ];
    }
  }
  function generateTickValues(data, axis, domain) {
    let tickValues = [];
    if (axis.type === "linear") {
      if (axis.tickValues) {
        return axis.tickValues;
      }
      let [minValue, maxValue] = domain;
      let targetCount = axis.tickCount ? Number(axis.tickCount) : 5;
      let tickStart = axis.tickStart === 0 || axis.tickStart === "0" ? 0 : axis.tickStart || "auto";
      let tickEnd = axis.tickEnd === 0 || axis.tickEnd === "0" ? 0 : axis.tickEnd || "auto";
      if (tickStart === "auto" && minValue > 0 && maxValue > 0) {
        minValue = 0;
      }
      if (tickEnd !== "auto" && tickEnd !== "max" && !isNaN(Number(tickEnd))) {
        maxValue = Number(tickEnd);
      }
      let interval = Number(axis.interval);
      let niceStep;
      if (axis.interval === "auto" || Number.isNaN(interval)) {
        let range = maxValue - minValue;
        let rawStep = range / (targetCount - 1);
        let magnitude = Math.pow(10, Math.floor(Math.log10(rawStep)));
        let normalizedStep = rawStep / magnitude;
        if (normalizedStep < 1.5) niceStep = 1;
        else if (normalizedStep < 3) niceStep = 2;
        else if (normalizedStep < 7) niceStep = 5;
        else niceStep = 10;
        niceStep *= magnitude;
      } else {
        niceStep = interval;
      }
      let start;
      if (tickStart === "auto") {
        start = Math.floor(minValue / niceStep) * niceStep;
      } else if (tickStart === "min") {
        start = minValue;
      } else {
        start = Number(tickStart);
      }
      let currentTick = start;
      while (currentTick <= maxValue + niceStep * 0.1) {
        if (currentTick >= minValue - niceStep * 0.1) {
          tickValues.push(currentTick);
        }
        currentTick += niceStep;
      }
      if (tickEnd === "max") {
        if (Math.abs(tickValues[tickValues.length - 1] - domain[1]) > niceStep * 0.1) {
          tickValues.push(domain[1]);
        }
      } else if (tickEnd !== "auto" && !isNaN(Number(tickEnd))) {
        const end = Number(tickEnd);
        const count = tickValues.length;
        tickValues = Array.from(
          { length: count },
          (_, i) => start + (end - start) * (i / (count - 1))
        );
      }
      if (tickValues.length > targetCount * 2) {
        let step = Math.ceil(tickValues.length / targetCount);
        tickValues = tickValues.filter((_, i) => i % step === 0);
      }
    } else if (axis.type === "time") {
      let tickCount = axis.tickCount || data.length;
      let [_, ticks] = generateDateFormatAndTicks(domain[0], domain[1], tickCount, axis.interval, axis.asArea);
      return ticks;
    } else if (axis.type === "categorical") {
      data.forEach((datum) => {
        let value3 = datum[axis.key];
        tickValues.push(value3);
      });
      if (axis.tickCount && tickValues.length > axis.tickCount) {
        const step = Math.ceil(tickValues.length / axis.tickCount);
        tickValues = tickValues.filter((_, i) => i % step === 0);
      }
    }
    return tickValues;
  }
  function generateTickLabels(tickValues, type, format, tickSuffix, tickPrefix, interval, locale) {
    let labels = [];
    if (type === "time") {
      if (!format) {
        [format] = generateDateFormatAndTicks(tickValues[0], tickValues[tickValues.length - 1], null, interval);
      }
      format = { ...format, timeZone: "UTC" };
      labels = tickValues.map((value3) => new Date(value3).toLocaleString(locale, format));
    } else if (type === "linear") {
      if (format) labels = tickValues.map((value3) => value3.toLocaleString(locale, format));
      else labels = tickValues.map((value3) => value3.toLocaleString(locale));
    } else {
      labels = tickValues.map((value3) => value3 + "");
    }
    if (tickPrefix) labels = labels.map((l) => tickPrefix + l);
    if (tickSuffix) labels = labels.map((l) => l + tickSuffix);
    return labels;
  }
  function scaleCategorical(domain, range, values) {
    const uniqueValues = [...new Set(values)];
    const count = uniqueValues.length;
    const indexMap = new Map(
      uniqueValues.map((category, index) => [category, index])
    );
    return function(value3, { area = false } = {}) {
      if (value3 === null || value3 === void 0) {
        return void 0;
      }
      const index = indexMap.get(value3);
      if (index === void 0) {
        return void 0;
      }
      if (area) {
        let step2 = (range[1] - range[0]) / (area ? count : count - 1);
        let start = range[0] + index * step2;
        let center = start + step2 / 2;
        let end = start + step2;
        return { start, center, end, width: step2 };
      }
      let step = (range[1] - range[0]) / (count - 1);
      let point = range[0] + index * step;
      return {
        start: point,
        center: point,
        end: point,
        width: 0
      };
    };
  }
  function scaleLinear(domain, range, values) {
    const domainDiff = domain[1] - domain[0];
    const rangeDiff = range[1] - range[0];
    return function(value3) {
      let point = range[0] + (value3 - domain[0]) * (rangeDiff / domainDiff);
      return {
        start: point,
        center: point,
        end: point,
        width: 0
      };
    };
  }
  function smoothX(points) {
    if (points.length < 2) return "";
    const sign = (x) => x < 0 ? -1 : 1;
    const calculateSlope = (x0, y0, x1, y1, x2, y2) => {
      const h0 = x1 - x0;
      const h1 = x2 - x1;
      const s0 = (y1 - y0) / (h0 || h1 < 0 && -0);
      const s1 = (y2 - y1) / (h1 || h0 < 0 && -0);
      const p = (s0 * h1 + s1 * h0) / (h0 + h1);
      return (sign(s0) + sign(s1)) * Math.min(Math.abs(s0), Math.abs(s1), 0.5 * Math.abs(p)) || 0;
    };
    const endpointSlope = (x0, y0, x1, y1, t) => {
      const h = x1 - x0;
      return h ? (3 * (y1 - y0) / h - t) / 2 : t;
    };
    let path = `M${points[0][0]},${points[0][1]}`;
    if (points.length === 2) {
      return path + `L${points[1][0]},${points[1][1]}`;
    }
    for (let i = 0; i < points.length - 2; i++) {
      const p0 = points[i];
      const p1 = points[i + 1];
      if (p0[0] === p1[0] && p0[1] === p1[1]) continue;
      const p2 = points[i + 2];
      const t1 = calculateSlope(p0[0], p0[1], p1[0], p1[1], p2[0], p2[1]);
      const dx = (p1[0] - p0[0]) / 3;
      const c1x = p0[0] + dx;
      const c1y = p0[1] + dx * (i === 0 ? endpointSlope(p0[0], p0[1], p1[0], p1[1], t1) : points[i - 1] ? calculateSlope(points[i - 1][0], points[i - 1][1], p0[0], p0[1], p1[0], p1[1]) : t1);
      const c2x = p1[0] - dx;
      const c2y = p1[1] - dx * t1;
      path += `C${c1x},${c1y} ${c2x},${c2y} ${p1[0]},${p1[1]}`;
    }
    const n = points.length;
    const last = points[n - 1];
    const secondLast = points[n - 2];
    if (!(last[0] === secondLast[0] && last[1] === secondLast[1])) {
      const t0 = calculateSlope(
        points[n - 3][0],
        points[n - 3][1],
        secondLast[0],
        secondLast[1],
        last[0],
        last[1]
      );
      const dx = (last[0] - secondLast[0]) / 3;
      const c1x = secondLast[0] + dx;
      const c1y = secondLast[1] + dx * t0;
      const c2x = last[0] - dx;
      const c2y = last[1] - dx * endpointSlope(secondLast[0], secondLast[1], last[0], last[1], t0);
      path += `C${c1x},${c1y} ${c2x},${c2y} ${last[0]},${last[1]}`;
    }
    return path;
  }
  function simpleCurve(points, smoothing = 0.05) {
    if (!points || points.length < 2) return "";
    let path = `M ${points[0][0]} ${points[0][1]}`;
    for (let i = 1; i < points.length; i++) {
      const current = points[i];
      const previous = points[i - 1];
      const prevPoint = points[i - 2] || previous;
      const nextPoint = points[i + 1] || current;
      const cp1x = previous[0] + (nextPoint[0] - prevPoint[0]) * smoothing;
      const cp1y = previous[1] + (nextPoint[1] - prevPoint[1]) * smoothing;
      const cp2x = current[0] - (nextPoint[0] - prevPoint[0]) * smoothing;
      const cp2y = current[1] - (nextPoint[1] - prevPoint[1]) * smoothing;
      path += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${current[0]} ${current[1]}`;
    }
    return path;
  }
  function noCurve(points) {
    return simpleCurve(points, 0);
  }
  function getBarPath(x, y, width, height, radius = null) {
    let topLeftRadius = radius?.topLeft ?? 0;
    let topRightRadius = radius?.topRight ?? 0;
    let bottomRightRadius = radius?.bottomRight ?? 0;
    let bottomLeftRadius = radius?.bottomLeft ?? 0;
    let maxRadius = Math.min(Math.abs(width) / 2, Math.abs(height) / 2);
    topLeftRadius = Math.min(topLeftRadius, maxRadius);
    topRightRadius = Math.min(topRightRadius, maxRadius);
    bottomRightRadius = Math.min(bottomRightRadius, maxRadius);
    bottomLeftRadius = Math.min(bottomLeftRadius, maxRadius);
    if (topLeftRadius === 0 && topRightRadius === 0 && bottomRightRadius === 0 && bottomLeftRadius === 0) {
      return `M${x},${y} h${width} v${height} h-${width} v-${height}`;
    }
    let topY = y;
    let bottomY = y + height;
    let topLeftRounded = topLeftRadius > 0;
    let topRightRounded = topRightRadius > 0;
    let bottomRightRounded = bottomRightRadius > 0;
    let bottomLeftRounded = bottomLeftRadius > 0;
    let leftX = x;
    let rightX = x + width;
    let path = "";
    if (topLeftRounded) {
      path += `M${leftX},${topY + topLeftRadius}`;
      path += `A${topLeftRadius},${topLeftRadius} 0 0 1 ${leftX + topLeftRadius},${topY}`;
    } else {
      path += `M${leftX},${topY}`;
    }
    if (topRightRounded) {
      path += `H${rightX - topRightRadius}`;
      path += `A${topRightRadius},${topRightRadius} 0 0 1 ${rightX},${topY + topRightRadius}`;
    } else {
      path += `H${rightX}`;
    }
    if (bottomRightRounded) {
      path += `V${bottomY - bottomRightRadius}`;
      path += `A${bottomRightRadius},${bottomRightRadius} 0 0 1 ${rightX - bottomRightRadius},${bottomY}`;
    } else {
      path += `V${bottomY}`;
    }
    if (bottomLeftRounded) {
      path += `H${leftX + bottomLeftRadius}`;
      path += `A${bottomLeftRadius},${bottomLeftRadius} 0 0 1 ${leftX},${bottomY - bottomLeftRadius}`;
    } else {
      path += `H${leftX}`;
    }
    path += `V${topY + (topLeftRounded ? topLeftRadius : 0)}`;
    path += `Z`;
    return path;
  }
  function isDateish(value3) {
    return value3 instanceof Date || typeof value3 === "string" && value3.includes("-") && !isNaN(Date.parse(value3));
  }
  function generateTimeDomain(values) {
    values.sort((a, b) => {
      if (typeof a === "string") {
        return a.localeCompare(b);
      }
      return a - b;
    });
    return [
      dateFromString(values[0]),
      dateFromString(values[values.length - 1])
    ];
  }
  function scaleTime(domain, range, values) {
    let minValue = new Date(domain[0]).getTime();
    let maxValue = new Date(domain[1]).getTime();
    let domainDiff = maxValue - minValue;
    let rangeDiff = range[1] - range[0];
    const uniqueValues = values ? [...new Set(values)] : [];
    const count = uniqueValues.length;
    return function(value3, { area = false } = {}) {
      if (!value3) return void 0;
      const dateValue = dateFromString(value3);
      if (isNaN(dateValue.getTime())) return void 0;
      const percent = (dateValue.getTime() - minValue) / domainDiff;
      if (area) {
        let index = Math.floor(percent * count);
        index = Math.min(Math.max(0, index), count - 1);
        let step = rangeDiff / count;
        let start = range[0] + index * step;
        let center = start + step / 2;
        let end = start + step;
        return { start, center, end, width: step };
      }
      let point = range[0] + percent * rangeDiff;
      return {
        start: point,
        center: point,
        end: point,
        width: 0
      };
    };
  }
  function generateDateFormatAndTicks(start, end, tickCount, interval, asArea = false) {
    let startDate = start;
    let endDate = end;
    const diffMs = endDate - startDate;
    const diffSeconds = diffMs / 1e3;
    const diffMinutes = diffSeconds / 60;
    const diffHours = diffMinutes / 60;
    const diffDays = diffHours / 24;
    const diffMonths = diffDays / 30;
    const diffYears = diffDays / 365;
    let format;
    let incrementFn;
    let firstTick;
    switch (interval) {
      case "second":
        format = { hour: "numeric", minute: "numeric", second: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds() + 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours(), startDate.getUTCMinutes(), startDate.getUTCSeconds()));
        break;
      case "minute":
        format = { hour: "numeric", minute: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes() + 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours(), startDate.getUTCMinutes()));
        break;
      case "hour":
        format = { hour: "numeric", minute: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours() + 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours()));
        break;
      case "day":
        format = { day: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
        break;
      case "week":
        format = { day: "numeric", month: "short" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + 7));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
        break;
      case "month":
        format = { month: "short", day: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + 1, 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), 1));
        break;
      case "year":
        format = { year: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear() + 1, 0, 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), 0, 1));
        break;
      default:
    }
    if (incrementFn) {
    } else if (diffHours < 1) {
      format = { hour: "numeric", minute: "numeric" };
      let hourStep = tickCount ? Math.max(1, Math.floor(diffMinutes / (tickCount - 1))) : 1;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes() + hourStep));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours()));
    } else if (diffDays < 1) {
      format = { hour: "numeric" };
      let hourStep = tickCount ? Math.max(1, Math.floor(diffHours / (tickCount - 1))) : 1;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours() + hourStep));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), 0));
    } else if (diffDays <= 7) {
      format = { weekday: "short", hour: "numeric" };
      let hourStep = tickCount ? Math.max(1, Math.floor(diffHours / (tickCount - 1))) : 6;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours() + hourStep));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), 0));
    } else if (diffDays <= 30) {
      format = { month: "short", day: "numeric" };
      let dateStep = tickCount ? Math.max(1, Math.floor(diffDays / (tickCount - 1))) : 1;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + dateStep));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
    } else if (diffMonths <= 6) {
      format = { month: "short", day: "numeric" };
      let dateStep = tickCount ? Math.max(1, Math.floor(diffDays / (tickCount - 1))) : 7;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + dateStep));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
    } else if (diffYears < 2) {
      format = { month: "short" };
      let monthStep = tickCount ? Math.max(1, Math.floor(diffMonths / (tickCount - 1))) : 1;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + monthStep, 1));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth()));
    } else {
      format = { year: "numeric" };
      let yearStep = tickCount ? Math.max(1, Math.floor(diffYears / (tickCount - 1))) : 1;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear() + yearStep, 0, 1));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), 0, 1));
    }
    let startingTick = startDate;
    if (asArea) {
      let nextTick = incrementFn(firstTick);
      while (nextTick <= startDate) {
        firstTick = nextTick;
        nextTick = incrementFn(firstTick);
      }
      startingTick = firstTick;
    }
    let ticks = [startingTick];
    let currentTick = startingTick;
    while (currentTick <= endDate) {
      currentTick = incrementFn(currentTick);
      if (currentTick <= endDate) {
        ticks.push(new Date(currentTick.getTime()));
      }
    }
    return [format, ticks];
  }
  function dateFromString(value3) {
    if (value3 instanceof Date) return value3;
    let {
      year,
      month,
      day,
      hour,
      minute,
      second,
      millisecond
    } = parseDateString(value3);
    return new Date(Date.UTC(year, month - 1, day, hour, minute, second, millisecond));
  }
  function parseDateString(dateStr) {
    const match = dateStr.match(/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?(?:T(\d{2}):(\d{2}):(\d{2})(?:\.(\d+))?(Z)?)?$/);
    if (!match) {
      throw new Error("Invalid date format");
    }
    return {
      year: match[1] ? parseInt(match[1], 10) : null,
      month: match[2] ? parseInt(match[2], 10) : null,
      day: match[3] ? parseInt(match[3], 10) : null,
      hour: match[4] ? parseInt(match[4], 10) : null,
      minute: match[5] ? parseInt(match[5], 10) : null,
      second: match[6] ? parseInt(match[6], 10) : null,
      millisecond: match[7] ? parseInt(match[7].padEnd(3, "0"), 10) : null,
      utc: !!match[8]
    };
  }

  // js/chart/index.js
  var UIChart = class extends HTMLElement {
    constructor() {
      super();
      this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
      let svgTemplate = this.querySelector('template[name="svg"]');
      let svg = hydrateTemplate(svgTemplate);
      svgTemplate.after(svg);
      this.init(svgTemplate, svg);
    }
    init(svgTemplate, svg) {
      if (!this._initialized) {
        this._data = this.hasAttribute("value") ? JSON.parse(this.getAttribute("value")) : [];
        this._observable = new Observable();
        this._selectable = { getState: () => this._data, setState: (value3) => {
          this._data = value3;
          this._observable.notify("data", this._data);
        } };
        this._controllable = new Controllable(this);
        this._controllable.initial((initial) => initial && this._selectable.setState(initial));
        this._controllable.getter(() => this._selectable.getState());
        this._controllable.setter((value3) => {
          this._selectable.setState(value3);
        });
        this._initialized = true;
      }
      let locale = this.hasAttribute("locale") ? this.getAttribute("locale") : getLocale();
      let hasBars = false;
      let templates = {
        svg: svgTemplate,
        bars: {},
        lines: {},
        areas: {},
        points: {},
        groups: [],
        stacks: [],
        cursor: svgTemplate.content.querySelector('template[name="cursor"]'),
        zeroLine: svgTemplate.content.querySelector('template[name="zero-line"]'),
        axes: {
          x: {
            template: svgTemplate.content.querySelector('template[name="axis"][axis="x"]'),
            axisLine: svgTemplate.content.querySelector('template[name="axis"][axis="x"] template[name="axis-line"]'),
            gridLine: svgTemplate.content.querySelector('template[name="axis"][axis="x"] template[name="grid-line"]'),
            tickMark: svgTemplate.content.querySelector('template[name="axis"][axis="x"] template[name="tick-mark"]'),
            tickLabel: svgTemplate.content.querySelector('template[name="axis"][axis="x"] template[name="tick-label"]')
          },
          y: {
            template: svgTemplate.content.querySelector('template[name="axis"][axis="y"]'),
            axisLine: svgTemplate.content.querySelector('template[name="axis"][axis="y"] template[name="axis-line"]'),
            gridLine: svgTemplate.content.querySelector('template[name="axis"][axis="y"] template[name="grid-line"]'),
            tickMark: svgTemplate.content.querySelector('template[name="axis"][axis="y"] template[name="tick-mark"]'),
            tickLabel: svgTemplate.content.querySelector('template[name="axis"][axis="y"] template[name="tick-label"]')
          }
        },
        tooltip: this.querySelector('template[name="tooltip"]'),
        summary: this.querySelector('template[name="summary"]')
      };
      svgTemplate.content.querySelectorAll('template[name="group"]').forEach((template) => {
        templates.groups.push(template);
      });
      svgTemplate.content.querySelectorAll('template[name="stack"]:not(template[name="group"] template[name="stack"])').forEach((template) => {
        templates.stacks.push(template);
      });
      svgTemplate.content.querySelectorAll('template[name="bar"]:not(template[name="stack"] template[name="bar"]):not(template[name="group"] template[name="bar"])').forEach((template) => {
        templates.bars[template.getAttribute("field")] = template;
        hasBars = true;
      });
      svgTemplate.content.querySelectorAll('template[name="line"]').forEach((template) => {
        templates.lines[template.getAttribute("field")] = template;
      });
      svgTemplate.content.querySelectorAll('template[name="area"]').forEach((template) => {
        templates.areas[template.getAttribute("field")] = template;
      });
      svgTemplate.content.querySelectorAll('template[name="point"]').forEach((template) => {
        templates.points[template.getAttribute("field")] = template;
      });
      let repositionCursor = () => {
      };
      let repositionTooltip = () => {
      };
      let updateSummary = () => {
      };
      let activatePoint = () => {
      };
      let cursor = null;
      let cursorType = "line";
      let overlay = null;
      let tooltip = null;
      let summary = null;
      let stacksMetadata = [];
      svgTemplate.content.querySelectorAll('template[name="stack"]:not(template[name="group"] template[name="stack"])').forEach((stackTemplate) => {
        let stackMetadata = {
          width: stackTemplate.getAttribute("width") ?? "90%",
          gap: stackTemplate.getAttribute("gap") ?? "0",
          radius: stackTemplate.hasAttribute("radius") ? parseRadius(stackTemplate.getAttribute("radius")) : null,
          children: []
        };
        stackTemplate.querySelectorAll('template[name="bar"]').forEach((barTemplate) => {
          let barMetadata = {
            type: "bar",
            field: barTemplate.getAttribute("field"),
            width: barTemplate.getAttribute("width"),
            minHeight: barTemplate.getAttribute("min-height") ?? "0",
            radius: parseRadius(barTemplate.getAttribute("radius") ?? "0")
          };
          stackMetadata.children.push(barMetadata);
          hasBars = true;
        });
        stacksMetadata.push(stackMetadata);
      });
      let groupsMetadata = [];
      svgTemplate.content.querySelectorAll('template[name="group"]').forEach((groupTemplate) => {
        let groupMetadata = {
          width: groupTemplate.getAttribute("width") ?? "90%",
          gap: groupTemplate.getAttribute("gap") ?? "2%",
          children: []
        };
        groupTemplate.querySelectorAll('template[name="stack"], template[name="bar"]:not(template[name="stack"] template[name="bar"])').forEach((itemTemplate) => {
          let itemMetadata = {};
          if (itemTemplate.getAttribute("name") === "stack") {
            itemMetadata = {
              type: "stack",
              width: itemTemplate.getAttribute("width"),
              gap: itemTemplate.getAttribute("gap") ?? "0",
              radius: itemTemplate.hasAttribute("radius") ? parseRadius(itemTemplate.getAttribute("radius")) : null,
              children: []
            };
            itemTemplate.querySelectorAll('template[name="bar"]').forEach((barTemplate) => {
              let barMetadata = {
                type: "bar",
                field: barTemplate.getAttribute("field"),
                width: barTemplate.getAttribute("width"),
                minHeight: barTemplate.getAttribute("min-height") ?? "0",
                radius: parseRadius(barTemplate.getAttribute("radius") ?? "0")
              };
              itemMetadata.children.push(barMetadata);
              hasBars = true;
            });
          } else if (itemTemplate.getAttribute("name") === "bar") {
            itemMetadata = {
              type: "bar",
              field: itemTemplate.getAttribute("field"),
              width: itemTemplate.getAttribute("width"),
              minHeight: itemTemplate.getAttribute("min-height") ?? "0",
              radius: parseRadius(itemTemplate.getAttribute("radius") ?? "4 0")
            };
            hasBars = true;
          }
          groupMetadata.children.push(itemMetadata);
        });
        groupsMetadata.push(groupMetadata);
      });
      overlay = document.createElementNS("http://www.w3.org/2000/svg", "rect");
      overlay.setAttribute("data-overlay", "");
      overlay.setAttribute("fill", "none");
      overlay.setAttribute("pointer-events", "all");
      overlay.addEventListener("mousemove", throttle(function(event) {
        repositionCursor(event);
        repositionTooltip(event);
        updateSummary(event);
        activatePoint(event);
      }, 1));
      overlay.addEventListener("mouseleave", () => {
        repositionCursor(null);
        repositionTooltip(null);
        updateSummary(null);
        activatePoint(null);
      });
      svg.appendChild(overlay);
      if (templates.cursor) {
        cursor = hydrateSvgTemplate(templates.cursor);
        cursor.setAttribute("data-cursor", "");
        svg.appendChild(cursor);
      }
      let gutter = { left: 8, right: 8, top: 8, bottom: 8 };
      if (templates.svg?.hasAttribute("gutter")) {
        let values = templates.svg?.getAttribute("gutter").split(" ").map((i) => i.replace("px", "")).map(Number);
        if (values.length === 1) {
          gutter.top = values[0];
          gutter.right = values[0];
          gutter.bottom = values[0];
          gutter.left = values[0];
        } else if (values.length === 2) {
          gutter.top = values[0];
          gutter.right = values[1];
          gutter.bottom = values[0];
          gutter.left = values[1];
        } else if (values.length === 3) {
          gutter.top = values[0];
          gutter.right = values[1];
          gutter.bottom = values[2];
          gutter.left = values[1];
        } else if (values.length === 4) {
          gutter.top = values[0];
          gutter.right = values[1];
          gutter.bottom = values[2];
          gutter.left = values[3];
        }
      }
      let [xKey, yKeys] = discoverXandYKeys(svg, hasBars);
      let svgRect = svg.parentElement.getBoundingClientRect();
      if (svgRect.width === 0 || svgRect.height === 0) {
        let visibilityObserver = new ResizeObserver((entries) => {
          let entry = entries[0];
          if (entry.contentRect.width > 0 && entry.contentRect.height > 0) {
            visibilityObserver.disconnect();
            this.init(svgTemplate, svg);
          }
        });
        visibilityObserver.observe(svg.parentElement);
        return;
      }
      if (templates.tooltip) {
        tooltip = hydrateTemplate(templates.tooltip);
        templates.tooltip.after(tooltip);
      }
      if (templates.summary) {
        summary = hydrateTemplate(templates.summary);
        templates.summary.after(summary);
      }
      let chart = generateChartObject({
        locale,
        data: this._data,
        stacksMetadata,
        groupsMetadata,
        width: svgRect.width,
        height: svgRect.height,
        inset: {
          left: 0,
          right: 0,
          top: 0,
          bottom: 0
        },
        axes: {
          x: {
            key: xKey,
            format: templates.axes.x.template?.hasAttribute("format") ? JSON.parse(templates.axes.x.template?.getAttribute("format")) : null,
            scale: templates.axes.x.template?.getAttribute("scale"),
            interval: templates.axes.x.template?.getAttribute("interval"),
            position: templates.axes.x.template?.getAttribute("position"),
            tickCount: templates.axes.x.template?.getAttribute("tick-count") || templates.axes.x.tickLabel?.getAttribute("target-tick-count"),
            tickStart: templates.axes.x.template?.getAttribute("tick-start"),
            tickEnd: templates.axes.x.template?.getAttribute("tick-end"),
            tickStep: templates.axes.x.template?.getAttribute("tick-step") ? Number(templates.axes.x.template?.getAttribute("tick-step")) : null,
            tickSuffix: templates.axes.x.template?.getAttribute("tick-suffix"),
            tickPrefix: templates.axes.x.template?.getAttribute("tick-prefix"),
            tickValues: templates.axes.x.template?.hasAttribute("tick-values") ? JSON.parse(templates.axes.x.template?.getAttribute("tick-values")) : null,
            asArea: hasBars || templates.cursor?.innerHTML?.match(/type="([^"]+)"/)?.[1] === "area"
          },
          y: {
            keys: yKeys,
            format: templates.axes.y.template?.hasAttribute("format") ? JSON.parse(templates.axes.y.template?.getAttribute("format")) : null,
            scale: templates.axes.y.template?.getAttribute("scale"),
            interval: templates.axes.y.template?.getAttribute("interval"),
            position: templates.axes.y.template?.getAttribute("position"),
            tickCount: templates.axes.y.template?.getAttribute("tick-count") || templates.axes.y.tickLabel?.getAttribute("target-tick-count"),
            tickStart: templates.axes.y.template?.getAttribute("tick-start"),
            tickEnd: templates.axes.y.template?.getAttribute("tick-end"),
            tickStep: templates.axes.y.template?.getAttribute("tick-step") ? Number(templates.axes.y.template?.getAttribute("tick-step")) : null,
            tickSuffix: templates.axes.y.template?.getAttribute("tick-suffix"),
            tickPrefix: templates.axes.y.template?.getAttribute("tick-prefix"),
            tickValues: templates.axes.y.template?.hasAttribute("tick-values") ? JSON.parse(templates.axes.y.template?.getAttribute("tick-values")) : null
          }
        }
      });
      let redraw = (checkOverflow = true) => {
        setAttribute2(svg, "viewBox", `0 0 ${chart.width} ${chart.height}`);
        if (chart.data.length === 0) {
          return;
        }
        if (chart.data.length === 1) {
          console.warn("ui-chart: chart only has one data point so it cannot be rendered.");
          return;
        }
        if (chart.data[0][chart.axes.x.key] === void 0) {
          console.warn(`ui-chart: axis field "${chart.axes.x.key}" does not exist`);
          return;
        }
        Object.entries(chart.series).forEach(([field, series]) => {
          let barGroupEl = svg.querySelector(`[data-bar-group][data-series="${field}"]`);
          let template = templates.bars[field];
          if (template) {
            let barWidth = template.hasAttribute("width") ? template.getAttribute("width") : "90%";
            let radius = parseRadius(template.hasAttribute("radius") ? template.getAttribute("radius") : "8 0");
            let minHeight = template.hasAttribute("min-height") ? Number(template.getAttribute("min-height")) : 0;
            barGroupEl?.remove();
            barGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            barGroupEl.setAttribute("data-bar-group", "");
            barGroupEl.setAttribute("data-series", field);
            series.bars({
              barWidth,
              minHeight,
              radius
            }).forEach((bar) => {
              let barEl = hydrateSvgTemplate(template);
              barEl.setAttribute("data-bar", "");
              barEl.setAttribute("data-series", field);
              barEl.setAttribute("d", bar.path(radius));
              barGroupEl.appendChild(barEl);
            });
            svg.appendChild(barGroupEl);
          }
        });
        Object.entries(chart.stackedSeries).forEach(([index, series]) => {
          let stackEl = svg.querySelector(`[data-stack][data-stack-index="${index}"]`);
          let stackTemplate = templates.stacks[index];
          if (!stackTemplate) return;
          stackEl?.remove();
          stackEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
          stackEl.setAttribute("data-stack", "");
          stackEl.setAttribute("data-stack-index", index);
          let stackTemplates = {
            bars: {}
          };
          stackTemplate.querySelectorAll('template[name="bar"]').forEach((template) => {
            stackTemplates.bars[template.getAttribute("field")] = template;
          });
          Object.entries(series.bars()).forEach(([field, bars]) => {
            let barTemplate = stackTemplates.bars[field];
            let stackBarGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            stackBarGroupEl.setAttribute("data-bar-group", "");
            stackBarGroupEl.setAttribute("data-series", field);
            bars.forEach((bar) => {
              let barEl = hydrateSvgTemplate(barTemplate);
              barEl.setAttribute("data-bar", "");
              barEl.setAttribute("data-series", field);
              barEl.setAttribute("d", bar.path());
              stackBarGroupEl.appendChild(barEl);
            });
            stackEl.appendChild(stackBarGroupEl);
          });
          svg.appendChild(stackEl);
        });
        Object.entries(chart.groupedSeries).forEach(([index, series]) => {
          let groupEl = svg.querySelector(`[data-group][data-group-index="${index}"]`);
          let groupTemplate = templates.groups[index];
          if (!groupTemplate) return;
          groupEl?.remove();
          groupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
          groupEl.setAttribute("data-group", "");
          groupEl.setAttribute("data-group-index", index);
          let groupTemplates = {
            bars: {}
          };
          groupTemplate.querySelectorAll('template[name="bar"]').forEach((template) => {
            groupTemplates.bars[template.getAttribute("field")] = template;
          });
          Object.entries(series.bars()).forEach(([field, bars]) => {
            let barTemplate = groupTemplates.bars[field];
            let groupBarGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            groupBarGroupEl.setAttribute("data-bar-group", "");
            groupBarGroupEl.setAttribute("data-series", field);
            bars.forEach((bar) => {
              let barEl = hydrateSvgTemplate(barTemplate);
              barEl.setAttribute("data-bar", "");
              barEl.setAttribute("data-series", field);
              barEl.setAttribute("d", bar.path());
              groupBarGroupEl.appendChild(barEl);
            });
            groupEl.appendChild(groupBarGroupEl);
          });
          svg.appendChild(groupEl);
        });
        Object.entries(chart.series).forEach(([field, series]) => {
          let line = svg.querySelector(`[data-line][data-series="${field}"]`);
          let template = templates.lines[field];
          if (template) {
            let curve = template.getAttribute("curve") || "smooth";
            if (!line) {
              let lineEl = hydrateSvgTemplate(template);
              lineEl.setAttribute("data-line", "");
              lineEl.setAttribute("data-series", field);
              lineEl.setAttribute("d", series.linePath(curve));
              svg.appendChild(lineEl);
            } else {
              line.setAttribute("d", series.linePath(curve));
            }
          }
        });
        Object.entries(chart.series).forEach(([field, series]) => {
          let area = svg.querySelector(`[data-area][data-series="${field}"]`);
          let template = templates.areas[field];
          if (template) {
            let curve = template.getAttribute("curve") || "smooth";
            if (!area) {
              let areaEl = hydrateSvgTemplate(template);
              areaEl.setAttribute("data-area", "");
              areaEl.setAttribute("data-series", field);
              areaEl.setAttribute("d", series.areaPath(curve));
              svg.appendChild(areaEl);
            } else {
              area.setAttribute("d", series.areaPath(curve));
            }
          }
        });
        Object.entries(chart.series).forEach(([field, series]) => {
          let pointGroupEl = svg.querySelector(`[data-point-group][data-series="${field}"]`);
          let template = templates.points[field];
          if (template) {
            svg.querySelector(`[data-point-group][data-series="${field}"]`)?.remove();
            pointGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            pointGroupEl.setAttribute("data-point-group", "");
            pointGroupEl.setAttribute("data-series", field);
            series.points.forEach((point) => {
              if (!isFinite(point.y)) return;
              let pointEl = hydrateSvgTemplate(template);
              pointEl.setAttribute("data-point", "");
              pointEl.setAttribute("data-series", field);
              pointEl.setAttribute("cx", point.x);
              pointEl.setAttribute("cy", point.y);
              pointGroupEl.appendChild(pointEl);
            });
            svg.appendChild(pointGroupEl);
          }
        });
        activatePoint = (event) => {
          svg.querySelectorAll("[data-point-group]").forEach((pointGroupEl) => {
            pointGroupEl.querySelectorAll("[data-point]").forEach((i) => i.removeAttribute("data-active"));
            if (event) {
              let mouseX = event.clientX - svg.getBoundingClientRect().left;
              let mouseY = event.clientY - svg.getBoundingClientRect().top;
              if (mouseX >= chart.inset.left && mouseX <= chart.width - chart.inset.right && mouseY >= chart.inset.top && mouseY <= chart.height - chart.inset.bottom) {
                let closestPoints = chart.closestXPoints(mouseX);
                closestPoints.forEach((point) => {
                  pointGroupEl.querySelectorAll(`[data-point][cx="${point.x}"]`).forEach((i) => i.setAttribute("data-active", ""));
                });
              }
            } else {
              pointGroupEl.querySelectorAll("[data-point]").forEach((i) => i.removeAttribute("data-active"));
            }
          });
        };
        if (templates.axes.x.template) {
          if (templates.axes.x.axisLine) {
            svg.querySelector('[data-axis-line][data-axis="x"]')?.remove();
            let axisLineEl = hydrateSvgTemplate(templates.axes.x.axisLine);
            axisLineEl.setAttribute("data-axis-line", "");
            axisLineEl.setAttribute("data-axis", "x");
            axisLineEl.setAttribute("x1", chart.axes.x.scale(chart.axes.x.domain[0]).center);
            axisLineEl.setAttribute("x2", chart.axes.x.scale(chart.axes.x.domain[1]).center);
            axisLineEl.setAttribute("y1", chart.axes.y.scale(chart.axes.y.domain[chart.axes.x.position === "bottom" ? 0 : 1]).center);
            axisLineEl.setAttribute("y2", chart.axes.y.scale(chart.axes.y.domain[chart.axes.x.position === "bottom" ? 0 : 1]).center);
            svg.appendChild(axisLineEl);
          }
          if (templates.axes.x.gridLine) {
            svg.querySelector('[data-grid-line-group][data-axis="x"]')?.remove();
            let gridLineGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            gridLineGroupEl.setAttribute("data-grid-line-group", "");
            gridLineGroupEl.setAttribute("data-axis", "x");
            chart.axes.x.ticks.forEach(({ value: value3 }) => {
              let gridLineEl = hydrateSvgTemplate(templates.axes.x.gridLine);
              gridLineEl.setAttribute("data-grid-line", "");
              gridLineEl.setAttribute("data-axis", "x");
              gridLineEl.setAttribute("x1", chart.axes.x.scale(value3).center);
              gridLineEl.setAttribute("x2", chart.axes.x.scale(value3).center);
              gridLineEl.setAttribute("y1", chart.inset.top);
              gridLineEl.setAttribute("y2", chart.height - chart.inset.bottom);
              gridLineGroupEl.appendChild(gridLineEl);
            });
            svg.appendChild(gridLineGroupEl);
          }
          if (templates.axes.x.tickMark) {
            svg.querySelector('[data-tick-mark-group][data-axis="x"]')?.remove();
            let tickMarkGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            tickMarkGroupEl.setAttribute("data-tick-mark-group", "");
            tickMarkGroupEl.setAttribute("data-axis", "x");
            chart.axes.x.ticks.forEach(({ value: value3, label }) => {
              let position = { x: chart.axes.x.scale(value3, { area: chart.axes.x.asArea }).center, y: chart.axes.y.scale(chart.axes.y.domain[chart.axes.x.position === "bottom" ? 0 : 1]).center };
              let tickMarkEl = hydrateSvgTemplate(templates.axes.x.tickMark);
              tickMarkEl.setAttribute("data-tick-mark", "");
              tickMarkEl.setAttribute("data-axis", "x");
              tickMarkEl.setAttribute("transform", `translate(${position.x}, ${position.y})`);
              tickMarkGroupEl.appendChild(tickMarkEl);
            });
            svg.appendChild(tickMarkGroupEl);
          }
          if (templates.axes.x.tickLabel) {
            svg.querySelector('[data-tick-label-group][data-axis="x"]')?.remove();
            let tickLabelGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            tickLabelGroupEl.setAttribute("data-tick-label-group", "");
            tickLabelGroupEl.setAttribute("data-axis", "x");
            chart.axes.x.ticks.forEach(({ value: value3, label }) => {
              let position = { x: chart.axes.x.scale(value3, { area: chart.axes.x.asArea }).center, y: chart.axes.y.scale(chart.axes.y.domain[chart.axes.x.position === "bottom" ? 0 : 1]).center };
              let tickLabelEl = hydrateSvgTemplate(templates.axes.x.tickLabel);
              tickLabelEl.querySelectorAll("slot").forEach((i) => i.replaceWith(document.createTextNode(label)));
              tickLabelEl.setAttribute("data-tick-label", "");
              tickLabelEl.setAttribute("data-axis", "x");
              tickLabelEl.setAttribute("transform", `translate(${position.x}, ${position.y})`);
              tickLabelGroupEl.appendChild(tickLabelEl);
            });
            svg.appendChild(tickLabelGroupEl);
            handleTickOverflow(tickLabelGroupEl, chart.axes.x);
          }
        }
        if (templates.axes.y.template) {
          if (templates.axes.y.axisLine) {
            svg.querySelector('[data-axis-line][data-axis="y"]')?.remove();
            let axisLineEl = hydrateSvgTemplate(templates.axes.y.axisLine);
            axisLineEl.setAttribute("data-axis-line", "");
            axisLineEl.setAttribute("data-axis", "y");
            axisLineEl.setAttribute("x1", chart.axes.x.scale(chart.axes.x.domain[chart.axes.y.position === "left" ? 0 : 1]).center);
            axisLineEl.setAttribute("x2", chart.axes.x.scale(chart.axes.x.domain[chart.axes.y.position === "left" ? 0 : 1]).center);
            axisLineEl.setAttribute("y1", chart.axes.y.scale(chart.axes.y.domain[0]).center);
            axisLineEl.setAttribute("y2", chart.axes.y.scale(chart.axes.y.domain[1]).center);
            svg.appendChild(axisLineEl);
          }
          if (templates.axes.y.gridLine) {
            svg.querySelector('[data-grid-line-group][data-axis="y"]')?.remove();
            let gridLineGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            gridLineGroupEl.setAttribute("data-grid-line-group", "");
            gridLineGroupEl.setAttribute("data-axis", "y");
            chart.axes.y.ticks.forEach(({ value: value3, label }) => {
              let position = { x: chart.axes.x.scale(chart.axes.x.domain[0]).center, y: chart.axes.y.scale(value3).center };
              let gridLineEl = hydrateSvgTemplate(templates.axes.y.gridLine);
              gridLineEl.setAttribute("data-grid-line", "");
              gridLineEl.setAttribute("data-axis", "y");
              gridLineEl.setAttribute("x1", chart.inset.left);
              gridLineEl.setAttribute("x2", chart.width - chart.inset.right);
              gridLineEl.setAttribute("y1", position.y);
              gridLineEl.setAttribute("y2", position.y);
              gridLineGroupEl.appendChild(gridLineEl);
            });
            svg.appendChild(gridLineGroupEl);
          }
          if (templates.axes.y.tickMark) {
            svg.querySelector('[data-tick-mark-group][data-axis="y"]')?.remove();
            let tickMarkGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            tickMarkGroupEl.setAttribute("data-tick-mark-group", "");
            tickMarkGroupEl.setAttribute("data-axis", "y");
            chart.axes.y.ticks.forEach(({ value: value3, label }) => {
              let position = { x: chart.axes.x.scale(chart.axes.x.domain[chart.axes.y.position === "left" ? 0 : 1]).center, y: chart.axes.y.scale(value3).center };
              let tickMarkEl = hydrateSvgTemplate(templates.axes.y.tickMark);
              tickMarkEl.setAttribute("data-tick-mark", "");
              tickMarkEl.setAttribute("data-axis", "y");
              tickMarkEl.setAttribute("transform", `translate(${position.x}, ${position.y})`);
              tickMarkGroupEl.appendChild(tickMarkEl);
            });
            svg.appendChild(tickMarkGroupEl);
          }
          if (templates.axes.y.tickLabel) {
            svg.querySelector('[data-tick-label-group][data-axis="y"]')?.remove();
            let tickLabelGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            tickLabelGroupEl.setAttribute("data-tick-label-group", "");
            tickLabelGroupEl.setAttribute("data-axis", "y");
            chart.axes.y.ticks.forEach(({ value: value3, label }) => {
              let position = { x: chart.axes.x.scale(chart.axes.x.domain[chart.axes.y.position === "left" ? 0 : 1]).center, y: chart.axes.y.scale(value3).center };
              let tickLabelEl = hydrateSvgTemplate(templates.axes.y.tickLabel);
              tickLabelEl.querySelectorAll("slot").forEach((i) => i.replaceWith(document.createTextNode(label)));
              tickLabelEl.setAttribute("data-tick-label", "");
              tickLabelEl.setAttribute("data-axis", "y");
              tickLabelEl.setAttribute("transform", `translate(${position.x}, ${position.y})`);
              tickLabelGroupEl.appendChild(tickLabelEl);
            });
            svg.appendChild(tickLabelGroupEl);
            handleTickOverflow(tickLabelGroupEl, chart.axes.y);
          }
        }
        if (templates.zeroLine && chart.axes.y.domain[0] < 0 && chart.axes.y.domain[1] > 0) {
          svg.querySelector('[data-zero-line][data-axis="y"]')?.remove();
          let zeroLineEl = hydrateSvgTemplate(templates.zeroLine);
          zeroLineEl.setAttribute("data-zero-line", "");
          zeroLineEl.setAttribute("data-axis", "y");
          zeroLineEl.setAttribute("x1", chart.axes.x.scale(chart.axes.x.domain[0]).center);
          zeroLineEl.setAttribute("x2", chart.axes.x.scale(chart.axes.x.domain[1]).center);
          zeroLineEl.setAttribute("y1", chart.axes.y.scale(0).center);
          zeroLineEl.setAttribute("y2", chart.axes.y.scale(0).center);
          svg.appendChild(zeroLineEl);
        }
        if (overlay) {
          overlay.setAttribute("width", chart.width);
          overlay.setAttribute("height", chart.height);
          overlay.setAttribute("x", 0);
          overlay.setAttribute("y", 0);
        }
        if (cursor) {
          cursorType = cursor.getAttribute("type") ?? "line";
          let renderCursor = (closestXPoint) => {
            if (cursorType === "area") {
              cursor.setAttribute("opacity", "0.1");
              let x2 = closestXPoint.start;
              let y = chart.axes.y.scale(chart.axes.y.domain[1]).center;
              let width = closestXPoint.width;
              let height = chart.axes.y.scale(chart.axes.y.domain[0]).center - chart.axes.y.scale(chart.axes.y.domain[1]).center;
              let radius = parseRadius(cursor.getAttribute("radius") ?? "0");
              cursor.setAttribute("fill", "currentColor");
              cursor.setAttribute("stroke", "none");
              cursor.setAttribute("d", getBarPath(x2, y, width, height, radius));
              return;
            }
            cursor.setAttribute("opacity", "1");
            let x = closestXPoint.center;
            let y1 = chart.axes.y.scale(chart.axes.y.domain[0]).center;
            let y2 = chart.axes.y.scale(chart.axes.y.domain[1]).center;
            cursor.setAttribute("d", `M ${x} ${y1} L ${x} ${y2}`);
          };
          repositionCursor = (event) => {
            if (event) {
              let svgRect2 = svg.getBoundingClientRect();
              let mouseX = event.clientX - svgRect2.left;
              let mouseY = event.clientY - svgRect2.top;
              if (mouseX >= chart.inset.left && mouseX <= chart.width - chart.inset.right && mouseY >= chart.inset.top && mouseY <= chart.height - chart.inset.bottom) {
                let closestXPoint = chart.closestXPoint(mouseX);
                renderCursor(closestXPoint);
              } else {
                cursor.setAttribute("opacity", "0");
              }
            } else {
              cursor.setAttribute("opacity", "0");
            }
          };
        }
        if (tooltip) {
          repositionTooltip = (event) => {
            if (event === null) {
              removeAttribute(tooltip, "data-active");
            } else {
              let mouseX = event.clientX - svg.getBoundingClientRect().left;
              let mouseY = event.clientY - svg.getBoundingClientRect().top;
              if (mouseX >= chart.inset.left && mouseX <= chart.width - chart.inset.right && mouseY >= chart.inset.top && mouseY <= chart.height - chart.inset.bottom) {
                let closestXPoint = chart.closestXPoint(mouseX);
                if (closestXPoint) {
                  setAttribute2(tooltip, "data-active", "");
                  let tooltipRect = tooltip.getBoundingClientRect();
                  let svgRect2 = svg.getBoundingClientRect();
                  let rightSpace = svgRect2.width - (closestXPoint.center + tooltipRect.width + 15);
                  let bottomSpace = svgRect2.height - (mouseY + tooltipRect.height + 15);
                  let xOffset = rightSpace < 0 ? closestXPoint.center - tooltipRect.width - 15 : closestXPoint.center + 15;
                  let yOffset = bottomSpace < 0 ? mouseY - tooltipRect.height - 15 : mouseY + 15;
                  tooltip.style.transform = `translate(${xOffset}px, ${yOffset}px)`;
                  let tooltipClone = templates.tooltip.content.cloneNode(true).firstElementChild;
                  tooltipClone.querySelectorAll("slot").forEach((slot) => {
                    let field = slot.getAttribute("field");
                    if (field) {
                      let format = slot.hasAttribute("format") ? JSON.parse(slot.getAttribute("format")) : null;
                      let value3 = closestXPoint.datum[field];
                      if (format === null) return slot.textContent = value3;
                      format = { ...format, timeZone: "UTC" };
                      if (isNumeric(value3)) {
                        value3 = Number(value3).toLocaleString(chart.locale, format);
                      } else if (isDateish2(value3)) {
                        value3 = new Date(value3).toLocaleDateString(chart.locale, format);
                      }
                      slot.textContent = value3;
                    }
                  });
                  tooltip.innerHTML = tooltipClone.innerHTML;
                } else {
                  removeAttribute(tooltip, "data-active");
                }
              } else {
                removeAttribute(tooltip, "data-active");
              }
            }
          };
        }
        if (summary) {
          updateSummary = (event) => {
            let closestXPoint = null;
            if (event !== null) {
              let mouseX = event.clientX - svg.getBoundingClientRect().left;
              let mouseY = event.clientY - svg.getBoundingClientRect().top;
              if (mouseX >= chart.inset.left && mouseX <= chart.width - chart.inset.right && mouseY >= chart.inset.top && mouseY <= chart.height - chart.inset.bottom) {
                closestXPoint = chart.closestXPoint(mouseX);
              }
            }
            closestXPoint = closestXPoint || chart.closestXPoint(chart.axes.x.range[1]);
            if (closestXPoint) {
              let summaryClone = templates.summary.content.cloneNode(true).firstElementChild;
              summaryClone.querySelectorAll("slot").forEach((slot) => {
                let field = slot.getAttribute("field");
                if (field) {
                  let format = slot.hasAttribute("format") ? JSON.parse(slot.getAttribute("format")) : {};
                  format = { ...format, timeZone: "UTC" };
                  let value3 = slot.hasAttribute("fallback") && event === null ? slot.getAttribute("fallback") : closestXPoint.datum[field];
                  if (isNumeric(value3)) {
                    value3 = Number(value3).toLocaleString(chart.locale, format);
                  } else if (isDateish2(value3)) {
                    value3 = new Date(value3).toLocaleString(chart.locale, format);
                  }
                  slot.textContent = value3;
                }
              });
              summary.innerHTML = summaryClone.innerHTML;
            }
          };
          updateSummary(null);
        }
        svg.appendChild(overlay);
        svg.querySelectorAll("[data-grid-line-group]").forEach((i) => overlay.before(i));
        svg.querySelectorAll("[data-axis-line]").forEach((i) => overlay.before(i));
        if (cursorType === "area") svg.querySelectorAll("[data-cursor]").forEach((i) => overlay.before(i));
        let groupIndex = 0;
        let stackIndex = 0;
        svgTemplate.content.querySelectorAll('template[name="group"], template[name="stack"]:not(template[name="group"] template[name="stack"]), template[name="bar"]:not(template[name="stack"] template[name="bar"]):not(template[name="group"] template[name="bar"]), template[name="line"], template[name="area"], template[name="point"]').forEach((template) => {
          let name = template.getAttribute("name");
          let field = template.getAttribute("field");
          let el;
          if (name === "group") {
            el = svg.querySelector(`[data-group][data-group-index="${groupIndex}"]`);
            groupIndex++;
          } else if (name === "stack") {
            el = svg.querySelector(`[data-stack][data-stack-index="${stackIndex}"]`);
            stackIndex++;
          } else if (name === "bar") {
            el = svg.querySelector(`[data-bar-group][data-series="${field}"]`);
          } else if (name === "point") {
            el = svg.querySelector(`[data-point-group][data-series="${field}"]`);
          } else {
            el = svg.querySelector(`[data-${name}][data-series="${field}"]`);
          }
          if (el) overlay.before(el);
        });
        svg.querySelectorAll("[data-zero-line]").forEach((i) => overlay.before(i));
        if (cursorType === "line") svg.querySelectorAll("[data-cursor]").forEach((i) => overlay.before(i));
        if (checkOverflow) {
          let overflow = getSvgOverflow(svg);
          adjustOverflowForPendingTickRotation(svg, chart, gutter, overflow);
          chart.updateDimensions({ width: chart.width, height: chart.height }, {
            left: gutter.left + overflow.left,
            right: gutter.right + overflow.right,
            top: gutter.top + overflow.top,
            bottom: gutter.bottom + overflow.bottom
          });
          redraw(false);
        }
      };
      this._observable.subscribe("resize", () => {
        let svgRect2 = svg.parentElement.getBoundingClientRect();
        if (svgRect2.width === 0 || svgRect2.height === 0) return;
        chart.updateDimensions({
          width: svgRect2.width,
          height: svgRect2.height
        }, {
          left: 0,
          right: 0,
          top: 0,
          bottom: 0
        });
        redraw();
        if (cursor) {
          cursor.setAttribute("opacity", "0");
        }
      });
      this._observable.subscribe("data", () => {
        chart.updateData(this._data);
        chart.updateDimensions({ width: chart.width, height: chart.height }, {
          left: 0,
          right: 0,
          top: 0,
          bottom: 0
        });
        redraw();
      });
      new ResizeObserver(() => {
        this._observable.notify("resize");
      }).observe(this);
      new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.attributeName !== "value") return;
          this._selectable.setState(this.hasAttribute("value") ? JSON.parse(this.getAttribute("value")) : []);
        });
      }).observe(this, { attributes: true, attributeFilter: ["value"] });
    }
  };
  customElements.define("ui-chart", UIChart);
  function discoverXandYKeys(svg, hasBars = false) {
    let xKey = null;
    let yKeys = [];
    svg.querySelectorAll('template[name="line"], template[name="area"], template[name="point"], template[name="bar"]:not(template[name="stack"] template[name="bar"]):not(template[name="group"] template[name="bar"])').forEach((template) => {
      yKeys.push(template.getAttribute("field") || "value");
    });
    svg.querySelectorAll('template[name="axis"][axis="x"]').forEach((template) => {
      xKey = template.getAttribute("field") || "index";
    });
    yKeys = Array.from(new Set(yKeys));
    if (xKey === null) xKey = "index";
    if (yKeys.length === 0 && !hasBars) yKeys = ["value"];
    return [xKey, yKeys];
  }
  function hydrateSvgTemplate(template) {
    let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("xmlns", "http://www.w3.org/2000/svg");
    svg.setAttribute("version", "1.1");
    svg.innerHTML = template.innerHTML;
    return svg.firstElementChild;
  }
  function adjustOverflowForPendingTickRotation(svg, chart, gutter, overflow) {
    let tickLabelGroupEl = svg.querySelector('[data-tick-label-group][data-axis="x"]');
    if (!tickLabelGroupEl) return;
    if (chart.axes.x.type !== "categorical") return;
    if (hasOverlappingTicks(tickLabelGroupEl, chart.axes.x)) return;
    let widthReduction = gutter.left + overflow.left + gutter.right + overflow.right - (chart.inset.left + chart.inset.right);
    if (widthReduction <= 0) return;
    let children = Array.from(tickLabelGroupEl.children).filter((child) => child.style.display !== "none");
    for (let i = 0; i < children.length - 1; i++) {
      let rect = children[i].getBoundingClientRect();
      let nextRect = children[i + 1].getBoundingClientRect();
      let adjustedGap = nextRect.left - rect.right - widthReduction / Math.max(1, children.length - 1);
      if (adjustedGap < 20) {
        let undo = rotateTickLabels(tickLabelGroupEl);
        let rotatedOverflow = getSvgOverflow(svg);
        overflow.bottom = Math.max(overflow.bottom, rotatedOverflow.bottom);
        undo();
        return;
      }
    }
  }
  function handleTickOverflow(tickLabelGroupEl, axis) {
    if (!hasOverlappingTicks(tickLabelGroupEl, axis)) return;
    if (axis.axis === "x" && axis.type === "categorical") {
      rotateTickLabels(tickLabelGroupEl);
    } else if (axis.axis === "x" && axis.type === "time") {
      let undo = hideEveryOtherTickLabel(tickLabelGroupEl);
      if (!hasOverlappingTicks(tickLabelGroupEl, axis)) return;
      undo();
      onlyShowFirstAndLastTickLabel(tickLabelGroupEl);
    }
  }
  function hasOverlappingTicks(tickLabelGroupEl, axis) {
    let children = Array.from(tickLabelGroupEl.children);
    children = children.filter((child) => {
      return child.style.display != "none";
    });
    for (let i = 0; i < children.length; i++) {
      let tickLabelEl = children[i];
      let nextTickLabelEl = children[i + 1];
      if (!nextTickLabelEl) break;
      let tickLabelRect = tickLabelEl.getBoundingClientRect();
      let nextTickLabelRect = nextTickLabelEl.getBoundingClientRect();
      let overlapMargin = 20;
      if (axis.axis === "y") {
        if (tickLabelRect.bottom + overlapMargin < nextTickLabelRect.top) {
          return true;
        }
      } else {
        if (tickLabelRect.right + overlapMargin > nextTickLabelRect.left) {
          return true;
        }
      }
    }
    return false;
  }
  function rotateTickLabels(tickLabelGroupEl) {
    let undos = [];
    Array.from(tickLabelGroupEl.children).forEach((child, index) => {
      let textNode = child.matches("text") ? child : child.querySelector("text");
      Array.from(tickLabelGroupEl.children).forEach((child2, index2) => {
        let textNode2 = child2.matches("text") ? child2 : child2.querySelector("text");
        if (textNode2) {
          textNode2.style.transform = "rotate(-45deg)";
          textNode2.style.textAnchor = "end";
          undos.push(() => {
            textNode2.style.transform = null;
            textNode2.style.textAnchor = null;
          });
        }
      });
    });
    return () => undos.forEach((undo) => undo());
  }
  function hideEveryOtherTickLabel(tickLabelGroupEl) {
    let children = Array.from(tickLabelGroupEl.children);
    children.forEach((child, index) => {
      if (index % 2 === 1) child.style.display = "none";
    });
    return () => children.forEach((child, index) => {
      child.style.display = "inline";
    });
  }
  function onlyShowFirstAndLastTickLabel(tickLabelGroupEl) {
    let children = Array.from(tickLabelGroupEl.children);
    children.forEach((child, index) => {
      child.style.display = "none";
    });
    let firstChild = children[0];
    let lastChild = children[children.length - 1];
    let firstTextNode = firstChild.matches("text") ? firstChild : firstChild.querySelector("text");
    let lastTextNode = lastChild.matches("text") ? lastChild : lastChild.querySelector("text");
    firstChild.style.display = "inline";
    firstTextNode.style.textAnchor = "start";
    lastChild.style.display = "inline";
    lastTextNode.style.textAnchor = "end";
    return () => children.forEach((child, index) => {
      child.style.display = "inline";
    });
  }
  function isNumeric(value3) {
    return typeof value3 === "number" || typeof value3 === "string" && !isNaN(Number(value3));
  }
  function isDateish2(value3) {
    return value3 instanceof Date || typeof value3 === "string" && !isNaN(Date.parse(value3));
  }
  function getSvgOverflow(svg) {
    let svgRect = svg.getBoundingClientRect();
    let contentRect = { top: Infinity, right: -Infinity, bottom: -Infinity, left: Infinity };
    Array.from(svg.children).forEach((el) => {
      if (el.hasAttribute("data-overlay")) return;
      if (el.hasAttribute("data-cursor")) return;
      if (el.style.display === "none") return;
      let rect = el.getBoundingClientRect();
      if (!rect.width && !rect.height) return;
      contentRect.top = Math.min(contentRect.top, rect.top);
      contentRect.right = Math.max(contentRect.right, rect.right);
      contentRect.bottom = Math.max(contentRect.bottom, rect.bottom);
      contentRect.left = Math.min(contentRect.left, rect.left);
    });
    return {
      top: Math.max(0, svgRect.top - contentRect.top),
      right: Math.max(0, contentRect.right - svgRect.right),
      bottom: Math.max(0, contentRect.bottom - svgRect.bottom),
      left: Math.max(0, svgRect.left - contentRect.left)
    };
  }
  function parseRadius(radiusString) {
    if (!radiusString || radiusString.trim() === "") {
      throw new Error("Radius string cannot be empty");
    }
    let radiusValues = radiusString.trim().split(/\s+/).map((val) => {
      let num = val.replace(/px/, "");
      return Number(num) || 0;
    });
    if (radiusValues.length === 0 || radiusValues.every((v) => isNaN(v))) {
      throw new Error(`Invalid radius string: "${radiusString}"`);
    }
    let radius = {
      topLeft: 0,
      topRight: 0,
      bottomRight: 0,
      bottomLeft: 0
    };
    if (radiusValues.length === 1) {
      radius.topLeft = radiusValues[0];
      radius.topRight = radiusValues[0];
      radius.bottomRight = radiusValues[0];
      radius.bottomLeft = radiusValues[0];
    } else if (radiusValues.length === 2) {
      radius.topLeft = radiusValues[0];
      radius.topRight = radiusValues[0];
      radius.bottomRight = radiusValues[1];
      radius.bottomLeft = radiusValues[1];
    } else if (radiusValues.length === 4) {
      radius.topLeft = radiusValues[0];
      radius.topRight = radiusValues[1];
      radius.bottomRight = radiusValues[2];
      radius.bottomLeft = radiusValues[3];
    } else {
      throw new Error(`Invalid radius string: "${radiusString}". Must be 1, 2, or 4 numbers`);
    }
    return radius;
  }

  // js/button.js
  var UIButton = class extends UIElement {
    boot() {
      setAttribute2(this, "role", "button");
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        disabled ? removeAttribute(this, "tabindex", "0") : setAttribute2(this, "tabindex", "0");
      });
      this._buttonType = this.getAttribute("type") || "button";
    }
    mount() {
      on(this, "keydown", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          this.disabled || this.click();
        }
        if (e.key === " ") {
          e.preventDefault();
        }
      });
      on(this, "keyup", (e) => {
        if (e.key === " ") {
          e.preventDefault();
          e.stopPropagation();
          this.disabled || this.click();
        }
      });
      on(this, "click", () => {
        if (this.disabled) return;
        if (this._buttonType === "submit") {
          this._handleSubmit();
        } else if (this._buttonType === "reset") {
          this._handleReset();
        }
      });
    }
    _handleSubmit() {
      let form = this.closest("form");
      if (form) {
        form.requestSubmit();
      }
    }
    _handleReset() {
      let form = this.closest("form");
      if (form) {
        form.reset();
      }
    }
  };
  element("button", UIButton);

  // js/close.js
  var UIClose = class extends UIElement {
    mount() {
      let button = this.querySelector("button,ui-button");
      on(button, "click", () => {
        let closeable = closest(this, (el) => !!el._closeable)?._closeable;
        closeable?.close();
      });
    }
  };
  element("close", UIClose);

  // js/toast.js
  var UIToastGroup = class extends UIElement {
    toasts = [];
    initiallyExpanded = false;
    expanded = false;
    mount() {
      this.initiallyExpanded = this.hasAttribute("expanded");
      let position = this.getAttribute("position") || "bottom right";
      this.verticalPosition = position.match(/\b(top|bottom)\b/)?.[0] || "bottom";
      this.horizontalPosition = position.match(/\b(left|start|center|right|end)\b/)?.[0] || "right";
      if (this.horizontalPosition === "left") {
        this.horizontalPosition = "start";
      } else if (this.horizontalPosition === "right") {
        this.horizontalPosition = "end";
      }
      setAttribute2(this, "role", "status");
    }
    showToast(options = {}) {
      let toastContainer = this.querySelector("ui-toast");
      if (!toastContainer) {
        return console.warn("ui-toast-group: no ui-toast element found", this);
      }
      if (this.matches(":popover-open")) {
        this.hidePopover();
      }
      this.showPopover();
      let duration = Number(options.duration === void 0 ? 5e3 : options.duration);
      let template = toastContainer.prepareToastTemplate(options);
      let toastEl = template.firstElementChild;
      let toast = {
        timeout: null,
        el: template,
        height: null,
        contentEl: toastEl,
        contentHeight: null
      };
      let show = () => {
        this.animateIn(toast);
        this.updateList();
      };
      let hide2 = () => {
        this.animateOut(toast);
        this.updateList();
      };
      this.toasts.unshift(toast);
      this.appendChild(template);
      show();
      template.hideToast = hide2;
      let toastTimeout = duration !== 0 && timeout(() => {
        toast.timeout = null;
        hide2();
      }, duration);
      if (toastTimeout) {
        toast.timeout = toastTimeout;
      }
      template.destroyToast = () => {
        if (toast.timeout) {
          toast.timeout.cancel();
          toast.timeout = null;
        }
        let index = this.toasts.indexOf(toast);
        if (index > -1) {
          this.toasts.splice(index, 1);
        }
        template.remove();
        if (this.toasts.length === 0) {
          this.expanded = false;
        }
        this.updateList();
      };
      template.addEventListener("mouseenter", () => {
        this.pauseTimeouts();
        if (!this.initiallyExpanded) {
          this.expanded = true;
          this.updateList();
        }
      });
      template.addEventListener("mouseleave", () => {
        this.resumeTimeouts();
        if (!this.initiallyExpanded) {
          this.expanded = false;
          this.updateList();
        }
      });
      template._closeable = new Closeable(template);
      template._closeable.onClose(() => template.destroyToast());
    }
    pauseTimeouts() {
      for (let toast of this.toasts) {
        toast.timeout?.pause();
      }
    }
    resumeTimeouts() {
      for (let toast of this.toasts) {
        toast.timeout?.resume();
      }
    }
    animateIn(toast) {
      let reverseVerticalPosition = this.verticalPosition === "top" ? "bottom" : "top";
      let verticalPositionInt = reverseVerticalPosition === "top" ? 1 : -1;
      let translateY = 1.5 * verticalPositionInt;
      toast.contentEl.style.opacity = 0;
      toast.contentEl.style.transform = `translateY(${translateY}rem)`;
      toast.el.style.zIndex = 5001;
      toast.contentEl.offsetHeight;
      toast.contentHeight = toast.contentEl.offsetHeight;
      toast.height = toast.el.offsetHeight;
      toast.contentEl.style.opacity = 100;
      toast.contentEl.style.transform = `translateY(0)`;
    }
    animateOut(toast) {
      let verticalPositionInt = this.verticalPosition === "top" ? 1 : -1;
      let translateY = 0.5 * verticalPositionInt;
      toast.contentEl.style.transform = `translateY(${translateY}rem)`;
      toast.contentEl.style.opacity = 0;
      let index = this.toasts.indexOf(toast);
      if (index > -1) {
        this.toasts.splice(index, 1);
      }
      let cleanup = () => {
        toast.el.remove();
        if (this.toasts.length === 0) {
          this.expanded = false;
        }
      };
      if (toast.contentEl.getAnimations().length) {
        toast.contentEl.addEventListener("transitionend", () => {
          cleanup();
        }, { once: true });
      } else {
        cleanup();
      }
    }
    updateList() {
      this.initiallyExpanded || this.expanded ? this.expand() : this.collapse();
    }
    expand() {
      let runningHeight = 0;
      for (let toast of this.toasts) {
        let verticalPositionInt = this.verticalPosition === "top" ? 1 : -1;
        toast.el.style.transform = `translateY(${verticalPositionInt * runningHeight}px)`;
        toast.el.style.opacity = 100;
        toast.contentEl.style.height = `${toast.contentHeight}px`;
        toast.contentEl.style.overflow = "auto";
        runningHeight += toast.height;
      }
    }
    collapse() {
      let i = 0;
      let frontToast = this.toasts[0];
      let frontToastHeight = frontToast?.contentHeight || 0;
      for (let toast of this.toasts) {
        let numberOfToastsToShow = 3;
        let scale = 0;
        let verticalPositionInt = this.verticalPosition === "top" ? 1 : -1;
        let translateY = 10 * verticalPositionInt * i;
        toast.el.style.zIndex = 5e3 - i;
        if (i >= numberOfToastsToShow) {
          scale = 1 - 0.05 * (numberOfToastsToShow - 1);
          toast.el.style.opacity = 0;
        } else {
          scale = 1 - 0.05 * i;
          toast.el.style.opacity = 100;
        }
        toast.contentEl.style.height = `${frontToastHeight}px`;
        toast.contentEl.style.overflow = "hidden";
        toast.el.style.transform = `scaleX(${scale}) translateY(${translateY}px)`;
        i++;
      }
    }
  };
  var UIToast = class extends UIElement {
    mount() {
      if (!this.closest("ui-toast-group")) {
        setAttribute2(this, "role", "status");
        document.addEventListener("keydown", (e) => {
          if (e.key === "Escape") {
            this.hideToast();
          }
        });
        this.defaultPosition = this.getAttribute("position") || "bottom end";
      }
    }
    showToast(options = {}) {
      let existingToast = this.template().nextElementSibling;
      if (existingToast) existingToast.destroyToast();
      let duration = Number(options.duration === void 0 ? 5e3 : options.duration);
      let position = options.dataset?.position ?? this.defaultPosition;
      if (options.dataset?.position !== void 0) {
        delete options.dataset.position;
      }
      this.setAttribute("position", position);
      let template = this.prepareToastTemplate(options);
      let show = () => {
        this.showPopover();
        template.classList.add("showing");
      };
      let hide2 = () => {
        template._hiding = true;
        template.classList.remove("showing");
        if (template.getAnimations().length) {
          template.addEventListener("transitionend", () => {
            template.remove();
            this.hidePopover();
          }, { once: true });
        } else {
          template.remove();
          this.hidePopover();
        }
      };
      this.appendChild(template);
      show();
      template.hideToast = hide2;
      let toastTimeout = duration !== 0 && timeout(() => {
        hide2();
      }, duration);
      template.destroyToast = () => {
        toastTimeout && toastTimeout.cancel();
        template.remove();
        this.hidePopover();
      };
      template.addEventListener("mouseenter", () => {
        toastTimeout && toastTimeout.pause();
      });
      template.addEventListener("mouseleave", () => {
        toastTimeout && toastTimeout.resume();
      });
      template._closeable = new Closeable(template);
      template._closeable.onClose(() => template.destroyToast());
    }
    hideToast() {
      let toast = this.template().nextElementSibling;
      toast && toast.destroyToast();
    }
    template() {
      return this.querySelector("template");
    }
    prepareToastTemplate(options) {
      let slots = options.slots || {};
      let dataset = options.dataset || {};
      let templateEl = this.template();
      if (!templateEl) {
        return console.warn("ui-toast: no template element found", this);
      }
      let template = templateEl.content.cloneNode(true).firstElementChild;
      template.setAttribute("aria-atomic", "true");
      Object.entries(slots).forEach(([key, value3]) => {
        if ([null, void 0, false].includes(value3)) return;
        template.querySelectorAll(`slot[name="${key}"]`).forEach((i) => {
          i.replaceWith(document.createTextNode(value3));
        });
      });
      Object.entries(dataset).forEach(([key, value3]) => {
        template.dataset[key] = value3;
      });
      template.querySelectorAll("slot").forEach((slot) => slot.remove());
      return template;
    }
  };
  element("toast-group", UIToastGroup);
  element("toast", UIToast);

  // js/radio.js
  var UIRadioGroup = class _UIRadioGroup extends UIControl {
    boot() {
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        this.walker().each((el) => {
          el.disabled = disabled;
        });
      });
      this._selectable = new SelectableGroup(this);
      this._controllable = new Controllable(this, { disabled: this._disabled, bubbles: true });
      this._focusable = new FocusableGroup(this, { wrap: true });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this._selectable.getState(),
        includeWhenEmpty: false
      });
      this._controllable.initial((initial) => initial && this._selectable.setState(initial));
      this._controllable.getter(() => this._selectable.getState());
      this._detangled = detangle();
      this._controllable.setter(this._detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(this._detangled(() => {
        this._controllable.dispatch();
      }));
      this._selectable.onInitAndChange(() => {
        this._submittable.update(this._selectable.getState());
      });
      on(this, "keydown", (e) => {
        if (["ArrowDown", "ArrowRight"].includes(e.key)) {
          this._focusable.focusNext();
          e.preventDefault();
          e.stopPropagation();
        } else if (["ArrowUp", "ArrowLeft"].includes(e.key)) {
          this._focusable.focusPrev();
          e.preventDefault();
          e.stopPropagation();
        }
      });
      setAttribute2(this, "role", "radiogroup");
      queueMicrotask(() => {
        this._submittable.update(this._selectable.getState());
      });
    }
    walker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UIRadioGroup) return reject();
        if (!(el.localName === "ui-radio")) return skip();
      });
    }
  };
  var UIRadio = class extends UIControl {
    boot() {
      let button = this;
      this._disableable = new Disableable(this);
      this._selectable = new Selectable(button, {
        value: this.hasAttribute("value") ? this.getAttribute("value") : Math.random().toString(36).substring(2, 10),
        label: this.hasAttribute("label") ? this.getAttribute("label") : null,
        selectedInitially: this.hasAttribute("checked"),
        dataAttr: "data-checked",
        ariaAttr: "aria-checked"
      });
      this._selectable.onChange(() => {
        if (this._selectable.isSelected()) this._focusable.focus(false);
      });
      this._disableable.onChange((disabled) => {
        if (disabled) {
          this._focusable.untabbable();
        } else {
          this._selectable.isSelected() && this._focusable.tabbable();
        }
      });
      setAttribute2(button, "role", "radio");
      this._focusable = new Focusable(button, { disableable: this._disableable, tabbableAttr: "data-active" });
      on(button, "click", this._disableable.disabled((e) => {
        e.preventDefault();
        e.stopPropagation();
      }), { capture: true });
      on(button, "click", this._disableable.enabled((e) => {
        this._selectable.press();
      }));
      on(button, "keydown", this._disableable.enabled((e) => {
        if (e.key === "Enter") {
          this.closest("form")?.requestSubmit();
        }
      }));
      on(button, "keydown", this._disableable.enabled((e) => {
        if (e.key === " ") {
          e.preventDefault();
          e.stopPropagation();
        }
      }));
      on(button, "keyup", this._disableable.enabled((e) => {
        if (e.key === " ") {
          this._selectable.press();
          e.preventDefault();
          e.stopPropagation();
        }
      }));
      respondToLabelClick3(button);
      on(button, "focus", (e) => {
        isUsingKeyboard() && this._selectable.select();
      });
    }
    get value() {
      return this._selectable?.getValue();
    }
    set value(val) {
      if (!this._selectable) return;
      this._selectable.value = val + "";
    }
    get checked() {
      return this._selectable.isSelected();
    }
    set checked(value3) {
      let detangled = this.closest("ui-radio-group")?._detangled || (() => {
      });
      detangled(() => {
        value3 && this._selectable.select();
      })();
    }
  };
  function respondToLabelClick3(el) {
    el.closest("label")?.addEventListener("click", (e) => {
      if (!el.contains(e.target)) {
        el.click();
      }
    });
  }
  element("radio-group", UIRadioGroup);
  element("radio", UIRadio);

  // js/table.js
  var UITableScrollArea = class extends UIElement {
    boot() {
      on(this, "scroll", (e) => {
        this.updateAttributes();
      }, { passive: true });
      new ResizeObserver(() => {
        this.updateAttributes();
      }).observe(this);
    }
    mount() {
      queueMicrotask(() => {
        this.updateAttributes();
      });
    }
    updateAttributes() {
      if (this.scrollLeft > 12) {
        setAttribute2(this, "data-scrolled-right", "");
      } else {
        removeAttribute(this, "data-scrolled-right");
      }
      if (this.scrollWidth - this.clientWidth - this.scrollLeft > 12) {
        setAttribute2(this, "data-scrolled-left", "");
      } else {
        removeAttribute(this, "data-scrolled-left");
      }
    }
  };
  element("table-scroll-area", UITableScrollArea);

  // js/tabs.js
  var UITabGroup = class _UITabGroup extends UIElement {
    boot() {
      this._disabled = this.hasAttribute("disabled");
    }
    mount() {
      this.walkPanels((el) => initializePanel(el));
      new MutationObserver((mutations) => {
        this.walkPanels((el) => initializePanel(el));
      }).observe(this, { childList: true });
    }
    showPanel(name) {
      this.walkPanels((el) => {
        if (el.getAttribute("name") === name) el.show();
        else el.hide();
      });
    }
    getPanel(name) {
      return this.walkPanels((el, bail) => {
        if (el.getAttribute("name") === name) {
          bail(el);
        }
      });
    }
    walkPanels(callback) {
      let bailed = false;
      let bailedReturn;
      for (let child of this.children) {
        if (child instanceof _UITabGroup) continue;
        if (child instanceof UITabs) continue;
        callback(child, (forward) => {
          bailed = true;
          bailedReturn = forward;
        });
        if (bailed) break;
      }
      return bailedReturn;
    }
  };
  var UITabsScrollArea = class extends UIElement {
    boot() {
      on(this, "scroll", (e) => {
        this.updateScrollVariable();
      }, { passive: true });
      new ResizeObserver(() => {
        this.updateScrollVariable();
      }).observe(this);
    }
    updateScrollVariable() {
      let percentage = Math.abs(this.scrollLeft) / (this.scrollWidth - this.clientWidth) * 100;
      this.style.setProperty("--flux-scroll-percentage", percentage + "%");
    }
  };
  var UITabs = class _UITabs extends UIControl {
    boot() {
      this._focusable = new FocusableGroup(this, { wrap: true });
      this._selectableGroup = new SelectableGroup(this);
      this._controllable = new Controllable(this, { disabled: this._disabled });
      this._controllable.initial((initial) => initial && this._selectableGroup.setState(initial));
      this._controllable.getter(() => this._selectableGroup.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this._selectableGroup.setState(value3);
      }));
      this._selectableGroup.onChange(detangled(() => {
        this._controllable.dispatch();
      }));
      on(this, "keydown", (e) => {
        if (["ArrowDown", "ArrowRight"].includes(e.key)) {
          this._focusable.focusNext();
          e.preventDefault();
          e.stopPropagation();
        } else if (["ArrowUp", "ArrowLeft"].includes(e.key)) {
          this._focusable.focusPrev();
          e.preventDefault();
          e.stopPropagation();
        }
      });
      setAttribute2(this, "role", "tablist");
    }
    mount() {
      this.initializeTabs();
      this._focusable.ensureTabbable();
      if (!this._selectableGroup.getState()) {
        this._selectableGroup.selectFirst();
      }
      new MutationObserver((mutations) => {
        this.initializeTabs();
        let selected = this._selectableGroup.selected();
        selected.el.closest("ui-tab-group").showPanel(selected.value);
      }).observe(this, { childList: true });
    }
    initializeTabs() {
      this.walker().each((el) => {
        if (el._initialized) return;
        if (el._disableable) return;
        if (el.hasAttribute("action")) return;
        initializeTab(el);
        el._initialized = true;
      });
    }
    walker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof UITabGroup) return reject();
        if (el instanceof _UITabs) return reject();
        if (!["a", "button"].includes(el.localName)) return skip();
      });
    }
  };
  function initializeTab(el) {
    el._disableable = new Disableable(el);
    let link = el.matches("a") ? el : null;
    let target = link || el;
    el._disableable.onInitAndChange((disabled) => {
      if (disabled) {
        setAttribute2(target, "aria-disabled", "true");
      } else {
        removeAttribute(target, "aria-disabled");
      }
    });
    let id = assignId(target, "tab");
    setAttribute2(target, "role", "tab");
    target._focusable = new Focusable(target, { disableable: el._disableable, tabbableAttr: "data-active" });
    if (!link) {
      let panel = el.getAttribute("name");
      el._selectable = new Selectable(el, {
        value: panel || Math.random().toString(36).substring(2, 10),
        label: el.hasAttribute("label") ? el.getAttribute("label") : el.textContent.trim(),
        selectedInitially: el.hasAttribute("selected"),
        toggleable: false
      });
      on(el, "click", () => el._selectable.press());
      el._selectable.onInitAndChange(() => {
        let panelEl = el.closest("ui-tab-group")?.getPanel(panel);
        el._selectable.getState() ? panelEl?.show() : panelEl?.hide();
        if (el._selectable.getState()) {
          el._focusable.focus(false);
        }
      });
      let pointerdownTimeout = null;
      if (!el.closest("ui-tab-group")?.hasAttribute("manual")) {
        on(el, "pointerdown", () => {
          clearTimeout(pointerdownTimeout);
          pointerdownTimeout = setTimeout(() => pointerdownTimeout = null);
        });
        on(el, "focus", () => {
          if (!pointerdownTimeout) {
            el._selectable.select();
          }
        });
      }
      queueMicrotask(() => {
        let container = el.closest("ui-tab-group");
        if (!container) return;
        let panelEl = container.getPanel(panel);
        if (!panelEl) throw new Error("Could not find panel...");
        setAttribute2(el, "aria-controls", panelEl.id);
        setAttribute2(panelEl, "aria-labelledby", el.id);
      });
    }
  }
  function initializePanel(el) {
    if (el._initialized) return;
    assignId(el, "tab-panel");
    setAttribute2(el, "role", "tabpanel");
    el.hasAttribute("tabindex") || setAttribute2(el, "tabindex", "-1");
    el.show = () => {
      setAttribute2(el, "data-selected", "");
      setAttribute2(el, "tabindex", "0");
    };
    el.hide = () => {
      removeAttribute(el, "data-selected");
      setAttribute2(el, "tabindex", "-1");
    };
    el._initialized = true;
  }
  element("tab-group", UITabGroup);
  element("tabs-scroll-area", UITabsScrollArea);
  element("tabs", UITabs);

  // js/otp.js
  var UIOTP = class extends UIControl {
    mount() {
      this.inputEls = this.querySelectorAll("[data-flux-otp-input]");
      this.length = this.inputEls.length;
      this.config = {
        mode: this.getAttribute("mode") || "numeric",
        autocomplete: this.getAttribute("autocomplete") || "one-time-code",
        autoSubmit: this.getAttribute("submit") === "auto",
        inputAriaLabelTemplate: this.getAttribute("data-flux-input-aria-label") || "Character {current} of {total}"
      };
      this.state = {
        length: 0,
        onChanges: [],
        getValue: () => {
          return Array.from(this.inputEls).map((input) => input.value).join("");
        },
        setValue: (value3) => {
          let cleaned = (value3 ?? "").replace(/\s/g, "").replace(this.invalidCharacters(), "").toUpperCase();
          for (let i = 0; i < this.length; i++) {
            this.inputEls[i].value = cleaned[i] || "";
          }
          this.state.length = cleaned.length;
          this.state.onChanges.forEach((i) => i(cleaned));
        },
        notify: () => {
          let value3 = this.state.getValue();
          this.state.length = value3.length;
          this.state.onChanges.forEach((i) => i(value3));
        },
        reapply: () => {
          let value3 = this.state.getValue();
          this.state.setValue(value3);
        },
        onChange: (callback) => {
          this.state.onChanges.push(callback);
        }
      };
      if (this.hasAttribute("value")) {
        this.state.setValue(this.getAttribute("value"));
      }
      this._controllable = new Controllable(this);
      this._controllable.initial((initial) => initial && this.state.setValue(initial));
      this._controllable.getter(() => this.state.getValue());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => this.state.setValue(value3)));
      this.state.onChange(detangled(() => this._controllable.dispatch()));
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.state.getValue()
      });
      this.state.onChange((value3) => {
        this._submittable.update(value3);
        if (value3.length === this.length && this.config.autoSubmit) {
          this.closest("form")?.requestSubmit();
        }
        let activeIndex = Array.from(this.inputEls).findIndex((input) => input === document.activeElement);
        if (activeIndex > this.nextIndex()) {
          this.focusIndex(this.nextIndex());
        }
      });
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        for (let inputEl of this.inputEls) {
          if (disabled) {
            setAttribute2(inputEl, "disabled", disabled);
          } else {
            removeAttribute(inputEl, "disabled");
          }
        }
      });
      this.updateTabIndexes(this.nextIndex());
      for (let i = 0; i < this.length; i++) {
        setAttribute2(this.inputEls[i], "autocomplete", i === 0 ? this.config.autocomplete : "off");
        setAttribute2(this.inputEls[i], "aria-label", this.config.inputAriaLabelTemplate.replace("{current}", i + 1).replace("{total}", this.length));
        let prevValue;
        on(this.inputEls[i], "beforeinput", (e) => {
          prevValue = e.target.value;
        });
        on(this.inputEls[i], "input", (e) => {
          e.stopPropagation();
          let value3 = e.target.value;
          if (value3.length > 1) {
            this.state.setValue(value3);
            this.focusIndex(this.nextIndex());
            e.preventDefault();
            return;
          }
          if (value3 == "") {
            this.state.reapply();
            if (i < this.nextIndex()) {
              this.focusIndex(i);
            } else {
              this.focusPrev(i);
            }
            return;
          }
          if (this.invalidCharacters().test(value3)) {
            e.target.value = prevValue;
            this.focusIndex(i);
            return;
          }
          if (this.config.mode !== "numeric") {
            e.target.value = value3.toUpperCase();
          }
          this.inputEls[i].blur();
          this.state.notify();
          this.focusNext(i);
        });
        on(this.inputEls[i], "keydown", (e) => {
          if (["Backspace", "Delete", "Clear"].includes(e.key)) {
            if (e.target.value === "") {
              requestAnimationFrame(() => this.focusPrev(i));
            }
          } else if (e.key === "ArrowRight") {
            this.focusNext(i);
            e.preventDefault();
          } else if (e.key === "ArrowLeft") {
            this.focusPrev(i);
            e.preventDefault();
          }
        });
        on(this.inputEls[i], "pointerdown", (e) => {
          this.focusIndex(Math.min(i, this.nextIndex()));
          e.preventDefault();
        });
        on(this.inputEls[i], "focus", (e) => {
          e.target.setSelectionRange(0, 1);
          this.updateTabIndexes(i);
        });
      }
    }
    trigger() {
      return this.inputEls[this.nextIndex()];
    }
    focusIndex(index) {
      let input = this.inputEls[index];
      if (document.activeElement === input) {
        input.blur();
      }
      input.focus();
    }
    focusPrev(i) {
      this.focusIndex(Math.max(0, i - 1));
    }
    focusNext(i) {
      this.focusIndex(Math.min(i + 1, this.nextIndex()));
    }
    nextIndex() {
      return Math.min(this.state.length, this.length - 1);
    }
    updateTabIndexes(active) {
      for (let i = 0; i < this.length; i++) {
        if (i === active) {
          setAttribute2(this.inputEls[i], "tabindex", "0");
        } else {
          setAttribute2(this.inputEls[i], "tabindex", "-1");
        }
      }
    }
    invalidCharacters() {
      switch (this.config.mode) {
        case "numeric":
          return /[^\d]/g;
        case "alpha":
          return /[^a-zA-Z]/g;
        case "alphanumeric":
          return /[^a-zA-Z0-9]/g;
        default:
          throw new Error(`Unknown OTP input mode "${this.config.mode}"`);
      }
    }
  };
  element("otp", UIOTP);

  // js/store.js
  document.addEventListener("alpine:init", () => {
    let Alpine = window.Alpine;
    let applyAppearance = window.Flux?.applyAppearance;
    if (!applyAppearance) {
      applyAppearance = () => {
        window.Flux.appearance = null;
        window.localStorage.removeItem("flux.appearance");
      };
    }
    let flux = Alpine.reactive({
      toast(...params) {
        let detail = { slots: {}, dataset: {} };
        if (typeof params[0] === "string") {
          detail.slots.text = params.shift();
        }
        if (typeof params[0] === "string") {
          detail.slots.heading = detail.slots.text;
          detail.slots.text = params.shift();
        }
        let options = params.shift() || {};
        if (options.text) detail.slots.text = options.text;
        if (options.heading) detail.slots.heading = options.heading;
        if (options.variant) detail.dataset.variant = options.variant;
        if (options.position) detail.dataset.position = options.position;
        if (options.duration !== void 0) detail.duration = options.duration;
        document.dispatchEvent(new CustomEvent("toast-show", { detail }));
      },
      modal(name) {
        return {
          show() {
            document.dispatchEvent(new CustomEvent("modal-show", { detail: { name } }));
          },
          close() {
            document.dispatchEvent(new CustomEvent("modal-close", { detail: { name } }));
          }
        };
      },
      modals() {
        return { close() {
          document.dispatchEvent(new CustomEvent("modal-close", { detail: {} }));
        } };
      },
      appearance: window.localStorage.getItem("flux.appearance") || "system",
      systemAppearanceChanged: 1,
      // A counter to trigger reactivity when the system appearance changes...
      get dark() {
        JSON.stringify(flux.systemAppearanceChanged);
        if (flux.appearance === "system") {
          let media2 = window.matchMedia("(prefers-color-scheme: dark)");
          return media2.matches;
        } else {
          return flux.appearance === "dark";
        }
      },
      set dark(value3) {
        let current = this.dark;
        if (value3 === current) return;
        if (value3) {
          flux.appearance = "dark";
        } else {
          flux.appearance = "light";
        }
      }
    });
    window.Flux = flux;
    Alpine.magic("flux", () => flux);
    Alpine.effect(() => {
      applyAppearance(flux.appearance);
    });
    document.addEventListener("livewire:navigating", (e) => {
      e.detail.onSwap(() => {
        applyAppearance(flux.appearance);
      });
    });
    let media = window.matchMedia("(prefers-color-scheme: dark)");
    media.addEventListener("change", () => {
      flux.systemAppearanceChanged++;
      applyAppearance(flux.appearance);
    });
    Alpine.data("fluxInputClearable", () => ({
      clear() {
        let input = this.$el.closest("[data-flux-input]").querySelector("input");
        input.value = "";
        input.dispatchEvent(new Event("input", { bubbles: false }));
        input.dispatchEvent(new Event("change", { bubbles: false }));
        input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
        input.focus();
      }
    }));
    Alpine.data("fluxInputViewable", () => ({
      open: false,
      toggle() {
        this.open = !this.open;
        let input = this.$el.closest("[data-flux-input]").querySelector("input");
        input.setAttribute("type", this.open ? "text" : "password");
      },
      init() {
        let input = this.$el.closest("[data-flux-input]")?.querySelector("input");
        if (!input) return;
        let observer = new MutationObserver(() => {
          let type = this.open ? "text" : "password";
          if (input.getAttribute("type") === type) return;
          input.setAttribute("type", type);
        });
        observer.observe(input, { attributes: true, attributeFilter: ["type"] });
      }
    }));
    Alpine.data("fluxInputCopyable", () => ({
      copied: false,
      copy() {
        this.copied = !this.copied;
        let input = this.$el.closest("[data-flux-input]").querySelector("input");
        navigator.clipboard && navigator.clipboard.writeText(input.value);
        input.dispatchEvent(new CustomEvent("copy", { bubbles: false }));
        setTimeout(() => this.copied = false, 2e3);
      }
    }));
    Alpine.data("fluxInputFile", (translations) => ({
      updateLabel(event) {
        this.$refs.name.textContent = event.target.files[1] ? event.target.files.length + " " + translations.files : event.target.files[0]?.name || translations.noFile;
      },
      init() {
        Object.defineProperty(this.$refs.input, "value", {
          ...Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, "value"),
          set(value3) {
            Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, "value").set.call(this, value3);
            if (!value3) this.dispatchEvent(new Event("change", { bubbles: true }));
          }
        });
      }
    }));
    Alpine.data("fluxModal", (name, scope) => ({
      handleShow(event) {
        if (event.detail.name === name) {
          if (scope && event.detail.scope === scope) {
            this.$el.showModal();
          } else if (!event.detail.scope) {
            this.$el.showModal();
          }
        }
      },
      handleClose(event) {
        if (!event.detail.name) {
          this.$el.close();
        } else if (event.detail.name === name) {
          if (scope && event.detail.scope === scope) {
            this.$el.close();
          } else if (!event.detail.scope) {
            this.$el.close();
          }
        }
      }
    }));
    Alpine.data("fluxCommandInputClearable", () => ({
      clear() {
        let input = this.$el.closest("[data-flux-command-input]").querySelector("input");
        input.value = "";
        input.dispatchEvent(new Event("input", { bubbles: false }));
        input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
        input.focus();
      }
    }));
    Alpine.data("fluxSelectSearchClearable", () => ({
      clear() {
        let input = this.$el.closest("[data-flux-select-search]").querySelector("input");
        input.value = "";
        input.dispatchEvent(new Event("input", { bubbles: false }));
        input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
        input.focus();
      }
    }));
    Alpine.data("fluxPillboxSearchClearable", () => ({
      clear() {
        let input = this.$el.closest("[data-flux-pillbox-search]").querySelector("input");
        input.value = "";
        input.dispatchEvent(new Event("input", { bubbles: false }));
        input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
        input.focus();
      }
    }));
  });

  // js/index.js
  if (!isSupported2() && !isPolyfilled()) {
    apply2();
  }
})();
