/*
 * (c) Copyright Ascensio System SIA 2023
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

import { 
    MediaPlaceholder,
    useBlockProps,
    BlockControls,
    MediaReplaceFlow,
    InspectorControls,
} from '@wordpress/block-editor';
import {
    PanelBody,
    __experimentalInputControl as InputControl
} from '@wordpress/components';
import { onlyofficeIcon } from "./index";
import { blockStyle } from "./index";
import { __ } from '@wordpress/i18n';
const mime = require('mime');

const Edit = ({attributes, setAttributes}) => {
    const onlyofficeAllowedExts = oo_media.formats;
    let onlyofficeAllowedMimes = [];

    for (let ext of onlyofficeAllowedExts) {
        let mimeType = null;
        switch (ext) {
            case '.docxf': {
                mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.docxf';
                break;
            }
            case '.oform': {
                mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.oform';
                break;
            }
            default: {
                mimeType = mime.getType(ext);
                break;
            }
        }

        if (mimeType) {
            onlyofficeAllowedMimes.push(mimeType);
        }
    }

    const blockProps = useBlockProps( { style: blockStyle } );
    return (
        attributes.id ?
            <div {...blockProps}>
                <InspectorControls key="setting">
                    <PanelBody title={__('Settings')}>
                        <InputControl label={__('Name')} value={attributes.fileName} onChange={ ( value ) => setAttributes({ fileName: value }) } />
                    </PanelBody>
                </InspectorControls>

                <p style={{display: 'flex'}}>
                    {onlyofficeIcon}
                    <p style={{marginLeft: '25px'}}> {attributes.fileName || ""}</p>
                </p>
                <BlockControls>
                    <MediaReplaceFlow
                        mediaId={ attributes.id }
                        allowedTypes={ onlyofficeAllowedMimes }
                        accept={ onlyofficeAllowedMimes.join() }
                        onSelect={ (el) => {
                            setAttributes({ id: el.id, fileName: el.filename || el.title + "." + mime.getExtension(el.mime_type) });
                        }}
                        name={__('Replace')}
                    />
                </BlockControls>
            </div>
            :
            <MediaPlaceholder
                labels={{title: 'ONLYOFFICE'}}
                allowedTypes={onlyofficeAllowedMimes}
                accept={onlyofficeAllowedMimes.join()}
                onSelect={(el) => {
                    setAttributes({ id: el.id, fileName: el.filename || el.title + "." + mime.getExtension(el.mime_type) });
                }}
            />
    )
};

export default Edit;
