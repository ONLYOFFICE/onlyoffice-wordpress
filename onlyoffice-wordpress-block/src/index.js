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

import { registerBlockType } from '@wordpress/blocks';

import json from '../block.json';
import edit from './edit';
import save from './save';

export const blockStyle = {
    padding: '20px',
};

export const onlyofficeIcon = (
    <svg
        width="66"
        height="60"
        viewBox="0 0 66 60"
        fill="black"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            opacity="0.5"
            fillRule="evenodd"
            clipRule="evenodd"
            d="M28.9406 59.2644L2.20792 46.9066C-0.0693069 45.8277 -0.0693069 44.1604 2.20792 43.1796L11.5148 38.8642L28.8416 46.9066C31.1188 47.9854 34.7822 47.9854 36.9604 46.9066L54.2871 38.8642L63.5941 43.1796C65.8713 44.2585 65.8713 45.9258 63.5941 46.9066L36.8614 59.2644C34.7822 60.2452 31.1188 60.2452 28.9406 59.2644Z"
            fill="black"
        />
        <path
            opacity="0.75"
            fillRule="evenodd"
            clipRule="evenodd"
            d="M28.9406 44.0606L2.20792 31.7028C-0.069307 30.6239 -0.069307 28.9566 2.20792 27.9758L11.3168 23.7584L28.9406 31.8989C31.2178 32.9778 34.8812 32.9778 37.0594 31.8989L54.6832 23.7584L63.7921 27.9758C66.0693 29.0547 66.0693 30.722 63.7921 31.7028L37.0594 44.0606C34.7822 45.1395 31.1188 45.1395 28.9406 44.0606Z"
            fill="black"
        />
        <path
            fillRule="evenodd"
            clipRule="evenodd"
            d="M28.9406 29.2518L2.20792 16.8939C-0.069307 15.8151 -0.069307 14.1478 2.20792 13.167L28.9406 0.809144C31.2178 -0.269715 34.8812 -0.269715 37.0594 0.809144L63.7921 13.167C66.0693 14.2458 66.0693 15.9132 63.7921 16.8939L37.0594 29.2518C34.7822 30.2325 31.1188 30.2325 28.9406 29.2518Z"
            fill="black"
        />
    </svg>
);

const { name } = json;

registerBlockType( name, {
    icon: onlyofficeIcon,
    edit,
    save,
} );
