/*
 * (c) Copyright Ascensio System SIA 2022
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
    InspectorControls
} from '@wordpress/block-editor';
import { PanelBody, RadioControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {onlyofficeIcon} from "./index";
import {blockStyle} from "./index";
const mime = require('mime');

const Edit = ({attributes, setAttributes}) => {
    const [url, setUrl] = useState(attributes.url);
    const [ option, setOption ] = useState( 'common' );

    const onlyofficeAllowedExts = attributes.formats || oo_media.formats;
    let onlyofficeAllowedMimes = [];

    for (let ext of onlyofficeAllowedExts) {
        onlyofficeAllowedMimes.push(mime.getType(ext));
    }

    if (!url && attributes.selectedAttachment && attributes.selectedAttachment.id) {
        const editorUrl = attributes.getEditorUrl + attributes.selectedAttachment.id;

        fetch(editorUrl).then((r) => r.json()).then((data) => {
            setUrl(data.url);
            setAttributes({ url: data.url });
        });
    }

    const blockProps = useBlockProps( { style: blockStyle } );
    return (
        attributes.selectedAttachment && attributes.selectedAttachment.id ?
            <div {...blockProps}>
                <InspectorControls key="setting">
                    <PanelBody title={__('Share Settings', 'onlyoffice-plugin')}>
                        <RadioControl
                            selected={ option }
                            options={ [
                                { label: __('Use common rules:', 'onlyoffice-plugin'), value: 'common' },
                                { label: __('Define more specific rules:', 'onlyoffice-plugin'), value: 'specific' },
                            ] }
                            onChange={ ( value ) => setOption( value ) }
                        />
                        <Button variant={'link'} text={__('Add rule', 'onlyoffice-plugin')} disabled={option === 'common'}/>
                    </PanelBody>
                </InspectorControls>
                <p style={{display: 'flex'}}>
                    {onlyofficeIcon}
                    <p style={{marginLeft: '25px'}}> {attributes.selectedAttachment.filename || `${attributes.selectedAttachment.title}.${mime.getExtension(attributes.selectedAttachment.mime_type)}`}</p>
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
