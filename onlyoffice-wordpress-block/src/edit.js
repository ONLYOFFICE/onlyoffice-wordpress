/*
* (c) Copyright Ascensio System SIA 2022
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*     http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

import { MediaPlaceholder, useBlockProps } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import {onlyofficeIcon} from "./index";
import {blockStyle} from "./index";
const mime = require('mime');

const Edit = ({attributes, setAttributes}) => {
    const [url, setUrl] = useState(attributes.url);

    const onlyofficeAllowedExts = attributes.formats || oo_media.formats;
    let onlyofficeAllowedMimes = [];

    for (let ext of onlyofficeAllowedExts) {
        onlyofficeAllowedMimes.push(mime.getType(ext));
    }

    if (!url && attributes.selectedAttachment) {
        const editorUrl = attributes.getEditorUrl + attributes.selectedAttachment.id;

        fetch(editorUrl).then((r) => r.json()).then((data) => {
            setUrl(data.url);
            setAttributes({ url: data.url });
        });
    }

    const blockProps = useBlockProps( { style: blockStyle } );
    return (
        attributes.selectedAttachment ?
            <div {...blockProps}>
                <p style={{display: 'flex'}}>
                    {onlyofficeIcon}
                    <p contentEditable={true}
                       style={{marginLeft: '25px'}}> {attributes.selectedAttachment.filename}</p>
                </p>
            </div>
            :
            <MediaPlaceholder
                labels={{title: 'ONLYOFFICE'}}
                allowedTypes={onlyofficeAllowedMimes}
                onSelect={(el) => {
                    setAttributes({selectedAttachment: el, getEditorUrl: oo_media.getEditorUrl, formats: oo_media.formats});
                }}
            />
    );
};

export default Edit;
