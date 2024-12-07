<script setup>
import { reactive, watch } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, numeric, helpers } from '@vuelidate/validators';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import Select from 'primevue/select';
import Message from 'primevue/message';
import ToggleSwitch from 'primevue/toggleswitch';
import { settingsService } from '@/services';
import { useSettingsDialogStore, useSettingsStore } from '@/stores';
import { notification } from '@/utils';
import currencies from '@/data/currencies';
import _ from 'lodash';

const settingsDialogStore = useSettingsDialogStore();
const settingsStore = useSettingsStore();

const state = reactive({
    currency: "",
    energyRate: "",
    isDarkMode: "",
});

const rules = {
    currency: {
        required: helpers.withMessage('Currency is required.', required),
    },
    energyRate: {
        required: helpers.withMessage('Energy rate is required.', required),
        numeric: helpers.withMessage('Units must be a numeric value.', numeric),
    }
};

const v$ = useVuelidate(rules, state);

async function handleSaveChanges()
{
    const isValid = await v$.value.$validate();

    if (!isValid) return;
    
    try {
        const payload = {
            settings: [
                {
                    key: 'currency',
                    value: state.currency,
                },
                {
                    key: 'energy_rate',
                    value: parseFloat(state.energyRate)
                },
                {
                    key: 'is_dark_mode',
                    value: state.isDarkMode
                }
            ]
        };

        settingsStore.init(
            await settingsService.editMultiple(payload)
        );

        notification.success('Settings updated successfully!');

        settingsDialogStore.close();

    } catch (error) {
        notification.error(error.message);
    }
}

watch(
    () => settingsDialogStore.isOpen,
    async (isOpen) => {
        if (!isOpen) return;

        try {
            const settings = await settingsService.fetchAll();

            settings.forEach(setting => {
                switch (setting.key) {
                    case 'currency':
                        state.currency = setting.value;
                        break;

                    case 'energy_rate':
                        state.energyRate = setting.value;
                        break;

                    case 'is_dark_mode':
                        state.isDarkMode = setting.value;
                        break;

                    default:
                        console.warn(`Unhandled setting key: ${setting.key}`);
                }
            });
        } catch (error) {
            notification.error('Failed to fetch settings: ' + error.message);
        }
    }
);

</script>
<template>
    <Dialog v-model:visible="settingsDialogStore.isOpen" modal header="Settings" :style="{ width: '25rem' }">
        <template #header>
            Settings
        </template>
        <div class="flex flex-col gap-2 mb-4">
            <label for="currency" class="font-semibold">Currency</label>
            <Select v-model="state.currency" :options="currencies" optionLabel="name" :invalid="v$.currency.$error" placeholder="Select a currency" class="flex-auto">
                <template #option="slotProps">
                    <template class="flex items-center">
                        <div>{{ slotProps.option.name }}</div>
                    </template>
                </template>
            </Select>
            <Message v-if="v$.currency.$error" severity="error">
                <span class="text-sm">{{ v$.currency.$errors[0]?.$message }}</span>
            </Message>
        </div>
        <div class="flex flex-col gap-2 mb-4">
            <label for="energy_rate" class="font-semibold">Energy Rate</label>
            <InputNumber 
                v-model="state.energyRate" 
                inputId="energy_rate"
                :minFractionDigits="1" :maxFractionDigits="2"
                :invalid="v$.energyRate.$error" 
                class="flex-auto" 
                aria-autocomplete="off"
                fluid
            ></InputNumber>
            <Message v-if="v$.energyRate.$error" severity="error">
                <span class="text-sm">{{ v$.energyRate.$errors[0]?.$message }}</span>
            </Message>
        </div>
        <div class="flex gap-2">
            <ToggleSwitch v-model="state.isDarkMode" id="isDarkMode"></ToggleSwitch>
            <label for="is_dark_mode">Dark mode</label>
        </div>
        <template #footer>
            <Button label="Cancel" text severity="secondary" @click="settingsDialogStore.close"></Button>
            <Button label="Apply" outlined severity="secondary" @click="handleSaveChanges"></Button>
        </template>
    </Dialog>
</template>