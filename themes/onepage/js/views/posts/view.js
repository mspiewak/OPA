define([
    'jquery',
    'underscore',
    'backbone',
    'collections/posts',
    'text!../../../templates/posts/view.html'
], function($, _, Backbone, PostsCollection, postViewTemplate){
   var PostsView = Backbone.View.extend({
        el: '.page',
        render: function(){
            that=this;
            var posts=new PostsCollection();
            posts.fetch({
                success: function(){
                    var template=_.template(postViewTemplate, {posts:posts.models});
                    that.$el.html(template);
                }
            });
        }
   });  
   return PostsView;
});