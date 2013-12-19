define([
    'underscore',
    'backbone'
], function(_, Backbone){
    var PostsCollection=Backbone.Collection.extend({
        url:'/blog/index.php/api/posts'
    });
    
    return PostsCollection;
});