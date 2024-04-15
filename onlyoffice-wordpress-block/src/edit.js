/*
 * (c) Copyright Ascensio System SIA 2024
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
import { useState, useEffect } from 'react';
import { getLogoByDocumentType } from "./logos";

const Edit = ({attributes, setAttributes}) => {
    const [documentType, setDocumentType] = useState(null);

    const richTextAllowedFormats = [ 'core/bold', 'core/image', 'core/italic', 'core/strikethrough', 'core/text-color', 'core/code', 'core/keyboard' , 'core/subscript', 'core/superscript' ];
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

    useEffect(() => {
        if (attributes.id) {
            ONLYOFFICE.formatsUtils.getFileName(attributes.id).then((fileName) => {
                const documentType = ONLYOFFICE.formatsUtils.getDocumentType(fileName);
                setDocumentType(documentType);
            });
        }
    }, [attributes.id]);

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
        var allTypes = ONLYOFFICE.mimeTypes;

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

    for (let ext of ONLYOFFICE.formatsUtils.getViewableExtensions()) {
        let mimeType = getMimeType(ext);

        if (mimeType) {
            onlyofficeAllowedMimes.push(mimeType);
        }
    }

    const blockProps = attributes.documentView === 'link' || ! attributes.id ?  useBlockProps( { style: null } ) : useBlockProps( { style: blockStyle } );
    return (
        <div {...blockProps}>
        {attributes.id ?
            <>
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
                                <div>
                                    <ToggleControl
                                        checked={attributes.inNewTab}
                                        label={__('Open in new tab')}
                                        onChange={(value) => setAttributes({ inNewTab: value })} 
                                        />
                                    <ToggleControl
                                        checked={attributes.showOpenButton}
                                        label={__('Show Open in ONLYOFFICE button', 'onlyoffice-plugin')}
                                        onChange={(value) => setAttributes({ showOpenButton: value })} 
                                        />
                                </div>
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
                        <div>
                            <RichText
                                tagName="a"
                                allowedFormats={ richTextAllowedFormats } 
                                onChange={ ( value ) => setAttributes({ fileName: value }) }
                                value={ attributes.fileName }
                            />
                            {
                                attributes.showOpenButton ?
                                    <div class="wp-block-onlyoffice-wordpress-onlyoffice__button-richtext-wrapper">
                                        <RichText
                                            tagName="div"
                                            className = {'wp-element-button'}
                                            value= { attributes.openButtonText || __('Open in ONLYOFFICE', 'onlyoffice-plugin') }
                                            allowedFormats={ richTextAllowedFormats }
                                            onChange={ ( openButtonText ) => setAttributes( { openButtonText } ) }
                                            placeholder={ __('Add text...') }
                                        />
                                    </div>
                                    :
                                    ''
                            }
                        </div>

                        :
                        <div className={ `wp-block-onlyoffice-wordpress-onlyoffice__embedded ${documentType}`}>
                            <div>
                                {getLogoByDocumentType(documentType)}
                                <p> {documentType ? attributes.fileName || "" : __('File not found!', 'onlyoffice-plugin')}</p>
                            </div>
                        </div>
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
            </>
            :
            <MediaPlaceholder
                labels={{title: 'ONLYOFFICE Docs'}}
                allowedTypes={onlyofficeAllowedMimes}
                accept={onlyofficeAllowedMimes.join()}
                onSelect={(el) => {
                    if (el && el.hasOwnProperty('id')) {
                        setAttributes({ id: el.id, fileName: el.filename || el.guid.raw.substring(el.guid.raw.lastIndexOf('/') + 1) });
                    }
                }}
            />
        }
        </div>
    )
};

export default Edit;
