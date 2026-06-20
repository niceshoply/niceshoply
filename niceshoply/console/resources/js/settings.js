/**
 * Settings Page Tab Management
 * Handles main navigation tabs (updates URL) and internal tabs (no URL update)
 */

class SettingsTabs {
  constructor() {
    this.mainNavSelector = '.settings-nav a';
    // 仅操作设置页右侧主 Tab，避免误伤 SMS/AI 等内部 .tab-pane
    this.mainTabPaneSelector = '#app-form > .row > .col-md-9 .tab-content > .tab-pane';
    this.headerSelector = '.setting-header';
    this.formSelector = '#app-form';
    this.lastMainTabUrl = window.location.href; // Store the main tab URL
    
    this.init();
  }

  /**
   * 解析 ?tab= 参数：兼容 basics 与 tab-setting-basics 两种写法
   */
  resolveMainTabTarget(tabParam) {
    if (!tabParam) {
      return null;
    }

    const normalized = tabParam.startsWith('tab-setting-')
      ? tabParam
      : `tab-setting-${tabParam}`;

    return `#${normalized}`;
  }

  /**
   * 切换主 Tab 显示状态（同时维护 show，避免 fade 半透明叠层）
   */
  activateMainTabPane(target) {
    if (!target || !$(target).length) {
      return false;
    }

    $(this.mainTabPaneSelector).removeClass('show active');
    $(target).addClass('show active');
    return true;
  }

  /**
   * Initialize settings tabs
   */
  init() {
    this.handleMainNavTabs();
    this.handleInternalTabs();
    this.handleUrlParams();
    this.handleBrowserNavigation();
    this.handleFormSubmit();
  }

  /**
   * Handle main navigation tabs (left sidebar)
   * These tabs update the URL when clicked
   */
  handleMainNavTabs() {
    $(this.mainNavSelector).on('click', (e) => {
      e.preventDefault();
      const $link = $(e.currentTarget);
      const target = $link.data('bs-target');
      
      if (!target) return;

      // Update active state
      $(this.mainNavSelector).removeClass('active settings-tab-active');
      $link.addClass('active settings-tab-active');
      
      // Show corresponding tab pane
      this.activateMainTabPane(target);
      
      // Update header text
      $(this.headerSelector).text($link.text());
      
      // Update URL without reloading page
      const tabId = target.replace('#tab-setting-', '');
      const newUrl = `${window.location.pathname}?tab=${tabId}`;
      // Add isSettingsTab flag to state to identify settings tab navigation
      window.history.pushState({ path: newUrl, isSettingsTab: true }, '', newUrl);
      
      // Store the main tab URL to prevent internal tabs from changing it
      this.lastMainTabUrl = newUrl;
      
      // Initialize internal tabs for the newly activated pane
      setTimeout(() => {
        this.initializeInternalTabs();
      }, 100);
    });
  }

  /**
   * Handle internal tabs (like language switchers in SMS templates)
   * These tabs do NOT update the URL, just switch content
   */
  handleInternalTabs() {
    // Find all internal tab containers (tabs inside main tab panes, not the main nav)
    // Exclude tabs that are direct children of .settings-nav
    const $internalTabs = $('.tab-pane .nav-tabs');
    const self = this;
    
    $internalTabs.each((index, container) => {
      const $container = $(container);
      const $tabs = $container.find('button[data-bs-toggle="tab"]');
      
      // Find corresponding tab content panes
      const $tabContent = $container.siblings('.tab-content');
      let $panes = $tabContent.find('.tab-pane');
      
      // If no sibling tab-content, try finding panes within the same parent
      if ($panes.length === 0) {
        $panes = $container.parent().find('.tab-content .tab-pane');
      }
      
      // Ensure first tab is active by default
      if ($tabs.length > 0) {
        // Remove all active classes
        $tabs.removeClass('active');
        if ($panes.length > 0) {
          $panes.removeClass('show active');
        }
        
        // Activate first tab and pane
        const $firstTab = $tabs.first();
        const firstTarget = $firstTab.data('bs-target');
        
        $firstTab.addClass('active');
        if (firstTarget && $(firstTarget).length) {
          $(firstTarget).addClass('show active');
        } else if ($panes.length > 0) {
          // Fallback: activate first pane
          $panes.first().addClass('show active');
        }
      }
      
      // Handle tab clicks (no URL update)
      $tabs.off('click.internal-tab').on('click.internal-tab', (e) => {
        e.preventDefault();
        e.stopPropagation(); // Prevent bubbling to main nav
        
        // Prevent any URL changes
        const $tab = $(e.currentTarget);
        const target = $tab.data('bs-target');
        
        if (target) {
          // Manually handle tab switching without Bootstrap's default behavior
          $container.find('button[data-bs-toggle="tab"]').removeClass('active');
          $tab.addClass('active');
          
          if ($panes.length > 0) {
            $panes.removeClass('show active');
            $(target).addClass('show active');
          }
          
          // Ensure URL doesn't change - restore to last main tab URL
          // Only restore if it's not a settings tab navigation
          if (window.location.href !== self.lastMainTabUrl) {
            const currentState = window.history.state;
            // Only restore if current state is not a settings tab navigation
            if (!currentState || !currentState.isSettingsTab) {
              window.history.replaceState(null, '', self.lastMainTabUrl);
            }
          }
        }
      });
      
      $tabs.off('shown.bs.tab.internal-tab').on('shown.bs.tab.internal-tab', (e) => {
        // This is an internal tab, don't update URL
        // Ensure URL doesn't change - restore to last main tab URL
        // Only restore if it's not a settings tab navigation
        if (window.location.href !== self.lastMainTabUrl) {
          const currentState = window.history.state;
          // Only restore if current state is not a settings tab navigation
          if (!currentState || !currentState.isSettingsTab) {
            window.history.replaceState(null, '', self.lastMainTabUrl);
          }
        }
        
        // Just handle any callbacks if needed
        const $tab = $(e.currentTarget);
        const targetId = $tab.data('bs-target');
        
        // Trigger custom event for internal tab handlers
        $(document).trigger('settings:internal-tab-changed', {
          tab: $tab,
          target: targetId
        });
      });
    });
    
    // Monitor URL changes and revert if caused by internal tabs
    setInterval(() => {
      const currentUrl = window.location.href;
      const currentState = window.history.state;
      
      // If URL changed but it's not a main tab change (doesn't match ?tab= pattern)
      // And it's not a settings tab navigation
      if (currentUrl !== self.lastMainTabUrl && !currentUrl.includes('?tab=')) {
        // Only revert if current state is not a settings tab navigation
        if (!currentState || !currentState.isSettingsTab) {
          // Revert to last known main tab URL
          window.history.replaceState(null, '', self.lastMainTabUrl);
        }
      } else if (currentUrl.includes('?tab=') && currentState && currentState.isSettingsTab) {
        // Update last known main tab URL when main tab changes (only for settings tabs)
        self.lastMainTabUrl = currentUrl;
      }
    }, 100);
  }

  /**
   * Handle URL parameters on page load
   */
  handleUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    if (tabParam) {
      const tabTarget = this.resolveMainTabTarget(tabParam);
      const $tabLink = $(`${this.mainNavSelector}[data-bs-target="${tabTarget}"]`);

      if ($tabLink.length && this.activateMainTabPane(tabTarget)) {
        $(this.mainNavSelector).removeClass('active settings-tab-active');
        $tabLink.addClass('active settings-tab-active');
        $(this.headerSelector).text($tabLink.text());
        this.lastMainTabUrl = window.location.href;
      } else {
        this.activateFirstTab();
      }
    } else {
      // No tab parameter, ensure first tab is active
      this.ensureFirstTabActive();
      // Update lastMainTabUrl
      this.lastMainTabUrl = window.location.href;
    }
    
    // Initialize internal tabs after main tabs are set
    this.initializeInternalTabs();
  }

  /**
   * Activate first main tab
   */
  activateFirstTab() {
    const $firstTab = $(this.mainNavSelector).first();
    if ($firstTab.length) {
      $(this.mainNavSelector).removeClass('active settings-tab-active');

      $firstTab.addClass('active settings-tab-active');
      const firstTarget = $firstTab.data('bs-target');
      if (firstTarget && this.activateMainTabPane(firstTarget)) {
        $(this.headerSelector).text($firstTab.text());
      }
    }
  }

  /**
   * Ensure first tab is active (preserve HTML defaults)
   */
  ensureFirstTabActive() {
    const $activeTab = $(`${this.mainNavSelector}.active`).first();

    if ($activeTab.length) {
      const target = $activeTab.data('bs-target');
      if (target) {
        this.activateMainTabPane(target);
      }
      $(this.headerSelector).text($activeTab.text());
    } else {
      this.activateFirstTab();
    }
  }

  /**
   * Initialize all internal tabs to show first tab
   */
  initializeInternalTabs() {
    // Find all internal tab containers within active tab pane
    const $activePane = $(`${this.mainTabPaneSelector}.active`);
    const $internalTabContainers = $activePane.find('.nav-tabs');
    
    $internalTabContainers.each((index, container) => {
      const $container = $(container);
      const $tabs = $container.find('button[data-bs-toggle="tab"]');
      
      // Find tab content panes - they should be siblings of nav-tabs
      const $tabContent = $container.siblings('.tab-content');
      let $panes = $tabContent.find('.tab-pane');
      
      // If no sibling tab-content, try finding panes within the same parent
      if ($panes.length === 0) {
        $panes = $container.parent().find('.tab-content .tab-pane');
      }
      
      if ($tabs.length > 0) {
        // Remove all active classes
        $tabs.removeClass('active');
        if ($panes.length > 0) {
          $panes.removeClass('show active');
        }
        
        // Activate first tab and pane
        const $firstTab = $tabs.first();
        const firstTarget = $firstTab.data('bs-target');
        
        $firstTab.addClass('active');
        if (firstTarget && $(firstTarget).length) {
          $(firstTarget).addClass('show active');
        } else if ($panes.length > 0) {
          // Fallback: activate first pane
          $panes.first().addClass('show active');
        }
      }
    });
  }

  /**
   * Handle browser back/forward buttons
   */
  handleBrowserNavigation() {
    window.addEventListener('popstate', (e) => {
      const urlParams = new URLSearchParams(window.location.search);
      const tabParam = urlParams.get('tab');
      
      if (tabParam) {
        const tabTarget = this.resolveMainTabTarget(tabParam);
        const $tabLink = $(`${this.mainNavSelector}[data-bs-target="${tabTarget}"]`);

        if ($tabLink.length && this.activateMainTabPane(tabTarget)) {
          $(this.mainNavSelector).removeClass('active settings-tab-active');
          $tabLink.addClass('active settings-tab-active');
          $(this.headerSelector).text($tabLink.text());
          this.initializeInternalTabs();
        }
      } else {
        // No tab parameter, activate first tab
        this.activateFirstTab();
        this.initializeInternalTabs();
      }
    });
  }

  /**
   * Handle form submit to preserve current tab
   */
  handleFormSubmit() {
    $(this.formSelector).on('submit', () => {
      const $activeTab = $(`${this.mainNavSelector}.active`);
      
      if ($activeTab.length) {
        const target = $activeTab.data('bs-target');
        if (target) {
          const tabId = target.replace('#tab-setting-', '');
          let $hiddenInput = $('#current_tab');
          
          if ($hiddenInput.length === 0) {
            $(this.formSelector).append(`<input type="hidden" name="tab" id="current_tab" value="${tabId}">`);
          } else {
            $hiddenInput.val(tabId);
          }
        }
      }
    });
  }
}

// Initialize when DOM is ready
$(document).ready(() => {
  // Only initialize on settings page
  if ($('.settings-nav').length > 0) {
    window.settingsTabs = new SettingsTabs();
  }
});

export default SettingsTabs;

