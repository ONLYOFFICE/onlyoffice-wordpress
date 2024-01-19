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
    RichText,
    HeightControl
} from '@wordpress/block-editor';
import {
    PanelBody,
    ToggleControl,
    SelectControl,
    __experimentalInputControl as InputControl
} from '@wordpress/components';
import { onlyofficeIcon } from "./index";
import { blockStyle } from "./index";
import { __ } from '@wordpress/i18n';

const Edit = ({attributes, setAttributes}) => {
    const onlyofficeAllowedExts = oo_media.formats;
    let onlyofficeAllowedMimes = [];
    const viewOptions = [
        {
            label: __('Embedded', 'onlyoffice-plugin'),
            value: 'embedded'
        },
        {
            label: __('Link'),
            value: 'link'
        }
    ];

    if (attributes.hasOwnProperty('width') && attributes.width.length > 0) {
        blockStyle.width = attributes.width;
    }

    if (attributes.hasOwnProperty('height') && attributes.height.length > 0) {
        blockStyle.height = attributes.height;
    }

    let showWidthControl = true;

    if (attributes.align === "full") {
        delete blockStyle.width;
        showWidthControl = false;
    }

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

    const blockProps = attributes.documentView === 'link' ?  useBlockProps( { style: null } ) : useBlockProps( { style: blockStyle } );
    return (
        attributes.id ?
            <div {...blockProps}>
                <InspectorControls key="setting">
                    <PanelBody title={__('Settings')}>
                        <InputControl label={__('Name')} value={attributes.fileName} onChange={ ( value ) => setAttributes({ fileName: value }) } />
                        <SelectControl
                            label={__('Document view', 'onlyoffice-plugin')}
                            value={attributes.documentView}
                            options={viewOptions}
                            onChange={(value) => {setAttributes({ documentView: value })}}
                            />
                        {
                            attributes.documentView === 'link' ?
                                <ToggleControl
                                    checked={attributes.inNewTab}
                                    label={__('Open in new tab')}
                                    onChange={(value) => setAttributes({ inNewTab: value })} 
                                    />
                                :
                                <div>
                                    {
                                        showWidthControl ?
                                            <HeightControl label={ __("Width", "onlyoffice-docspace-plugin") } value={attributes.width} onChange={ ( value ) => setAttributes({ width: value }) }/>
                                            :
                                            ''
                                        }
                                    <HeightControl label={ __("Height", "onlyoffice-docspace-plugin") } value={attributes.height} onChange={ ( value ) => setAttributes({ height: value }) }/>
                                </div>
                        }
                    </PanelBody>
                </InspectorControls>

                {
                    attributes.documentView === 'link' ?
                        <RichText
                            tagName="a"
                            allowedFormats={ [ 'core/bold', 'core/image', 'core/italic', 'core/strikethrough', 'core/text-color', 'core/code', 'core/keyboard' , 'core/subscript', 'core/superscript' ] } 
                            onChange={ ( value ) => setAttributes({ fileName: value }) }
                            value={ attributes.fileName }
                        />
                        :
                        <p style={{display: 'flex'}}>
                            {onlyofficeIcon}
                            <p style={{marginLeft: '25px'}}> {attributes.fileName || ""}</p>
                        </p>
                }

                <BlockControls group="other">
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
