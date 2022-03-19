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

const OO_WORD_FORMATS = [
    '.doc',
    '.docx',
    '.docm',
    '.dot',
    '.dotx',
    '.dotm',
    '.odt',
    '.fodt',
    '.ott',
    '.rtf',
    '.txt',
    '.html',
    '.htm',
    '.mht',
    '.xml',
    '.pdf',
    '.djvu',
    '.fb2',
    '.epub',
    '.xps',
    '.oxps',
];
const OO_CELL_FORMATS = [
    '.xls',
    '.xlsx',
    '.xlsm',
    '.xlt',
    '.xltx',
    '.xltm',
    '.ods',
    '.fods',
    '.ots',
    '.csv',
];
const OO_SLIDE_FORMATS = [
    '.pps',
    '.ppsx',
    '.ppsm',
    '.ppt',
    '.pptx',
    '.pptm',
    '.pot',
    '.potx',
    '.potm',
    '.odp',
    '.fodp',
    '.otp',
];

const Edit = ({attributes, setAttributes}) => {
    const [url, setUrl] = useState(attributes.url);

    const onlyofficeAllowedExts = OO_SLIDE_FORMATS.concat(OO_CELL_FORMATS).concat(OO_WORD_FORMATS);
    let onlyofficeAllowedMimes = [];

    for (let ext of onlyofficeAllowedExts) {
        onlyofficeAllowedMimes.push(mime.getType(ext));
    }

    if (!url && attributes.selectedAttachment) {
        const editorUrl = attributes.getEditorUrl + attributes.selectedAttachment.id + (attributes.getEditorUrl.indexOf('wp-json') === -1 ? '&_wpnonce=' : '?_wpnonce=')
            + attributes.nonce;

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
                    setAttributes({selectedAttachment: el, nonce: oo_media.nonce, getEditorUrl: oo_media.getEditorUrl});
                }}
            />
    );
};

export default Edit;