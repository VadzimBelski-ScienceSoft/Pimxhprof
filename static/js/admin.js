pimcore.registerNS("pimcore.plugin.Pimxhprof");

pimcore.plugin.Pimxhprof = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.Pimxhprof";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params,broker){
        // add a sub-menu item under "Extras" in the main menu
        var toolbar = Ext.getCmp("pimcore_panel_toolbar");

        var action = new Ext.Action({
            id: "my_plugin_menu_item",
            text: "XHProf Performance",
            icon:"/plugins/Pimxhprof/static/img/xhprof16x16.gif",
            handler: this.showTab
        });

        toolbar.items.items[1].menu.add(action);
    },

    showTab: function() {

        Pimxhprof.panel = new Ext.Panel({
            id:         "xhprof_index",
            title:      "XHProf Performance",
            border:     false,
            layout:     "fit",
            closable:   true,
            html : '<iframe width ="100%" height="100%" src="/plugin/Pimxhprof/Index"></iframe>'
        });

        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.add(Pimxhprof.panel);
        tabPanel.activate("xhprof_index");

        pimcore.layout.refresh();
    }
});

var Pimxhprof = new pimcore.plugin.Pimxhprof();