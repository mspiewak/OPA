define([
    'jquery',
    'underscore',
    'backbone',
    'collections/posts',
    'text!../../../templates/posts/list.html'
], function($, _, Backbone, PostsCollection, postListTemplate){
   var PostsListView = Backbone.View.extend({
        el: '.page',
        render: function(){
            that=this;
            var posts=new PostsCollection();
            posts.fetch({
                success: function(){
                    var template=_.template(postListTemplate, {posts:posts.models});
                    that.$el.html(template);
                }
            });
        }
   });  
   return PostsListView;
});