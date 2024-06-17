document.addEventListener( 'DOMContentLoaded', function(){
    let tasks = [];
    let taskCounter = 0;
    const taskList = document.getElementsByClassName( 'task-item' )

    for( let i = 0; i < taskList.length; i++ ){
        taskList[ i ].addEventListener( 'change', function( event ){
            let isChecked = event.target.checked
            if( isChecked ){
                taskList[ i ].classList.add( 'is-done' )
            }else{
                taskList[ i ].classList.remove( 'is-done' )
            }
            sendCheckedRequest( taskList[ i ] )
        } )
    }

    const sendCheckedRequest = ( taskItem ) => {
        //send ajax call to /done
        const id = taskItem.getAttribute( 'data-id' )
        const done = taskItem.checked
        const url = `/auth/dashboard/done/${id}`
        fetch( url )
            .then( response => response.json() )
            .then( data => {
                console.log( "Request ready" )
            } )
    }
} )