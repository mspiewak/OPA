define([
    'jquery',
    'underscore',
    'backbone',
    'models/post'
], function($, _, Backbone, PostModel){
   var PostDelete = Backbone.View.extend({
        el: '.page',
        render: function(options){
            var post=new PostModel({id:options.id});
            post.destroy({
                success:function(){
                    Backbone.history.navigate('', true);
                }
            });
        }
   });  
   return PostDelete;
});