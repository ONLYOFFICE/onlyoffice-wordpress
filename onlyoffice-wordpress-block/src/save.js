const Save = ( { attributes } ) => {
    return (
        <div style={{height: '650px', maxWidth: 'inherit', padding: '20px'}}>
            { attributes.url && <iframe width="100%" height="100%" src={ attributes.url }/>}
         </div>
    );
};
export default Save;
