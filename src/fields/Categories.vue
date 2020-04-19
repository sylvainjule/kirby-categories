<template>
    <k-field v-bind="$props" class="k-categories-field">
        <template slot="options">
            <k-button v-if="canAdd" :id="_uid" icon="add" @click="addCategory">
                {{ $t('add') }}
            </k-button>
        </template>

        <k-empty v-if="isEmpty" icon="tag" @click="addCategory">
            There is no category yet
        </k-empty>
        <div v-else class="k-categories-table">
            <div class="k-categories-header">
                <div v-for="language in sortedLanguages" :key="language.code" :class="[{disabled: !isCurrent(language)}, 'language']" @click="changeLanguage(language)">
                    {{ language.name }}
                </div>
                <div class="delete-placeholder"></div>
            </div>
            <k-draggable :list="value[0]" :handle="true" element="ul" class="k-categories-list" @end="onInput">
                <li v-for="(category, catIndex) in value[0]" class="k-categories-item">
                    <k-sort-handle />
                    <div v-for="(language, index) in sortedLanguages" :key="language.code" :class="['language', {'has-input': isCurrent(language)}]">
                        <k-input v-if="isCurrent(language)" type="text" :id="category.id" :ref="category.id" theme="field" :value="category.text" @input="onTextInput(catIndex, $event)"></k-input>
                        <div v-else class="disabled">{{ category.translations[language.code] }}</div>
                    </div>
                    <k-button icon="trash" @click="removeCategory(category.id)"/>
                </li>
            </k-draggable>
        </div>
    </k-field>
</template>

<script>
export default {
    props: {
        default: [String, Boolean],
        defaultFirst: Boolean,
        limit: Number,
        prefix: String,

        // general options
        label: String,
        disabled: Boolean,
        help: String,
        parent: String,
        value: [Array],
        name: [String, Number],
        required: Boolean,
        type: String
    },
    computed: {
        languages() {
            return this.$store.state.languages.all
        },
        sortedLanguages() {
            if(!this.defaultFirst) return this.languages

            let dflt      = this.languages.find(l => l.default == true)
            let languages = this.languages.filter(l => l.default !== true)
                languages.unshift(dflt)

            return languages
        },
        currentLanguage() {
            return this.$store.state.languages.current
        },
        currentCode() {
            return this.currentLanguage.code
        },
        exists() {
            return this.value && this.value.length
        },
        isEmpty() {
            return !this.exists || !this.value[0].length
        },
        canAdd() {
            return !this.limit || !this.value || !this.value.length || this.value[0].length < this.limit
        },
        newIndex() {
            return !this.value || !this.value.length ? 1 : this.value[1] + 1
        },
    },
    methods: {
        isCurrent(language) {
            return language.code == this.currentCode
        },
        addCategory() {
            var newId = this.prefix + this.newIndex
            let newCategory = [{
                id: newId,
                panelIndex: this.newIndex,
                text: '',
                translations: {}
            }]
            this.languages.forEach(l => { newCategory[0].translations[l.code] = '' })

            if(this.exists) {
                this.value[0] = newCategory.concat(this.value[0])
                this.value[1] = this.newIndex
            } else {
                this.value = [newCategory, 1]
            }

            this.onInput()
            this.$nextTick(() => {
                this.$refs[newId][0].focus()
            })
        },
        removeCategory(id) {
            this.value[0] = this.value[0].filter(category => category.id !== id)
            this.onInput()
        },
        onTextInput(index = false, value) {
            this.value[0][index].text = value
            this.value[0][index].translations[this.currentCode] = value
            this.onInput()
        },
        onInput() {
            this.$emit('input', this.value)
        },
        changeLanguage(language) {
            this.$store.dispatch('languages/current', language);
            this.$emit('change', language);
        }
    }
};
</script>

<style lang="scss">
    @import '../assets/css/styles.scss'
</style>
