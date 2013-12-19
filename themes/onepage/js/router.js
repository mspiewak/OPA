define([
  'jquery',
  'underscore',
  'backbone',
  'vm'
], function ($, _, Backbone, Vm) {
  var AppRouter = Backbone.Router.extend({
    initialize: function(options) {
      this.appView = {};
    },
 
    register: function (route, name, path) {
      var self = this;
 
      this.route(route, name, function () {
        var args = arguments;
 
        require([path], function (module) {
          var options = null;
          var parameters = route.match(/[:\*]\w+/g);
          
          if (parameters) {
            options = {};
            _.each(parameters, function(name, index) {
              options[name.substring(1)] = args[index];
            });
          }
          
          var page = Vm.create(self.appView, name, module, options);
          page.render(options);
        });
      });
    }
  });
 
  var initialize = function(options){
    var router = new AppRouter(options);
 
    router.register('', 'home', 'views/posts/list');
    router.register('new', 'editPost', 'views/posts/edit');
    router.register('edit/:id', 'editPost', 'views/posts/edit');
    router.register('delete/:id', 'deletePost', 'views/posts/delete');
    router.register('view', 'sampleView', 'views/posts/view');
    router.register('view/change', 'changeImage', 'views/posts/change');
   
    Backbone.history.start();
  };
 
  return {
    initialize: initialize
  };
});
