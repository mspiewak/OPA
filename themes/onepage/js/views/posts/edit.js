define([
    'jquery',
    'underscore',
    'backbone',
    'models/post',
    'text!../../../templates/posts/edit.html'
], function($, _, Backbone, PostModel, postEditTemplate){
    
    $.fn.serializeObject = function() {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
    
   var PostsListView = Backbone.View.extend({
        el: '.page',
        render: function(options){
            var that=this;
            if(options && options.id){
                that.post=new PostModel({id:options.id});
                that.post.fetch({
                    success: function(post){
                        var template=_.template(postEditTemplate, {post:post});
                        that.$el.html(template);
                    }
                });
            }
            else{                            
                var template=_.template(postEditTemplate, {post:null});
                this.$el.html(template);
            }
        },
        events:{
            'click .submit': 'savePost',
            'click .delete': 'deletePost'
        },
        savePost: function(ev){
            var postDetails = $(ev.currentTarget).parent('form').serializeObject();
            var post = new PostModel();
            post.save(postDetails, {
                success:function(){
                    Backbone.history.navigate('', true);
                }
            });
            return false;
        },
        deletePost: function(ev){
            var post = new PostModel({id:$(ev.currentTarget).parent('form').find('input[name="id"]').val()});
            this.post.destroy({
                success:function(){
                    Backbone.history.navigate('', true);
                }
            });
            return false;
        }
   });  
   return PostsListView;
});