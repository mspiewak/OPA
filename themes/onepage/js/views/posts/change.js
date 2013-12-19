define([
    'jquery',
    'underscore',
    'backbone'
], function($, _, Backbone){
   var PostsChangeView = Backbone.View.extend({
        el: '.second',
        render: function(){
            this.$el.children('img').attr("src","http://www.digitaltrends.com/wp-content/uploads/2011/01/nasa-2025-aircraft-concept-the-boeing-company.jpg");
        }
   });  
   return PostsChangeView;
});