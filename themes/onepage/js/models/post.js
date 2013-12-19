define([
    'underscore',
    'backbone'
], function(_, Backbone){
    var PostModel=Backbone.Model.extend({
        urlRoot:'/blog/index.php/api/posts'
    });
    
    return PostModel;
});