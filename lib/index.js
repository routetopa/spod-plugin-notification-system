var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);

/*app.get('/', function(req, res){
    res.sendFile(__dirname + '/index.html');
});*/

/*io.on('connection', function(socket){
    socket.on('chat message', function(msg){
        io.emit('chat message', msg);
    });
});*/

io.on('connection', function(socket){
    socket.on('realtime_notification', function(msg){

        switch(msg.plugin)
        {
            case 'cocreation' :
                io.emit('realtime_message_' + msg.entity_type + "_" + msg.entity_id, msg);
                break;
            case 'spodpublic' :
                console.log(msg);
                io.emit('realtime_message_' + msg.room_id, msg);
                break;
            default:
                break;
        }
    });


});

http.listen(3000, function(){
    console.log('listening on *:3000');
});