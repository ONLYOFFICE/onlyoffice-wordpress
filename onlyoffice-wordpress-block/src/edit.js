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

const Edit = ({attributes, setAttributes}) => {
    const onlyofficeAllowedExts = oo_media.formats;
    let onlyofficeAllowedMimes = [];

    const getMimeType = function( name ) {
        var allTypes = oo_media.mimeTypes;

        if (allTypes[name] !== undefined) {
            return allTypes[name];
        }

        for(var key in allTypes) {
            if(key.indexOf(name) !== -1) {
                return allTypes[key];
            }
        }

        return false;
    };

    for (let ext of onlyofficeAllowedExts) {
        let mimeType = getMimeType(ext);

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
                            if (el && el.hasOwnProperty('id')) {
                                setAttributes({ id: el.id, fileName: el.filename || el.guid.raw.substring(el.guid.raw.lastIndexOf('/') + 1) });
                            }
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
                    if (el && el.hasOwnProperty('id')) {
                        setAttributes({ id: el.id, fileName: el.filename || el.guid.raw.substring(el.guid.raw.lastIndexOf('/') + 1) });
                    }
                }}
            />
    )
};

export default Edit;
